<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Atencion;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarPanelResidenteAlerta;
use App\Modulos\Alertas\Presentacion\Livewire\Funcionalidades\CampanaNotificaciones;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\AlertasPanel;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class PanelAlertaPreparadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_parcial_grafico_preparado_no_consulta_base_y_conserva_historial(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create(['nombres' => 'Ana', 'ap_paterno' => 'Prueba']);
        $actor->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($actor);
        $residente = Residente::factory()->create();
        $area = Area::create(['cod_area' => 'ARE_PANEL_TEST', 'nombre' => 'Área de prueba', 'estado' => 'ACTIVA']);
        Atencion::create(['cod_atencion' => 'ATE_PANEL_TEST', 'cod_residente' => $residente->getKey(),
            'cod_area' => $area->getKey(), 'cod_personal' => $actor->personal->getKey(),
            'tipo_atencion' => 'CONTROL', 'fecha_hora' => now(), 'estado' => 'REGISTRADA', 'observacion' => 'Registro de prueba.']);
        SignoVital::create(['cod_signo' => 'SIG_PANEL_TEST', 'cod_residente' => $residente->getKey(),
            'cod_personal' => $actor->personal->getKey(), 'fecha_hora' => now(), 'estado' => 'REGISTRADO', 'temperatura' => 36.5]);
        foreach ([AlertasPanel::class, CampanaNotificaciones::class] as $clase) {
            $panel = Livewire::test($clase)->call('verGraficos', $residente->getKey())->assertStatus(200);
            // Segunda petición: repone la preparación después de hidratación Livewire.
            $panel->set('drawerGrafico', true)->assertSet('drawerGrafico', true);
            $instancia = $panel->instance();
            view()->share('__livewire', $instancia);
            $preparado = app(ConsultarPanelResidenteAlerta::class)->consultar($actor, $residente->getKey(), 'graficos');
            $vista = view('livewire.alertas.modales.drawer-graficos', [
                'drawerGrafico' => true, 'adultoDrawer' => $preparado, 'signosDrawer' => $preparado->signosVitales,
                '__livewire' => $instancia,
            ]);
            DB::enableQueryLog();
            DB::flushQueryLog();
            try {
                $html = $vista->render();
                $this->assertStringContainsString($residente->getKey(), $html);
                $this->assertSame([], DB::getQueryLog());
            } finally {
                DB::disableQueryLog();
                view()->share('__livewire', null);
            }
        }
        $this->assertDatabaseCount('atenciones', 1);
        $this->assertDatabaseCount('signos_vitales', 1);
    }

    public function test_campana_rechaza_residente_fuera_de_asignacion_y_no_expone_panel(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create(['nombres' => 'Ana', 'ap_paterno' => 'Prueba']);
        $actor->assignRole('ENFERMEROS');
        $this->actingAs($actor);
        $residente = Residente::factory()->create();
        foreach (['verGraficos', 'verUbicacion'] as $metodo) {
            Livewire::test(CampanaNotificaciones::class)->call($metodo, $residente->getKey())->assertForbidden();
        }
        $this->assertDatabaseCount('signos_vitales', 0);
        $this->assertDatabaseCount('atenciones', 0);
    }

    public function test_campana_no_consulta_con_cuenta_inactiva_aunque_conserve_rol(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create(['estado' => 'INACTIVO']);
        $actor->assignRole('ADMINISTRADOR');
        $this->actingAs($actor);
        $residente = Residente::factory()->create();
        foreach (['verGraficos', 'verUbicacion'] as $metodo) {
            Livewire::test(CampanaNotificaciones::class)->call($metodo, $residente->getKey())->assertForbidden();
        }
    }
}
