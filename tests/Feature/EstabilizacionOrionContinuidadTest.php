<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\Diagnostico;
use App\Models\EjecucionCuidado;
use App\Models\IntervencionCuidado;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\PlanCuidado;
use App\Models\ProgramacionCuidado;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarContinuidadAsistencialExperta;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\AgendaEnfermeria;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EstabilizacionOrionContinuidadTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;

    private Personal $personal;

    private Residente $residente;

    private Turno $turno;

    private Jornada $jornada;

    private PlanCuidado $plan;

    private IntervencionCuidado $intervencion;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-20 09:00:00');
        $this->seed(DatabaseSeeder::class);

        $this->enfermero = User::factory()->create([
            'cod_usuario' => 'USU_ENF_REDISENO',
            'estado' => 'ACTIVO',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');

        $this->personal = Personal::create([
            'cod_personal' => 'PER_ENF_REDISENO',
            'cod_usuario' => $this->enfermero->cod_usuario,
            'nombres' => 'Carla',
            'apellido_paterno' => 'Encinas',
            'numero_documento' => 'DOC_ENF_REDISENO',
            'profesion' => 'Enfermera',
            'estado' => 'ACTIVO',
        ]);

        $this->turno = Turno::create([
            'cod_turno' => 'TUR_REDISENO',
            'orden' => 1,
            'nombre' => 'Mañana',
            'hora_inicio' => '07:00',
            'hora_fin' => '15:00',
            'estado' => 'ACTIVO',
        ]);

        $this->jornada = Jornada::create([
            'cod_jornada' => 'JOR_REDISENO',
            'cod_turno' => $this->turno->cod_turno,
            'cod_usuario_apertura' => $this->enfermero->cod_usuario,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);

        $area = Area::firstOrCreate(
            ['cod_area' => 'ARE_REDISENO'],
            ['nombre' => 'Área Geriátrica', 'descripcion' => 'Prueba rediseño', 'estado' => 'ACTIVO']
        );

        AsignacionPersonal::create([
            'cod_asignacion_personal' => 'ASP_REDISENO',
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_personal' => $this->personal->cod_personal,
            'cod_area' => $area->cod_area,
            'funcion' => 'ENFERMERO',
            'tipo_asignacion' => 'TURNO',
            'fecha_asignacion' => today(),
            'estado' => 'ACTIVO',
        ]);

        $this->residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_REDISENO',
            'nombres' => 'Margarita',
            'apellido_paterno' => 'Paz',
            'fecha_nacimiento' => '1940-03-15',
            'estado' => 'ADMITIDO',
        ]);

        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_REDISENO',
            'cod_residente' => $this->residente->cod_residente,
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_personal' => $this->personal->cod_personal,
            'nivel_supervision' => 'ALTO',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);

        $this->plan = PlanCuidado::create([
            'cod_plan' => 'PLC_REDISENO',
            'cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'cod_area' => $area->cod_area,
            'nombre' => 'Plan de Prevención y Cuidado',
            'objetivo_general' => 'Mantenimiento integral y prevención de úlceras',
            'fecha_hora_apertura' => now()->subDays(5),
            'estado' => 'ACTIVO',
        ]);

        $this->intervencion = IntervencionCuidado::create([
            'cod_intervencion' => 'INT_REDISENO',
            'cod_plan' => $this->plan->cod_plan,
            'nombre' => 'Cambio postural programado',
            'descripcion' => 'Realizar lateralización en decúbito lateral derecho o izquierdo.',
            'prioridad' => 'ALTA',
            'estado' => 'ACTIVA',
        ]);

        $this->actingAs($this->enfermero);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function consultar(): array
    {
        return app(ConsultarContinuidadAsistencialExperta::class)
            ->consultar($this->enfermero, $this->residente, now());
    }

    public function test_consulta_cognitiva_tres_modales_excluyentes_y_sin_escritura(): void
    {
        $this->mock(\App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoVersionado::class)->shouldNotReceive('instantanea');
        $this->mock(\App\Modulos\ApoyoDecision\Aplicacion\Contratos\ConocimientoObservacional::class)->shouldNotReceive('vigente', 'historico', 'verificar');
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('¿Qué encontró el sistema?')->assertSee('Solo lectura · Enfermería')
            ->assertDontSee('O.R.I.O.N.')->assertDontSee('Registrar mediciones')
            ->call('abrirSignificado')->assertSet('significadoAbierto', true)
            ->call('abrirDetalleArea')->assertSet('significadoAbierto', false)->assertSet('detalleAreaAbierto', true)
            ->call('abrirEvolucion')->assertSet('detalleAreaAbierto', false)->assertSet('evolucionAbierta', true)
            ->assertSee('Comparar áreas')->assertSee('Ver datos')
            ->call('abrirConsulta', 'registro')->assertSet('evolucionAbierta', false)->assertSet('consultaAbierta', true)
            ->call('seleccionarCriterio', 'COG-ATE')->assertSet('consultaAbierta', false)->assertSet('criterio', 'COG-ATE')
            ->call('abrirSignificado')->assertSet('criterio', 'COG-ATE')
            ->call('abrirConsultaResultado', 'registro')->assertSet('criterio', 'COG-MEM')->assertSet('consultaAbierta', true)->assertSet('significadoAbierto', false);
        $this->assertDatabaseCount('ejecuciones_cuidado', 0);
    }

    public function test_detalle_cuidado_relee_ocurrencia_y_no_admite_identidad_manipulada(): void
    {
        $this->programar();
        $item = $this->consultar()['cuidadosPendientesHoy']->first();
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('abrirConsulta', 'cuidados')->call('verCuidado', $item['clave'])
            ->assertSet('consultaAbierta', false)->assertSet('cuidadoSeleccionado', $item['clave'])
            ->assertSee('Realizar lateralización')->call('cerrarCuidado')->assertSet('consultaAbierta', true);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('verCuidado', hash('sha256', 'otra-intervencion'))->assertNotFound();
        $this->assertDatabaseCount('ejecuciones_cuidado', 0);
    }

    public function test_detalle_cuidado_no_permanece_visible_tras_revocar_acceso(): void
    {
        $this->programar();
        $clave = $this->consultar()['cuidadosPendientesHoy']->first()['clave'];
        $panel = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->call('verCuidado', $clave);
        Role::findByName('ENFERMEROS')->revokePermissionTo('ejecuciones_cuidado.ver');
        $panel->call('$refresh')->assertNotFound();
        $this->assertDatabaseCount('ejecuciones_cuidado', 0);
    }

    public function test_modal_evolucion_rechaza_series_instrumentales_y_puntos_no_autorizados(): void
    {
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('abrirEvolucion')->call('cambiarComparacion', 'serie', 'ajena')->assertNotFound();
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('verMedicion', 'punto-ajeno')->assertNotFound();
    }

    public function test_fallo_instrumental_no_se_presenta_como_ausencia_y_permite_reintento(): void
    {
        $this->enfermero->givePermissionTo(\App\Modulos\ApoyoDecision\Aplicacion\Consultas\CapturasInstrumentalesResidente::PERMISOS_LECTURA);
        $fallar = true;
        DB::connection()->beforeExecuting(static function (string $consulta, array $bindings, $conexion) use (&$fallar): void {
            if ($fallar && str_contains($consulta, 'aplicaciones_instrumento')) {
                $fallar = false;
                throw new \Illuminate\Database\QueryException($conexion->getName(), $consulta, $bindings, new \RuntimeException('SQL_RESERVADO_SINTETICO'));
            }
        });
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('No fue posible consultar las mediciones instrumentales.')
            ->assertDontSee('SQL_RESERVADO_SINTETICO')->assertDontSee('No hay un desglose de subcomponentes autorizado');
        $componente->call('reintentar')->assertDontSee('No fue posible consultar las mediciones instrumentales.');
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_registro_de_componente_ajeno_no_se_abre_ni_crea_datos(): void
    {
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('abrirRegistroComponente', 'COMPONENTE_AJENO')->assertNotFound();
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    private function programar(string $hora = '10:00', array $datos = []): ProgramacionCuidado
    {
        return ProgramacionCuidado::create($datos + ['cod_intervencion' => $this->intervencion->getKey(),
            'frecuencia' => 'Horario explícito de prueba', 'hora_programada' => $hora, 'fecha_activacion' => today(), 'estado' => 'ACTIVA']);
    }

    private function ejecutar(string $fecha, string $estado = 'REALIZADA'): EjecucionCuidado
    {
        return EjecucionCuidado::create(['cod_intervencion' => $this->intervencion->getKey(), 'cod_residente' => $this->residente->getKey(),
            'cod_jornada' => $this->jornada->getKey(), 'cod_personal' => $this->personal->getKey(), 'fecha_hora_programada' => $fecha,
            'fecha_hora_ejecucion' => $estado === 'REALIZADA' ? now() : null, 'resultado' => $estado === 'REALIZADA' ? 'Registro sintético' : null,
            'estado' => $estado]);
    }

    public function test_permiso_diagnostico_revocado_oculta_contenido_y_no_consulta_tabla(): void
    {
        $atencion = Atencion::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(),
            'cod_area' => $this->plan->cod_area, 'tipo_atencion' => 'CONSULTA', 'fecha_hora' => now(), 'estado' => 'FINALIZADA']);
        Diagnostico::create(['cod_residente' => $this->residente->getKey(), 'cod_personal' => $this->personal->getKey(), 'cod_atencion' => $atencion->getKey(),
            'nombre' => 'DIAGNOSTICO_RESERVADO_TEST', 'tipo' => 'PRINCIPAL', 'certeza' => 'CONFIRMADO', 'fecha_hora' => now(), 'estado' => 'ACTIVO']);
        $panel = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('abrirConsulta', 'registro')->assertSee('DIAGNOSTICO_RESERVADO_TEST');
        Role::findByName('ENFERMEROS')->revokePermissionTo('diagnosticos.ver');
        $sql = [];
        DB::listen(static function ($q) use (&$sql): void {
            $sql[] = $q->sql;
        });
        $panel->call('$refresh')->assertDontSee('DIAGNOSTICO_RESERVADO_TEST');
        self::assertFalse($panel->viewData('puedeVerDiagnosticos'));
        self::assertSame([], array_values(array_filter($sql, static fn ($q) => preg_match('/from ["`]diagnosticos["`]/i', $q))));
        self::assertSame(1, Diagnostico::count());
    }

    public static function permisosCuidados(): array
    {
        return [['planes_cuidado.ver'], ['intervenciones_cuidado.ver'], ['programaciones_cuidado.ver'], ['ejecuciones_cuidado.ver']];
    }

    #[DataProvider('permisosCuidados')]
    public function test_no_consulta_cuidados_si_falta_cualquiera_de_sus_permisos(string $permiso): void
    {
        $this->programar();
        Role::findByName('ENFERMEROS')->revokePermissionTo($permiso);
        $sql = [];
        DB::listen(static function ($q) use (&$sql): void {
            $sql[] = $q->sql;
        });
        $r = $this->consultar();
        self::assertFalse($r['puedeVerCuidados']);
        self::assertCount(0, $r['cuidadosPendientesHoy']);
        self::assertCount(0, $r['otrosCuidadosVigentes']);
        self::assertSame([], array_values(array_filter($sql, static fn ($q) => preg_match('/from ["`](planes_cuidado|intervenciones_cuidado|programaciones_cuidado|ejecuciones_cuidado)["`]/i', $q))));
    }

    public function test_dos_horarios_una_realizada_solo_resuelve_su_ocurrencia(): void
    {
        $this->programar('08:00');
        $this->programar('12:00');
        $this->ejecutar(today()->format('Y-m-d').' 08:00:00');
        $r = $this->consultar();
        self::assertCount(1, $r['cuidadosPendientesHoy']);
        self::assertSame('12:00', $r['cuidadosPendientesHoy'][0]['hora']);
        self::assertSame('REALIZADA', $r['otrosCuidadosVigentes'][0]['estado']);
        $items = Livewire::test(AgendaEnfermeria::class)->set('filtroEstado', 'todas')->viewData('itemsAgenda');
        self::assertSame('REALIZADA', $items->firstWhere('hora_programada', '08:00')['estado']);
        self::assertSame('PENDIENTE', $items->firstWhere('hora_programada', '12:00')['estado']);
        self::assertSame(1, EjecucionCuidado::count());
    }

    public function test_ejecucion_de_ayer_realizada_hoy_no_completa_ocurrencia_actual(): void
    {
        $this->programar();
        $this->ejecutar(today()->subDay()->format('Y-m-d').' 10:00:00');
        self::assertCount(1, $this->consultar()['cuidadosPendientesHoy']);
        $items = Livewire::test(AgendaEnfermeria::class)->viewData('itemsAgenda');
        self::assertSame('PENDIENTE', $items->firstWhere('hora_programada', '10:00')['estado']);
        self::assertNull($items->firstWhere('hora_programada', '10:00')['ejecucion']);
    }

    public function test_fila_pendiente_no_se_convierte_en_realizada(): void
    {
        $this->programar();
        $this->ejecutar(today()->format('Y-m-d').' 10:00:00', 'PENDIENTE');
        self::assertCount(1, $this->consultar()['cuidadosPendientesHoy']);
        $items = Livewire::test(AgendaEnfermeria::class)->viewData('itemsAgenda');
        self::assertSame('PENDIENTE', $items->firstWhere('hora_programada', '10:00')['estado']);
    }

    public function test_programacion_de_manana_no_esta_pendiente_hoy_y_no_inventa_hora(): void
    {
        $this->programar('10:00', ['fecha_activacion' => today()->addDay()]);
        $r = $this->consultar();
        self::assertCount(0, $r['cuidadosPendientesHoy']);
        self::assertSame('SIN_OCURRENCIA_HOY', $r['otrosCuidadosVigentes'][0]['estado']);
        self::assertCount(0, Livewire::test(AgendaEnfermeria::class)->set('filtroEstado', 'todas')->viewData('itemsAgenda'));
    }

    public function test_sin_programacion_no_inventa_ocurrencia_a_las_ocho(): void
    {
        self::assertCount(0, $this->consultar()['cuidadosPendientesHoy']);
        self::assertSame('SIN_OCURRENCIA_HOY', $this->consultar()['otrosCuidadosVigentes'][0]['estado']);
        self::assertCount(0, Livewire::test(AgendaEnfermeria::class)->set('filtroEstado', 'todas')->viewData('itemsAgenda'));
    }

    public function test_revocacion_de_asignacion_deniega_la_consulta_sin_escrituras(): void
    {
        $this->programar();
        AsignacionResidenteJornada::query()->where('cod_residente', $this->residente->getKey())->update(['estado' => 'CERRADA']);
        $this->expectException(AuthorizationException::class);
        $this->consultar();
    }

    public function test_revocar_permiso_de_cuidados_despues_de_abrir_agenda_deniega_interaccion(): void
    {
        $this->programar();
        $agenda = Livewire::test(AgendaEnfermeria::class)->assertSee($this->intervencion->nombre);
        Role::findByName('ENFERMEROS')->revokePermissionTo('ejecuciones_cuidado.ver');
        $agenda->call('$refresh')->assertForbidden();
        self::assertSame(0, EjecucionCuidado::count());
    }

    public function test_apertura_y_cierre_del_plan_limitan_cada_instante_programado(): void
    {
        $this->programar('08:00');
        $this->programar('12:00');
        $this->programar('14:00');
        $this->plan->update(['fecha_hora_apertura' => today()->setTime(10, 0), 'fecha_hora_cierre' => today()->setTime(13, 0)]);
        $r = $this->consultar();
        self::assertCount(1, $r['cuidadosPendientesHoy']);
        self::assertSame('12:00', $r['cuidadosPendientesHoy'][0]['hora']);
        $items = Livewire::test(AgendaEnfermeria::class)->set('filtroEstado', 'todas')->viewData('itemsAgenda');
        self::assertCount(1, $items);
        self::assertSame('12:00', $items->first()['hora_programada']);
    }

    public function test_modal_no_admite_intervencion_ni_programacion_de_otro_plan(): void
    {
        $ajeno = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $planAjeno = PlanCuidado::create(['cod_residente' => $ajeno->getKey(), 'cod_personal' => $this->personal->getKey(),
            'cod_area' => $this->plan->cod_area, 'nombre' => 'PLAN_AJENO_RESERVADO', 'estado' => 'ACTIVO']);
        $intervencionAjena = IntervencionCuidado::create(['cod_plan' => $planAjeno->getKey(), 'nombre' => 'INTERVENCION_AJENA',
            'descripcion' => 'Descripción sintética reservada', 'prioridad' => 'MEDIA', 'estado' => 'ACTIVA']);
        $programacionAjena = ProgramacionCuidado::create(['cod_intervencion' => $intervencionAjena->getKey(), 'hora_programada' => '10:00',
            'frecuencia' => 'Original ajeno', 'fecha_activacion' => today(), 'estado' => 'ACTIVA']);
        Livewire::test(AgendaEnfermeria::class)->call('abrirModalRegistrar', $intervencionAjena->getKey(), $this->residente->getKey())
            ->assertNotFound();
        Livewire::test(AgendaEnfermeria::class)->call('abrirModalRegistrar', $this->intervencion->getKey(), $this->residente->getKey(), $programacionAjena->getKey())
            ->assertNotFound();
        self::assertSame(0, EjecucionCuidado::count());
    }
}
