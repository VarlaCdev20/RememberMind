<?php

namespace Tests\Feature;

use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Frontend\Livewire\Compartido\Clinica\FichaPaciente;
use App\Frontend\Livewire\Enfermeria\Cuidados\DashboardTurno;
use App\Frontend\Livewire\Enfermeria\Cuidados\MisPacientes;
use App\Frontend\Livewire\Enfermeria\Cuidados\PaseTurnoPanel;
use App\Models\AdultoMayor;
use App\Models\Alerta;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\EjecucionCuidado;
use App\Models\IntervencionCuidado;
use App\Models\Jornada;
use App\Models\Medicamento;
use App\Models\PaseTurno;
use App\Models\Personal;
use App\Models\PlanCuidado;
use App\Models\Prescripcion;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class TurnoCompletoEnfermeroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-10 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_enfermero_sin_asignacion_recibe_403_al_intentar_operar(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class]);
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');
        $pacienteAjeno = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);

        $this->actingAs($enfermero);
        Livewire::test(FichaPaciente::class, ['adulto' => $pacienteAjeno->cod_residente])
            ->assertForbidden();
    }

    public function test_flujo_completo_de_turno_de_enfermero_con_alcance_estricto(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class]);

        // 1. Crear turnos de enfermería
        $turnoManana = TurnoEnfermeria::create([
            'orden' => 1,
            'nombre' => 'Mañana Operativa',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'estado' => 'ACTIVO',
        ]);
        $turnoTarde = TurnoEnfermeria::create([
            'orden' => 2,
            'nombre' => 'Tarde Operativa',
            'hora_inicio' => '15:00:00',
            'hora_fin' => '23:00:00',
            'estado' => 'ACTIVO',
        ]);

        // 2. Crear enfermeros
        $enfermero = User::factory()->create([
            'nombres' => 'Carla',
            'ap_paterno' => 'Encinas',
            'ap_materno' => 'Guardia',
            'estado' => 'ACTIVO',
        ]);
        $enfermero->assignRole('ENFERMEROS');

        $enfermeroReceptor = User::factory()->create([
            'nombres' => 'Roberto',
            'ap_paterno' => 'Mendoza',
            'ap_materno' => 'Relevo',
            'estado' => 'ACTIVO',
        ]);
        $enfermeroReceptor->assignRole('ENFERMEROS');

        // 3. Crear 2 adultos mayores: Asignado vs No Asignado
        $pacienteAsignado = AdultoMayor::factory()->create([
            'nombres' => 'Juan Asignado',
            'ap_paterno' => 'Pérez',
            'cod_est_adul' => 'EST_001',
        ]);

        $pacienteNoAsignado = AdultoMayor::factory()->create([
            'nombres' => 'Carlos Ajeno',
            'ap_paterno' => 'Gómez',
            'cod_est_adul' => 'EST_001',
        ]);
        $jornadaManana = Jornada::create([
            'cod_jornada' => 'JOR_FLUJO_MANANA',
            'cod_turno' => $turnoManana->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);
        $personalEnfermero = $enfermero->personal()->firstOrFail();
        $areaEnfermeria = Area::create([
            'cod_area' => 'ARE_FLUJO_ENF',
            'nombre' => 'Enfermería operativa',
            'estado' => 'ACTIVA',
        ]);

        // 4. Asignar ÚNICAMENTE al pacienteAsignado en AsignacionResidenteJornada
        AsignacionResidenteJornada::create([
            'cod_residente' => $pacienteAsignado->cod_residente,
            'cod_jornada' => $jornadaManana->cod_jornada,
            'cod_personal' => $personalEnfermero->cod_personal,
            'fecha_hora' => now(),
            'nivel_supervision' => 'ESTANDAR',
            'estado' => 'ACTIVA',
            'observacion' => 'Asignación de turno matutino',
        ]);
        AsignacionPersonal::create([
            'cod_jornada' => $jornadaManana->cod_jornada,
            'cod_personal' => $personalEnfermero->cod_personal,
            'cod_area' => $areaEnfermeria->cod_area,
            'fecha_asignacion' => now(),
        ]);

        $atencion = Atencion::create([
            'cod_residente' => $pacienteAsignado->cod_residente,
            'cod_area' => Area::query()->firstOrFail()->cod_area,
            'cod_personal' => $enfermero->personal()->firstOrFail()->cod_personal,
            'tipo_atencion' => 'PRESCRIPCION_MEDICA',
            'fecha_hora' => now(),
            'estado' => 'FINALIZADA',
        ]);

        // Medicación y Plan de cuidados para pacienteAsignado
        $med = Prescripcion::create([
            'cod_residente' => $pacienteAsignado->cod_residente,
            'cod_atencion' => $atencion->cod_atencion,
            'cod_medicamento' => Medicamento::create([
                'cod_medicamento' => 'MED_TURNO_TEST',
                'nombre_generico' => 'Enalapril',
                'nombre_comercial' => 'Enalapril 10mg',
                'forma_farmaceutica' => 'COMPRIMIDO',
                'concentracion' => '10 mg',
                'control_especial' => false,
                'estado' => 'ACTIVO',
            ])->cod_medicamento,
            'cod_personal' => $enfermero->personal()->firstOrFail()->cod_personal,
            'nombre_medicamento' => 'Enalapril 10mg',
            'dosis' => '1 comprimido',
            'frecuencia' => 'Cada 12 horas',
            'via_administracion' => 'Oral',
            'hora_programada' => '08:00',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        $plan = PlanCuidado::create([
            'cod_residente' => $pacienteAsignado->cod_residente,
            'cod_area' => $areaEnfermeria->cod_area,
            'cod_personal' => $personalEnfermero->cod_personal,
            'tipo_plan' => 'INICIAL',
            'prioridad' => 'ESTANDAR',
            'estado' => 'ACTIVO',
            'observacion' => 'ADMISION',
            'fecha_hora_apertura' => today(),
        ]);
        $intervencion = IntervencionCuidado::create([
            'cod_intervencion' => 'INT_FLUJO_MARCHA',
            'cod_plan' => $plan->cod_plan,
            'nombre' => 'Ejercicios de marcha asistida',
            'descripcion' => 'Movilización asistida durante el turno.',
            'prioridad' => 'MEDIA',
            'estado' => 'ACTIVA',
        ]);

        $tarea = EjecucionCuidado::create([
            'cod_intervencion' => $intervencion->cod_intervencion,
            'cod_residente' => $pacienteAsignado->cod_residente,
            'cod_jornada' => $jornadaManana->cod_jornada,
            'cod_personal' => $personalEnfermero->cod_personal,
            'fecha_hora_programada' => today()->setTime(10, 0),
            'estado' => 'PENDIENTE',
        ]);

        // Autenticar como enfermero del turno
        $this->actingAs($enfermero);

        $this->get(route('dashboard'))->assertRedirect(route('admin.enfermeria.dashboard'));
        $this->get(route('admin.enfermeria.dashboard'))->assertOk()->assertSee('Juan Asignado');
        $this->get(route('admin.enfermeria.pacientes'))->assertOk()->assertSee('Juan Asignado');
        $this->get(route('admin.enfermeria.pacientes.ficha', ['adulto' => $pacienteAsignado, 'tab' => 'signos']))->assertOk()
            ->assertSee('Juan Asignado')->assertSee('Registrar signos');
        $this->get(route('admin.enfermeria.pacientes.ficha', ['adulto' => $pacienteAsignado, 'tab' => 'medicacion']))->assertOk()
            ->assertSee('Juan Asignado')->assertSee('Enalapril 10mg');
        $this->get(route('admin.enfermeria.pase-turno'))->assertOk()->assertSee('Pase de Turno');
        $this->get(route('admin.alertas-clinicas.index'))->assertOk()->assertSee('Alertas clínicas');

        // ─── PASO 1: VERIFICAR ALCANCE (VE SOLO SUS PACIENTES) ───────────────
        $service = app(TurnoEnfermeriaService::class);
        $this->assertTrue($service->esPacienteAsignado($pacienteAsignado, $enfermero));
        $this->assertFalse($service->esPacienteAsignado($pacienteNoAsignado, $enfermero));

        // En Mis Pacientes solo ve a Juan Asignado
        Livewire::test(MisPacientes::class)
            ->assertSee('Juan Asignado')
            ->assertDontSee('Carlos Ajeno');

        // En Dashboard ve al asignado y su tarea
        Livewire::test(DashboardTurno::class)
            ->assertSee('Juan Asignado')
            ->assertSee('Ejercicios de marcha asistida');

        // ─── PASO 2: ABRIR FICHA DEL PACIENTE ASIGNADO ─────────────────────────
        $ficha = Livewire::test(FichaPaciente::class, ['adulto' => $pacienteAsignado->cod_residente])
            ->assertOk()
            ->assertSee('Juan Asignado');

        $ficha->call('abrirModalSignos')->assertSet('modalSignos', true)
            ->call('cerrarModalSignos')->assertSet('modalSignos', false)
            ->call('abrirModalMedicacion', $med->cod_prescripcion)->assertSet('modalMed', true)
            ->call('cerrarModalMedicacion')->assertSet('modalMed', false)
            ->call('abrirModalSeguimiento')->assertSet('modalSeguimiento', true)
            ->call('cerrarModalSeguimiento')->assertSet('modalSeguimiento', false)
            ->call('abrirModalTarea', $tarea->cod_tarea)->assertSet('modalTarea', true)
            ->call('cerrarModalTarea')->assertSet('modalTarea', false);

        // ─── PASO 3: REGISTRAR SIGNOS VITALES ──────────────────────────────────
        $ficha->call('abrirRegistrarSignos')
            ->set('signoPA', '135/85')
            ->set('signoFC', 78)
            ->set('signoTemp', 36.8)
            ->set('signoSat', 97)
            ->set('signoGlucosa', 110)
            ->call('guardarSignos')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('signos_vitales', [
            'cod_residente' => $pacienteAsignado->cod_residente,
            'presion_sistolica' => 135,
            'presion_diastolica' => 85,
            'frecuencia_cardiaca' => 78,
            'temperatura' => 36.8,
        ]);

        // ─── PASO 4: OMITIR MEDICACIÓN (CON MOTIVO REAL) ──────────────────────
        $ficha->call('abrirAdministrarMed', $med->cod_prescripcion)
            ->set('medAccion', 'OMITIR')
            ->set('medAdministrado', false)
            ->set('medMotivoOmision', 'Paciente presenta náuseas y rechaza la toma oral matutina')
            ->call('confirmarAdministracionMed')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('administraciones_medicacion', [
            'cod_residente' => $pacienteAsignado->cod_residente,
            'cod_prescripcion' => $med->cod_prescripcion,
            'resultado' => 'OMITIDA',
            'motivo_omision' => 'Paciente presenta náuseas y rechaza la toma oral matutina',
        ]);

        // ─── PASO 5: EJECUTAR TAREA DE CUIDADOS ────────────────────────────────
        $ficha->call('completarTarea', $tarea->cod_tarea)
            ->assertHasNoErrors();

        $this->assertSame('REALIZADA', $tarea->fresh()->estado);
        $this->assertNotNull($tarea->fresh()->fecha_hora_ejecucion);

        // ─── PASO 6: REGISTRAR SEGUIMIENTO CON INCIDENTE / MÉDICO ─────────────
        $ficha->call('abrirRegistrarSeguimiento')
            ->set('segEstado', 'VIGILANCIA')
            ->set('segAlimentacion', 'PARCIAL')
            ->set('segMovilidad', 'ASISTIDA')
            ->set('segSueno', 'INTERRUMPIDO')
            ->set('segObs', 'Se observa marcha inestable y náuseas post-ingesta. Se solicita revisión médica.')
            ->set('segRequiereMedico', true)
            ->set('segIncidente', true)
            ->call('guardarSeguimiento')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('atenciones', [
            'cod_residente' => $pacienteAsignado->cod_residente,
            'tipo_atencion' => 'SEGUIMIENTO_DIARIO',
            'estado' => 'FINALIZADA',
            'observacion' => 'Se observa marcha inestable y náuseas post-ingesta. Se solicita revisión médica.',
        ]);

        // ─── PASO 7: VERIFICAR GENERACIÓN AUTOMÁTICA DE ALERTAS ────────────────
        $alertaGenerada = Alerta::where('cod_residente', $pacienteAsignado->cod_residente)
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
            ->first();
        $this->assertNotNull($alertaGenerada, 'Debe existir una alerta abierta para el paciente');

        // ─── PASO 8: ATENDER Y CERRAR ALERTA ───────────────────────────────────
        $ficha->call('abrirModalAtenderAlerta', $alertaGenerada->cod_alerta)
            ->set('accionTomadaAlerta', 'Se realiza valoración presencial y reposo asistido en cama.')
            ->call('guardarAtenderAlerta')
            ->assertHasNoErrors();

        $this->assertSame('EN_ATENCION', $alertaGenerada->fresh()->estado);

        $ficha->call('abrirModalCerrarAlerta', $alertaGenerada->cod_alerta)
            ->set('observacionCierreAlerta', 'Médico de turno evaluó y estabilizó al residente.')
            ->call('guardarCerrarAlerta')
            ->assertHasNoErrors();

        $this->assertSame('CERRADA', $alertaGenerada->fresh()->estado);
        $this->assertDatabaseHas('eventos_alerta', [
            'cod_alerta' => $alertaGenerada->cod_alerta,
            'cod_usuario' => $enfermero->cod_usuario,
            'tipo_evento' => 'CIERRE',
            'descripcion' => 'Médico de turno evaluó y estabilizó al residente.',
        ]);

        $ficha->call('cambiarTab', 'alertas')
            ->assertSee('Médico de turno evaluó y estabilizó al residente.');

        // ─── PASO 9: VERIFICAR HISTORIAL CLÍNICO INTEGRADO 360° ──────────────
        $ficha->call('cambiarTab', 'historial')
            ->assertSee('Cronología de Eventos Clínicos y Cuidados')
            ->assertSee('Control de Signos Vitales')
            ->assertSee('Evolución de Enfermería');

        $cronologia = $ficha->instance()->historialCronologico;
        $this->assertNotEmpty($cronologia);

        $tipos = collect($cronologia)->pluck('tipo')->unique()->values()->all();
        $this->assertContains('SIGNOS', $tipos);
        $this->assertContains('MEDICACION', $tipos);
        $this->assertContains('TAREA', $tipos);
        $this->assertContains('SEGUIMIENTO', $tipos);
        $this->assertContains('ALERTA', $tipos);
    }

    public function test_generar_y_recibir_pase_de_turno(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class]);

        $turnoManana = TurnoEnfermeria::create([
            'orden' => 1,
            'nombre' => 'Mañana Operativa',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'estado' => 'ACTIVO',
        ]);
        $turnoTarde = TurnoEnfermeria::create([
            'orden' => 2,
            'nombre' => 'Tarde Operativa',
            'hora_inicio' => '15:00:00',
            'hora_fin' => '23:00:00',
            'estado' => 'ACTIVO',
        ]);

        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');

        $enfermeroReceptor = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermeroReceptor->assignRole('ENFERMEROS');

        $personalSaliente = Personal::create([
            'cod_personal' => 'PER_PASE_SALIENTE',
            'cod_usuario' => $enfermero->cod_usuario,
            'nombres' => 'Enfermero',
            'apellido_paterno' => 'Saliente',
            'numero_documento' => 'PASE-SALIENTE',
            'profesion' => 'ENFERMERIA',
            'estado' => 'ACTIVO',
        ]);
        $personalEntrante = Personal::create([
            'cod_personal' => 'PER_PASE_ENTRANTE',
            'cod_usuario' => $enfermeroReceptor->cod_usuario,
            'nombres' => 'Enfermero',
            'apellido_paterno' => 'Entrante',
            'numero_documento' => 'PASE-ENTRANTE',
            'profesion' => 'ENFERMERIA',
            'estado' => 'ACTIVO',
        ]);
        $jornadaManana = Jornada::create([
            'cod_jornada' => 'JOR_PASE_MANANA',
            'cod_turno' => $turnoManana->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);
        $jornadaTarde = Jornada::create([
            'cod_jornada' => 'JOR_PASE_TARDE',
            'cod_turno' => $turnoTarde->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);
        $codArea = Area::create([
            'cod_area' => 'ARE_PASE_ENF',
            'nombre' => 'Enfermería de pases',
            'estado' => 'ACTIVA',
        ])->cod_area;

        $paciente = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);

        AsignacionResidenteJornada::create([
            'cod_residente' => $paciente->cod_residente,
            'cod_jornada' => $jornadaManana->cod_jornada,
            'cod_personal' => $personalSaliente->cod_personal,
            'fecha_hora' => now(),
            'nivel_supervision' => 'ESTANDAR',
            'estado' => 'ACTIVA',
            'observacion' => 'Asignación matutina',
        ]);
        AsignacionResidenteJornada::create([
            'cod_residente' => $paciente->cod_residente,
            'cod_jornada' => $jornadaTarde->cod_jornada,
            'cod_personal' => $personalEntrante->cod_personal,
            'fecha_hora' => now(),
            'nivel_supervision' => 'ESTANDAR',
            'estado' => 'ACTIVA',
            'observacion' => 'Asignación de relevo',
        ]);
        AsignacionPersonal::create([
            'cod_jornada' => $jornadaManana->cod_jornada,
            'cod_personal' => $personalSaliente->cod_personal,
            'cod_area' => $codArea,
            'fecha_asignacion' => now(),
        ]);

        $this->actingAs($enfermero);
        Livewire::test(PaseTurnoPanel::class)
            ->call('abrirGenerar')
            ->set('codResidente', $paciente->cod_residente)
            ->set('turnoSalienteId', $turnoManana->cod_turno)
            ->set('turnoEntranteId', $turnoTarde->cod_turno)
            ->set('enfermeroEntranteId', $enfermeroReceptor->cod_usuario)
            ->set('resumenTurno', 'Se entrega al residente con signos estables y controles del turno completados.')
            ->call('generarPase')
            ->assertHasNoErrors();

        $pase = PaseTurno::where('cod_residente', $paciente->cod_residente)->first();
        $this->assertNotNull($pase);
        $this->assertSame('GENERADO', $pase->estado);

        // Relevo entrante confirma la recepción de guardia
        $this->actingAs($enfermeroReceptor);
        Carbon::setTestNow('2026-09-10 16:00:00');
        AsignacionPersonal::create([
            'cod_jornada' => $jornadaTarde->cod_jornada,
            'cod_personal' => $personalEntrante->cod_personal,
            'cod_area' => $codArea,
            'fecha_asignacion' => now(),
        ]);
        Livewire::test(PaseTurnoPanel::class)
            ->call('recibirPase', $pase->cod_pase)
            ->assertHasNoErrors();

        $this->assertSame('RECIBIDO', $pase->fresh()->estado);
        $this->assertNotNull($pase->fresh()->fecha_recibido);
    }
}
