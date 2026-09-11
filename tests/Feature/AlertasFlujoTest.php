<?php
namespace Tests\Feature;

use App\Livewire\Alertas\AlertasPanel;
use App\Models\{AdultoMayor, AlertaAdulto, User, SignosVitalesAdulto, SeguimientoDiario};
use App\Services\Alertas\DeteccionAlertasService;
use Database\Seeders\EstadoAdultoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AlertasFlujoTest extends TestCase
{
    use RefreshDatabase;

    private function preparar(array $permisos): array
    {
        $this->seed(EstadoAdultoSeeder::class);
        $user = User::factory()->create();
        foreach ($permisos as $p) $user->givePermissionTo(Permission::findOrCreate($p, 'web'));
        $this->actingAs($user);
        return [$user, AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001'])];
    }

    public function test_registrar_asignar_atender_agregar_accion_cerrar_y_consultar_historial(): void
    {
        [$user, $adulto] = $this->preparar(['alertas.ver', 'alertas.crear', 'alertas.atender', 'alertas.cerrar', 'alertas.gestionar']);
        $this->get(route('admin.enfermeria.alertas'))->assertOk()->assertSee('Detectar pendientes');
        $panel = Livewire::test(AlertasPanel::class)->call('abrirCrear')
            ->set('codAm', $adulto->cod_am)->set('tipoAlerta', 'Revisión requerida')
            ->set('motivo', 'Se solicita seguimiento del residente.')->call('guardarAlerta')->assertHasNoErrors();
        $alerta = AlertaAdulto::sole();
        $panel->call('verDetalle', $alerta->cod_alerta)->set('responsableId', $user->cod_usu)
            ->call('asignarResponsable')->assertHasNoErrors()
            ->call('atenderAlerta', $alerta->cod_alerta)->set('accionTomada', 'Se inicia revisión presencial.')
            ->call('guardarAtencion')->assertHasNoErrors();
        $this->assertSame('EN_ATENCION', $alerta->fresh()->estado);
        $panel->call('verDetalle', $alerta->cod_alerta)->set('accion', 'Se informa al equipo responsable.')
            ->call('guardarAccion')->assertHasNoErrors()
            ->call('cerrarAlerta', $alerta->cod_alerta)->set('observacionCierre', 'Seguimiento concluido por el equipo.')
            ->call('confirmarCierre')->assertHasNoErrors()->set('filtroEstado', 'CERRADA')
            ->call('verDetalle', $alerta->cod_alerta)->assertSee('Se informa al equipo responsable.');
        $this->assertSame('CERRADA', $alerta->fresh()->estado);
        $this->assertSame(4, $alerta->acciones()->count());
        $this->assertSame($user->cod_usu, $alerta->fresh()->cerrado_por);
        $panel->set('accion', 'Intento de cambiar el historial.')->call('guardarAccion')->assertStatus(409);
        $this->assertSame(4, $alerta->acciones()->count());
    }

    public function test_lectura_no_permite_mutar_y_los_filtros_no_mezclan_estados(): void
    {
        [, $adulto] = $this->preparar(['alertas.ver']);
        AlertaAdulto::create(['cod_am' => $adulto->cod_am, 'origen' => 'MANUAL', 'tipo_alerta' => 'CERRADA ESPECIAL', 'motivo' => 'Seguimiento concluido', 'estado' => 'CERRADA']);
        Livewire::test(AlertasPanel::class)->set('search', $adulto->nombres)->assertDontSee('CERRADA ESPECIAL')
            ->call('abrirCrear')->assertForbidden();
    }

    public function test_detecta_signos_y_seguimiento_sin_duplicar_ni_reabrir_alertas_cerradas(): void
    {
        [, $adulto] = $this->preparar(['alertas.ver', 'alertas.crear']);
        SignosVitalesAdulto::create(['cod_am' => $adulto->cod_am, 'fecha' => today(), 'hora' => '10:00:00', 'saturacion' => 85, 'estado' => 'VIGENTE']);
        $this->assertDatabaseHas('alertas_adulto', ['cod_am' => $adulto->cod_am, 'origen' => 'SIGNOS', 'nivel' => 'CRITICO']);
        $alerta = AlertaAdulto::sole();
        $alerta->update(['estado' => 'CERRADA']);
        $this->assertSame(0, app(DeteccionAlertasService::class)->detectar());
        $this->assertSame(1, AlertaAdulto::count());
        $this->assertSame('CERRADA', $alerta->fresh()->estado);
    }
}

