<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Compartido\Clinica\SignosVitalesPanel;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SignosVitalesPanelClasificacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_panel_cuenta_y_filtra_sin_datos_sin_confundirlos_con_normales(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['nombres' => 'Medica', 'ap_paterno' => 'Prueba', 'estado' => 'ACTIVO']);
        $usuario->assignRole('MEDICO GENERAL/GERIATRA');
        $this->actingAs($usuario);

        $sinRegistro = Residente::factory()->create(['nombres' => 'SinRegistro', 'cod_est_adul' => 'EST_001']);
        $sinMediciones = Residente::factory()->create(['nombres' => 'SinMediciones', 'cod_est_adul' => 'EST_001']);
        $critico = Residente::factory()->create(['nombres' => 'ConPulsoCritico', 'cod_est_adul' => 'EST_001']);

        foreach ([$sinMediciones, $critico] as $residente) {
            SignoVital::create([
                'cod_residente' => $residente->cod_residente,
                'cod_personal' => $usuario->personal->cod_personal,
                'fecha_hora' => today()->setTime(10, 0),
                'frecuencia_cardiaca' => $residente->is($critico) ? 135 : null,
                'estado' => 'VIGENTE',
            ]);
        }

        $panel = Livewire::test(SignosVitalesPanel::class);
        $this->assertSame(2, $panel->viewData('kpiSinDatos'));
        $this->assertSame(1, $panel->viewData('kpiCriticos'));
        $this->assertSame(0, $panel->viewData('kpiNormales'));

        $panel->set('filtroAlerta', 'sin_dato')
            ->assertSee($sinRegistro->nombres)
            ->assertSee($sinMediciones->nombres)
            ->assertDontSee($critico->nombres);
    }
}
