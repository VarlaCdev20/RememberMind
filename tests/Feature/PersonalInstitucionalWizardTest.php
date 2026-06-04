<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LocDepartamento;
use App\Models\LocMunicipio;
use App\Models\LocZona;
use App\Models\LocCalle;
use App\Livewire\Admin\PersonalInstitucional\Partials\PersonalInstitucionalForm;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\PersonalIngresanteConfirmacionMail;

class PersonalInstitucionalWizardTest extends TestCase
{
    // We use DatabaseTransactions to rollback after the test
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    public function test_wizard_end_to_end_flow_for_nursing_role(): void
    {
        Mail::fake();
        Storage::fake('public');

        // Retrieve existing admin user to authenticate
        $admin = User::where('correo', 'admincasaamandita@gmail.com')->first();
        if (!$admin) {
            $this->markTestSkipped('Admin user not found. Run migrations and seeders first.');
        }

        // Get location catalog entries for La Paz
        $depto = LocDepartamento::where('nombre', 'LA PAZ')->first();
        if (!$depto) {
            $this->markTestSkipped('LocCatalogosSeeder not executed.');
        }
        $mun = LocMunicipio::where('departamento_id', $depto->id)->where('nombre', 'LA PAZ')->first();
        $zona = LocZona::where('municipio_id', $mun->id)->where('nombre', 'SAN PEDRO')->first();
        $calle = LocCalle::where('zona_id', $zona->id)->first();

        // Create a unique test email and CI
        $testEmail = 'wizard.test.' . uniqid() . '@gmail.com';
        $testCI = rand(1000000, 9999999);

        // Instantiate and test the Livewire component
        $component = Livewire::actingAs($admin)
            ->test(PersonalInstitucionalForm::class)
            // Paso 1: Datos de Acceso
            ->set('pasoActual', 1)
            ->set('correo', '  Wizard.Test@gmail.com  ') // should normalize to lowercase
            ->set('estado', 'ACTIVO')
            ->set('fecha_registro', '2026-06-04')
            ->set('hora_registro', '08:30')
            ->call('avanzarPaso')
            ->assertSet('pasoActual', 2)
            ->assertSet('correo', 'wizard.test@gmail.com') // Normalized!

            // Paso 2: Identificación Personal
            ->set('nombres', 'Juan')
            ->set('ap_paterno', 'Perez')
            ->set('ap_materno', 'Lopez')
            ->set('numero_documento', $testCI)
            ->set('expedido', 'LP')
            ->set('genero', 'M')
            ->set('fecha_nacimiento', '1995-05-15')
            ->assertSet('edad', 31) // Calculated age (2026 - 1995)
            ->call('avanzarPaso')
            ->assertSet('pasoActual', 3)

            // Paso 3: Contacto
            ->set('telefono', '76543210')
            ->set('telefono_alternativo', '2224444')
            ->set('ciudad_id', $depto->id)
            ->set('municipio_id', $mun->id)
            ->set('zona_id', $zona->id)
            ->set('calle_id', $calle->id)
            ->set('nro_casa', '123')
            ->call('avanzarPaso')
            ->assertSet('pasoActual', 4)

            // Paso 4: Rol y Clasificación
            ->set('roles_seleccionados', ['ENFERMEROS'])
            ->call('avanzarPaso')
            ->assertSet('pasoActual', 5)

            // Paso 5: Datos de Clasificación (Autocalculados, just advance)
            ->call('avanzarPaso')
            ->assertSet('pasoActual', 6)

            // Paso 6: Datos Laborales (Salud / Enfermero)
            ->set('anios_exp', 5)
            ->set('matricula_prof', 'MAT-12345')
            ->set('institucion_formacion', 'UMSA')
            ->set('subtipo_enfermeria', 'GENERAL_ADMISION')
            ->call('avanzarPaso')
            ->assertSet('pasoActual', 7)

            // Paso 7: Documentación
            ->set('archivos_temporales.CI', UploadedFile::fake()->create('ci_anverso_reverso.pdf', 100))
            ->set('archivos_temporales.TITULO', UploadedFile::fake()->create('titulo_profesional.pdf', 200))
            ->set('archivos_temporales.CONTRATO', UploadedFile::fake()->create('contrato_firmado.pdf', 150))
            ->set('archivos_temporales.CONFIDENCIALIDAD', UploadedFile::fake()->create('confidencialidad_firmado.pdf', 150))
            ->call('avanzarPaso')
            ->assertSet('pasoActual', 8);

        // Step 8: Confirmation and Saving
        $component->call('preGuardar');
        
        // Assert that the modal/SweetAlert final confirmation was dispatched
        $component->assertDispatched('confirmarRegistroFinal');

        // Call the final guardar() method
        $component->call('guardar');

        // Assert database persistence
        $this->assertDatabaseHas('users', [
            'correo' => 'wizard.test@gmail.com',
            'nombres' => 'JUAN',
            'ap_paterno' => 'PEREZ',
            'ap_materno' => 'LOPEZ',
            'numero_documento' => (string)$testCI,
            'expedido' => 'LP',
            'genero' => 'M',
            'estado' => 'DOCUMENTACION_PENDIENTE',
        ]);

        $user = User::where('correo', 'wizard.test@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('ENFERMEROS'));

        // Assert nursing specific fields in personal_salud
        $this->assertDatabaseHas('personal_salud', [
            'cod_usu' => $user->cod_usu,
            'anios_exp' => 5,
            'matricula_prof' => 'MAT-12345',
            'institucion_formacion' => 'UMSA',
            'subtipo_enfermeria' => 'GENERAL_ADMISION',
            'tipo_personal_salud' => 'ENFERMERO',
        ]);

        // Assert uploaded files exist in DB
        $this->assertDatabaseHas('documentos_usuarios', [
            'cod_usu' => $user->cod_usu,
            'nombre_documento' => 'Cédula de Identidad (Anverso y Reverso)',
            'estado' => 'VALIDADO',
        ]);
        $this->assertDatabaseHas('documentos_usuarios', [
            'cod_usu' => $user->cod_usu,
            'nombre_documento' => 'Título o Certificado Profesional',
            'estado' => 'VALIDADO',
        ]);

        // Verify confirmation email was sent
        Mail::assertSent(PersonalIngresanteConfirmacionMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->correo);
        });
    }
}
