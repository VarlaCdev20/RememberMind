<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\EjecucionCuidado;
use App\Models\EventoAlerta;
use App\Models\IntervencionCuidado;
use App\Models\Jornada;
use App\Models\PlanCuidado;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Acciones\AlertasService;
use App\Modulos\Alertas\Aplicacion\Acciones\DeteccionAlertasService;
use App\Modulos\Alertas\Aplicacion\Consultas\BandejaAlertas;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarTrazaAlerta;
use App\Modulos\Alertas\Presentacion\Livewire\Funcionalidades\CampanaNotificaciones;
use App\Modulos\Alertas\Presentacion\Livewire\Paginas\AlertasPanel;
use App\Modulos\Reportes\Aplicacion\Consultas\RoleDashboardDataService;
use Carbon\Carbon;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AlertasRolesConsistenciaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-20 10:00:00'));
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function actor(string $rol): User
    {
        $actor = User::factory()->create(['nombres' => 'Profesional sintético', 'apellido_paterno' => 'Prueba']);
        $actor->assignRole($rol);
        $this->actingAs($actor);

        return $actor;
    }

    private function alerta(Residente $residente, string $estado = 'ABIERTA', string $modulo = 'MANUAL'): Alerta
    {
        return Alerta::create(['cod_residente' => $residente->getKey(), 'estado' => $estado,
            'tipo' => 'Seguimiento sintético', 'prioridad' => 'ALTO', 'modulo' => $modulo,
            'descripcion' => 'Hecho sintético para comprobar continuidad.']);
    }

    private function asignarTurno(User $actor, Residente $residente): void
    {
        $area = Area::create(['nombre' => 'Enfermería sintética', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Turno sintético', 'hora_inicio' => '07:00:00', 'hora_cierre' => '15:00:00', 'estado' => 'ACTIVO', 'orden' => 1]);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_personal' => $actor->personal->getKey(), 'cod_area' => $area->getKey(),
            'cod_jornada' => $jornada->getKey(), 'tipo_asignacion' => 'RESPONSABLE', 'fecha_asignacion' => now(), 'estado' => 'ACTIVA']);
        AsignacionResidenteJornada::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $actor->personal->getKey(),
            'cod_jornada' => $jornada->getKey(), 'nivel_supervision' => 'DIRECTA', 'fecha_hora' => now(), 'estado' => 'ACTIVA']);
    }

    public function test_enfermeria_bandeja_y_campana_conservan_reconocidas_asignadas_sin_residentes_ajenos(): void
    {
        $actor = $this->actor('ENFERMEROS');
        $propio = Residente::factory()->create();
        $ajeno = Residente::factory()->create();
        $this->asignarTurno($actor, $propio);
        foreach (['ABIERTA', 'RECONOCIDA', 'ASIGNADA', 'EN_ATENCION', 'ATENDIDA', 'CERRADA', 'ANULADA'] as $estado) {
            $this->alerta($propio, $estado);
        }
        $externa = $this->alerta($ajeno);
        $esperados = app(BandejaAlertas::class)->pendientes($actor)->pluck('cod_alerta')->sort()->values()->all();
        $this->assertCount(4, $esperados);
        Livewire::test(AlertasPanel::class)->set('filtroEspecialidad', '')->assertViewHas('alertas', fn ($a) => $a->pluck('cod_alerta')->sort()->values()->all() === $esperados)
            ->call('verDetalle', $externa->getKey())->assertForbidden();
        Livewire::test(CampanaNotificaciones::class)->assertSet('conteoAlertas', 4)->assertSet('conteoRecordatorios', 0)
            ->call('togglePanel')->assertSet('conteoAlertas', 4);
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_enfermeria_registra_actuacion_y_cierra_con_eventos_sin_perder_historia(): void
    {
        $actor = $this->actor('ENFERMEROS');
        $r = Residente::factory()->create();
        $this->asignarTurno($actor, $r);
        $a = $this->alerta($r, 'ASIGNADA', 'SIGNOS');
        $s = app(AlertasService::class);
        $s->registrarDesdePanel($a, 'Intervención sintética documentada.', $actor, atencion: true);
        $this->assertSame('EN_ATENCION', $a->fresh()->estado);
        $evento = $a->eventos()->sole()->getAttributes();
        $s->cerrar($a->fresh(), 'Resultado comprobado y documentado.', $actor, coordinacion: true);
        $this->assertSame('CERRADA', $a->fresh()->estado);
        $this->assertSame($evento, EventoAlerta::findOrFail($evento['cod_evento_alerta'])->getAttributes());
        $this->assertSame(2, $a->eventos()->count());
    }

    public function test_roles_de_lectura_no_pueden_intervenir_ni_cerrar_con_permiso_aislado(): void
    {
        $r = Residente::factory()->create();
        $a = $this->alerta($r, 'ABIERTA', 'SIGNOS');
        foreach (['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'GERENTE', 'ADMINISTRADOR', 'SUPERADMINISTRADOR'] as $rol) {
            $actor = $this->actor($rol);
            $actor->givePermissionTo(['alertas.seguimiento', 'alertas.cerrar']);
            foreach (['intervenir', 'atendida'] as $accion) {
                try {
                    if ($accion === 'intervenir') {
                        app(AlertasService::class)->registrarDesdePanel($a, 'Intento clínico indebido.', $actor, atencion: true);
                    } else {
                        app(AlertasService::class)->cambiarEstado($a, 'ATENDIDA', 'Intento de dar por atendida clínicamente.', $actor);
                    }
                    $this->fail($rol.' no debe ejecutar '.$accion);
                } catch (HttpException $e) {
                    $this->assertSame(403, $e->getStatusCode());
                }
            }
        }
        $this->assertSame('ABIERTA', $a->fresh()->estado);
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_administracion_documenta_coordinacion_sin_intervencion_ni_cambio_clinico(): void
    {
        $actor = $this->actor('ADMINISTRADOR');
        $a = $this->alerta(Residente::factory()->create(), 'RECONOCIDA', 'SIGNOS');
        app(AlertasService::class)->registrarDesdePanel($a, 'Se coordinó revisión con el equipo autorizado.', $actor);
        $this->assertSame('RECONOCIDA', $a->fresh()->estado);
        $this->assertSame('SEGUIMIENTO', $a->eventos()->sole()->tipo_evento);
        Livewire::test(AlertasPanel::class)->call('verDetalle', $a->getKey())
            ->assertSee('Seguimiento de coordinación')->assertDontSee('Registrar intervención');
    }

    public function test_cierre_desde_estados_admitidos_y_reintento_no_duplica_eventos(): void
    {
        $actor = $this->actor('ADMINISTRADOR');
        foreach (['ABIERTA', 'RECONOCIDA', 'ASIGNADA', 'EN_ATENCION', 'ATENDIDA'] as $estado) {
            $a = $this->alerta(Residente::factory()->create(), $estado);
            app(AlertasService::class)->cerrar($a, 'Coordinación operativa concluida.', $actor, coordinacion: true);
            $this->assertSame($estado, $a->eventos()->sole()->estado_anterior);
            try {
                app(AlertasService::class)->cerrar($a->fresh(), 'Reintento de cierre.', $actor, coordinacion: true);
                $this->fail('El reintento no debe registrar otro evento.');
            } catch (HttpException $e) {
                $this->assertSame(409, $e->getStatusCode());
            }
            $this->assertSame(1, $a->eventos()->count());
        }
    }

    public function test_los_filtros_se_conservan_y_el_total_muestra_todos_los_estados(): void
    {
        $this->actor('ADMINISTRADOR');
        $r = Residente::factory()->create();
        $a = $this->alerta($r, 'RECONOCIDA');
        $this->alerta($r, 'CERRADA');
        Livewire::test(AlertasPanel::class)->set('filtroOrigen', 'MANUAL')->set('search', $r->nombres)
            ->call('verDetalle', $a->getKey())->set('accion', 'x')->call('guardarAccion')
            ->assertHasErrors('accion')->assertSet('accion', 'x')->assertSet('filtroOrigen', 'MANUAL')
            ->call('cerrarModales')->call('limpiarFiltros')->assertViewHas('alertas', fn ($p) => $p->total() === 2)
            ->call('setFiltroRapido', 'modalCrear', 'indebido')->assertSet('modalCrear', false);
    }

    public function test_tipo_respeta_60_caracteres_sin_persistencia_parcial(): void
    {
        $actor = $this->actor('ADMINISTRADOR');
        $actor->givePermissionTo('alertas.gestionar');
        $r = Residente::factory()->create();
        Livewire::test(AlertasPanel::class)->set('codResidente', $r->getKey())->set('tipoAlerta', str_repeat('A', 61))
            ->set('motivo', 'Descripción sintética suficiente.')->call('guardarAlerta')->assertHasErrors('tipoAlerta');
        $this->assertDatabaseCount('alertas', 0);
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_preventiva_reconocida_o_asignada_no_se_duplica(): void
    {
        $actor = $this->actor('ADMINISTRADOR');
        $actor->givePermissionTo('alertas.gestionar');
        $r = Residente::factory()->create();
        foreach (['RECONOCIDA', 'ASIGNADA'] as $estado) {
            $a = $this->alerta($r, $estado);
            $nuevo = app(DeteccionAlertasService::class)->registrarPreventivaSiNoExiste($r->getKey(), 'MANUAL', $a->tipo, 'ALTO', 'La misma condición de prueba.');
            $this->assertNull($nuevo);
        }
        $this->assertDatabaseCount('alertas', 2);
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_solicitud_medica_lee_motivo_persistido_y_conserva_referencia(): void
    {
        $actor = $this->actor('ADMINISTRADOR');
        $actor->givePermissionTo('alertas.gestionar');
        $r = Residente::factory()->create();
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVA']);
        $origen = Atencion::create(['cod_residente' => $r->getKey(), 'cod_personal' => $actor->personal->getKey(),
            'cod_area' => $area->getKey(), 'fecha_hora' => now(), 'motivo' => 'Requiere medico', 'estado' => 'REALIZADA', 'observacion' => 'Solicitud registrada.']);
        $this->assertSame(1, app(DeteccionAlertasService::class)->detectar($r->getKey()));
        $this->assertDatabaseHas('alertas', ['modulo' => 'SOLICITUD_MEDICA', 'cod_registro' => $origen->getKey()]);
        $this->assertSame(0, app(DeteccionAlertasService::class)->detectar($r->getKey()));
        $this->assertDatabaseCount('eventos_alerta', 1);
    }

    public function test_campana_no_concede_alertas_a_roles_sin_permiso_ni_a_familia(): void
    {
        $this->alerta(Residente::factory()->create());
        foreach (['NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO', 'FAMILIAR'] as $rol) {
            $actor = $this->actor($rol);
            if ($rol !== 'FAMILIAR') {
                $actor->roles()->firstOrFail()->revokePermissionTo('alertas.ver');
                $actor->unsetRelation('roles');
                $this->assertFalse($actor->can('alertas.ver'));
            } else {
                // Familia sigue excluida aunque reciba el permiso genérico por error.
                $actor->givePermissionTo('alertas.ver');
            }
            $this->assertFalse(app(BandejaAlertas::class)->pendientes($actor)->exists());
            Livewire::test(CampanaNotificaciones::class)->assertSet('conteoAlertas', 0)->assertDontSee('Ver todas las alertas');
            if (in_array($rol, ['NUTRICIONISTA', 'FISIOTERAPEUTA'], true)) {
                $data = app(RoleDashboardDataService::class)->forRole($actor, $rol);
                $this->assertNotContains('Alertas a tu cargo', array_column($data['metrics'], 'label'));
            }
        }
    }

    public function test_cuenta_revocada_no_devuelve_pendientes(): void
    {
        $actor = $this->actor('ADMINISTRADOR');
        $this->alerta(Residente::factory()->create());
        User::whereKey($actor->getKey())->update(['estado' => 'INACTIVO']);
        $this->assertSame(0, app(BandejaAlertas::class)->pendientes($actor)->count());
        Livewire::test(AlertasPanel::class)->assertForbidden();
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_atendida_no_se_reabre_mediante_intervencion(): void
    {
        $actor = $this->actor('ENFERMEROS');
        $r = Residente::factory()->create();
        $this->asignarTurno($actor, $r);
        $a = $this->alerta($r, 'ATENDIDA');
        try {
            app(AlertasService::class)->registrarDesdePanel($a, 'Intento de reapertura implícita.', $actor, atencion: true);
            $this->fail('Una alerta atendida solo admite cierre.');
        } catch (HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
        $this->assertSame('ATENDIDA', $a->fresh()->estado);
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_cuidado_futuro_no_es_omitido_y_el_vencido_usa_fecha_canonica(): void
    {
        $actor = $this->actor('ENFERMEROS');
        $r = Residente::factory()->create();
        $this->asignarTurno($actor, $r);
        $jornada = Jornada::sole();
        $plan = PlanCuidado::create(['cod_residente' => $r->getKey(), 'cod_personal' => $actor->personal->getKey(),
            'cod_area' => Area::sole()->getKey(), 'tipo_plan' => 'HIGIENE', 'nombre' => 'Cuidado sintético', 'fecha_hora_apertura' => now(), 'estado' => 'ACTIVO']);
        $intervencion = IntervencionCuidado::create(['cod_plan' => $plan->getKey(), 'nombre' => 'Cuidado sintético', 'descripcion' => 'Intervención sintética programada.', 'prioridad' => 'MEDIA', 'estado' => 'ACTIVO']);
        $datos = ['cod_intervencion' => $intervencion->getKey(), 'cod_residente' => $r->getKey(), 'cod_personal' => $actor->personal->getKey(),
            'cod_jornada' => $jornada->getKey(), 'estado' => 'PENDIENTE'];
        $futuro = EjecucionCuidado::create($datos + ['fecha_hora_programada' => now()->addHour()]);
        $vencido = EjecucionCuidado::create($datos + ['fecha_hora_programada' => now()->subHour()]);
        $this->assertSame(1, app(DeteccionAlertasService::class)->detectar($r->getKey()));
        $this->assertDatabaseHas('alertas', ['modulo' => 'PLAN', 'cod_registro' => $vencido->getKey()]);
        $this->assertDatabaseMissing('alertas', ['cod_registro' => $futuro->getKey()]);
        $this->assertSame(0, app(DeteccionAlertasService::class)->detectar($r->getKey()));
        $this->assertDatabaseCount('eventos_alerta', 1);
    }

    public function test_el_diagnostico_programado_declara_bloqueo_sin_cuenta_ficticia(): void
    {
        $this->artisan('alertas:verificar-vigilancia')->expectsOutputToContain('falta aprobar la autoría')->assertExitCode(1);
        $this->assertDatabaseCount('alertas', 0);
        $this->assertDatabaseCount('eventos_alerta', 0);
        $this->assertDatabaseCount('usuarios', 0);
    }

    public function test_operaciones_clinicas_rechazan_actor_distinto_y_cuenta_revocada_sin_efectos(): void
    {
        $enfermero = $this->actor('ENFERMEROS');
        $residente = Residente::factory()->create();
        $this->asignarTurno($enfermero, $residente);
        $alerta = $this->alerta($residente);
        $this->actor('ADMINISTRADOR');
        $servicio = app(AlertasService::class);
        foreach (['actor_distinto', 'cuenta_revocada'] as $caso) {
            if ($caso === 'cuenta_revocada') {
                $this->actingAs($enfermero);
                User::whereKey($enfermero->getKey())->update(['estado' => 'INACTIVO']);
            }
            try {
                $servicio->registrarIntervencion($alerta, 'Actuación sintética rechazada.', $enfermero);
                $this->fail('Una identidad inválida no puede actuar.');
            } catch (HttpException $exception) {
                $this->assertSame(403, $exception->getStatusCode());
            }
            $this->assertSame('ABIERTA', $alerta->fresh()->estado);
            $this->assertDatabaseCount('eventos_alerta', 0);
        }
    }

    public function test_cierre_http_de_coordinacion_conserva_su_significado_operativo(): void
    {
        $actor = $this->actor('ADMINISTRADOR');
        $residente = Residente::factory()->create();
        foreach (['CERRADA', 'ANULADA'] as $estado) {
            $alerta = $this->alerta($residente);
            app(AlertasService::class)->cambiarEstado($alerta, $estado, 'Resultado operativo sintético.', $actor);
            $this->assertSame($estado, $alerta->fresh()->estado);
            $this->assertStringContainsString('no acredita resolución clínica', $alerta->eventos()->sole()->descripcion);
            $this->assertSame($actor->getKey(), $alerta->eventos()->sole()->cod_usuario);
        }
    }

    public function test_graficos_no_exponen_historia_clinica_por_permiso_de_alertas(): void
    {
        $residente = Residente::factory()->create();
        foreach (['ADMINISTRADOR', 'GERENTE'] as $rol) {
            $actor = $this->actor($rol);
            $this->assertTrue($actor->can('alertas.ver'));
            $this->assertFalse($actor->can('signos_vitales.ver'));
            foreach ([AlertasPanel::class, CampanaNotificaciones::class] as $componente) {
                Livewire::test($componente)->call('verGraficos', $residente->getKey())->assertForbidden();
            }
            DB::enableQueryLog();
            DB::flushQueryLog();
            try {
                Livewire::test(CampanaNotificaciones::class)->call('verUbicacion', $residente->getKey())
                    ->assertStatus(200)->assertSet('drawerUbicacion', true)
                    ->assertDontSee('Riesgos Clínicos')->assertDontSee('Moderado');
                $consultas = collect(DB::getQueryLog())->pluck('query')->implode(' ');
                $this->assertStringNotContainsString('"alergias"', $consultas);
                $this->assertStringNotContainsString('"diagnosticos"', $consultas);
            } finally {
                DB::disableQueryLog();
            }
            $this->assertDatabaseCount('eventos_alerta', 0);
        }
    }

    public function test_cerrar_una_captura_sin_guardar_pide_confirmacion_y_permita_recuperarla(): void
    {
        $actor = $this->actor('ENFERMEROS');
        $residente = Residente::factory()->create();
        $this->asignarTurno($actor, $residente);
        $alerta = $this->alerta($residente);
        foreach ([['abrirCrear', 'motivo', 'modalCrear'], ['atenderAlerta', 'accionTomada', 'modalAtender'], ['cerrarAlerta', 'observacionCierre', 'modalCerrar']] as [$metodo, $campo, $modal]) {
            $panel = Livewire::test(AlertasPanel::class);
            $metodo === 'abrirCrear' ? $panel->call($metodo) : $panel->call($metodo, $alerta->getKey());
            $panel->set($campo, 'Captura sintética todavía sin guardar.')
                ->call('solicitarCerrarModales')->assertSet('confirmarDescarte', true)->assertSet($modal, true)
                ->call('continuarCaptura')->assertSet('confirmarDescarte', false)->assertSet($campo, 'Captura sintética todavía sin guardar.')
                ->call('solicitarCerrarModales')->call('descartarCaptura')->assertSet($modal, false)->assertSet($campo, '');
            $this->assertSame('ABIERTA', $alerta->fresh()->estado);
            $this->assertDatabaseCount('alertas', 1);
            $this->assertDatabaseCount('eventos_alerta', 0);
        }
    }

    public function test_formulario_clinico_rechaza_roles_de_coordinacion_y_lectura_sin_efectos(): void
    {
        $alerta = $this->alerta(Residente::factory()->create());
        foreach (['ADMINISTRADOR', 'SUPERADMINISTRADOR', 'GERENTE', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A'] as $rol) {
            $actor = $this->actor($rol);
            $actor->givePermissionTo('alertas.seguimiento');
            Livewire::test(AlertasPanel::class)->call('atenderAlerta', $alerta->getKey())->assertForbidden();
        }
        $this->assertSame('ABIERTA', $alerta->fresh()->estado);
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_enfermeria_fuera_de_turno_conserva_consulta_sin_ofrecer_mutaciones(): void
    {
        $actor = $this->actor('ENFERMEROS');
        $residente = Residente::factory()->create();
        $this->asignarTurno($actor, $residente);
        $alerta = $this->alerta($residente);
        $this->travelTo(now()->setTime(16, 0));
        $panel = Livewire::test(AlertasPanel::class)->call('verDetalle', $alerta->getKey())
            ->assertViewHas('puedeIntervenir', false)->assertViewHas('puedeReconocer', false)->assertViewHas('puedeAsignar', false)
            ->assertSee('Para registrar acciones necesitas un turno vigente')->assertSee('Registrar intervención')
            ->assertSee('Asignar responsable')->assertDontSee('Reconocer aviso · continúa pendiente');
        $this->assertStringContainsString('<fieldset disabled', $panel->html());
        Livewire::test(AlertasPanel::class)->call('atenderAlerta', $alerta->getKey())->assertForbidden();
        Livewire::test(AlertasPanel::class)->call('verGraficos', $residente->getKey())
            ->assertStatus(200)->assertSet('drawerGrafico', true)->assertSee('Mediciones del residente');
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_selector_de_responsable_excluye_enfermeria_sin_acceso_al_residente(): void
    {
        $propio = $this->actor('ENFERMEROS');
        $residente = Residente::factory()->create();
        $this->asignarTurno($propio, $residente);
        $ajeno = $this->actor('ENFERMEROS');
        $this->actor('ADMINISTRADOR');
        $alerta = $this->alerta($residente);
        Livewire::test(AlertasPanel::class)->call('verDetalle', $alerta->getKey())
            ->assertViewHas('usuarios', fn ($usuarios) => $usuarios->contains('cod_usuario', $propio->getKey())
                && ! $usuarios->contains('cod_usuario', $ajeno->getKey()))
            ->set('responsableId', $propio->getKey())->call('solicitarCerrarModales')->assertSet('confirmarDescarte', true);
        $this->assertNull($alerta->fresh()->cod_personal_responsable);
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_filtro_incluye_origenes_cerrados_y_rechaza_intervalo_invertido(): void
    {
        $this->actor('ADMINISTRADOR');
        $residente = Residente::factory()->create();
        $this->alerta($residente, 'ABIERTA', 'MANUAL');
        $this->alerta($residente, 'CERRADA', 'VALORACION');
        Livewire::test(AlertasPanel::class)->assertViewHas('origenesDisponibles', ['MANUAL', 'VALORACION'])
            ->set('filtroEstado', '')->set('filtroOrigen', 'VALORACION')->assertViewHas('alertas', fn ($p) => $p->total() === 1)
            ->set('fechaDesde', '2026-10-20')->set('fechaHasta', '2026-10-19')->assertHasErrors(['fechaHasta' => 'after_or_equal'])
            ->call('limpiarFiltros')->assertHasNoErrors()
            ->set('fechaDesde', '2026-99-99')->assertHasErrors(['fechaDesde' => 'date_format'])
            ->assertViewHas('alertas', fn ($p) => $p->total() === 2);
    }

    public function test_coordinacion_tiene_campos_neutrales_y_admite_el_maximo_validado_del_cierre(): void
    {
        $this->actor('SUPERADMINISTRADOR');
        $alerta = $this->alerta(Residente::factory()->create());
        Livewire::test(AlertasPanel::class)->call('abrirCrear')
            ->assertSee('Situación que requiere revisión')->assertDontSee('CRISIS_HIPERTENSIVA')->assertSee('maxlength="60"', false)
            ->call('cerrarModales')->call('cerrarAlerta', $alerta->getKey())->call('confirmarCierre')
            ->assertHasErrors('observacionCierre')->assertSee('Debe ingresar la justificación del cierre.')
            ->set('observacionCierre', str_repeat('A', 10000))->call('confirmarCierre')->assertHasNoErrors()
            ->assertSee('Este cierre no acredita resolución clínica.');
        $this->assertSame('CERRADA', $alerta->fresh()->estado);
        $this->assertSame(1, $alerta->eventos()->count());
    }

    public function test_resumen_representa_la_misma_consulta_y_no_oculta_estados_anteriores(): void
    {
        $this->actor('SUPERADMINISTRADOR');
        $residente = Residente::factory()->create();
        $anterior = $this->alerta($residente, 'ACTIVO', 'SIGNOS');
        $cerrada = $this->alerta($residente, 'CERRADA', 'MEDICACION');
        $panel = Livewire::test(AlertasPanel::class)->set('mostrarAnalisis', true)->assertSet('filtroEstado', '')
            ->assertViewHas('alertas', fn ($p) => $p->total() === 2)
            ->assertViewHas('chartData', fn ($data) => array_sum($data['origenes']) === 2)
            ->assertSee('Origen de las alertas')->assertDontSee('Sin datos por mostrar')
            ->set('filtroEstado', 'ACTIVO')->assertViewHas('alertas', fn ($p) => $p->total() === 1)
            ->assertViewHas('chartData', fn ($data) => $data['origenes'] === ['SIGNOS' => 1] && $data['estados'] === ['ACTIVO' => 1])
            ->set('filtroEstado', 'ACTIVAS')->assertSee('Ninguna alerta coincide con estos filtros')
            ->assertDontSee('Sin datos por mostrar')->assertSee('Ajusta el estado, la prioridad o las fechas');
        $this->assertSame('ACTIVO', $anterior->fresh()->estado);
        $this->assertSame('CERRADA', $cerrada->fresh()->estado);
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_redisenio_busqueda_por_nombre_completo_y_grupos_sin_reclasificar_historia(): void
    {
        $actor = $this->actor('SUPERADMINISTRADOR');
        $r = Residente::factory()->create(['nombres' => 'Ana Lucía', 'apellido_paterno' => 'Prueba', 'apellido_materno' => 'Sintética']);
        $urgente = $this->alerta($r, 'ASIGNADA');
        $urgente->update(['tipo' => 'INCIDENTE', 'prioridad' => 'CRITICA']);
        $media = $this->alerta($r, 'PENDIENTE');
        $media->update(['prioridad' => 'MEDIA']);
        $cerrada = $this->alerta($r, 'CERRADA');
        $anterior = $this->alerta($r, 'ACTIVO');
        $antes = Alerta::query()->orderBy('cod_alerta')->get()->toArray();

        $panel = Livewire::test(AlertasPanel::class)
            ->assertDontSee('<details', false)->assertDontSee('Lista equivalente de nodos', false)
            ->assertSee('Fichas de consulta')
            ->set('search', 'Ana Lucía Prueba')->assertViewHas('alertas', fn ($p) => $p->total() === 2)
            ->call('filtrarResumen', 'urgentes')
            ->assertViewHas('alertas', fn ($p) => $p->pluck('cod_alerta')->all() === [$urgente->getKey()])
            ->assertSee('Incidente registrado')->assertSee('Prioridad crítica')
            ->call('filtrarResumen', 'por_atender')->assertViewHas('alertas', fn ($p) => $p->total() === 2)
            ->call('filtrarResumen', 'cerradas')
            ->assertViewHas('alertas', fn ($p) => $p->pluck('cod_alerta')->all() === [$cerrada->getKey()])
            ->call('filtrarResumen', 'anteriores')
            ->assertViewHas('alertas', fn ($p) => $p->pluck('cod_alerta')->all() === [$anterior->getKey()])
            ->assertViewHas('conteos', fn ($c) => $c['anteriores'] === 1 && $c['criticas'] === 1 && $c['por_atender'] === 2)
            ->assertSee('Estado anterior conservado');

        $panel->call('verDetalle', $anterior->getKey())->set('buscarResponsable', 'Profesional')->assertHasNoErrors();
        $visible = html_entity_decode(strip_tags($panel->html()));
        $this->assertStringNotContainsString($anterior->getKey(), $visible);
        $this->assertStringNotContainsString($r->getKey(), $visible);

        $traza = app(ConsultarTrazaAlerta::class)->consultar($actor, $urgente->getKey());
        $this->assertSame('Incidente registrado', collect($traza['nodos'])->firstWhere('id', 'alerta')['texto']);
        $this->assertSame($antes, Alerta::query()->orderBy('cod_alerta')->get()->toArray());
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_redisenio_costo_de_busqueda_y_analisis_bajo_demanda(): void
    {
        $this->actor('SUPERADMINISTRADOR');
        $r = Residente::factory()->create(['nombres' => 'Residente Sintético']);
        $this->alerta($r);
        $panel = Livewire::test(AlertasPanel::class);
        $medidas = [];
        foreach ([false, true] as $analisis) {
            DB::enableQueryLog();
            DB::flushQueryLog();
            $inicio = hrtime(true);
            $panel->set('mostrarAnalisis', $analisis);
            $consultas = DB::getQueryLog();
            DB::disableQueryLog();
            $medidas[$analisis ? 'analisis' : 'bandeja'] = [
                'consultas' => count($consultas),
                'agrupaciones' => count(array_filter($consultas, fn ($q) => str_contains(strtolower($q['query']), 'group by'))),
                'sql_ms' => round(array_sum(array_column($consultas, 'time')), 2),
                'componente_ms' => round((hrtime(true) - $inicio) / 1e6, 2),
            ];
        }
        if ($destino = getenv('RM_ALERTAS_MEDICION')) {
            file_put_contents($destino, json_encode($medidas, JSON_PRETTY_PRINT));
        }
        $this->assertSame(3, $medidas['analisis']['agrupaciones'] - $medidas['bandeja']['agrupaciones']);
        $this->assertSame(3, $medidas['analisis']['consultas'] - $medidas['bandeja']['consultas']);
        $this->assertSame(1, Alerta::count());
        $this->assertDatabaseCount('eventos_alerta', 0);
    }

    public function test_redisenio_ciclo_con_chips_traza_y_ventanas_excluyentes(): void
    {
        $actor = $this->actor('ENFERMEROS');
        $r = Residente::factory()->create(['nombres' => 'Residente Sintético', 'apellido_paterno' => 'Interacción']);
        $this->asignarTurno($actor, $r);
        $panel = Livewire::test(AlertasPanel::class)->call('abrirCrear')
            ->assertDontSee('<select', false)->assertDontSee('role="combobox"', false)
            ->call('guardarAlerta')->assertHasErrors(['codResidente', 'tipoAlerta', 'motivo'])
            ->set('codResidente', $r->getKey())->set('tipoAlerta', 'Aviso sintético')
            ->set('nivel', 'CRITICO')->set('motivo', 'Hecho sintético de prueba para el seguimiento autorizado.')
            ->call('guardarAlerta')->assertHasNoErrors()->assertSet('modalCrear', false);
        $a = Alerta::query()->sole();
        $this->assertSame(1, $a->eventos()->count());
        $panel->set('search', 'sin coincidencia')->call('filtrarResumen', 'urgentes')
            ->assertSet('search', '')->assertViewHas('alertas', fn ($p) => $p->total() === 1)
            ->call('seleccionarTraza', $a->getKey())->assertViewHas('traza', fn ($t) => $t['codigo'] === $a->getKey())
            ->call('verDetalle', $a->getKey())->assertSet('modalDetalle', true)
            ->call('verUbicacion', $r->getKey())->assertSet('modalDetalle', false)->assertSet('drawerUbicacion', true)
            ->call('atenderAlerta', $a->getKey())->assertSet('drawerUbicacion', false)
            ->assertSee('Respuesta observada')->assertSee('Comunicación y continuidad')
            ->set('accionTomada', 'Atención sintética realizada y registrada.')->call('guardarAtencion')->assertHasNoErrors()
            ->call('filtrarResumen', 'en_atencion')->assertViewHas('alertas', fn ($p) => $p->total() === 1)
            ->call('verDetalle', $a->getKey())->set('accion', 'Seguimiento sintético documentado.')->call('guardarAccion')
            ->call('cerrarAlerta', $a->getKey())->set('observacionCierre', 'Cierre sintético con historial conservado.')
            ->call('confirmarCierre')->assertSet('modalResultadoCierre', true)
            ->call('filtrarResumen', 'cerradas')->assertViewHas('alertas', fn ($p) => $p->total() === 1);
        $this->assertSame('CERRADA', $a->fresh()->estado);
        $this->assertSame(['CREACION', 'INTERVENCION', 'SEGUIMIENTO', 'CIERRE'], $a->eventos()->orderBy('fecha_hora')->orderBy('cod_evento_alerta')->pluck('tipo_evento')->all());
        $this->assertSame(1, Alerta::count());
        Livewire::test(AlertasPanel::class)->call('atenderAlerta', $a->getKey())->assertStatus(409);
        $this->assertSame(4, $a->eventos()->count());
    }

    public function test_redisenio_traza_no_expone_residente_ajeno_ni_fuente_cruzada(): void
    {
        $actor = $this->actor('ENFERMEROS');
        $propio = Residente::factory()->create();
        $ajeno = Residente::factory()->create();
        $this->asignarTurno($actor, $propio);
        $a = $this->alerta($propio, 'ABIERTA', 'SIGNOS');
        $externa = $this->alerta($ajeno);
        // Una referencia inexistente jamás se presenta como registro de origen.
        $a->update(['cod_registro' => 'SIG-INEXISTENTE', 'cod_personal_responsable' => $actor->personal->getKey()]);
        Livewire::test(AlertasPanel::class)->assertViewHas('traza', fn ($t) => ! $t['fuenteDisponible'] && ! in_array('origen', array_column($t['nodos'], 'id')))
            ->call('seleccionarTraza', $externa->getKey())->assertNotFound();
        $signoAjeno = SignoVital::create(['cod_residente' => $ajeno->getKey(), 'cod_personal' => $actor->personal->getKey(), 'temperatura' => 36.5]);
        $a->update(['cod_registro' => $signoAjeno->getKey()]);
        $this->assertFalse(app(ConsultarTrazaAlerta::class)->consultar($actor, $a->getKey())['fuenteDisponible']);
        $signoPropio = SignoVital::create(['cod_residente' => $propio->getKey(), 'cod_personal' => $actor->personal->getKey(), 'temperatura' => 36.5]);
        $a->update(['cod_registro' => $signoPropio->getKey()]);
        $this->assertTrue(app(ConsultarTrazaAlerta::class)->consultar($actor, $a->getKey())['fuenteDisponible']);
        $administrador = $this->actor('ADMINISTRADOR');
        $this->assertFalse(app(ConsultarTrazaAlerta::class)->consultar($administrador, $a->getKey())['fuenteDisponible']);
        $this->assertDatabaseCount('eventos_alerta', 0);
        $this->actor('FAMILIAR');
        Livewire::test(AlertasPanel::class)->assertForbidden();
    }

    public function test_redisenio_reintento_de_alta_conserva_una_alerta_y_un_evento(): void
    {
        $actor = $this->actor('ENFERMEROS');
        $r = Residente::factory()->create();
        $this->asignarTurno($actor, $r);
        $datos = ['origen' => 'MANUAL', 'tipo_alerta' => 'Aviso sintético', 'nivel' => 'MEDIO', 'motivo' => 'Motivo sintético registrado durante esta prueba.'];
        $s = app(AlertasService::class);
        $id = (string) Str::uuid();
        $primera = $s->crearDesdePanel($r->getKey(), $datos, $actor, $id);
        $segunda = $s->crearDesdePanel($r->getKey(), $datos, $actor, $id);
        $this->assertSame($primera->getKey(), $segunda->getKey());
        $this->assertDatabaseCount('alertas', 1);
        $this->assertDatabaseCount('eventos_alerta', 1);
    }
}
