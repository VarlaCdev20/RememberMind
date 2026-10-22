<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Livewire\Mechanisms\HandleRequests\HandleRequests;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RechazoLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function snapshotDashboard(): string
    {
        $html = $this->get(route('admin.enfermeria.dashboard'))->assertOk()->getContent();
        preg_match_all('/wire:snapshot="([^"]+)"/', $html, $coincidencias);
        foreach ($coincidencias[1] as $codificado) {
            $snapshot = html_entity_decode($codificado, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $datos = json_decode($snapshot, true, flags: JSON_THROW_ON_ERROR);
            if (str_contains($datos['memo']['name'], 'dashboard-turno')) {
                return $snapshot;
            }
        }
        $this->fail('Falta el snapshot del dashboard en la respuesta HTTP.');
    }

    private function refrescarPorHttp(string $snapshot): TestResponse
    {
        return $this->withHeader('X-Livewire', '1')->postJson(app(HandleRequests::class)->getUpdateUri(), [
            'components' => [['snapshot' => $snapshot, 'updates' => [],
                'calls' => [['path' => '', 'method' => 'refrescarTurno', 'params' => []]]]],
        ]);
    }

    public function test_los_refrescos_http_reales_de_enfermeria_no_rechazan_una_cuenta_activa(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ENFERMEROS');
        $this->actingAs($usuario);
        $snapshot = $this->snapshotDashboard();
        foreach (range(1, 3) as $refresco) {
            $respuesta = $this->refrescarPorHttp($snapshot)->assertOk()->assertJsonCount(1, 'components');
            $snapshot = $respuesta->json('components.0.snapshot');
            $this->assertAuthenticatedAs($usuario);
        }
        $this->assertDatabaseCount('signos_vitales', 0);
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_cuenta_desactivada_no_sigue_leyendo_por_una_pantalla_ya_abierta(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ENFERMEROS');
        $this->actingAs($usuario);
        $snapshot = $this->snapshotDashboard();
        $usuario->update(['estado' => 'INACTIVO']);
        $this->refrescarPorHttp($snapshot)->assertForbidden();
        $this->assertDatabaseCount('signos_vitales', 0);
    }

    public function test_permiso_retirado_se_revalida_en_la_actualizacion_http(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ENFERMEROS');
        $this->actingAs($usuario);
        $snapshot = $this->snapshotDashboard();
        Role::findByName('ENFERMEROS', 'web')->revokePermissionTo('enfermeria.ver_dashboard');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $usuario->unsetRelation('roles')->unsetRelation('permissions');
        $this->refrescarPorHttp($snapshot)->assertForbidden();
        $this->assertDatabaseCount('administraciones_medicacion', 0);
    }

    public function test_preview_no_envia_actualizaciones_periodicas_que_el_middleware_bloquea(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($usuario)->withSession([RolePreviewService::SESSION_KEY => 'ENFERMEROS']);
        foreach (['admin.enfermeria.dashboard', 'admin.enfermeria.alertas'] as $ruta) {
            $this->get(route($ruta))->assertOk()->assertDontSee('wire:poll', false);
        }
    }

    public function test_la_cuenta_operativa_conserva_actualizacion_de_alertas_y_turno(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ENFERMEROS');
        $this->actingAs($usuario)->get(route('admin.enfermeria.dashboard'))->assertOk()
            ->assertSee('wire:poll.60s="refrescarTurno"', false);
        $this->get(route('admin.enfermeria.alertas'))->assertOk()->assertSee('wire:poll.visible.60s', false);
    }

    public function test_el_rechazo_asincrono_sigue_siendo_403_y_su_log_no_contiene_datos_del_formulario(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('GERENTE');
        Route::middleware(['web', 'auth'])->post('/prueba-rechazo-livewire', fn () => abort(403))
            ->name('prueba.rechazo-livewire');
        Log::shouldReceive('notice')->once()->with('Solicitud Livewire rechazada', [
            'ruta' => 'prueba.rechazo-livewire', 'categoria' => 'RESTRICCION_CONTEXTUAL', 'cuenta_activa' => true,
        ]);
        $this->actingAs($usuario)->withHeader('X-Livewire', '1')->post('/prueba-rechazo-livewire', [
            'observacion' => 'Contenido clínico sintético que no debe registrarse en logs',
        ])->assertForbidden();
        $this->assertDatabaseCount('signos_vitales', 0);
    }

    public function test_el_middleware_sigue_bloqueando_post_en_preview_y_registra_la_causa(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('SUPERADMINISTRADOR');
        Route::middleware(['web', 'auth'])->post('/prueba-preview-livewire', fn () => response('No debe ejecutarse'))
            ->name('prueba.preview-livewire');
        Log::shouldReceive('notice')->once()->with('Solicitud Livewire rechazada', [
            'ruta' => 'prueba.preview-livewire', 'categoria' => 'PREVISUALIZACION', 'cuenta_activa' => true,
        ]);
        $this->actingAs($usuario)->withSession([RolePreviewService::SESSION_KEY => 'ENFERMEROS'])
            ->withHeader('X-Livewire', '1')->post('/prueba-preview-livewire')->assertForbidden()
            ->assertDontSee('No debe ejecutarse');
        $this->assertSame(['SUPERADMINISTRADOR'], $usuario->fresh()->getRoleNames()->all());
    }
}
