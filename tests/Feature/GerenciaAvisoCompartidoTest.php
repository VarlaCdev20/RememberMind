<?php

namespace Tests\Feature;

use App\Modulos\Institucion\Infraestructura\Persistencia\HistoriaLaboral;

use App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\EventoAsignacionPersonal;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Alertas\Presentacion\Livewire\Funcionalidades\CampanaNotificaciones;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use App\Modulos\Institucion\Aplicacion\Acciones\ConservarEventoAsignacion;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarAvisosPlanificacion;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarHorariosPersonal;
use App\Modulos\Institucion\Aplicacion\Consultas\ConsultarAvisosPlanificacion;
use App\Modulos\Institucion\Infraestructura\Jobs\PublicarAvisoPlanificacion;
use App\Modulos\Institucion\Presentacion\Livewire\Paginas\AvisosPlanificacionPanel;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Process\Process;
use Tests\Support\EntornoPruebas;
use Tests\TestCase;

class GerenciaAvisoCompartidoTest extends TestCase
{
    private User $actor;
    private User $administrador;
    private User $trabajador;
    private array $entrada;

    protected function setUp(): void
    {
        parent::setUp();
        $db = DB::connection();
        if (! app()->environment('testing') || ! (($db->getDriverName() === 'sqlite' && $db->getDatabaseName() === ':memory:')
            || ($db->getDriverName() === 'pgsql' && EntornoPruebas::postgresDesechable($db->getDatabaseName(), $db->getConfig('username'), $db->getConfig('host'))))) {
            throw new \RuntimeException('Solo se permite una base desechable confirmada.');
        }
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
        $this->freezeTime();
        (require base_path('database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php'))->up();
        (require base_path('database/propuestas/gerencia/2026_10_26_000002_historia_vinculos_asignaciones.php'))->up();
        (require base_path('database/propuestas/gerencia/2026_10_27_000003_campos_aviso_evento_asignacion.php'))->up();
        \Tests\Support\PrepararHistoriaLaboral::instalar();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->actor = $this->cuenta('GERENTE', '91000001');
        $this->administrador = $this->cuenta('ADMINISTRADOR', '91000002');
        $this->trabajador = $this->cuenta('PSICOLOGO/A', '91000003');
        $area = Area::create(['nombre' => 'Área sintética', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'Mañana', 'hora_inicio' => '08:00:00', 'hora_cierre' => '16:00:00', 'estado' => 'ACTIVO']);
        $this->entrada = ['usuarioId' => $this->trabajador->getKey(), 'f_cod_area' => $area->getKey(), 'f_cod_turno' => $turno->getKey(),
            'f_fecha_inicio' => '2026-11-02', 'f_dias_semana' => ['LUNES'], 'f_tipo_asignacion' => 'REGULAR', 'modoEdicion' => false];
        config(['remembermind.avisos_planificacion_habilitados' => true, 'queue.default' => 'sync']);
        $this->actingAs($this->actor); request()->setMethod('POST');
    }

    protected function tearDown(): void
    {
        RefreshDatabaseState::$migrated = false;
        parent::tearDown();
    }

    private function cuenta(string $rol, string $ci): User
    {
        $cuenta = User::factory()->create(); $cuenta->assignRole($rol);
        Personal::create(['cod_usuario' => $cuenta->getKey(), 'nombres' => 'Persona', 'apellido_paterno' => 'Sintética',
            'numero_documento' => $ci, 'estado' => 'ACTIVO', 'profesion' => $rol]);
        return $cuenta;
    }

    private function guardar(): EventoAsignacionPersonal
    {
        app(GestionarHorariosPersonal::class)->guardar($this->entrada);
        return EventoAsignacionPersonal::sole();
    }

    public function test_horario_confirmado_abre_la_jornada_y_se_consulta_sin_esperar_el_aviso(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-02 07:00:00'));
        Queue::fake();
        $enfermera = $this->cuenta('ENFERMEROS', '91000006');
        $jornada = Jornada::create(['cod_turno' => $this->entrada['f_cod_turno'],
            'fecha_jornada' => '2026-11-02', 'estado' => 'PLANIFICADA']);

        app(GestionarHorariosPersonal::class)->guardar([...$this->entrada, 'usuarioId' => $enfermera->getKey()]);

        $this->assertDatabaseCount('jornadas', 1);
        $this->assertSame('ABIERTA', $jornada->fresh()->estado);
        $this->assertSame($this->actor->getKey(), $jornada->fresh()->cod_usuario_apertura);
        $this->assertSame('ACTIVO', AsignacionPersonal::sole()->estado);
        $this->assertSame('PENDIENTE', EventoAsignacionPersonal::sole()->estado_aviso_planificacion);
        $this->assertNull(app(MiTurnoService::class)->resolverJornadaActual($enfermera->personal));
        $this->travel(2)->hours();
        $this->assertSame($jornada->getKey(), app(MiTurnoService::class)->resolverJornadaActual($enfermera->personal)?->getKey());

        $this->actingAs($enfermera)->get(route('admin.avisos-planificacion.index'))
            ->assertOk()->assertSeeText('Mis horarios')->assertSeeText('02/11/2026 08:00');
        $this->actingAs($this->trabajador)->get(route('admin.avisos-planificacion.index'))
            ->assertOk()->assertSeeText('Sin horarios asignados')->assertDontSeeText('02/11/2026 08:00');
    }

    public function test_aviso_unico_se_publica_despues_del_commit_y_conserva_el_afectado(): void
    {
        DB::beginTransaction();
        $evento = $this->guardar();
        $this->assertSame('PENDIENTE', $evento->estado_aviso_planificacion);
        $this->assertSame($this->trabajador->getKey(), $evento->cod_usuario_afectado_aviso);
        $this->assertSame(0, app(ConsultarAvisosPlanificacion::class)->consulta($this->trabajador)->count());
        DB::commit();
        $this->assertSame('DISPONIBLE', $evento->fresh()->estado_aviso_planificacion);
        $this->assertSame(1, HistoriaLaboral::consulta('eventos_asignacion_personal')->count());
        $this->assertSame(1, app(ConsultarAvisosPlanificacion::class)->consulta($this->trabajador)->count());
        $this->assertSame(1, app(ConsultarAvisosPlanificacion::class)->consulta($this->administrador)->count());
        $this->assertSame(100, count(\Illuminate\Support\Facades\Schema::getTableListing()));
    }

    public function test_rollback_no_guarda_evento_aviso_o_trabajo_de_cola(): void
    {
        Queue::fake(); DB::beginTransaction(); $this->guardar(); DB::rollBack();
        $this->assertSame(0, RegistrosConsumo::consulta('asignaciones_personal')->count());
        $this->assertSame(0, HistoriaLaboral::consulta('eventos_asignacion_personal')->count());
        Queue::assertNothingPushed();
    }

    public function test_fallo_postcommit_y_reintento_conservan_asignacion_original_y_un_solo_aviso(): void
    {
        $fallar = true;
        Event::listen('eloquent.updating: '.EventoAsignacionPersonal::class, function ($e) use (&$fallar) {
            if ($fallar && $e->estado_aviso_planificacion === 'DISPONIBLE') throw new \RuntimeException('Fallo sintético de publicación');
        });
        $evento = $this->guardar(); $original = $evento->getAttributes();
        $this->assertSame('FALLIDO', $evento->estado_aviso_planificacion);
        $this->assertSame(1, RegistrosConsumo::consulta('asignaciones_personal')->count());
        $fallar = false;
        $accion = app(GestionarAvisosPlanificacion::class);
        $this->assertSame('DISPONIBLE', $accion->reintentar($this->actor, $evento->getKey()));
        $accion->reintentar($this->actor, $evento->getKey());
        $this->assertSame(2, $evento->fresh()->numero_intentos_aviso);
        foreach (['cod_asignacion_personal', 'cod_usuario_registro', 'fecha_hora_efecto', 'motivo', 'estado_nuevo', 'cod_usuario_afectado_aviso'] as $campo) {
            $this->assertSame($original[$campo], $evento->fresh()->getAttributes()[$campo]);
        }
        $this->assertSame(1, DB::table('activity_log')->where('event', 'aviso_planificacion_disponible')->count());
        $this->assertSame(1, DB::table('activity_log')->where('event', 'aviso_planificacion_fallido')->count());
        $this->assertSame(1, HistoriaLaboral::consulta('eventos_asignacion_personal')->count());
    }

    public function test_cola_indisponible_deja_pendiente_y_permite_recuperacion_sin_repetir_cambio(): void
    {
        Bus::shouldReceive('dispatch')->andThrow(new \RuntimeException('Cola indisponible sintética'));
        $evento = $this->guardar();
        $this->assertSame('PENDIENTE', $evento->estado_aviso_planificacion);
        $this->assertSame('DISPONIBLE', app(GestionarAvisosPlanificacion::class)->reintentar($this->actor, $evento->getKey()));
        $this->assertSame(1, $evento->fresh()->numero_intentos_aviso);
        $this->assertSame(1, RegistrosConsumo::consulta('asignaciones_personal')->count());
    }

    public function test_trabajo_conservado_en_cola_sql_se_ejecuta_sin_repetir_la_asignacion(): void
    {
        config(['queue.default' => 'database']);
        $evento = $this->guardar();
        $this->assertSame('PENDIENTE', $evento->estado_aviso_planificacion);
        $this->assertDatabaseCount('jobs', 1);
        $this->assertSame('planificacion', DB::table('jobs')->sole()->queue);
        $this->artisan('queue:work', ['connection' => 'database', '--queue' => 'planificacion', '--once' => true, '--tries' => 1])->assertSuccessful();
        $this->assertSame('DISPONIBLE', $evento->fresh()->estado_aviso_planificacion);
        $this->assertSame(1, $evento->fresh()->numero_intentos_aviso);
        $this->assertDatabaseCount('jobs', 0);
        $this->assertSame(1, HistoriaLaboral::consulta('eventos_asignacion_personal')->count());
        $this->assertSame(1, RegistrosConsumo::consulta('asignaciones_personal')->count());
    }

    public function test_bandeja_compartida_no_depende_de_membresia_individual_y_excluye_familiares(): void
    {
        $this->guardar();
        $nuevo = $this->cuenta('ADMINISTRADOR', '91000004');
        $consulta = app(ConsultarAvisosPlanificacion::class);
        $this->assertSame(1, $consulta->consulta($nuevo)->count()); //Bandeja compartida adoptada.
        $nuevo->syncRoles(['FAMILIAR']);
        $this->assertFalse($consulta->puedeConsultar($nuevo));
        $otro = $this->cuenta('ENFERMEROS', '91000005');
        $this->assertSame(0, $consulta->consulta($otro)->count());
        $this->trabajador->personal->update(['estado' => 'INACTIVO']);
        $this->assertFalse($consulta->puedeConsultar($this->trabajador));
    }

    public function test_mas_de_cincuenta_cambios_ajenos_no_ocultan_el_propio_tras_perder_administracion(): void
    {
        $this->trabajador->assignRole('ADMINISTRADOR');
        $propio = $this->guardar();
        $asignacion = AsignacionPersonal::sole();
        $this->travel(1)->seconds();
        app(GestionarHorariosPersonal::class)->guardar([...$this->entrada, 'usuarioId' => $this->administrador->getKey()]);
        $ajena = AsignacionPersonal::where('cod_personal', $this->administrador->personal->getKey())->sole();
        foreach (range(1, 51) as $i) DB::transaction(fn () => app(ConservarEventoAsignacion::class)->conservar($ajena, $this->actor,
            'RECTIFICACION', 'ACTIVO', now(), 'Rectificación sintética independiente'));
        $this->trabajador->syncRoles(['PSICOLOGO/A']);
        $filas = app(ConsultarAvisosPlanificacion::class)->consulta($this->trabajador)->limit(50)->get();
        $this->assertCount(1, $filas);
        $this->assertSame($propio->getKey(), $filas->sole()->getKey());
    }

    public function test_cambiar_la_cuenta_del_personal_no_transfiere_un_aviso_anterior(): void
    {
        $evento = $this->guardar();
        $nueva = User::factory()->create();
        $nueva->assignRole('PSICOLOGO/A');
        $this->trabajador->personal->update(['cod_usuario' => $nueva->getKey()]);
        $consulta = app(ConsultarAvisosPlanificacion::class);
        $this->assertFalse($consulta->puedeConsultar($this->trabajador));
        $this->assertTrue($consulta->puedeConsultar($nueva));
        $this->assertSame(0, $consulta->consulta($nueva)->count());
        $this->assertSame($this->trabajador->getKey(), $evento->fresh()->cod_usuario_afectado_aviso);
        $this->assertSame(1, $consulta->consulta($this->administrador)->count());
    }

    public function test_finalizacion_muestra_tipo_fecha_efectiva_y_estado_sin_motivos_libres(): void
    {
        $this->guardar();
        app(GestionarHorariosPersonal::class)->confirmarFinalizar(['usuarioId' => $this->trabajador->getKey(),
            'codAsignacionFinalizar' => AsignacionPersonal::sole()->getKey(), 'f_fecha_fin_finalizar' => '2026-11-01']);
        $consulta = app(ConsultarAvisosPlanificacion::class);
        $fila = $consulta->presentar($consulta->consulta($this->trabajador)->first());
        $this->assertSame('Asignación finalizada', $fila['titulo']);
        $this->assertSame('31/10/2026 23:59', $fila['efecto']);
        $this->assertSame('Inactiva', $fila['situacion_asignacion']);
        $this->assertArrayNotHasKey('motivo', $fila);
    }

    public function test_reintento_idor_permiso_revocado_y_vinculo_actual_no_apropian_el_evento(): void
    {
        Queue::fake(); $evento = $this->guardar();
        try { app(GestionarAvisosPlanificacion::class)->reintentar($this->administrador, $evento->getKey()); $this->fail('No es emisor.'); }
        catch (HttpException $e) { $this->assertSame(403, $e->getStatusCode()); }
        $this->actor->syncRoles(['ADMINISTRADOR']); $this->actor->revokePermissionTo('turnos.asignar'); //el rol aún lo concede.
        $this->actor->syncRoles(['FAMILIAR']);
        try { app(GestionarAvisosPlanificacion::class)->reintentar($this->actor, $evento->getKey()); $this->fail('Familiar no publica.'); }
        catch (HttpException $e) { $this->assertSame(403, $e->getStatusCode()); }
        $this->assertSame(0, $evento->fresh()->numero_intentos_aviso);
    }

    public function test_pagina_campana_y_reintento_presentan_confirmaciones_reales(): void
    {
        Queue::fake(); $evento = $this->guardar();
        $this->get(route('admin.avisos-planificacion.index'))->assertOk();
        Livewire::test(AvisosPlanificacionPanel::class)->assertSee('aviso pendiente de preparación')
            ->call('reintentar', $evento->getKey())->assertSee('El aviso está disponible');
        $this->actingAs($this->administrador);
        $this->get(route('admin.avisos-planificacion.index'))
            ->assertOk()
            ->assertSee('Asignación registrada')
            ->assertSee('Ir a asignaciones de residentes')
            ->assertSee(route('admin.asignacion-turno.index'));
        $this->get(route('admin.asignacion-turno.index'))->assertOk();
        $this->actingAs($this->trabajador);
        Livewire::test(CampanaNotificaciones::class)->assertSee('Asignación registrada')->assertSee('Ver todos los avisos de planificación');
        Livewire::test(AvisosPlanificacionPanel::class)->assertSee('Disponible en la bandeja')
            ->assertDontSee('Reintentar aviso')->assertDontSee('Ir a asignaciones de residentes');
        $this->assertFalse($this->actor->can('prescripciones.crear'));
    }

    public function test_historico_sin_seguimiento_no_se_inicializa_y_flag_apagado_no_altera_flujo(): void
    {
        config(['remembermind.avisos_planificacion_habilitados' => false]);
        $evento = $this->guardar();
        $this->assertNull($evento->estado_aviso_planificacion);
        config(['remembermind.avisos_planificacion_habilitados' => true]);
        try { app(GestionarAvisosPlanificacion::class)->reintentar($this->actor, $evento->getKey()); $this->fail('No inventar seguimiento.'); }
        catch (HttpException $e) { $this->assertSame(409, $e->getStatusCode()); }
        $this->assertNull($evento->fresh()->cod_usuario_afectado_aviso);
    }

    public static function escenariosConcurrentes(): array
    {
        return ['dos_reintentos' => ['doble'], 'suspension_mientras_espera' => ['suspender'],
            'permiso_revocado_mientras_espera' => ['revocar'], 'reversion_espera_insercion_confirmada' => ['revertir']];
    }

    #[Group('postgresql')]
    #[DataProvider('escenariosConcurrentes')]
    public function test_concurrencia_real_publicacion_autorizacion_y_reversion(string $escenario): void
    {
        $db = DB::connection();
        if ($db->getDriverName() !== 'pgsql') {
            $this->markTestSkipped('La concurrencia requiere el runner PostgreSQL desechable.');
        }
        $this->assertSame('pgsql', $db->getDriverName());
        Queue::fake();
        if ($escenario === 'revertir') config(['remembermind.avisos_planificacion_habilitados' => false]);
        $evento = $this->guardar();
        config(['remembermind.avisos_planificacion_habilitados' => true]);
        if ($escenario === 'revocar') {
            $this->actor->syncRoles(['GERENTE']);
            $this->actor->roles->first()->revokePermissionTo('turnos.asignar');
            $this->actor->givePermissionTo('turnos.asignar');
        }
        $root = sys_get_temp_dir().'/remembermind-gerencia-race-'.bin2hex(random_bytes(16)); File::makeDirectory($root);
        $nombre = 'rm_aviso_'.bin2hex(random_bytes(7));
        $datos = ['modo' => $escenario === 'revertir' ? 'revertir_campos_aviso' : 'aviso_compartido', 'actor' => $this->actor->getKey(),
            'evento' => $evento->getKey(), 'aplicacion' => $nombre, 'identificar_workers' => true, 'sin_barrera' => $escenario !== 'doble'];
        file_put_contents($root.'/datos.json', json_encode($datos, JSON_THROW_ON_ERROR));
        $env = ['APP_ENV' => 'testing', 'APP_CONFIG_CACHE' => $root.'/config.php', 'APP_ROUTES_CACHE' => $root.'/routes.php',
            'DB_CONNECTION' => 'pgsql', 'DB_URL' => '', 'DB_HOST' => $db->getConfig('host'), 'DB_PORT' => (string) $db->getConfig('port'),
            'DB_DATABASE' => $db->getDatabaseName(), 'DB_USERNAME' => $db->getConfig('username'), 'DB_PASSWORD' => $db->getConfig('password'),
            'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array'];
        $procesos = [];
        try {
            DB::beginTransaction();
            HistoriaLaboral::consulta('eventos_asignacion_personal')->where('cod_evento_asignacion', $evento->getKey())->lockForUpdate()->first();
            if ($escenario === 'revertir') {
                // El evento es el seguimiento insertado antes de que el rollback obtenga su lock de tabla.
                $fila = $evento->getAttributes();
                $fila['cod_evento_asignacion'] = 'QA_CONCURRENTE'; $fila['numero_evento'] = 2; $fila['cod_evento_anterior'] = $evento->getKey();
                $fila['cod_usuario_afectado_aviso'] = $this->trabajador->getKey();
                $fila['estado_aviso_planificacion'] = 'PENDIENTE'; $fila['numero_intentos_aviso'] = 0;
                HistoriaLaboral::insertar('eventos_asignacion_personal', [$fila]);
            }
            $cantidad = $escenario === 'doble' ? 2 : 1;
            foreach (range(1, $cantidad) as $numero) {
                $p = new Process([PHP_BINARY, base_path('tests/Support/GerenciaConcurrenteWorker.php'), $root, (string) $numero], base_path(), $env, timeout: 35);
                $p->start(); $procesos[] = $p;
            }
            $limite = microtime(true) + 20;
            do {
                DB::select('select pg_stat_clear_snapshot()');
                $esperando = (int) DB::scalar("select count(*) from pg_stat_activity where datname=current_database() and application_name in (?,?) and wait_event_type='Lock'", [$nombre.'_1', $nombre.'_2']);
                if ($esperando === $cantidad || collect($procesos)->contains(fn ($p) => ! $p->isRunning())) break;
                usleep(20000);
            } while (microtime(true) < $limite);
            $this->assertSame($cantidad, $esperando, 'El proceso debe esperar un bloqueo PostgreSQL real.');
            if ($escenario === 'suspender') DB::table('usuarios')->where('cod_usuario', $this->actor->getKey())->update(['estado' => 'INACTIVO']);
            if ($escenario === 'revocar') $this->actor->revokePermissionTo('turnos.asignar');
            DB::commit();
            foreach ($procesos as $p) {
                $p->wait(); $this->assertTrue($p->isSuccessful(), $p->getErrorOutput());
                $this->assertSame(match ($escenario) { 'suspender', 'revocar' => 'DENEGADO', 'revertir' => 'RECHAZADO', default => 'CONFIRMADO' },
                    json_decode($p->getOutput(), true, flags: JSON_THROW_ON_ERROR)['estado']);
            }
            $this->assertSame(match ($escenario) { 'doble' => 1, 'revertir' => null, default => 0 }, $evento->fresh()->numero_intentos_aviso);
            $this->assertSame(match ($escenario) { 'doble' => 'DISPONIBLE', 'revertir' => null, default => 'PENDIENTE' }, $evento->fresh()->estado_aviso_planificacion);
            if ($escenario === 'revertir') $this->assertSame('PENDIENTE', EventoAsignacionPersonal::findOrFail('QA_CONCURRENTE')->estado_aviso_planificacion);
            $this->assertSame(1, RegistrosConsumo::consulta('asignaciones_personal')->count());
            $this->assertSame($escenario === 'revertir' ? 2 : 1, HistoriaLaboral::consulta('eventos_asignacion_personal')->count());
            $this->assertSame($escenario === 'doble' ? 1 : 0, DB::table('activity_log')->where('event', 'aviso_planificacion_disponible')->count());
        } finally {
            if (DB::transactionLevel() > 0) DB::rollBack();
            foreach ($procesos as $p) if ($p->isRunning()) $p->stop();
            $real = realpath($root);
            if ($real && dirname($real) === realpath(sys_get_temp_dir()) && preg_match('/^remembermind-gerencia-race-[a-f0-9]{32}$/D', basename($real)) && ! is_link($real)) File::deleteDirectory($real);
        }
    }
}
