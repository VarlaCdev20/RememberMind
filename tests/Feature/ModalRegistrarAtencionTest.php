<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Compartido\Clinica\FichaPaciente;
use App\Models\AdultoMayor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ModalRegistrarAtencionTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;
    private AdultoMayor $adulto;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ RolesAndPermissionsSeeder::class]);

        $this->enfermero = User::factory()->create([
            'estado' => 'ACTIVO',
            'nombres' => 'Laura',
            'ap_paterno' => 'González',
        ]);
        $this->enfermero->assignRole('SUPERADMINISTRADOR');

        $this->adulto = AdultoMayor::factory()->create([
            'cod_est_adul' => 'EST_001',
            'nombres' => 'Florencia Beatriz',
            'ap_paterno' => 'Quispe',
            'ap_materno' => 'Gutiérrez',
        ]);

        $this->actingAs($this->enfermero);
    }

    public function test_modal_central_registrar_atencion_contiene_estructura_y_8_opciones(): void
    {
        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_residente])
            // Botón principal
            ->assertSee('+ REGISTRAR ATENCIÓN')
            ->assertDontSee('openAtencion') // Dropdown antiguo eliminado
            // Header del modal
            ->assertSee('Registrar atención clínica')
            ->assertSee('Selecciona el tipo de atención que deseas registrar.')
            // Contexto del residente
            ->assertSee('Florencia Beatriz Quispe Gutiérrez')
            ->assertSee($this->adulto->cod_residente)
            ->assertSee('Vigilancia')
            // Opción 1: Signos vitales
            ->assertSee('Signos vitales')
            ->assertSee('PA, FC, FR, SpO₂, Temperatura, Dolor, etc.')
            // Opción 2: Cuidado de enfermería
            ->assertSee('Cuidado de enfermería')
            ->assertSee('Higiene, alimentación, hidratación, movilidad, eliminación, piel, etc.')
            // Opción 3: Medicación
            ->assertSee('Medicación')
            ->assertSee('Dosis programadas, PRN, registro de administración.')
            // Opción 4: Evolución de enfermería
            ->assertSee('Evolución de enfermería')
            ->assertSee('Estado general, cambios observados, intervención, seguimiento.')
            // Opción 5: Seguimiento de guardia
            ->assertSee('Seguimiento de guardia')
            ->assertSee('Observación, reevaluación, continuidad de cuidados.')
            // Opción 6: Incidente / Caída
            ->assertSee('Incidente / Caída')
            ->assertSee('Caídas, lesiones, eventos adversos, acciones realizadas.')
            // Opción 7: Dolor / Síntoma
            ->assertSee('Dolor / Síntoma')
            ->assertSee('Dolor EVA, localización, intensidad, intervención.')
            // Opción 8: Procedimiento / Dispositivo
            ->assertSee('Procedimiento / Dispositivo')
            ->assertSee('Curaciones, sondas, catéteres, oxígeno, etc.')
            // Footer
            ->assertSee('Toda la información se registra en el historial clínico del residente, con trazabilidad y fecha/hora automática.')
            ->assertSee('Cancelar');
    }
}
