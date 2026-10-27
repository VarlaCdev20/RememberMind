<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use App\Modulos\Identidad\Aplicacion\Consultas\HistorialCorreoDocumental;
use App\Modulos\Identidad\Aplicacion\Servicios\EnviosCorreoDocumental;
use App\Modulos\Identidad\Infraestructura\Jobs\EnviarFichaUsuarioJob;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\CorreosPanel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class HistorialCorreoDocumentalTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private User $destino;

    protected function setUp(): void
    {
        parent::setUp();
        config(['queue.default' => 'database']);
        Queue::fake();
        foreach (['usuarios.ver', 'usuarios.editar', 'usuarios.crear'] as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }
        $this->actor = User::factory()->create(['estado' => 'ACTIVO', 'nombres' => 'Autora Sintética']);
        $this->actor->givePermissionTo(['usuarios.ver', 'usuarios.editar']);
        $this->destino = User::factory()->create(['estado' => 'ACTIVO', 'nombres' => 'Destino Sintético', 'ap_paterno' => 'Prueba']);
        $this->actingAs($this->actor);
    }

    private function registrar(string $estado = 'PENDIENTE', string $tipo = 'ficha', ?string $solicitud = null, ?User $actor = null, ?User $usuario = null): string
    {
        $solicitud ??= (string) Str::uuid();
        app(EnviosCorreoDocumental::class)->registrar($actor ?? $this->actor, $usuario ?? $this->destino, $solicitud, $tipo, $estado, $estado === 'PENDIENTE' ? 0 : 1);

        return $solicitud;
    }

    public function test_http_historial_y_accesos_desde_usuarios_renderizan_sin_enviar(): void
    {
        $this->registrar('ACEPTADO');
        $url = route('admin.usuarios.correos.index');
        $this->get($url)->assertOk()->assertSee('Historial de correos')->assertSee('Destino Sintético')->assertSee('Aceptado por el servidor');
        $this->get($url.'?usuario='.$this->destino->getKey())->assertOk()->assertSee('Envíos para');
        $this->get(route('admin.usuarios.index'))->assertOk()->assertSee($url, false);
        Queue::assertNothingPushed();
    }

    public function test_listado_consolida_ultimo_estado_antes_de_filtrar_y_detalle_conserva_traza(): void
    {
        $solicitud = $this->registrar();
        $this->registrar('REINTENTO', solicitud: $solicitud);
        $this->registrar('ACEPTADO', solicitud: $solicitud);
        $consulta = app(HistorialCorreoDocumental::class);
        $this->assertSame(1, $consulta->ultimos($this->actor, [])->count());
        $this->assertSame(0, $consulta->ultimos($this->actor, ['estado' => 'REINTENTO'])->count());
        Livewire::test(CorreosPanel::class)->call('abrirDetalle', $solicitud)
            ->assertSet('mostrarDetalle', true)->assertSee('Recorrido del envío')->assertSee('Reintento programado')->assertSee('Aceptado por el servidor')
            ->call('cerrarDetalle')->assertSet('seleccion', null)->assertSet('mostrarDetalle', false);
        $this->assertCount(3, $consulta->detalle($this->actor, $solicitud)['eventos']);
        Queue::assertNothingPushed();
    }

    public function test_otro_solicitante_no_puede_ver_ni_reenviar_la_solicitud_ajena(): void
    {
        $otro = User::factory()->create(['estado' => 'ACTIVO', 'nombres' => 'Otra Persona']);
        $id = $this->registrar('FALLIDO', actor: $otro);
        $this->assertSame(0, app(HistorialCorreoDocumental::class)->ultimos($this->actor, [])->count());
        Livewire::test(CorreosPanel::class)->call('abrirDetalle', $id)->assertStatus(404);
        Queue::assertNothingPushed();
    }

    public function test_cuenta_inactiva_sin_permiso_e_invitado_no_acceden(): void
    {
        $url = route('admin.usuarios.correos.index');
        $this->actor->syncPermissions([]);
        $this->get($url)->assertForbidden();
        Livewire::test(CorreosPanel::class)->assertForbidden();
        $this->actor->givePermissionTo('usuarios.ver');
        $this->actor->update(['estado' => 'INACTIVO']);
        Livewire::test(CorreosPanel::class)->assertForbidden();
    }

    public function test_invitado_no_accede_al_historial(): void
    {
        $this->app['auth']->forgetGuards();
        $this->flushSession();
        $this->get(route('admin.usuarios.correos.index'))->assertRedirect(route('login'));
    }

    public function test_revocar_permiso_de_paquete_lo_oculta_sin_ocultar_fichas(): void
    {
        $id = $this->registrar('FALLIDO', 'paquete');
        $this->registrar();
        $panel = Livewire::test(CorreosPanel::class);
        $this->actor->revokePermissionTo('usuarios.editar');
        $panel->call('abrirDetalle', $id)->assertStatus(404);
        $this->assertSame(1, app(HistorialCorreoDocumental::class)->ultimos($this->actor, [])->count());
    }

    public function test_reenvio_fallido_es_nueva_solicitud_sin_borrar_historia_y_doble_accion_no_duplica(): void
    {
        $id = $this->registrar('FALLIDO');
        $panel = Livewire::test(CorreosPanel::class)->call('abrirDetalle', $id)->call('reenviar')
            ->assertSee('Nueva solicitud registrada')->assertSee('Pendiente');
        $nuevo = $panel->get('seleccion');
        $this->assertNotSame($id, $nuevo);
        $this->assertSame('FALLIDO', app(HistorialCorreoDocumental::class)->detalle($this->actor, $id)['actual']['estado']);
        $panel->call('abrirDetalle', $id)->call('reenviar')->assertSee('Ya existe una solicitud');
        Queue::assertPushed(EnviarFichaUsuarioJob::class, 1);
    }

    public function test_aceptado_indeterminado_y_pendiente_no_admiten_reenvio_desde_historial(): void
    {
        foreach (['ACEPTADO', 'INDETERMINADO', 'PENDIENTE'] as $estado) {
            $id = $this->registrar($estado);
            Livewire::test(CorreosPanel::class)->call('abrirDetalle', $id)->assertDontSee('Volver a solicitar envío')->call('reenviar')->assertStatus(422);
        }
        Queue::assertNothingPushed();
    }

    public function test_filtros_nombres_v2_fechas_tipo_estado_y_limpieza(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 25)->setTime(23, 59));
        $id = $this->registrar('ACEPTADO', 'paquete');
        $otro = User::factory()->create(['estado' => 'ACTIVO', 'nombres' => 'Nombre Distinto']);
        $this->registrar('FALLIDO', usuario: $otro);
        $consulta = app(HistorialCorreoDocumental::class);
        $this->assertSame([$id], $consulta->ultimos($this->actor, ['busqueda' => 'prueba', 'desde' => '2026-10-25', 'hasta' => '2026-10-25', 'tipo' => 'paquete', 'estado' => 'ACEPTADO'])->get()->pluck('properties.solicitud')->all());
        $this->assertSame(0, $consulta->ultimos($this->actor, ['hasta' => '2026-10-24'])->count());
        Livewire::test(CorreosPanel::class)->set('busqueda', 'No existe')->call('aplicarFiltros')->assertSee('No hay solicitudes para mostrar')
            ->call('limpiarFiltros')->assertSet('busqueda', '')->assertSee('Paquete documental');
    }

    public function test_filtros_invalidos_y_fechas_invertidas_se_rechazan(): void
    {
        Livewire::test(CorreosPanel::class)->set('estado', 'INVENTADO')->call('aplicarFiltros')->assertHasErrors('estado');
        Livewire::test(CorreosPanel::class)->set('desde', '2026-10-25')->set('hasta', '2026-10-01')
            ->call('aplicarFiltros')->assertHasErrors('hasta');
        Livewire::test(CorreosPanel::class)->set('desde', 'no-fecha')->call('aplicarFiltros')->assertHasErrors('desde');
    }

    public function test_paginacion_cuenta_solicitudes_sin_duplicar_eventos_y_no_muestra_codigos(): void
    {
        for ($i = 0; $i < 14; $i++) {
            $this->registrar();
        }
        $this->assertSame(12, app(HistorialCorreoDocumental::class)->ultimos($this->actor, [])->paginate(12)->count());
        Livewire::test(CorreosPanel::class)->assertSee('Solicitudes de envío')->call('gotoPage', 2)->assertSet('paginators.page', 2)
            ->assertDontSee('COD_USU_')->assertDontSee('smtp.gmail.com');
    }

    public function test_abrir_historial_no_modifica_auditoria_ni_genera_correos(): void
    {
        $id = $this->registrar();
        $antes = Activity::count();
        Livewire::test(CorreosPanel::class)->call('abrirDetalle', $id)->call('$refresh')->call('cerrarDetalle');
        $this->assertSame($antes, Activity::count());
        Queue::assertNothingPushed();
    }

    public function test_error_de_configuracion_es_visible_y_no_simula_envio(): void
    {
        $id = $this->registrar('FALLIDO');
        config(['queue.default' => 'sync']);
        Livewire::test(CorreosPanel::class)->call('abrirDetalle', $id)->call('reenviar')
            ->assertHasErrors('envio')->assertSee('No se pudo registrar el envío');
        Queue::assertNothingPushed();
    }

    public function test_previsualizacion_no_puede_reenviar(): void
    {
        $id = $this->registrar('FALLIDO');
        session([RolePreviewService::SESSION_KEY => 'ADMINISTRADOR']);
        Livewire::test(CorreosPanel::class)->call('abrirDetalle', $id)->call('reenviar')->assertForbidden();
        Queue::assertNothingPushed();
    }

    public function test_filtro_usuario_no_mezcla_destinatarios_y_rechaza_query_invalido(): void
    {
        $this->registrar();
        $otro = User::factory()->create(['estado' => 'ACTIVO', 'nombres' => 'Fuera del filtro']);
        $this->registrar(usuario: $otro);
        $url = route('admin.usuarios.correos.index', ['usuario' => $this->destino->getKey()]);
        $this->get($url)->assertOk()->assertSee('Destino Sintético')->assertDontSee('Fuera del filtro');
        $this->getJson(route('admin.usuarios.correos.index').'?usuario[]=invalido')->assertStatus(400);
    }

    public function test_permiso_solo_de_paquete_no_expone_ficha_y_revocacion_total_bloquea_actualizacion(): void
    {
        $this->registrar('ACEPTADO', 'ficha');
        $this->registrar('ACEPTADO', 'paquete');
        $this->actor->syncPermissions(['usuarios.crear']);
        Livewire::test(CorreosPanel::class)->assertSee('Paquete documental')->assertDontSee('Ficha institucional</strong>', false);
        $this->assertSame(1, app(HistorialCorreoDocumental::class)->ultimos($this->actor, [])->count());
        $panel = Livewire::test(CorreosPanel::class);
        $this->actor->syncPermissions([]);
        $panel->call('$refresh')->assertForbidden();
    }

    public function test_limite_de_solicitudes_livewire_no_encola_al_superarse(): void
    {
        $id = $this->registrar('FALLIDO');
        for ($i = 0; $i < 10; $i++) {
            RateLimiter::hit('correo:reenvio:'.$this->actor->getKey(), 60);
        }
        Livewire::test(CorreosPanel::class)->call('abrirDetalle', $id)->call('reenviar')
            ->assertHasErrors('envio')->assertSee('Espera un minuto');
        Queue::assertNothingPushed();
    }
}
