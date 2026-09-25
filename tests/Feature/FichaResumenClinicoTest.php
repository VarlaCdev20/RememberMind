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

class FichaResumenClinicoTest extends TestCase
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

    public function test_resumen_muestra_estado_vacio_sin_inventar_signos_vitales(): void
    {
        $residente = AdultoMayor::factory()->create();

        Livewire::test(FichaPaciente::class, ['adulto' => $residente->cod_residente])
            ->call('cambiarTab', 'resumen')
            ->assertSee('Sin datos clínicos para graficar')
            ->assertSee('No existen controles recientes')
            ->assertDontSee('120/80')
            ->assertDontSee('36.5°C')
            ->assertDontSee('68.5 kg');
    }

    public function test_resumen_muestra_exclusivamente_el_ultimo_control_persistido(): void
    {
        $residente = AdultoMayor::factory()->create();

        SignoVital::query()->create([
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $this->usuario->personal->cod_personal,
            'fecha_hora' => now(),
            'presion_sistolica' => 138,
            'presion_diastolica' => 84,
            'frecuencia_cardiaca' => 77,
            'frecuencia_respiratoria' => 18,
            'temperatura' => 37.1,
            'saturacion_oxigeno' => 95,
            'estado' => 'VIGENTE',
        ]);

        Livewire::test(FichaPaciente::class, ['adulto' => $residente->cod_residente])
            ->call('cambiarTab', 'resumen')
            ->assertSee('138/84')
            ->assertSee('77')
            ->assertSee('95%')
            ->assertSee('37.1 °C')
            ->assertDontSee('120/80');
    }
}
