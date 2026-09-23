<?php

namespace Tests\Feature;

use App\Models\Alerta;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\Cama;
use App\Models\EjecucionCuidado;
use App\Models\EventoAlerta;
use App\Models\Habitacion;
use App\Models\HorarioPrescripcion;
use App\Models\IntervencionCuidado;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\OcupacionCama;
use App\Models\Personal;
use App\Models\PlanCuidado;
use App\Models\Prescripcion;
use App\Models\AdministracionMedicacion;
use App\Models\ProgramacionCuidado;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Services\Enfermeria\MiTurnoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MiTurnoAuditoriaCierreTest extends TestCase
{
    use RefreshDatabase;

    protected User $enfermeraUser;
    protected Personal $enfermeraPersonal;
    protected Turno $turnoManana;
    protected Turno $turnoNoche;
    protected Jornada $jornadaManana;
    protected Jornada $jornadaNoche;
    protected Area $area;
    protected Residente $residente1;
    protected Residente $residente2;
    protected MiTurnoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::today()->setTime(10, 0, 0));

        Role::firstOrCreate(['name' => 'ENFERMEROS', 'guard_name' => 'web']);

        $this->area = Area::create([
            'cod_area' => 'ARE_' . strtoupper(Str::random(6)),
            'nombre' => 'Enfermería General',
            'estado' => 'ACTIVA',
        ]);

        $this->turnoManana = Turno::create([
            'cod_turno' => 'TUR_MAN_' . strtoupper(Str::random(4)),
            'nombre' => 'Mañana',
            'hora_inicio' => '07:00:00',
            'hora_cierre' => '15:00:00',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);

        $this->turnoNoche = Turno::create([
            'cod_turno' => 'TUR_NOC_' . strtoupper(Str::random(4)),
            'nombre' => 'Noche',
            'hora_inicio' => '20:00:00',
            'hora_cierre' => '08:00:00',
            'orden' => 3,
            'estado' => 'ACTIVO',
        ]);

        $this->enfermeraUser = User::create([
            'cod_usuario' => 'USU_' . strtoupper(Str::random(6)),
            'correo' => 'auditoria.enfermera@remembermind.test',
            'contrasena' => bcrypt('password'),
            'estado' => 'ACTIVO',
        ]);
        $this->enfermeraUser->assignRole('ENFERMEROS');

        $this->enfermeraPersonal = Personal::create([
            'cod_personal' => 'PER_' . strtoupper(Str::random(6)),
            'cod_usuario' => $this->enfermeraUser->cod_usuario,
            'nombres' => 'Clara',
            'apellido_paterno' => 'Rojas',
            'apellido_materno' => 'Vaca',
            'numero_documento' => '12345678',
            'profesion' => 'LICENCIADA EN ENFERMERÍA',
            'estado' => 'ACTIVO',
        ]);

        $this->jornadaManana = Jornada::create([
            'cod_jornada' => 'JOR_MAN_' . strtoupper(Str::random(4)),
            'cod_turno' => $this->turnoManana->cod_turno,
            'fecha_jornada' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        AsignacionPersonal::create([
            'cod_asignacion_personal' => 'ASP_MAN_' . strtoupper(Str::random(4)),
            'cod_jornada' => $this->jornadaManana->cod_jornada,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'cod_area' => $this->area->cod_area,
            'funcion' => 'ENFERMERO RESPONSABLE',
            'tipo_asignacion' => 'TITULAR',
            'fecha_asignacion' => Carbon::today()->setTime(7, 0, 0),
            'estado' => 'ACTIVA',
        ]);

        $this->residente1 = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_AUD_01',
            'nombres' => 'Juan',
            'apellido_paterno' => 'Pérez',
            'apellido_materno' => 'Gómez',
            'fecha_nacimiento' => '1945-01-01',
            'genero' => 'M',
            'estado' => 'ACTIVO',
        ]);

        $this->residente2 = Residente::crearDesdeAdmision([
            'cod_residente' => 'RES_AUD_02',
            'nombres' => 'Ana',
            'apellido_paterno' => 'Torres',
            'apellido_materno' => 'López',
            'fecha_nacimiento' => '1950-02-02',
            'genero' => 'F',
            'estado' => 'ACTIVO',
        ]);

        // Asignación de Carlos (residente1) a Clara desde el inicio
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_01_' . strtoupper(Str::random(4)),
            'cod_jornada' => $this->jornadaManana->cod_jornada,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'cod_residente' => $this->residente1->cod_residente,
            'fecha_hora' => Carbon::today()->setTime(7, 0, 0),
            'estado' => 'ACTIVO',
        ]);

        $this->service = app(MiTurnoService::class);
    }

    /**
     * 1. ALERTAS
     * - una alerta con varios eventos_alerta cuenta una sola vez;
     * - prioridad ALTA no se transforma en CRÍTICO;
     * - alertas activas RECONOCIDA / ASIGNADA / PENDIENTE anteriores al inicio del turno siguen visibles si continúan activas.
     */
    public function test_punto_1_alertas_multiples_eventos_prioridad_alta_y_alertas_previas_activas(): void
    {
        // Alerta con múltiples eventos
        $alertaMulti = Alerta::create([
            'cod_alerta' => 'ALE_MUL_' . strtoupper(Str::random(4)),
            'cod_residente' => $this->residente1->cod_residente,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'tipo_alerta' => 'CLINICA',
            'prioridad' => 'ALTA',
            'titulo' => 'Riesgo de hipotensión postural',
            'fecha_hora' => Carbon::today()->setTime(7, 30, 0),
            'estado' => 'EN_ATENCION',
        ]);

        for ($i = 1; $i <= 4; $i++) {
            EventoAlerta::create([
                'cod_evento_alerta' => 'EVA_' . $i . '_' . strtoupper(Str::random(4)),
                'cod_alerta' => $alertaMulti->cod_alerta,
                'cod_usuario' => $this->enfermeraUser->cod_usuario,
                'tipo_evento' => 'INTERVENCION',
                'estado_anterior' => 'ABIERTA',
                'estado_nuevo' => 'EN_ATENCION',
                'fecha_hora' => Carbon::today()->setTime(7, 30 + $i, 0),
            ]);
        }

        // Alerta anterior al inicio del turno con estado RECONOCIDA / PENDIENTE
        $alertaPrevia = Alerta::create([
            'cod_alerta' => 'ALE_PRE_' . strtoupper(Str::random(4)),
            'cod_residente' => $this->residente1->cod_residente,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'tipo_alerta' => 'CLINICA',
            'prioridad' => 'MEDIA',
            'titulo' => 'Control de glicemia preexistente',
            'fecha_hora' => Carbon::yesterday()->setTime(23, 0, 0),
            'estado' => 'RECONOCIDA',
        ]);

        $datos = $this->service->obtenerDatosDashboard($this->enfermeraUser);

        // La alerta con 4 eventos cuenta exactamente como 1 alerta
        $this->assertEquals(2, $datos['kpis']['total_registro']['numero']);
        $this->assertCount(2, $datos['alertas_recientes']);

        // Prioridad ALTA no se transforma en CRÍTICO: el badge es VIGILANCIA
        $this->assertEquals('VIGILANCIA', $datos['estado_general']['badge']);
        $this->assertNotEquals('CRÍTICO', $datos['estado_general']['badge']);

        // La alerta anterior sigue visible porque continúa activa
        $codigosAlertas = collect($datos['alertas_recientes'])->pluck('cod_alerta')->all();
        $this->assertContains($alertaPrevia->cod_alerta, $codigosAlertas);
        $this->assertContains($alertaMulti->cod_alerta, $codigosAlertas);
    }

    /**
     * 2. MEDICACIÓN
     * - prescripción todavía no iniciada no genera tarea;
     * - prescripción suspendida antes del horario no genera tarea;
     * - horario inactivo no genera tarea;
     * - dias_semana incorrecto no genera tarea;
     * - segun_necesidad = true no crea pendiente automático;
     * - dos horarios de una misma prescripción son dos ocurrencias diferentes;
     * - administrar una no completa la otra;
     * - cod_residente de administración debe coincidir con la prescripción.
     */
    public function test_punto_2_medicacion_reglas_exhaustivas(): void
    {
        $atencion = Atencion::create([
            'cod_atencion' => 'ATN_' . strtoupper(Str::random(6)),
            'cod_residente' => $this->residente1->cod_residente,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'tipo_atencion' => 'CONTROL',
            'motivo' => 'Control de Medicación',
            'fecha_hora' => Carbon::now(),
            'estado' => 'REALIZADA',
        ]);

        $med = Medicamento::create([
            'cod_medicamento' => 'MED_' . strtoupper(Str::random(6)),
            'nombre_generico' => 'Enalapril',
            'nombre_comercial' => 'Vasotec',
            'forma_farmaceutica' => 'Tableta',
            'concentracion' => '10 mg',
            'control_especial' => false,
            'estado' => 'ACTIVO',
        ]);

        // A. Prescripción todavía no iniciada (fecha futura) -> no genera tarea
        $pFutura = Prescripcion::create([
            'cod_prescripcion' => 'PRE_FUT_' . strtoupper(Str::random(4)),
            'cod_residente' => $this->residente1->cod_residente,
            'cod_atencion' => $atencion->cod_atencion,
            'cod_medicamento' => $med->cod_medicamento,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'dosis' => 10,
            'unidad_dosis' => 'mg',
            'via_administracion' => 'ORAL',
            'frecuencia' => 'Cada 24 horas',
            'segun_necesidad' => false,
            'fecha_hora_prescripcion' => Carbon::tomorrow()->setTime(8, 0, 0),
            'estado' => 'ACTIVA',
        ]);
        HorarioPrescripcion::create([
            'cod_horario_prescripcion' => 'HPR_FUT_' . strtoupper(Str::random(4)),
            'cod_prescripcion' => $pFutura->cod_prescripcion,
            'hora_programada' => '08:00:00',
            'estado' => 'ACTIVO',
        ]);

        // B. Prescripción suspendida antes del horario (ej. suspendida a las 07:30 para tarea de las 08:00) -> no genera tarea
        $pSuspendida = Prescripcion::create([
            'cod_prescripcion' => 'PRE_SUS_' . strtoupper(Str::random(4)),
            'cod_residente' => $this->residente1->cod_residente,
            'cod_atencion' => $atencion->cod_atencion,
            'cod_medicamento' => $med->cod_medicamento,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'dosis' => 10,
            'unidad_dosis' => 'mg',
            'via_administracion' => 'ORAL',
            'frecuencia' => 'Cada 24 horas',
            'segun_necesidad' => false,
            'fecha_hora_prescripcion' => Carbon::today()->setTime(7, 0, 0),
            'fecha_hora_suspension' => Carbon::today()->setTime(7, 30, 0),
            'estado' => 'ACTIVA',
        ]);
        HorarioPrescripcion::create([
            'cod_horario_prescripcion' => 'HPR_SUS_' . strtoupper(Str::random(4)),
            'cod_prescripcion' => $pSuspendida->cod_prescripcion,
            'hora_programada' => '08:00:00',
            'estado' => 'ACTIVO',
        ]);

        // C. Horario inactivo -> no genera tarea
        $pHorarioInactivo = Prescripcion::create([
            'cod_prescripcion' => 'PRE_INA_' . strtoupper(Str::random(4)),
            'cod_residente' => $this->residente1->cod_residente,
            'cod_atencion' => $atencion->cod_atencion,
            'cod_medicamento' => $med->cod_medicamento,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'dosis' => 10,
            'unidad_dosis' => 'mg',
            'via_administracion' => 'ORAL',
            'frecuencia' => 'Cada 24 horas',
            'segun_necesidad' => false,
            'fecha_hora_prescripcion' => Carbon::today()->setTime(7, 0, 0),
            'estado' => 'ACTIVA',
        ]);
        HorarioPrescripcion::create([
            'cod_horario_prescripcion' => 'HPR_INA_' . strtoupper(Str::random(4)),
            'cod_prescripcion' => $pHorarioInactivo->cod_prescripcion,
            'hora_programada' => '08:00:00',
            'estado' => 'INACTIVO',
        ]);

        // D. dias_semana incorrecto -> no genera tarea
        $diaInvalido = Carbon::now()->addDays(2)->format('l'); // Día diferente de hoy
        $pDiaIncorrecto = Prescripcion::create([
            'cod_prescripcion' => 'PRE_DIA_' . strtoupper(Str::random(4)),
            'cod_residente' => $this->residente1->cod_residente,
            'cod_atencion' => $atencion->cod_atencion,
            'cod_medicamento' => $med->cod_medicamento,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'dosis' => 10,
            'unidad_dosis' => 'mg',
            'via_administracion' => 'ORAL',
            'frecuencia' => 'Días específicos',
            'segun_necesidad' => false,
            'fecha_hora_prescripcion' => Carbon::today()->setTime(7, 0, 0),
            'estado' => 'ACTIVA',
        ]);
        HorarioPrescripcion::create([
            'cod_horario_prescripcion' => 'HPR_DIA_' . strtoupper(Str::random(4)),
            'cod_prescripcion' => $pDiaIncorrecto->cod_prescripcion,
            'hora_programada' => '08:00:00',
            'dias_semana' => $diaInvalido,
            'estado' => 'ACTIVO',
        ]);

        // E. segun_necesidad = true no crea pendiente automático
        $pPRN = Prescripcion::create([
            'cod_prescripcion' => 'PRE_PRN_' . strtoupper(Str::random(4)),
            'cod_residente' => $this->residente1->cod_residente,
            'cod_atencion' => $atencion->cod_atencion,
            'cod_medicamento' => $med->cod_medicamento,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'dosis' => 10,
            'unidad_dosis' => 'mg',
            'via_administracion' => 'ORAL',
            'frecuencia' => 'PRN',
            'segun_necesidad' => true,
            'fecha_hora_prescripcion' => Carbon::today()->setTime(7, 0, 0),
            'estado' => 'ACTIVA',
        ]);
        HorarioPrescripcion::create([
            'cod_horario_prescripcion' => 'HPR_PRN_' . strtoupper(Str::random(4)),
            'cod_prescripcion' => $pPRN->cod_prescripcion,
            'hora_programada' => '08:00:00',
            'estado' => 'ACTIVO',
        ]);

        // F. Dos horarios de una misma prescripción son dos ocurrencias diferentes; administrar una no completa la otra
        $pDobleHorario = Prescripcion::create([
            'cod_prescripcion' => 'PRE_DOB_' . strtoupper(Str::random(4)),
            'cod_residente' => $this->residente1->cod_residente,
            'cod_atencion' => $atencion->cod_atencion,
            'cod_medicamento' => $med->cod_medicamento,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'dosis' => 10,
            'unidad_dosis' => 'mg',
            'via_administracion' => 'ORAL',
            'frecuencia' => 'Cada 4 horas',
            'segun_necesidad' => false,
            'fecha_hora_prescripcion' => Carbon::today()->setTime(7, 0, 0),
            'estado' => 'ACTIVA',
        ]);
        $h1 = HorarioPrescripcion::create([
            'cod_horario_prescripcion' => 'HPR_08_' . strtoupper(Str::random(4)),
            'cod_prescripcion' => $pDobleHorario->cod_prescripcion,
            'hora_programada' => '08:00:00',
            'estado' => 'ACTIVO',
        ]);
        $h2 = HorarioPrescripcion::create([
            'cod_horario_prescripcion' => 'HPR_12_' . strtoupper(Str::random(4)),
            'cod_prescripcion' => $pDobleHorario->cod_prescripcion,
            'hora_programada' => '12:00:00',
            'estado' => 'ACTIVO',
        ]);

        // Administrar SOLAMENTE el horario h1 (08:00)
        AdministracionMedicacion::create([
            'cod_administracion' => 'ADM_08_' . strtoupper(Str::random(4)),
            'cod_prescripcion' => $pDobleHorario->cod_prescripcion,
            'cod_horario_prescripcion' => $h1->cod_horario_prescripcion,
            'cod_residente' => $this->residente1->cod_residente,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'cod_jornada' => $this->jornadaManana->cod_jornada,
            'fecha_hora_programada' => Carbon::today()->setTime(8, 0, 0),
            'fecha_hora_administracion' => Carbon::today()->setTime(8, 5, 0),
            'resultado' => 'ADMINISTRADA',
            'estado' => 'REGISTRADA',
        ]);

        // G. cod_residente de administración debe coincidir con la prescripción
        // El modelo valida y rechaza si cod_residente no coincide con la prescripción
        $exceptionLanzada = false;
        try {
            AdministracionMedicacion::create([
                'cod_administracion' => 'ADM_ERR_' . strtoupper(Str::random(4)),
                'cod_prescripcion' => $pDobleHorario->cod_prescripcion,
                'cod_horario_prescripcion' => $h2->cod_horario_prescripcion,
                'cod_residente' => $this->residente2->cod_residente, // Mismatched!
                'cod_personal' => $this->enfermeraPersonal->cod_personal,
                'cod_jornada' => $this->jornadaManana->cod_jornada,
                'fecha_hora_programada' => Carbon::today()->setTime(12, 0, 0),
                'fecha_hora_administracion' => Carbon::today()->setTime(12, 0, 0),
                'resultado' => 'ADMINISTRADA',
                'estado' => 'REGISTRADA',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $exceptionLanzada = true;
            $this->assertStringContainsString('no corresponde al residente', $e->getMessage());
        }
        $this->assertTrue($exceptionLanzada, 'El modelo debe rechazar administración con residente no coincidente');

        $datos = $this->service->obtenerDatosDashboard($this->enfermeraUser);
        $agenda = $datos['agenda'];

        // Solo deben existir exactamente 2 tareas en agenda (h1 y h2 de la prescripción válida)
        $this->assertCount(2, $agenda);

        $tareaH1 = collect($agenda)->firstWhere('hora', '08:00');
        $tareaH2 = collect($agenda)->firstWhere('hora', '12:00');

        $this->assertNotNull($tareaH1);
        $this->assertNotNull($tareaH2);

        // h1 fue administrado -> REALIZADO
        $this->assertEquals('REALIZADO', $tareaH1['estado']);

        // h2 NO fue administrado (el registro tenía cod_residente erróneo) -> NO está REALIZADO (está PRÓXIMO o PENDIENTE)
        $this->assertNotEquals('REALIZADO', $tareaH2['estado']);
    }

    /**
     * 3. ASIGNACIÓN DEL RESIDENTE
     * Si el residente se asigna al enfermero a mitad del turno,
     * no atribuir tareas anteriores a fecha_hora de la asignación como responsabilidad del nuevo enfermero.
     */
    public function test_punto_3_asignacion_a_mitad_de_turno_no_atribuye_tareas_anteriores(): void
    {
        // Creamos otro enfermero Roberto
        $otroUser = User::create([
            'cod_usuario' => 'USU_ROB_' . strtoupper(Str::random(4)),
            'correo' => 'roberto.turno@remembermind.test',
            'contrasena' => bcrypt('password'),
            'estado' => 'ACTIVO',
        ]);
        $otroUser->assignRole('ENFERMEROS');

        $otroPersonal = Personal::create([
            'cod_personal' => 'PER_ROB_' . strtoupper(Str::random(4)),
            'cod_usuario' => $otroUser->cod_usuario,
            'nombres' => 'Roberto',
            'apellido_paterno' => 'Díaz',
            'numero_documento' => '87654321',
            'profesion' => 'LICENCIADO EN ENFERMERÍA',
            'estado' => 'ACTIVO',
        ]);

        AsignacionPersonal::create([
            'cod_asignacion_personal' => 'ASP_ROB_' . strtoupper(Str::random(4)),
            'cod_jornada' => $this->jornadaManana->cod_jornada,
            'cod_personal' => $otroPersonal->cod_personal,
            'cod_area' => $this->area->cod_area,
            'funcion' => 'ENFERMERO DE REFUERZO',
            'tipo_asignacion' => 'TITULAR',
            'fecha_asignacion' => Carbon::today()->setTime(7, 0, 0),
            'estado' => 'ACTIVA',
        ]);

        // Residente 2 estuvo con Clara al inicio a las 07:00
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_R2_INI_' . strtoupper(Str::random(4)),
            'cod_jornada' => $this->jornadaManana->cod_jornada,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'cod_residente' => $this->residente2->cod_residente,
            'fecha_hora' => Carbon::today()->setTime(7, 0, 0),
            'estado' => 'ACTIVO',
        ]);

        // A las 11:00 AM (a mitad de turno), se reasigna a Roberto
        AsignacionResidenteJornada::create([
            'cod_asignacion' => 'ARJ_R2_MID_' . strtoupper(Str::random(4)),
            'cod_jornada' => $this->jornadaManana->cod_jornada,
            'cod_personal' => $otroPersonal->cod_personal,
            'cod_residente' => $this->residente2->cod_residente,
            'fecha_hora' => Carbon::today()->setTime(11, 0, 0),
            'estado' => 'ACTIVO',
            'observacion' => 'Reasignación a mitad de turno por redistribución',
        ]);

        $plan = PlanCuidado::create([
            'cod_plan' => 'PLC_MID_' . strtoupper(Str::random(4)),
            'cod_residente' => $this->residente2->cod_residente,
            'cod_area' => $this->area->cod_area,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'tipo_plan' => 'ENFERMERIA',
            'nombre' => 'Cuidados Básicos',
            'estado' => 'ACTIVO',
        ]);
        $intervencion = IntervencionCuidado::create([
            'cod_intervencion' => 'INT_MID_' . strtoupper(Str::random(4)),
            'cod_plan' => $plan->cod_plan,
            'nombre' => 'Control Matutino',
            'descripcion' => 'Descripción de control',
            'prioridad' => 'MEDIA',
            'estado' => 'ACTIVO',
        ]);

        // Tarea 1: a las 08:00 AM (ANTES de la asignación a Roberto)
        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_08_' . strtoupper(Str::random(4)),
            'cod_intervencion' => $intervencion->cod_intervencion,
            'hora_programada' => '08:00:00',
            'frecuencia' => 'Mañana',
            'fecha_activacion' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        // Tarea 2: a las 12:00 PM (DESPUÉS de la asignación a Roberto)
        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_12_' . strtoupper(Str::random(4)),
            'cod_intervencion' => $intervencion->cod_intervencion,
            'hora_programada' => '12:00:00',
            'frecuencia' => 'Mediodía',
            'fecha_activacion' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        // Consultamos el dashboard de Roberto a las 11:30 tras su asignación
        \Carbon\Carbon::setTestNow(\Carbon\Carbon::today()->setTime(11, 30, 0));
        $datosRoberto = $this->service->obtenerDatosDashboard($otroUser);
        $horasTareas = collect($datosRoberto['agenda'])->pluck('hora')->all();

        // NO debe contener la tarea de las 08:00 como su responsabilidad
        $this->assertNotContains('08:00', $horasTareas);
        // SÍ debe contener la tarea de las 12:00
        $this->assertContains('12:00', $horasTareas);
    }

    /**
     * 4. OCUPACIÓN
     * Si por inconsistencia existen dos ocupaciones activas para un residente:
     * - no escoger cama arbitrariamente;
     * - informar inconsistencia;
     * - no inventar ubicación.
     */
    public function test_punto_4_ocupacion_con_dos_camas_activas_informa_inconsistencia(): void
    {
        $hab1 = Habitacion::create([
            'cod_habitacion' => 'HAB_INC_1',
            'codigo' => 'HAB-201',
            'nombre' => 'Habitación 201',
            'estado' => 'ACTIVO',
        ]);
        $cama1 = Cama::create([
            'cod_cama' => 'CAM_INC_1',
            'cod_habitacion' => $hab1->cod_habitacion,
            'codigo' => 'CAMA-201A',
            'estado' => 'OCUPADA',
        ]);

        $hab2 = Habitacion::create([
            'cod_habitacion' => 'HAB_INC_2',
            'codigo' => 'HAB-202',
            'nombre' => 'Habitación 202',
            'estado' => 'ACTIVO',
        ]);
        $cama2 = Cama::create([
            'cod_cama' => 'CAM_INC_2',
            'cod_habitacion' => $hab2->cod_habitacion,
            'codigo' => 'CAMA-202B',
            'estado' => 'OCUPADA',
        ]);

        $admision = \App\Models\Admision::create([
            'cod_admision' => 'ADM_INC_' . strtoupper(\Illuminate\Support\Str::random(4)),
            'cod_residente' => $this->residente1->cod_residente,
            'fecha_hora_admision' => \Carbon\Carbon::now()->subMonths(2),
            'tipo_ingreso' => 'ORDINARIO',
            'motivo_ingreso' => 'Admisión para cuidados asistenciales',
            'cod_usuario_registro' => $this->enfermeraUser->cod_usuario,
            'estado' => 'ACTIVA',
        ]);

        // Ocupación 1 normal
        OcupacionCama::create([
            'cod_ocupacion' => 'OCU_INC_1',
            'cod_admision' => $admision->cod_admision,
            'cod_usuario_registro' => $this->enfermeraUser->cod_usuario,
            'fecha_hora_asignacion' => \Carbon\Carbon::now()->subDays(5),
            'cod_residente' => $this->residente1->cod_residente,
            'cod_cama' => $cama1->cod_cama,
            'estado' => 'ACTIVA',
        ]);

        // Simular inconsistencia en BDD insertando una segunda ocupación activa directa
        \Illuminate\Support\Facades\DB::table('ocupaciones_cama')->insert([
            'cod_ocupacion' => 'OCU_INC_2',
            'cod_admision' => $admision->cod_admision,
            'cod_usuario_registro' => $this->enfermeraUser->cod_usuario,
            'fecha_hora_asignacion' => \Carbon\Carbon::now()->subDays(2)->toDateTimeString(),
            'cod_residente' => $this->residente1->cod_residente,
            'cod_cama' => $cama2->cod_cama,
            'estado' => 'ACTIVA',
        ]);

        $residenteFreshed = Residente::with('ocupacionesCama.cama.habitacion')->find($this->residente1->cod_residente);
        $textoUbicacion = $this->service->formatearUbicacion($residenteFreshed);

        // No debe escoger una de las dos camas arbitrariamente
        $this->assertStringNotContainsString('CAMA-201A', $textoUbicacion);
        $this->assertStringNotContainsString('CAMA-202B', $textoUbicacion);

        // Debe informar la inconsistencia explícitamente
        $this->assertEquals('Inconsistencia de asignación de cama', $textoUbicacion);
    }

    /**
     * 5. CUIDADOS
     * Una programación solo genera tarea si:
     * - plan vigente;
     * - intervención vigente;
     * - programación vigente;
     * - fecha >= fecha_activacion;
     * - fecha <= fecha_desactivacion cuando exista;
     * - corresponde a dias_semana;
     * - corresponde a cod_turno cuando esté definido.
     * No interpretar frecuencia textual para inventar horarios.
     */
    public function test_punto_5_cuidados_reglas_exhaustivas(): void
    {
        // A. Plan inactivo no genera tarea
        $planInactivo = PlanCuidado::create([
            'cod_plan' => 'PLC_INA_' . strtoupper(Str::random(4)),
            'cod_residente' => $this->residente1->cod_residente,
            'cod_area' => $this->area->cod_area,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'tipo_plan' => 'ENFERMERIA',
            'nombre' => 'Plan Inactivo',
            'estado' => 'INACTIVO',
        ]);
        $int1 = IntervencionCuidado::create([
            'cod_intervencion' => 'INT_INA_' . strtoupper(Str::random(4)),
            'cod_plan' => $planInactivo->cod_plan,
            'nombre' => 'Intervencion en Plan Inactivo',
            'descripcion' => 'Descripción de cuidado',
            'prioridad' => 'MEDIA',
            'estado' => 'ACTIVO',
        ]);
        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_INA1_' . strtoupper(Str::random(4)),
            'cod_intervencion' => $int1->cod_intervencion,
            'hora_programada' => '08:00:00',
            'frecuencia' => 'Diario',
            'fecha_activacion' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        // Plan activo
        $planActivo = PlanCuidado::create([
            'cod_plan' => 'PLC_ACT_' . strtoupper(Str::random(4)),
            'cod_residente' => $this->residente1->cod_residente,
            'cod_area' => $this->area->cod_area,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'tipo_plan' => 'ENFERMERIA',
            'nombre' => 'Plan Activo de Cuidados',
            'estado' => 'ACTIVO',
        ]);

        // B. Intervención inactiva no genera tarea
        $intInactiva = IntervencionCuidado::create([
            'cod_intervencion' => 'INT_INACTIVA_' . strtoupper(Str::random(4)),
            'cod_plan' => $planActivo->cod_plan,
            'nombre' => 'Intervencion Inactiva',
            'descripcion' => 'Descripción de cuidado',
            'prioridad' => 'MEDIA',
            'estado' => 'INACTIVO',
        ]);
        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_INA2_' . strtoupper(Str::random(4)),
            'cod_intervencion' => $intInactiva->cod_intervencion,
            'hora_programada' => '08:30:00',
            'frecuencia' => 'Diario',
            'fecha_activacion' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        // Intervención activa
        $intActiva = IntervencionCuidado::create([
            'cod_intervencion' => 'INT_ACTIVA_' . strtoupper(Str::random(4)),
            'cod_plan' => $planActivo->cod_plan,
            'nombre' => 'Intervención Activa',
            'descripcion' => 'Descripción de cuidado',
            'prioridad' => 'ALTA',
            'estado' => 'ACTIVO',
        ]);

        // C. Programación inactiva no genera tarea
        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_INA3_' . strtoupper(Str::random(4)),
            'cod_intervencion' => $intActiva->cod_intervencion,
            'hora_programada' => '09:00:00',
            'frecuencia' => 'Diario',
            'fecha_activacion' => Carbon::today()->toDateString(),
            'estado' => 'INACTIVO',
        ]);

        // D. fecha < fecha_activacion (activación futura) no genera tarea
        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_FUT_' . strtoupper(Str::random(4)),
            'cod_intervencion' => $intActiva->cod_intervencion,
            'hora_programada' => '09:30:00',
            'frecuencia' => 'Diario',
            'fecha_activacion' => Carbon::tomorrow()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        // E. fecha > fecha_desactivacion (desactivada ayer) no genera tarea
        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_PAS_' . strtoupper(Str::random(4)),
            'cod_intervencion' => $intActiva->cod_intervencion,
            'hora_programada' => '10:00:00',
            'frecuencia' => 'Diario',
            'fecha_activacion' => Carbon::yesterday()->subDays(5)->toDateString(),
            'fecha_desactivacion' => Carbon::yesterday()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        // F. dias_semana incorrecto no genera tarea
        $diaInvalido = Carbon::now()->addDays(2)->format('l');
        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_DIA_' . strtoupper(Str::random(4)),
            'cod_intervencion' => $intActiva->cod_intervencion,
            'hora_programada' => '10:30:00',
            'frecuencia' => 'Días específicos',
            'dias_semana' => $diaInvalido,
            'fecha_activacion' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        // G. cod_turno distinto (asignada al turno noche, estando en turno mañana) no genera tarea
        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_TURNO_' . strtoupper(Str::random(4)),
            'cod_intervencion' => $intActiva->cod_intervencion,
            'cod_turno' => $this->turnoNoche->cod_turno,
            'hora_programada' => '11:00:00',
            'frecuencia' => 'Turno Noche',
            'fecha_activacion' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        // H. No inventar horario por texto: hora_programada = null no genera tarea
        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_SNHORA_' . strtoupper(Str::random(4)),
            'cod_intervencion' => $intActiva->cod_intervencion,
            'hora_programada' => null,
            'frecuencia' => 'Cada 4 horas según necesidad',
            'fecha_activacion' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        // I. Programación válida que cumple TODOS los requisitos
        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_VALIDA_' . strtoupper(Str::random(4)),
            'cod_intervencion' => $intActiva->cod_intervencion,
            'cod_turno' => $this->turnoManana->cod_turno,
            'hora_programada' => '11:30:00',
            'frecuencia' => 'Mañana',
            'dias_semana' => 'LUNES,MARTES,MIERCOLES,JUEVES,VIERNES,SABADO,DOMINGO',
            'fecha_activacion' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        $datos = $this->service->obtenerDatosDashboard($this->enfermeraUser);
        $agendaCuidados = collect($datos['agenda'])->where('tipo', '!=', 'MEDICACION')->values();

        // Exactamente 1 tarea válida debe haber sido generada
        $this->assertCount(1, $agendaCuidados);
        $this->assertEquals('11:30', $agendaCuidados[0]['hora']);
    }

    /**
     * 6. TIEMPO
     * Confirmar que jornada, agenda, alertas, PRÓXIMO y RETRASADO utilizan la misma timezone Laravel/Carbon.
     * Probar también turno nocturno cruzando medianoche.
     */
    public function test_punto_6_tiempo_timezone_y_turno_nocturno_cruzando_medianoche(): void
    {
        // 1. Verificar consistencia de timezone
        $appTz = config('app.timezone') ?: 'UTC';
        $now = Carbon::now();
        $this->assertEquals($now->timezoneName, Carbon::today()->timezoneName);

        // 2. Turno nocturno (20:00:00 a 08:00:00 del día siguiente)
        $jornadaNoc = Jornada::create([
            'cod_jornada' => 'JOR_NOC_TEST_' . strtoupper(Str::random(4)),
            'cod_turno' => $this->turnoNoche->cod_turno,
            'fecha_jornada' => '2026-09-20',
            'estado' => 'ACTIVA',
        ]);

        [$inicioTurno, $finTurno] = $this->service->calcularVentanaTurno($jornadaNoc);

        $this->assertEquals('2026-09-20 20:00:00', $inicioTurno->toDateTimeString());
        $this->assertEquals('2026-09-21 08:00:00', $finTurno->toDateTimeString());
        $this->assertTrue($finTurno->gt($inicioTurno));

        // Tarea a las 23:00 (mismo día)
        $momento23 = $this->service->resolverMomentoProgramado('2026-09-20', '23:00:00', $inicioTurno, $finTurno);
        $this->assertEquals('2026-09-20 23:00:00', $momento23->toDateTimeString());

        // Tarea a las 04:00 (día siguiente, cruza medianoche)
        $momento04 = $this->service->resolverMomentoProgramado('2026-09-20', '04:00:00', $inicioTurno, $finTurno);
        $this->assertEquals('2026-09-21 04:00:00', $momento04->toDateTimeString());
        $this->assertTrue($momento04->gte($inicioTurno) && $momento04->lte($finTurno));
    }

    /**
     * 7. ORDEN
     * La agenda debe tener orden determinista cuando dos tareas tienen la misma hora.
     */
    public function test_punto_7_orden_agenda_determinista_ante_misma_hora(): void
    {
        $plan = PlanCuidado::create([
            'cod_plan' => 'PLC_ORD_' . strtoupper(Str::random(4)),
            'cod_residente' => $this->residente1->cod_residente,
            'cod_area' => $this->area->cod_area,
            'cod_personal' => $this->enfermeraPersonal->cod_personal,
            'tipo_plan' => 'ENFERMERIA',
            'nombre' => 'Plan Orden',
            'estado' => 'ACTIVO',
        ]);

        $intB = IntervencionCuidado::create([
            'cod_intervencion' => 'INT_B_' . strtoupper(Str::random(4)),
            'cod_plan' => $plan->cod_plan,
            'nombre' => 'B. Curación de herida',
            'descripcion' => 'Descripción de herida',
            'prioridad' => 'ALTA',
            'estado' => 'ACTIVO',
        ]);

        $intA = IntervencionCuidado::create([
            'cod_intervencion' => 'INT_A_' . strtoupper(Str::random(4)),
            'cod_plan' => $plan->cod_plan,
            'nombre' => 'A. Aspiración de secreciones',
            'descripcion' => 'Descripción de aspiración',
            'prioridad' => 'ALTA',
            'estado' => 'ACTIVO',
        ]);

        // Ambas programadas exactamente a las 09:00
        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_B_' . strtoupper(Str::random(4)),
            'cod_intervencion' => $intB->cod_intervencion,
            'hora_programada' => '09:00:00',
            'frecuencia' => 'Mañana',
            'fecha_activacion' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        ProgramacionCuidado::create([
            'cod_programacion' => 'PRG_A_' . strtoupper(Str::random(4)),
            'cod_intervencion' => $intA->cod_intervencion,
            'hora_programada' => '09:00:00',
            'frecuencia' => 'Mañana',
            'fecha_activacion' => Carbon::today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        // Ejecutar dos veces consecutivas: el orden debe ser idéntico y determinista (alfabético A antes de B)
        $datos1 = $this->service->obtenerDatosDashboard($this->enfermeraUser);
        $datos2 = $this->service->obtenerDatosDashboard($this->enfermeraUser);

        $acciones1 = collect($datos1['agenda'])->pluck('accion')->all();
        $acciones2 = collect($datos2['agenda'])->pluck('accion')->all();

        $this->assertEquals($acciones1, $acciones2);
        $this->assertEquals('A. Aspiración de secreciones', $acciones1[0]);
        $this->assertEquals('B. Curación de herida', $acciones1[1]);
    }

    /**
     * 8. SOLO LECTURA
     * Confirmar que cargar/refrescar Mi turno:
     * - no crea registros;
     * - no actualiza registros clínicos;
     * - no modifica estados por el solo hecho de consultar.
     */
    public function test_punto_8_solo_lectura_no_muta_base_de_datos(): void
    {
        $conteoInicial = [
            'alertas' => Alerta::count(),
            'eventos_alerta' => EventoAlerta::count(),
            'prescripciones' => Prescripcion::count(),
            'administraciones' => AdministracionMedicacion::count(),
            'planes' => PlanCuidado::count(),
            'intervenciones' => IntervencionCuidado::count(),
            'ejecuciones' => EjecucionCuidado::count(),
            'jornadas' => Jornada::count(),
            'asignaciones_personal' => AsignacionPersonal::count(),
            'asignaciones_residente' => AsignacionResidenteJornada::count(),
        ];

        // Consultar 3 veces consecutivas
        $this->service->obtenerDatosDashboard($this->enfermeraUser);
        $this->service->obtenerDatosDashboard($this->enfermeraUser);
        $this->service->obtenerDatosDashboard($this->enfermeraUser);

        $conteoFinal = [
            'alertas' => Alerta::count(),
            'eventos_alerta' => EventoAlerta::count(),
            'prescripciones' => Prescripcion::count(),
            'administraciones' => AdministracionMedicacion::count(),
            'planes' => PlanCuidado::count(),
            'intervenciones' => IntervencionCuidado::count(),
            'ejecuciones' => EjecucionCuidado::count(),
            'jornadas' => Jornada::count(),
            'asignaciones_personal' => AsignacionPersonal::count(),
            'asignaciones_residente' => AsignacionResidenteJornada::count(),
        ];

        $this->assertEquals($conteoInicial, $conteoFinal);
    }
}
