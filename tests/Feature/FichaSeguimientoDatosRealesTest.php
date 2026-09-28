<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Compartido\Clinica\FichaPaciente;
use App\Models\AdultoMayor;
use App\Models\SignoVital;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FichaSeguimientoDatosRealesTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->usuario = User::factory()->create([
            'estado' => 'ACTIVO',
            'nombres' => 'Elena',
            'ap_paterno' => 'Rojas',
        ]);
        $this->usuario->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($this->usuario);
    }

    public function test_seguimiento_vacio_no_muestra_metricas_ni_conclusiones_simuladas(): void
    {
        $residente = AdultoMayor::factory()->create();

        Livewire::test(FichaPaciente::class, ['adulto' => $residente->cod_residente])
            ->call('cambiarTab', 'seguimiento')
            ->assertSee('No existen controles de signos vitales')
            ->assertSee('No existe una valoración funcional registrada')
            ->assertSee('Sin registros para los filtros seleccionados')
            ->assertDontSee('Hidratación adecuada')
            ->assertDontSee('Control clínico periódico')
            ->assertDontSee('65.0 kg');
    }

    public function test_seguimiento_muestra_el_control_real_sin_clasificarlo_como_normal(): void
    {
        $residente = AdultoMayor::factory()->create();

        SignoVital::query()->create([
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $this->usuario->personal->cod_personal,
            'fecha_hora' => now(),
            'presion_sistolica' => 142,
            'presion_diastolica' => 91,
            'frecuencia_cardiaca' => 79,
            'temperatura' => 37.2,
            'saturacion_oxigeno' => 94,
            'estado' => 'VIGENTE',
        ]);

        Livewire::test(FichaPaciente::class, ['adulto' => $residente->cod_residente])
            ->call('cambiarTab', 'seguimiento')
            ->assertSee('142/91 mmHg')
            ->assertSee('79 lpm')
            ->assertSee('94%')
            ->assertSee('37.2 °C')
            ->assertSee('REGISTRADO')
            ->assertDontSee('NORMAL');
    }
}
