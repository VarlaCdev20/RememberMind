<?php

namespace Tests\Feature;

use App\Livewire\Cuidados\DashboardTurno;
use App\Livewire\Cuidados\FichaPaciente;
use App\Livewire\Cuidados\MisPacientes;
use App\Livewire\Cuidados\PaseTurnoPanel;
use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AsignacionTurnoAdulto;
use App\Models\MedicacionAdulto;
use App\Models\PaseTurno;
use App\Models\PlanCuidado;
use App\Models\RecepcionTurno;
use App\Models\SeguimientoDiario;
use App\Models\SignosVitalesAdulto;
use App\Models\TareaPlanCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use Database\Seeders\EstadoAdultoSeeder;
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
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');
        $pacienteAjeno = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);

        $this->actingAs($enfermero);
        Livewire::test(FichaPaciente::class, ['adulto' => $pacienteAjeno->cod_am])
            ->assertForbidden();
    }

    public function test_flujo_completo_de_turno_de_enfermero_con_alcance_estricto(): void
    {
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);

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

        // 4. Asignar ÚNICAMENTE al pacienteAsignado en AsignacionTurnoAdulto
        AsignacionTurnoAdulto::create([
            'cod_am' => $pacienteAsignado->cod_am,
            'cod_turno' => $turnoManana->cod_turno,
            'cod_usu_enfermero' => $enfermero->cod_usu,
            'fecha_inicio' => today()->toDateString(),
            'nivel_supervision' => 'ESTANDAR',
            'estado' => 'ACTIVA',
            'motivo_asignacion' => 'Asignación de turno matutino',
            'asignado_por' => $enfermero->cod_usu,
        ]);
        RecepcionTurno::create([
            'cod_turno' => $turnoManana->cod_turno,
            'cod_usuario' => $enfermero->cod_usu,
            'fecha_hora_recepcion' => now(),
        ]);

        // Medicación y Plan de cuidados para pacienteAsignado
        $med = MedicacionAdulto::create([
            'cod_am' => $pacienteAsignado->cod_am,
            'nombre_medicamento' => 'Enalapril 10mg',
            'dosis' => '1 comprimido',
            'frecuencia' => 'Cada 12 horas',
            'via_administracion' => 'Oral',
            'hora_programada' => '08:00',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        $plan = PlanCuidado::create([
            'cod_am' => $pacienteAsignado->cod_am,
            'tipo_plan' => 'INICIAL',
            'version' => 1,
            'nivel_cuidado' => 'ESTANDAR',
            'estado' => 'ACTIVO',
            'origen' => 'ADMISION',
            'fecha_inicio' => today(),
        ]);

        $tarea = TareaPlanCuidado::create([
            'cod_plan' => $plan->cod_plan,
            'cod_am' => $pacienteAsignado->cod_am,
            'cod_turno' => $turnoManana->cod_turno,
            'area' => 'MOVILIZACION',
            'titulo' => 'Ejercicios de marcha asistida',
            'fecha_programada' => today()->toDateString(),
            'hora_programada' => '10:00:00',
            'prioridad' => 'MEDIA',
            'estado' => 'PENDIENTE',
            'registrado_por' => $enfermero->cod_usu,
        ]);

        // Autenticar como enfermero del turno
        $this->actingAs($enfermero);

        $this->get(route('dashboard'))->assertRedirect(route('admin.enfermeria.dashboard'));
        $this->get(route('admin.enfermeria.dashboard'))->assertOk()->assertSee('Juan Asignado');
        $this->get(route('admin.enfermeria.pacientes'))->assertOk()->assertSee('Juan Asignado');
        $this->get(route('admin.enfermeria.pacientes.ficha', $pacienteAsignado))->assertOk()
            ->assertSee('Juan Asignado')->assertSee('Registrar signos')->assertSee('Enalapril 10mg');
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
        $ficha = Livewire::test(FichaPaciente::class, ['adulto' => $pacienteAsignado->cod_am])
            ->assertOk()
            ->assertSee('Juan Asignado');

        $ficha->call('abrirModalSignos')->assertSet('modalSignos', true)
            ->call('cerrarModalSignos')->assertSet('modalSignos', false)
            ->call('abrirModalMedicacion', $med->cod_med_adulto)->assertSet('modalMed', true)
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

        $this->assertDatabaseHas('signos_vitales_adulto', [
            'cod_am' => $pacienteAsignado->cod_am,
            'presion_arterial' => '135/85',
            'frecuencia_cardiaca' => 78,
            'temperatura' => 36.8,
        ]);

        // ─── PASO 4: OMITIR MEDICACIÓN (CON MOTIVO REAL) ──────────────────────
        $ficha->call('abrirAdministrarMed', $med->cod_med_adulto)
            ->set('medAccion', 'OMITIR')
            ->set('medAdministrado', false)
            ->set('medMotivoOmision', 'Paciente presenta náuseas y rechaza la toma oral matutina')
            ->call('confirmarAdministracionMed')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('administracion_medicacion', [
            'cod_am' => $pacienteAsignado->cod_am,
            'cod_med_adulto' => $med->cod_med_adulto,
            'administrado' => false,
            'motivo_omision' => 'Paciente presenta náuseas y rechaza la toma oral matutina',
        ]);

        // ─── PASO 5: EJECUTAR TAREA DE CUIDADOS ────────────────────────────────
        $ficha->call('completarTarea', $tarea->cod_tarea)
            ->assertHasNoErrors();

        $this->assertSame('REALIZADA', $tarea->fresh()->estado);
        $this->assertNotNull($tarea->fresh()->fecha_realizada);

        // ─── PASO 6: REGISTRAR SEGUIMIENTO CON INCIDENTE / MÉDICO ─────────────
        $ficha->call('abrirRegistrarSeguimiento')
            ->set('segObs', 'Se observa marcha inestable y náuseas post-ingesta. Se solicita revisión médica.')
            ->set('segRequiereMedico', true)
            ->set('segIncidente', true)
            ->call('guardarSeguimiento')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('seguimientos_diarios', [
            'cod_am' => $pacienteAsignado->cod_am,
            'requiere_medico' => true,
            'incidente' => true,
        ]);

        // ─── PASO 7: VERIFICAR GENERACIÓN AUTOMÁTICA DE ALERTAS ────────────────
        $alertaGenerada = AlertaAdulto::where('cod_am', $pacienteAsignado->cod_am)
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
        $this->assertSame($enfermero->cod_usu, $alertaGenerada->fresh()->cerrado_por);

        $ficha->call('cambiarTab', 'alertas')
            ->assertSee('Médico de turno evaluó y estabilizó al residente.');

        // ─── PASO 9: VERIFICAR HISTORIAL CLÍNICO INTEGRADO 360° ──────────────
        $ficha->call('cambiarTab', 'historial')
            ->assertSee('Cronología de Eventos Clínicos 360°')
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
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);

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

        $paciente = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);

        AsignacionTurnoAdulto::create([
            'cod_am' => $paciente->cod_am,
            'cod_turno' => $turnoManana->cod_turno,
            'cod_usu_enfermero' => $enfermero->cod_usu,
            'fecha_inicio' => today()->toDateString(),
            'nivel_supervision' => 'ESTANDAR',
            'estado' => 'ACTIVA',
            'motivo_asignacion' => 'Asignación matutina',
            'asignado_por' => $enfermero->cod_usu,
        ]);
        AsignacionTurnoAdulto::create([
            'cod_am' => $paciente->cod_am,
            'cod_turno' => $turnoTarde->cod_turno,
            'cod_usu_enfermero' => $enfermeroReceptor->cod_usu,
            'fecha_inicio' => today()->toDateString(),
            'nivel_supervision' => 'ESTANDAR',
            'estado' => 'ACTIVA',
            'motivo_asignacion' => 'Asignación de relevo',
            'asignado_por' => $enfermero->cod_usu,
        ]);
        RecepcionTurno::create([
            'cod_turno' => $turnoManana->cod_turno,
            'cod_usuario' => $enfermero->cod_usu,
            'fecha_hora_recepcion' => now(),
        ]);

        $this->actingAs($enfermero);
        Livewire::test(PaseTurnoPanel::class)
            ->call('abrirGenerar')
            ->set('codAm', $paciente->cod_am)
            ->set('turnoSalienteId', $turnoManana->cod_turno)
            ->set('turnoEntranteId', $turnoTarde->cod_turno)
            ->set('enfermeroEntranteId', $enfermeroReceptor->cod_usu)
            ->set('resumenTurno', 'Se entrega al residente con signos estables y controles del turno completados.')
            ->call('generarPase')
            ->assertHasNoErrors();

        $pase = PaseTurno::where('cod_am', $paciente->cod_am)->first();
        $this->assertNotNull($pase);
        $this->assertSame('GENERADO', $pase->estado);

        // Relevo entrante confirma la recepción de guardia
        $this->actingAs($enfermeroReceptor);
        Carbon::setTestNow('2026-09-10 16:00:00');
        RecepcionTurno::create([
            'cod_turno' => $turnoTarde->cod_turno,
            'cod_usuario' => $enfermeroReceptor->cod_usu,
            'fecha_hora_recepcion' => now(),
        ]);
        Livewire::test(PaseTurnoPanel::class)
            ->call('recibirPase', $pase->cod_pase)
            ->assertHasNoErrors();

        $this->assertSame('RECIBIDO', $pase->fresh()->estado);
        $this->assertNotNull($pase->fresh()->fecha_recibido);
    }
}
