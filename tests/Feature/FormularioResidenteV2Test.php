<?php

namespace Tests\Feature;

use App\Backend\Modulos\Residentes\Servicios\AdultoMayorService;
use App\Frontend\Livewire\Compartido\Residentes\AdultoMayorFormModal;
use App\Models\Contacto;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\SeguroResidente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FormularioResidenteV2Test extends TestCase
{
    use RefreshDatabase;

    public function test_formulario_carga_relaciones_v2_sin_valores_ficticios(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->givePermissionTo(Permission::findOrCreate('residentes.gestionar', 'web'));
        $this->actingAs($usuario);

        $residente = Residente::factory()->create([
            'grupo_sanguineo' => 'AB',
            'factor_rh' => '-',
            'direccion' => 'Av. Siempre Viva 45',
        ]);
        $seguro = SeguroResidente::create([
            'cod_seguro' => 'SEG_FORM_01',
            'cod_residente' => $residente->cod_residente,
            'entidad' => 'CAJA NACIONAL CNS',
            'estado' => 'ACTIVO',
        ]);
        $contacto = Contacto::create([
            'cod_contacto' => 'CON_FORM_01',
            'nombres' => 'María Elena',
            'apellido_paterno' => 'Rojas',
            'celular' => '71234567',
            'estado' => 'ACTIVO',
        ]);
        ResidenteContacto::create([
            'cod_residente_contacto' => 'RC_FORM_01',
            'cod_residente' => $residente->cod_residente,
            'cod_contacto' => $contacto->cod_contacto,
            'parentesco' => 'HIJO/A',
            'responsable_principal' => true,
            'contacto_emergencia' => true,
            'autoriza_informacion' => true,
            'autoriza_salida' => false,
            'estado' => 'ACTIVO',
        ]);

        Livewire::test(AdultoMayorFormModal::class)
            ->call('abrir', $residente->cod_residente)
            ->assertSet('grupo_sanguineo', 'AB-')
            ->assertSet('seguro_salud', $seguro->entidad)
            ->assertSet('contacto_emergencia_nombre', 'María Elena Rojas')
            ->assertSet('contacto_emergencia_celular', '71234567')
            ->assertSet('contacto_emergencia_parentesco', 'HIJO/A')
            ->assertSet('departamento_residencia', null)
            ->assertSet('ciudad_municipio', null)
            ->assertSet('zona', null)
            ->assertSet('consentimiento_datos', false)
            ->assertDontSee('Familiar Registrado');
    }

    public function test_actualizacion_persiste_datos_en_tablas_normalizadas_v2(): void
    {
        $residente = Residente::factory()->create();

        app(AdultoMayorService::class)->actualizarAdultoMayor($residente->cod_residente, [
            'nombres' => 'Rosa María',
            'grupo_sanguineo' => 'A-',
            'seguro_salud' => 'SEGURO PRIVADO',
            'celular' => '70112233',
            'telefono_fijo' => '2244668',
            'calle' => 'AV. ARCE 123',
            'zona' => 'SOPHOCACHI',
            'ciudad_municipio' => 'LA PAZ',
            'contacto_emergencia_nombre' => 'Lucía Vargas',
            'contacto_emergencia_parentesco' => 'HIJO/A',
            'contacto_emergencia_celular' => '76543210',
            'contacto_emergencia_direccion' => 'CALLE 10',
            'responsable_principal' => true,
            'autorizado_informacion_medica' => true,
        ]);

        $this->assertDatabaseHas('residentes', [
            'cod_residente' => $residente->cod_residente,
            'nombres' => 'Rosa María',
            'grupo_sanguineo' => 'A',
            'factor_rh' => '-',
            'celular' => '70112233',
            'telefono' => '2244668',
            'direccion' => 'AV. ARCE 123, SOPHOCACHI, LA PAZ',
        ]);
        $this->assertDatabaseHas('seguros_residente', [
            'cod_residente' => $residente->cod_residente,
            'entidad' => 'SEGURO PRIVADO',
            'estado' => 'ACTIVO',
        ]);
        $this->assertDatabaseHas('contactos', [
            'nombres' => 'Lucía',
            'apellido_paterno' => 'Vargas',
            'celular' => '76543210',
        ]);
        $this->assertDatabaseHas('residentes_contactos', [
            'cod_residente' => $residente->cod_residente,
            'parentesco' => 'HIJO/A',
            'responsable_principal' => true,
            'contacto_emergencia' => true,
            'autoriza_informacion' => true,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_actualizacion_es_atomica_si_el_contacto_es_invalido(): void
    {
        $residente = Residente::factory()->create(['nombres' => 'Nombre Original']);

        try {
            app(AdultoMayorService::class)->actualizarAdultoMayor($residente->cod_residente, [
                'nombres' => 'Nombre Parcial',
                'seguro_salud' => 'SUS',
                'contacto_emergencia_nombre' => 'Incompleto',
                'contacto_emergencia_parentesco' => 'OTRO',
                'contacto_emergencia_celular' => '70001122',
                'contacto_emergencia_direccion' => null,
            ]);
            $this->fail('La actualización incompleta debía ser rechazada.');
        } catch (ValidationException) {
            $this->assertSame('Nombre Original', $residente->fresh()->nombres);
            $this->assertDatabaseMissing('seguros_residente', [
                'cod_residente' => $residente->cod_residente,
                'estado' => 'ACTIVO',
            ]);
        }
    }
}
