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
use App\Models\PaseTurno;
use App\Models\Personal;
use App\Models\PlanCuidado;
use App\Models\ProgramacionCuidado;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use App\Modulos\Cuidados\Aplicacion\Acciones\PaseTurnoService;
use App\Modulos\Cuidados\Presentacion\Livewire\Paginas\AgendaEnfermeria;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class RedisenoEnfermeriaIntegralTest extends TestCase
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

    public function test_agenda_clasifica_las_4_categorias_de_filtro_y_sus_contadores(): void
    {
        // Programación para la intervención
        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_001',
            'cod_intervencion' => $this->intervencion->cod_intervencion,
            'frecuencia' => 'Cada 4 horas',
            'hora_programada' => '10:00',
            'fecha_activacion' => today(),
            'estado' => 'ACTIVA',
        ]);

        $test = Livewire::test(AgendaEnfermeria::class);

        // Estado inicial por atender (pendiente)
        $test->assertSee('Por atender')
            ->assertSee('No realizadas')
            ->assertSee('Realizadas')
            ->assertSee('Todas')
            ->assertSee('Cambio postural programado');

        $contadores = $test->viewData('contadores');
        $this->assertEquals(1, $contadores['por_atender']);
        $this->assertEquals(0, $contadores['no_realizadas']);
        $this->assertEquals(0, $contadores['realizadas']);
        $this->assertEquals(1, $contadores['todas']);

        // Filtrar por realizadas (debe estar vacía)
        $test->set('filtroEstado', 'realizadas')
            ->assertDontSee('Firmada')
            ->assertSee('No hay cuidados en este filtro');

        // Filtrar por no realizadas (debe estar vacía)
        $test->set('filtroEstado', 'no_realizadas')
            ->assertSee('No hay cuidados en este filtro');

        // Filtrar por todas
        $test->set('filtroEstado', 'todas')
            ->assertSee('Cambio postural programado');
    }

    public function test_agenda_soporta_multiples_programaciones_sin_colapsar_por_keyby(): void
    {
        // Dos programaciones en distintos horarios para la misma intervención
        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_0800',
            'cod_intervencion' => $this->intervencion->cod_intervencion,
            'frecuencia' => 'Turno mañana',
            'hora_programada' => '08:00',
            'fecha_activacion' => today(),
            'estado' => 'ACTIVA',
        ]);

        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_1200',
            'cod_intervencion' => $this->intervencion->cod_intervencion,
            'frecuencia' => 'Turno mañana',
            'hora_programada' => '12:00',
            'fecha_activacion' => today(),
            'estado' => 'ACTIVA',
        ]);

        // Ejecutar únicamente la de las 08:00
        Livewire::test(AgendaEnfermeria::class)
            ->call('abrirModalRegistrar', $this->intervencion->cod_intervencion, $this->residente->cod_residente, 'PRG_0800', '08:00')
            ->call('setFueRealizado', true)
            ->set('resultado', 'Satisfactorio')
            ->call('registrarEjecucion')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('ejecuciones_cuidado', 1);

        // Al renderizar la agenda nuevamente:
        // La de las 08:00 debe figurar como realizada/firmada
        // La de las 12:00 debe seguir figurando como pendiente
        $items = Livewire::test(AgendaEnfermeria::class)->viewData('itemsAgenda');

        $this->assertCount(2, $items);

        $item08 = $items->firstWhere('hora_programada', '08:00');
        $item12 = $items->firstWhere('hora_programada', '12:00');

        $this->assertNotNull($item08);
        $this->assertNotNull($item12);

        $this->assertEquals('REALIZADA', $item08['estado']);
        $this->assertEquals('PENDIENTE', $item12['estado']);
    }

    public function test_modal_exige_especificar_si_fue_realizado_o_no(): void
    {
        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_EXP',
            'cod_intervencion' => $this->intervencion->cod_intervencion,
            'frecuencia' => 'Diario',
            'hora_programada' => '10:00',
            'fecha_activacion' => today(),
            'estado' => 'ACTIVA',
        ]);

        Livewire::test(AgendaEnfermeria::class)
            ->call('abrirModalRegistrar', $this->intervencion->cod_intervencion, $this->residente->cod_residente, 'PRG_EXP', '10:00')
            ->assertSet('fueRealizado', null)
            ->assertSet('resultado', '')
            ->call('revisarAntesDeConfirmar')
            ->assertHasErrors(['fueRealizado']);
    }

    public function test_modal_no_preselecciona_satisfactorio(): void
    {
        $test = Livewire::test(AgendaEnfermeria::class)
            ->call('abrirModalRegistrar', $this->intervencion->cod_intervencion, $this->residente->cod_residente, null, '10:00');

        $this->assertSame('', $test->get('resultado'));
    }

    public function test_modal_exige_observacion_clinica_descriptiva_ante_dificultad_o_cambio_relevante(): void
    {
        $test = Livewire::test(AgendaEnfermeria::class)
            ->call('abrirModalRegistrar', $this->intervencion->cod_intervencion, $this->residente->cod_residente, null, '10:00')
            ->call('setFueRealizado', true)
            ->set('resultado', 'Con dificultad / colaboración parcial')
            ->set('observacion', '')
            ->call('revisarAntesDeConfirmar')
            ->assertHasErrors(['observacion']);

        // Con observación válida de al menos 5 caracteres y <= 500
        $test->set('observacion', 'Residente manifestó dolor en miembro inferior al lateralizar.')
            ->call('revisarAntesDeConfirmar')
            ->assertHasNoErrors()
            ->assertSet('pasoRevision', true);
    }

    public function test_modal_exige_motivo_y_observacion_ante_omision_respetando_maximo_500_caracteres(): void
    {
        $test = Livewire::test(AgendaEnfermeria::class)
            ->call('abrirModalRegistrar', $this->intervencion->cod_intervencion, $this->residente->cod_residente, null, '10:00')
            ->call('setFueRealizado', false)
            ->set('motivoOmision', '')
            ->set('observacion', '')
            ->call('revisarAntesDeConfirmar')
            ->assertHasErrors(['motivoOmision']);

        // Con motivo corto
        $test->set('motivoOmision', 'No')
            ->call('revisarAntesDeConfirmar')
            ->assertHasErrors(['motivoOmision']);

        // Con motivo largo mayor a 500 caracteres
        $textoLargo = str_repeat('A', 505);
        $test->set('motivoOmision', $textoLargo)
            ->set('observacion', 'Observación válida')
            ->call('revisarAntesDeConfirmar')
            ->assertHasErrors(['motivoOmision']);

        // Con motivo y observación válidos
        $test->set('motivoOmision', 'Residente rechaza de forma expresa la movilización en este momento.')
            ->set('observacion', 'Se conversa con residente y se acuerda reevaluar más tarde.')
            ->call('revisarAntesDeConfirmar')
            ->assertHasNoErrors()
            ->assertSet('pasoRevision', true);
    }

    public function test_modal_incorpora_flujo_dos_pasos_revisar_y_confirmar(): void
    {
        $test = Livewire::test(AgendaEnfermeria::class)
            ->call('abrirModalRegistrar', $this->intervencion->cod_intervencion, $this->residente->cod_residente, null, '10:00')
            ->assertSet('pasoRevision', false)
            ->call('setFueRealizado', true)
            ->set('resultado', 'Realizado según protocolo')
            ->set('observacion', 'Sin incidencias durante el procedimiento.')
            ->call('revisarAntesDeConfirmar')
            ->assertSet('pasoRevision', true);

        // Volver a editar permite retroceder al formulario
        $test->call('volverAEditar')
            ->assertSet('pasoRevision', false);

        // Pasar nuevamente a revisión y confirmar registro
        $test->call('revisarAntesDeConfirmar')
            ->assertSet('pasoRevision', true)
            ->call('registrarEjecucion')
            ->assertHasNoErrors()
            ->assertSet('modalRegistrarAbierto', false);

        $this->assertDatabaseHas('ejecuciones_cuidado', [
            'cod_intervencion' => $this->intervencion->cod_intervencion,
            'cod_residente' => $this->residente->cod_residente,
            'estado' => 'REALIZADA',
            'resultado' => 'Realizado según protocolo',
        ]);
    }

    public function test_error_en_servidor_mantiene_modal_abierto_con_datos_intactos(): void
    {
        // Registrar una primera vez para generar duplicado
        EjecucionCuidado::create([
            'cod_ejecucion' => 'EJC_DUP_01',
            'cod_intervencion' => $this->intervencion->cod_intervencion,
            'cod_residente' => $this->residente->cod_residente,
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_personal' => $this->personal->cod_personal,
            'fecha_hora_programada' => Carbon::parse(today()->toDateString().' 10:00'),
            'fecha_hora_ejecucion' => now(),
            'resultado' => 'Satisfactorio',
            'estado' => 'REALIZADA',
        ]);

        $test = Livewire::test(AgendaEnfermeria::class)
            ->call('abrirModalRegistrar', $this->intervencion->cod_intervencion, $this->residente->cod_residente, null, '10:00')
            ->call('setFueRealizado', true)
            ->set('resultado', 'Satisfactorio')
            ->set('observacion', 'Texto de observación que debe mantenerse')
            ->call('revisarAntesDeConfirmar')
            ->call('registrarEjecucion');

        // El modal NO debe cerrarse y los datos no deben perderse
        $test->assertSet('modalRegistrarAbierto', true)
            ->assertSet('resultado', 'Satisfactorio')
            ->assertSet('observacion', 'Texto de observación que debe mantenerse')
            ->assertHasErrors(['errorModal']);
    }

    public function test_exito_cierra_modal_genera_aviso_compacto_con_ver_registro_y_conserva_filtro_residente(): void
    {
        $test = Livewire::test(AgendaEnfermeria::class)
            ->set('filtroResidente', $this->residente->cod_residente)
            ->call('abrirModalRegistrar', $this->intervencion->cod_intervencion, $this->residente->cod_residente, null, '11:00')
            ->call('setFueRealizado', true)
            ->set('resultado', 'Satisfactorio')
            ->call('registrarEjecucion')
            ->assertHasNoErrors()
            ->assertSet('modalRegistrarAbierto', false);

        // Se conserva el filtro del residente
        $this->assertEquals($this->residente->cod_residente, $test->get('filtroResidente'));

        // Se genera el aviso compacto
        $ultimo = $test->get('ultimoRegistro');
        $this->assertNotNull($ultimo);
        $this->assertEquals('REALIZADA', $ultimo['estado']);
        $this->assertEquals('Satisfactorio', $ultimo['resultado']);

        // Ver registro lleva a pestaña historial
        $test->call('cambiarTab', 'historial')
            ->assertSet('tab', 'historial')
            ->assertSee('Satisfactorio');
    }

    public function test_pase_turno_muestra_omisiones_originales_con_motivo_observacion_profesional_y_fecha(): void
    {
        // Registrar un cuidado omitido en la jornada
        EjecucionCuidado::create([
            'cod_ejecucion' => 'EJC_OMITIDA_TEST',
            'cod_intervencion' => $this->intervencion->cod_intervencion,
            'cod_residente' => $this->residente->cod_residente,
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_personal' => $this->personal->cod_personal,
            'fecha_hora_programada' => now(),
            'fecha_hora_ejecucion' => now(),
            'resultado' => 'No realizada / Omitida',
            'motivo_omision' => 'Rechazo del residente por molestia en cadera derecha',
            'observacion' => 'Se comunica a médico de guardia y se mantiene reposo',
            'estado' => 'NO_REALIZADA',
        ]);

        $service = app(PaseTurnoService::class);
        $contexto = $service->obtenerContextoClinicoResidente($this->residente->cod_residente, $this->jornada);

        $this->assertArrayHasKey('cuidados_omitidos', $contexto);
        $this->assertCount(1, $contexto['cuidados_omitidos']);

        $omitido = $contexto['cuidados_omitidos'][0];
        $this->assertEquals('Cambio postural programado', $omitido['intervencion']);
        $this->assertEquals('Rechazo del residente por molestia en cadera derecha', $omitido['motivo_omision']);
        $this->assertEquals('Se comunica a médico de guardia y se mantiene reposo', $omitido['observacion']);
        $this->assertStringContainsString('Carla Encinas', $omitido['personal']);
    }

    public function test_recepcion_de_pase_mantiene_flujo_sin_marcar_omisiones_como_realizadas_ni_crear_ordenes(): void
    {
        $enfermeroEntrante = User::factory()->create(['cod_usuario' => 'USU_ENTRANTE', 'estado' => 'ACTIVO']);
        $enfermeroEntrante->assignRole('ENFERMEROS');
        $personalEntrante = Personal::create([
            'cod_personal' => 'PER_ENTRANTE',
            'cod_usuario' => $enfermeroEntrante->cod_usuario,
            'nombres' => 'Manuel',
            'apellido_paterno' => 'Receptor',
            'numero_documento' => 'DOC_ENTRANTE',
            'profesion' => 'Enfermero',
            'estado' => 'ACTIVO',
        ]);

        $turnoEntrante = Turno::create([
            'cod_turno' => 'TUR_TARDE',
            'orden' => 2,
            'nombre' => 'Tarde',
            'hora_inicio' => '15:00',
            'hora_fin' => '23:00',
            'estado' => 'ACTIVO',
        ]);

        $jornadaEntrante = Jornada::create([
            'cod_jornada' => 'JOR_TARDE',
            'cod_turno' => $turnoEntrante->cod_turno,
            'cod_usuario_apertura' => $enfermeroEntrante->cod_usuario,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);

        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_ENTRANTE',
            'cod_residente' => $this->residente->cod_residente,
            'cod_jornada' => $jornadaEntrante->cod_jornada,
            'cod_personal' => $personalEntrante->cod_personal,
            'nivel_supervision' => 'MEDIO',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);

        // Registrar omisión en la jornada saliente
        $omision = EjecucionCuidado::create([
            'cod_ejecucion' => 'EJC_OMISION_PAS',
            'cod_intervencion' => $this->intervencion->cod_intervencion,
            'cod_residente' => $this->residente->cod_residente,
            'cod_jornada' => $this->jornada->cod_jornada,
            'cod_personal' => $this->personal->cod_personal,
            'fecha_hora_programada' => now(),
            'fecha_hora_ejecucion' => now(),
            'resultado' => 'No realizada / Omitida',
            'motivo_omision' => 'Falta de cooperación transitoria',
            'observacion' => 'Monitorear en turno tarde',
            'estado' => 'NO_REALIZADA',
        ]);

        // Crear pase de turno entregado
        $pase = PaseTurno::create([
            'cod_pase' => 'PAS_TEST_REDISENO',
            'cod_residente' => $this->residente->cod_residente,
            'cod_jornada_saliente' => $this->jornada->cod_jornada,
            'cod_jornada_entrante' => $jornadaEntrante->cod_jornada,
            'cod_personal_saliente' => $this->personal->cod_personal,
            'cod_personal_entrante' => $personalEntrante->cod_personal,
            'fecha_hora' => now(),
            'estado_general' => 'ESTABLE',
            'resumen' => 'Resumen clínico del turno con cuidado omitido.',
            'estado' => 'ENTREGADO',
        ]);

        // El enfermero entrante recibe el pase
        $service = app(PaseTurnoService::class);
        $paseRecibido = $service->confirmarRecepcion($pase, 'Recibo conformidad de turno y pendientes.', $enfermeroEntrante);

        $this->assertEquals('RECIBIDO', $paseRecibido->estado);
        $this->assertEquals('PER_ENTRANTE', $paseRecibido->cod_personal_entrante);

        // REGLA INSTITUCIONAL: Recibir el pase NO cambia el estado de la omisión original
        $this->assertEquals('NO_REALIZADA', $omision->fresh()->estado);

        // NO se crean órdenes médicas ni diagnósticos automáticos
        $this->assertDatabaseCount('diagnosticos', 0);
    }

    public function test_informe_cognitivo_panel_lateral_muestra_diagnostico_medico_solo_si_autorizado_y_cuidados_pendientes(): void
    {
        $area = Area::firstOrCreate(
            ['cod_area' => 'ARE_REDISENO'],
            ['nombre' => 'Área Geriátrica', 'descripcion' => 'Prueba rediseño', 'estado' => 'ACTIVO']
        );

        $atencion = Atencion::create([
            'cod_atencion' => 'ATE_MED_TEST',
            'cod_residente' => $this->residente->cod_residente,
            'cod_personal' => $this->personal->cod_personal,
            'cod_area' => $area->cod_area,
            'tipo_atencion' => 'CONSULTA',
            'fecha_hora' => now(),
            'estado' => 'FINALIZADA',
        ]);

        // Crear un diagnóstico médico para el residente
        Diagnostico::create([
            'cod_diagnostico' => 'DIA_TEST_01',
            'cod_residente' => $this->residente->cod_residente,
            'cod_atencion' => $atencion->cod_atencion,
            'cod_personal' => $this->personal->cod_personal,
            'nombre' => 'Deterioro cognitivo leve de perfil amnésico',
            'codigo_clinico' => 'G31.84',
            'tipo' => 'PRINCIPAL',
            'certeza' => 'CONFIRMADO',
            'fecha_hora' => now(),
            'estado' => 'ACTIVO',
        ]);

        // Ver con perfil de Enfermería
        $componente = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente]);

        $componente->call('abrirConsulta', 'registro')
            ->assertSee('Diagnóstico médico registrado')
            ->assertSee('Deterioro cognitivo leve de perfil amnésico')
            ->assertDontSee('G31.84');
        $componente->call('abrirConsulta', 'cuidados')
            ->assertSee('Cambio postural programado')
            ->assertSee('Ir a mi agenda de turno');
    }
}
