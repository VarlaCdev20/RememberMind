<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Compartido\Clinica\FichaPaciente;
use App\Frontend\Livewire\Enfermeria\Cuidados\RegistrosEnfermeria;
use App\Frontend\Livewire\Enfermeria\Cuidados\ReporteEnfermeria;
use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\Area;
use App\Models\AreaInstitucional;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\CuracionHerida;
use App\Models\DispositivoClinico;
use App\Models\EjecucionCuidado;
use App\Models\Herida;
use App\Models\HorarioPrescripcion;
use App\Models\Incidente;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\Personal;
use App\Models\Prescripcion;
use App\Models\RegistroIngesta;
use App\Models\Residente;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EnfermeriaV2CorreccionCriticaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function crearEscenario(): array
    {
        $enfermera = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermera->assignRole('ENFERMEROS');
        $personal = Personal::query()->create([
            'cod_personal' => 'PER_CRIT_TEST',
            'cod_usuario' => $enfermera->cod_usuario,
            'nombres' => 'Clara',
            'apellido_paterno' => 'Mendez',
            'numero_documento' => 'ENF-CRIT-01',
            'profesion' => 'ENFERMERIA',
            'estado' => 'ACTIVO',
        ]);
        $turno = TurnoEnfermeria::query()->create([
            'cod_turno' => 'TUR_CRIT_TEST',
            'nombre' => 'TURNO TEST',
            'hora_inicio' => '00:00:00',
            'hora_cierre' => '23:59:59',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);
        $jornada = Jornada::query()->create([
            'cod_jornada' => 'JOR_CRIT_TEST',
            'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);
        $area = Area::query()->firstOrCreate(
            ['cod_area' => 'ARE_CRIT_TEST'],
            ['nombre' => 'Enfermería crítica', 'estado' => 'ACTIVA'],
        );
        AsignacionPersonal::query()->create([
            'cod_asignacion_personal' => 'ASP_CRIT_TEST',
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $personal->cod_personal,
            'cod_area' => $area->cod_area,
            'funcion' => 'ENFERMERO',
            'tipo_asignacion' => 'TURNO',
            'fecha_asignacion' => today(),
            'estado' => 'ACTIVO',
        ]);
        $residente = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_CRIT_TEST',
            'nombres' => 'Manuel',
            'apellido_paterno' => 'Belgrano',
            'fecha_nacimiento' => '1942-06-03',
            'estado' => 'ADMITIDO',
        ]);
        AsignacionResidenteJornada::query()->create([
            'cod_asignacion' => 'ARJ_CRIT_TEST',
            'cod_residente' => $residente->cod_residente,
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $personal->cod_personal,
            'nivel_supervision' => 'ESTANDAR',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);

        return [$enfermera, $personal, $residente, $jornada];
    }

    public function test_registros_enfermeria_carga_sin_buscar_relaciones_v1_ni_consultar_tablas_v1(): void
    {
        [$enfermera, , $residente] = $this->crearEscenario();
        $this->actingAs($enfermera);

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = strtolower($query->sql);
        });

        $test = Livewire::test(RegistrosEnfermeria::class, ['codResidente' => $residente->cod_residente]);
        $test->assertStatus(200);

        $tablasV1Prohibidas = [
            'registros_cuidados',
            'incidentes_residente',
            'lesiones_residente',
            'dispositivos_residente',
            'historial_estado_operativo',
        ];

        foreach ($queries as $sql) {
            foreach ($tablasV1Prohibidas as $tabla) {
                $this->assertStringNotContainsString(
                    $tabla,
                    $sql,
                    "Error: RegistrosEnfermeria ejecuto una consulta sobre la tabla V1 prohibida '{$tabla}'."
                );
            }
        }
    }

    public function test_registros_enfermeria_dispositivos_provienen_de_dispositivos_clinicos(): void
    {
        [$enfermera, , $residente] = $this->crearEscenario();
        $this->actingAs($enfermera);

        Livewire::test(RegistrosEnfermeria::class, ['codResidente' => $residente->cod_residente])
            ->set('seccion', 'DISPOSITIVOS')
            ->set('tipoDispositivo', 'SONDA_VESICAL')
            ->set('indicacionDispositivo', 'Drenaje urinario postoperatorio')
            ->set('ubicacionDispositivo', 'Uretra')
            ->call('guardarDispositivo')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('dispositivos_clinicos', [
            'cod_residente' => $residente->cod_residente,
            'tipo' => 'SONDA_VESICAL',
            'estado' => 'ACTIVO',
        ]);

        $disp = DispositivoClinico::where('cod_residente', $residente->cod_residente)->firstOrFail();

        Livewire::test(RegistrosEnfermeria::class, ['codResidente' => $residente->cod_residente])
            ->set('seccion', 'DISPOSITIVOS')
            ->set('motivoRetiroDispositivo', 'Fin de indicacion medica')
            ->call('retirarDispositivo', $disp->cod_dispositivo)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('dispositivos_clinicos', [
            'cod_dispositivo' => $disp->cod_dispositivo,
            'estado' => 'RETIRADO',
        ]);
    }

    public function test_registros_enfermeria_heridas_y_curaciones_usan_tablas_v2(): void
    {
        [$enfermera, $personal, $residente] = $this->crearEscenario();
        $this->actingAs($enfermera);

        $herida = Herida::create([
            'cod_herida' => 'HER_TEST_01',
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $personal->cod_personal,
            'tipo_herida' => 'ULCERA_PRESION',
            'ubicacion' => 'SACRO',
            'clasificacion' => 'GRADO_II',
            'estado' => 'ACTIVA',
            'fecha_hora_identificacion' => now(),
        ]);

        Livewire::test(RegistrosEnfermeria::class, ['codResidente' => $residente->cod_residente])
            ->set('seccion', 'INCIDENTES')
            ->set('lesionId', $herida->cod_herida)
            ->set('lesionMedible', true)
            ->set('largoLesion', 2.5)
            ->set('anchoLesion', 1.8)
            ->set('profundidadLesion', 0.5)
            ->set('aspectoLesion', 'Lecho con tejido de granulacion')
            ->set('accionLesion', 'Limpieza con suero fisiologico y parche')
            ->call('guardarSeguimientoLesion')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('curaciones_herida', [
            'cod_herida' => $herida->cod_herida,
            'cod_personal' => $personal->cod_personal,
            'tejido' => 'Lecho con tejido de granulacion',
            'procedimiento' => 'Limpieza con suero fisiologico y parche',
        ]);

        Livewire::test(RegistrosEnfermeria::class, ['codResidente' => $residente->cod_residente])
            ->set('seccion', 'INCIDENTES')
            ->set('resultadoCierreLesion', 'Cicatrizacion completa')
            ->set('motivoCierreLesion', 'Alta de enfermeria')
            ->call('cerrarLesion', $herida->cod_herida)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('heridas', [
            'cod_herida' => $herida->cod_herida,
            'estado' => 'CERRADA',
        ]);
    }

    public function test_registros_enfermeria_cuidados_ejecutados_usan_ejecuciones_cuidado_y_seguimientos_v2(): void
    {
        [$enfermera, , $residente] = $this->crearEscenario();
        $this->actingAs($enfermera);

        // 1. Plan e intervencion activa para el residente actual
        $plan = \App\Models\PlanCuidado::query()->create([
            'cod_plan' => 'PLC_TEST_01',
            'cod_residente' => $residente->cod_residente,
            'estado' => 'ACTIVO',
        ]);
        $intervencion = \App\Models\IntervencionCuidado::query()->create([
            'cod_intervencion' => 'INT_TEST_01',
            'cod_plan' => $plan->cod_plan,
            'nombre' => 'Curacion y aposito',
            'descripcion' => 'Cambio de aposito esteril',
            'estado' => 'ACTIVA',
        ]);

        // 1. Procedimiento general -> ejecuciones_cuidado
        Livewire::test(RegistrosEnfermeria::class, ['codResidente' => $residente->cod_residente])
            ->set('intervencionId', $intervencion->cod_intervencion)
            ->set('seccion', 'CUIDADOS')
            ->set('tipo', 'PROCEDIMIENTO')
            ->set('subtipo', 'Cambio de aposito')
            ->set('resultado', 'REALIZADO')
            ->set('observacion', 'Tolerado sin incidencias')
            ->call('guardarCuidado')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('ejecuciones_cuidado', [
            'cod_residente' => $residente->cod_residente,
            'resultado' => 'REALIZADO',
            'observacion' => 'Cambio de aposito: Tolerado sin incidencias',
        ]);

        // 2. Alimentacion -> registros_ingesta
        Livewire::test(RegistrosEnfermeria::class, ['codResidente' => $residente->cod_residente])
            ->set('seccion', 'CUIDADOS')
            ->set('tipo', 'ALIMENTACION')
            ->set('subtipo', 'Desayuno completo')
            ->set('porcentaje', 75)
            ->set('nivelAyuda', 'SUPERVISION')
            ->set('observacion', 'Buena deglucion')
            ->call('guardarCuidado')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('registros_ingesta', [
            'cod_residente' => $residente->cod_residente,
            'tipo_comida' => 'Desayuno completo',
            'porcentaje_consumido' => 75,
            'estado' => 'VIGENTE',
        ]);
    }

    public function test_guardar_nuevo_evento_registra_en_incidentes_v2_asocia_residente_y_personal_y_no_usa_v1(): void
    {
        [$enfermera, $personal, $residente, $jornada] = $this->crearEscenario();
        $this->actingAs($enfermera);

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = strtolower($query->sql);
        });

        $alertasAntes = DB::table('alertas')->count();

        Livewire::test(FichaPaciente::class, ['adulto' => $residente->cod_residente])
            ->set('nuevoEventoTipo', 'CAIDA')
            ->set('nuevoEventoFechaHora', now()->toDateTimeString())
            ->set('nuevoEventoLugar', 'Comedor central')
            ->set('nuevoEventoDescripcion', 'Perdida de equilibrio al levantarse de la silla')
            ->set('nuevoEventoDolor', 4)
            ->set('nuevoEventoMedicoInformado', true)
            ->call('guardarNuevoEvento')
            ->assertHasNoErrors();

        // 1. Verifica insercion en incidentes V2
        $this->assertDatabaseHas('incidentes', [
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $personal->cod_personal,
            'cod_jornada' => $jornada->cod_jornada,
            'tipo_incidente' => 'CAIDA',
            'lugar' => 'Comedor central',
            'gravedad' => 'MODERADA',
            'requiere_medico' => true,
        ]);

        // 2. Verifica que NO consulta ni escribe en tabla V1
        foreach ($queries as $sql) {
            $this->assertStringNotContainsString(
                'incidentes_residente',
                $sql,
                'Error: guardarNuevoEvento intento consultar o escribir en incidentes_residente.'
            );
        }

        // 3. Verifica que NO se creo alerta automatica (requisito explicito)
        $alertasDespues = DB::table('alertas')->count();
        $this->assertSame($alertasAntes, $alertasDespues, 'No debe crearse alerta automatica por cada incidente.');
    }

    public function test_guardar_nuevo_evento_bloquea_usuario_no_autorizado_o_inactivo(): void
    {
        [, , $residente] = $this->crearEscenario();

        // Usuario inactivo
        $inactivo = User::factory()->create(['estado' => 'INACTIVO']);
        $inactivo->assignRole('ENFERMEROS');
        Personal::query()->create([
            'cod_personal' => 'PER_INACTIVO',
            'cod_usuario' => $inactivo->cod_usuario,
            'nombres' => 'Inactivo',
            'apellido_paterno' => 'Test',
            'numero_documento' => 'DOC-INACT',
            'profesion' => 'ENFERMERIA',
            'estado' => 'ACTIVO',
        ]);

        $this->actingAs($inactivo);

        $adulto = AdultoMayor::query()->findOrFail($residente->cod_residente);
        $component = new FichaPaciente();
        $component->adultoMayor = $adulto;
        $component->nuevoEventoTipo = 'CAIDA';
        $component->nuevoEventoFechaHora = now()->toDateTimeString();
        $component->nuevoEventoLugar = 'Comedor';
        $component->nuevoEventoDescripcion = 'Intento no autorizado';

        $this->expectException(HttpException::class);
        $component->guardarNuevoEvento();
    }

    public function test_reporte_enfermeria_cuenta_administrada_y_omitida_correctamente_sin_columna_administrado(): void
    {
        [$enfermera, $personal, $residente, $jornada] = $this->crearEscenario();
        $this->actingAs($enfermera);

        $area = AreaInstitucional::first();
        $med = Medicamento::query()->create([
            'cod_medicamento' => 'MED_CRIT_01',
            'nombre_comercial' => 'Paracetamol 500mg',
            'nombre_generico' => 'Paracetamol',
            'forma_farmaceutica' => 'Tableta',
            'concentracion' => '500 mg',
            'control_especial' => false,
            'estado' => 'ACTIVO',
        ]);

        $atencion = Atencion::query()->create([
            'cod_atencion' => 'ATE_CRIT_01',
            'cod_residente' => $residente->cod_residente,
            'cod_area' => $area?->cod_area ?: 'ARE_0001',
            'cod_personal' => $personal->cod_personal,
            'tipo_atencion' => 'CONSULTA MEDICA',
            'motivo' => 'Evaluacion farmacologica',
            'fecha_hora' => now()->subMinute(),
            'estado' => 'ABIERTA',
        ]);

        $prescripcion = Prescripcion::query()->create([
            'cod_prescripcion' => 'PRE_CRIT_01',
            'cod_residente' => $residente->cod_residente,
            'cod_atencion' => $atencion->cod_atencion,
            'cod_medicamento' => $med->cod_medicamento,
            'cod_personal' => $personal->cod_personal,
            'dosis' => 500,
            'unidad_dosis' => 'mg',
            'via_administracion' => 'ORAL',
            'frecuencia' => 'Cada 8 horas',
            'fecha_hora_prescripcion' => now(),
            'segun_necesidad' => false,
            'estado' => 'ACTIVA',
        ]);

        // 1. ADMINISTRADA
        AdministracionMedicacion::query()->create([
            'cod_administracion' => 'ADM_CRIT_01',
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $personal->cod_personal,
            'cod_jornada' => $jornada->cod_jornada,
            'fecha_hora_programada' => today()->setTime(8, 0),
            'fecha_hora_administracion' => today()->setTime(8, 5),
            'resultado' => 'ADMINISTRADA',
            'estado' => 'ADMINISTRADA',
        ]);

        // 2. ADMINISTRADO (variante de genero)
        AdministracionMedicacion::query()->create([
            'cod_administracion' => 'ADM_CRIT_02',
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $personal->cod_personal,
            'cod_jornada' => $jornada->cod_jornada,
            'fecha_hora_programada' => today()->setTime(12, 0),
            'fecha_hora_administracion' => today()->setTime(12, 5),
            'resultado' => 'ADMINISTRADO',
            'estado' => 'ADMINISTRADA',
        ]);

        // 3. OMITIDA
        AdministracionMedicacion::query()->create([
            'cod_administracion' => 'ADM_CRIT_03',
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $personal->cod_personal,
            'cod_jornada' => $jornada->cod_jornada,
            'fecha_hora_programada' => today()->setTime(16, 0),
            'resultado' => 'OMITIDA',
            'motivo_omision' => 'Residente dormido',
            'estado' => 'OMITIDA',
        ]);

        // 4. RECHAZADA (cuenta como omision/no administrada)
        AdministracionMedicacion::query()->create([
            'cod_administracion' => 'ADM_CRIT_04',
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $personal->cod_personal,
            'cod_jornada' => $jornada->cod_jornada,
            'fecha_hora_programada' => today()->setTime(20, 0),
            'resultado' => 'RECHAZADA',
            'motivo_omision' => 'Residente rechaza toma',
            'estado' => 'OMITIDA',
        ]);

        // 5. PENDIENTE (no administrada ni omitida)
        AdministracionMedicacion::query()->create([
            'cod_administracion' => 'ADM_CRIT_05',
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $personal->cod_personal,
            'cod_jornada' => $jornada->cod_jornada,
            'fecha_hora_programada' => today()->setTime(23, 0),
            'resultado' => 'PENDIENTE',
            'estado' => 'PENDIENTE',
        ]);

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = strtolower($query->sql);
        });

        $component = Livewire::test(ReporteEnfermeria::class);
        $indicadores = $component->viewData('indicadores');

        $this->assertSame(2, $indicadores['dosis'], 'Debe contar exactamente 2 dosis administradas.');
        $this->assertSame(1, $indicadores['omisiones'], 'Debe contar exactamente 1 omision real.');

        // Verificar que no se consulto la columna prohibida administrado
        foreach ($queries as $sql) {
            $this->assertStringNotContainsString(
                'administrado',
                $sql,
                'Error: ReporteEnfermeria intento consultar la columna prohibida administrado.'
            );
        }
    }

    public function test_estado_seguimiento_en_memoria_no_modifica_estado_institucional_en_bd(): void
    {
        [$enfermera, $personal, $residente, $jornada] = $this->crearEscenario();
        $this->assertSame('ADMITIDO', $residente->estado);

        \App\Models\Alerta::query()->create([
            'cod_alerta' => 'ALE_SEG_TEST',
            'cod_residente' => $residente->cod_residente,
            'cod_personal' => $personal->cod_personal,
            'tipo' => 'SIGNOS_VITALES',
            'titulo' => 'Alerta critica de prueba',
            'prioridad' => 'CRITICA',
            'fecha_hora' => now(),
            'estado' => 'ACTIVA',
        ]);

        $miTurnoService = app(\App\Backend\Modulos\Enfermeria\Servicios\MiTurnoService::class);
        $dashboard = $miTurnoService->obtenerDatosDashboard($enfermera);

        $card = collect($dashboard['residentes'])->firstWhere('cod_residente', $residente->cod_residente);
        $this->assertNotNull($card);
        $this->assertSame('CRÍTICO', $card['estado_seguimiento']);

        $this->assertSame('ADMITIDO', $residente->fresh()->estado, 'El calculo de seguimiento en memoria no debe mutar residentes.estado.');
        $this->assertFalse(method_exists($residente, 'getEstadoOperativoAttribute'), 'Residente::getEstadoOperativoAttribute no debe existir.');
    }

    public function test_registros_enfermeria_no_permite_cambio_estado_institucional(): void
    {
        $this->assertFalse(
            method_exists(RegistrosEnfermeria::class, 'cambiarEstadoOperativo'),
            'RegistrosEnfermeria no debe contener metodo para mutar estado institucional.'
        );
        $this->assertFalse(
            property_exists(RegistrosEnfermeria::class, 'estadoOperativo'),
            'RegistrosEnfermeria no debe exponer propiedad estadoOperativo.'
        );
    }

    public function test_curacion_solo_aparece_para_residente_dueno_de_la_herida_via_has_many_through(): void
    {
        [$enfermera, $personal, $residenteA, $jornada] = $this->crearEscenario();
        $residenteB = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_CUR_B',
            'nombres' => 'Residente',
            'apellido_paterno' => 'Herida B',
            'fecha_nacimiento' => '1945-01-01',
            'estado' => 'ACTIVO',
        ]);

        $heridaA = Herida::query()->create([
            'cod_herida' => 'HER_A_001',
            'cod_residente' => $residenteA->cod_residente,
            'cod_personal' => $personal->cod_personal,
            'tipo_herida' => 'QUIRURGICA',
            'ubicacion' => 'Abdomen',
            'fecha_hora_identificacion' => now(),
            'estado' => 'ACTIVA',
        ]);

        $heridaB = Herida::query()->create([
            'cod_herida' => 'HER_B_001',
            'cod_residente' => $residenteB->cod_residente,
            'cod_personal' => $personal->cod_personal,
            'tipo_herida' => 'UPP',
            'ubicacion' => 'Sacro',
            'fecha_hora_identificacion' => now(),
            'estado' => 'ACTIVA',
        ]);

        CuracionHerida::query()->create([
            'cod_curacion' => 'CUR_A_001',
            'cod_herida' => $heridaA->cod_herida,
            'cod_personal' => $personal->cod_personal,
            'cod_jornada' => $jornada->cod_jornada,
            'fecha_hora' => now(),
            'procedimiento' => 'Limpieza y cura oclusiva',
            'dolor' => '3',
        ]);

        $curacionesA = $residenteA->curaciones()->get();
        $this->assertTrue($curacionesA->contains('cod_curacion', 'CUR_A_001'));
        $this->assertCount(1, $curacionesA);

        $curacionesB = $residenteB->curaciones()->get();
        $this->assertFalse($curacionesB->contains('cod_curacion', 'CUR_A_001'));
        $this->assertCount(0, $curacionesB);
    }

    public function test_no_se_puede_ejecutar_intervencion_cuyo_plan_pertenece_a_otro_residente(): void
    {
        [$enfermera, $personal, $residenteA, $jornada] = $this->crearEscenario();
        $this->actingAs($enfermera);

        $residenteB = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_CROSS_B',
            'nombres' => 'Residente',
            'apellido_paterno' => 'Cross B',
            'fecha_nacimiento' => '1945-01-01',
            'estado' => 'ACTIVO',
        ]);

        $planB = \App\Models\PlanCuidado::query()->create([
            'cod_plan' => 'PLC_CROSS_B',
            'cod_residente' => $residenteB->cod_residente,
            'cod_personal' => $personal->cod_personal,
            'estado' => 'ACTIVO',
        ]);

        $intervencionB = \App\Models\IntervencionCuidado::query()->create([
            'cod_intervencion' => 'INT_CROSS_B',
            'cod_plan' => $planB->cod_plan,
            'nombre' => 'Cambio de posicion',
            'descripcion' => 'Cambio postural cada 2h',
            'estado' => 'ACTIVA',
        ]);

        $component = new RegistrosEnfermeria();
        $component->codResidente = $residenteA->cod_residente;
        $component->tipo = 'PROCEDIMIENTO';
        $component->subtipo = 'Postural';
        $component->intervencionId = $intervencionB->cod_intervencion;

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('La intervenci');

        $component->guardarCuidado();

        $this->assertDatabaseMissing('ejecuciones_cuidado', [
            'cod_residente' => $residenteA->cod_residente,
            'cod_intervencion' => $intervencionB->cod_intervencion,
        ]);
    }

    public function test_ejecucion_cuidado_rechaza_arbitrariedad_si_no_hay_intervencion_activa(): void
    {
        [$enfermera, , $residente] = $this->crearEscenario();
        $this->actingAs($enfermera);

        $component = new RegistrosEnfermeria();
        $component->codResidente = $residente->cod_residente;
        $component->tipo = 'HIGIENE';
        $component->subtipo = 'Aseo matutino';

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('El residente no cuenta con un plan de cuidados activo');

        $component->guardarCuidado();

        $this->assertDatabaseMissing('ejecuciones_cuidado', [
            'cod_residente' => $residente->cod_residente,
        ]);
    }

    public function test_administracion_medicacion_clasificacion_valores_reales(): void
    {
        $adm = new AdministracionMedicacion(['resultado' => 'ADMINISTRADA']);
        $this->assertTrue($adm->esAdministrada());
        $this->assertFalse($adm->esOmitida());
        $this->assertFalse($adm->esRechazada());

        $admRealizada = new AdministracionMedicacion(['resultado' => 'REALIZADA']);
        $this->assertTrue($admRealizada->esAdministrada());
        $this->assertFalse($admRealizada->esOmitida());

        $admOmitida = new AdministracionMedicacion(['resultado' => 'OMITIDA']);
        $this->assertFalse($admOmitida->esAdministrada());
        $this->assertTrue($admOmitida->esOmitida());
        $this->assertFalse($admOmitida->esRechazada());

        $admRechazada = new AdministracionMedicacion(['resultado' => 'RECHAZADA']);
        $this->assertFalse($admRechazada->esAdministrada());
        $this->assertFalse($admRechazada->esOmitida(), 'RECHAZADA no debe clasificarse como OMITIDA.');
        $this->assertTrue($admRechazada->esRechazada());
    }
}
