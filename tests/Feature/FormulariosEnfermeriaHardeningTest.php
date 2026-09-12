<?php

namespace Tests\Feature;

use App\Http\Requests\Clinica\StoreSignosVitalesRequest;
use App\Http\Requests\Medicacion\StoreAdministracionMedicacionRequest;
use App\Livewire\Alertas\AlertasPanel;
use App\Livewire\Clinica\RegistroSignosVitalesModal;
use App\Livewire\Cuidados\AsignacionTurnoPanel;
use App\Livewire\Cuidados\DashboardTurno;
use App\Livewire\Cuidados\FichaPaciente;
use App\Livewire\Cuidados\MisPacientes;
use App\Livewire\Cuidados\PaseTurnoPanel;
use App\Livewire\Cuidados\SeguimientoDiarioPanel;
use App\Livewire\Cuidados\TareasPlanPanel;
use App\Livewire\Cuidados\TurnosEnfermeriaPanel;
use App\Livewire\Medicacion\AdministracionMedicacionModal;
use App\Livewire\Valoraciones\ValoracionBarthelModal;
use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AsignacionTurnoAdulto;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\MedicacionAdulto;
use App\Models\PaseTurno;
use App\Models\PlanCuidado;
use App\Models\RecepcionTurno;
use App\Models\SignosVitalesAdulto;
use App\Models\TareaPlanCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Clinica\ValidacionSignosVitalesService;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class FormulariosEnfermeriaHardeningTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermeroAsignado;
    private User $enfermeroNoAsignado;
    private User $enfermeroReceptor;
    private AdultoMayor $adulto;
    private TurnoEnfermeria $turno;
    private TurnoEnfermeria $turnoTarde;
    private Habitacion $habitacion;
    private Cama $cama;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-11 10:00:00');
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);

        $this->enfermeroAsignado = User::factory()->create(['estado' => 'ACTIVO']);
        $this->enfermeroAsignado->assignRole('ENFERMEROS');

        $this->enfermeroNoAsignado = User::factory()->create(['estado' => 'ACTIVO']);
        $this->enfermeroNoAsignado->assignRole('ENFERMEROS');

        $this->enfermeroReceptor = User::factory()->create(['estado' => 'ACTIVO']);
        $this->enfermeroReceptor->assignRole('ENFERMEROS');

        $this->adulto = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);

        $this->turno = TurnoEnfermeria::create([
            'orden' => 1,
            'nombre' => 'Mañana Test',
            'hora_inicio' => '07:00',
            'hora_fin' => '15:00',
            'estado' => 'ACTIVO',
        ]);

        $this->turnoTarde = TurnoEnfermeria::create([
            'orden' => 2,
            'nombre' => 'Tarde Test',
            'hora_inicio' => '15:00',
            'hora_fin' => '23:00',
            'estado' => 'ACTIVO',
        ]);

        $this->habitacion = Habitacion::create([
            'codigo' => 'HAB-101',
            'nombre' => 'Habitación 101',
            'tipo_habitacion' => 'DOBLE',
            'capacidad' => 2,
            'estado' => 'DISPONIBLE',
        ]);

        $this->cama = Cama::create([
            'cod_habitacion' => $this->habitacion->cod_habitacion,
            'codigo' => 'CAM-101-A',
            'estado' => 'DISPONIBLE',
        ]);

        // Asignación activa del adulto al enfermeroAsignado
        AsignacionTurnoAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'cod_turno' => $this->turno->cod_turno,
            'cod_usu_enfermero' => $this->enfermeroAsignado->cod_usu,
            'cod_cama' => $this->cama->cod_cama,
            'fecha_inicio' => today()->toDateString(),
            'nivel_supervision' => 'ESTANDAR',
            'estado' => 'ACTIVA',
            'registrado_por' => $this->enfermeroAsignado->cod_usu,
        ]);
        AsignacionTurnoAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'cod_turno' => $this->turnoTarde->cod_turno,
            'cod_usu_enfermero' => $this->enfermeroReceptor->cod_usu,
            'fecha_inicio' => today()->toDateString(),
            'nivel_supervision' => 'ESTANDAR',
            'estado' => 'ACTIVA',
            'registrado_por' => $this->enfermeroAsignado->cod_usu,
        ]);
        RecepcionTurno::create([
            'cod_turno' => $this->turno->cod_turno,
            'cod_usuario' => $this->enfermeroAsignado->cod_usu,
            'fecha_hora_recepcion' => now(),
        ]);
    }

    // =========================================================================
    // 1. SIGNOS VITALES: RANGOS, RELACIÓN SISTÓLICA/DIASTÓLICA, IMC Y VACÍOS
    // =========================================================================

    public function test_signos_vitales_rechaza_sistolica_menor_o_igual_a_diastolica(): void
    {
        $this->actingAs($this->enfermeroAsignado);

        Livewire::test(RegistroSignosVitalesModal::class)
            ->call('abrir', $this->adulto->cod_am)
            ->set('pa_sistolica', 80)
            ->set('pa_diastolica', 120) // Invertida
            ->set('fc', 75)
            ->call('guardar')
            ->assertHasErrors(['pa_sistolica']);

        Livewire::test(RegistroSignosVitalesModal::class)
            ->call('abrir', $this->adulto->cod_am)
            ->set('pa_sistolica', 100)
            ->set('pa_diastolica', 100) // Iguales
            ->set('fc', 75)
            ->call('guardar')
            ->assertHasErrors(['pa_sistolica']);
    }

    public function test_signos_vitales_permite_valor_atipico_solo_con_confirmacion_explicita(): void
    {
        $this->actingAs($this->enfermeroAsignado);

        Livewire::test(RegistroSignosVitalesModal::class)
            ->call('abrir', $this->adulto->cod_am)
            ->set('pa_sistolica', 80)
            ->set('pa_diastolica', 120)
            ->set('confirmar_presion_atipica', true)
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('signos_vitales_adulto', [
            'cod_am' => $this->adulto->cod_am,
            'presion_sistolica' => 80,
            'presion_diastolica' => 120,
            'valor_atipico_confirmado' => true,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_signos_vitales_rechaza_sistolica_sin_diastolica_y_viceversa(): void
    {
        $this->actingAs($this->enfermeroAsignado);

        Livewire::test(RegistroSignosVitalesModal::class)
            ->call('abrir', $this->adulto->cod_am)
            ->set('pa_sistolica', 120)
            ->set('pa_diastolica', null) // Falta diastólica
            ->set('fc', 75)
            ->call('guardar')
            ->assertHasErrors(['pa_sistolica']);

        Livewire::test(RegistroSignosVitalesModal::class)
            ->call('abrir', $this->adulto->cod_am)
            ->set('pa_sistolica', null)
            ->set('pa_diastolica', 80) // Falta sistólica
            ->set('fc', 75)
            ->call('guardar')
            ->assertHasErrors(['pa_sistolica']);
    }

    public function test_signos_vitales_rechaza_formulario_totalmente_vacio(): void
    {
        $this->actingAs($this->enfermeroAsignado);

        Livewire::test(RegistroSignosVitalesModal::class)
            ->call('abrir', $this->adulto->cod_am)
            ->set('pa_sistolica', null)
            ->set('pa_diastolica', null)
            ->set('fc', null)
            ->set('fr', null)
            ->set('temperatura', null)
            ->set('saturacion', null)
            ->set('glucosa', null)
            ->set('peso', null)
            ->set('dolor', null)
            ->call('guardar')
            ->assertHasErrors(['general']);
    }

    public function test_signos_vitales_rechaza_valores_fuera_de_rango_biologico(): void
    {
        $this->actingAs($this->enfermeroAsignado);

        // Temperatura fuera del límite técnico definido (46°C > 45°C)
        Livewire::test(RegistroSignosVitalesModal::class)
            ->call('abrir', $this->adulto->cod_am)
            ->set('temperatura', 46)
            ->call('guardar')
            ->assertHasErrors(['temperatura']);

        // FC fuera del límite técnico definido (301 bpm > 300)
        Livewire::test(RegistroSignosVitalesModal::class)
            ->call('abrir', $this->adulto->cod_am)
            ->set('fc', 301)
            ->call('guardar')
            ->assertHasErrors(['fc']);
    }

    public function test_signos_vitales_calcula_imc_en_servidor_y_normaliza_talla(): void
    {
        // 70 kg y 175 cm (o 1.75 m) -> IMC = 70 / (1.75^2) = 22.857... -> round 22.9
        $imc1 = ValidacionSignosVitalesService::calcularImc(70, 175);
        $imc2 = ValidacionSignosVitalesService::calcularImc(70, 1.75);
        $this->assertSame(22.9, $imc1);
        $this->assertSame(22.9, $imc2);

        $tallaNorm = ValidacionSignosVitalesService::normalizarTalla(1.68);
        $this->assertSame(168.0, $tallaNorm);
    }

    // =========================================================================
    // 2. MEDICACIÓN: PERTENENCIA, ESTADO, HORA REAL, OMISIÓN Y DUPLICADOS
    // =========================================================================

    public function test_medicacion_rechaza_farmaco_perteneciente_a_otro_adulto(): void
    {
        $this->actingAs($this->enfermeroAsignado);
        $otroAdulto = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);

        $medOtro = MedicacionAdulto::create([
            'cod_am' => $otroAdulto->cod_am,
            'nombre_medicamento' => 'Enalapril 10mg',
            'dosis' => '1 tableta',
            'via_administracion' => 'Oral',
            'frecuencia' => 'Cada 12 horas',
            'hora_programada' => '08:00',
            'fecha_inicio' => today(),
            'estado' => 'ACTIVO',
            'registrado_por' => $this->enfermeroAsignado->cod_usu,
        ]);

        Livewire::test(AdministracionMedicacionModal::class)
            ->call('abrirModalAdministracion', $this->adulto->cod_am, $medOtro->cod_med_adulto)
            ->assertHasErrors(['cod_med_adulto']);
    }

    public function test_medicacion_rechaza_farmaco_inactivo(): void
    {
        $this->actingAs($this->enfermeroAsignado);

        $medInactivo = MedicacionAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'nombre_medicamento' => 'Paracetamol 500mg',
            'dosis' => '1 tableta',
            'via_administracion' => 'Oral',
            'frecuencia' => 'Cada 8 horas',
            'hora_programada' => '09:00',
            'fecha_inicio' => today(),
            'estado' => 'SUSPENDIDO', // Inactivo
            'registrado_por' => $this->enfermeroAsignado->cod_usu,
        ]);

        Livewire::test(AdministracionMedicacionModal::class)
            ->call('abrirModalAdministracion', $this->adulto->cod_am, $medInactivo->cod_med_adulto)
            ->assertDispatched('swal'); // Avisa de medicamento no activo
    }

    public function test_medicacion_exige_motivo_al_omitir_y_hora_real_al_administrar(): void
    {
        $this->actingAs($this->enfermeroAsignado);

        $med = MedicacionAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'nombre_medicamento' => 'Losartán 50mg',
            'dosis' => '1 tableta',
            'via_administracion' => 'Oral',
            'frecuencia' => 'Cada 24 horas',
            'hora_programada' => '08:00',
            'fecha_inicio' => today(),
            'estado' => 'ACTIVO',
            'registrado_por' => $this->enfermeroAsignado->cod_usu,
        ]);

        // Intentar omitir sin motivo
        Livewire::test(AdministracionMedicacionModal::class)
            ->call('abrirModalAdministracion', $this->adulto->cod_am, $med->cod_med_adulto)
            ->set('administrado', false)
            ->set('motivo_omision', '')
            ->call('guardar')
            ->assertHasErrors(['motivo_omision']);

        // Intentar omitir con motivo demasiado corto (< 5 chars)
        Livewire::test(AdministracionMedicacionModal::class)
            ->call('abrirModalAdministracion', $this->adulto->cod_am, $med->cod_med_adulto)
            ->set('administrado', false)
            ->set('motivo_omision', 'No')
            ->call('guardar')
            ->assertHasErrors(['motivo_omision']);
    }

    public function test_medicacion_rechaza_dosis_duplicada_misma_fecha_y_hora(): void
    {
        $this->actingAs($this->enfermeroAsignado);

        $med = MedicacionAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'nombre_medicamento' => 'Amlodipino 5mg',
            'dosis' => '1 tableta',
            'via_administracion' => 'Oral',
            'frecuencia' => 'Cada 24 horas',
            'hora_programada' => '10:00',
            'fecha_inicio' => today(),
            'estado' => 'ACTIVO',
            'registrado_por' => $this->enfermeroAsignado->cod_usu,
        ]);

        // Primera administración exitosa
        Livewire::test(AdministracionMedicacionModal::class)
            ->call('abrirModalAdministracion', $this->adulto->cod_am, $med->cod_med_adulto)
            ->set('hora_programada', '10:00')
            ->set('hora_real', '10:05')
            ->set('administrado', true)
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('administracion_medicacion', 1);

        // Intento de segunda administración de la misma dosis programada
        Livewire::test(AdministracionMedicacionModal::class)
            ->call('abrirModalAdministracion', $this->adulto->cod_am, $med->cod_med_adulto)
            ->set('hora_programada', '10:00')
            ->set('hora_real', '10:15')
            ->set('administrado', true)
            ->call('guardar')
            ->assertHasErrors(['cod_med_adulto']);

        $this->assertDatabaseCount('administracion_medicacion', 1);
    }

    // =========================================================================
    // 3. SEGUIMIENTO DIARIO: INTEGRIDAD, INCIDENTES Y SOLICITUD MÉDICA
    // =========================================================================

    public function test_seguimiento_rechaza_porcentaje_invalido_y_horas_incoherentes(): void
    {
        $this->actingAs($this->enfermeroAsignado);

        // Porcentaje > 100
        Livewire::test(SeguimientoDiarioPanel::class)
            ->call('abrirCrear')
            ->set('codAm', $this->adulto->cod_am)
            ->set('codTurno', $this->turno->cod_turno)
            ->set('porcentajeAlimentacion', 150)
            ->set('observacion', 'Evolución normal durante el turno.')
            ->call('guardar')
            ->assertHasErrors(['porcentajeAlimentacion']);

        // Hora fin anterior a inicio
        Livewire::test(SeguimientoDiarioPanel::class)
            ->call('abrirCrear')
            ->set('codAm', $this->adulto->cod_am)
            ->set('codTurno', $this->turno->cod_turno)
            ->set('horaInicio', '14:00')
            ->set('horaFin', '10:00') // Anterior a inicio
            ->set('porcentajeAlimentacion', 100)
            ->set('observacion', 'Evolución normal durante el turno.')
            ->call('guardar')
            ->assertHasErrors(['horaFin']);
    }

    public function test_seguimiento_exige_detalle_ante_incidente(): void
    {
        $this->actingAs($this->enfermeroAsignado);

        Livewire::test(SeguimientoDiarioPanel::class)
            ->call('abrirCrear')
            ->set('codAm', $this->adulto->cod_am)
            ->set('codTurno', $this->turno->cod_turno)
            ->set('incidente', true)
            ->set('observacion', 'Caída.') // Demasiado corto (< 15 chars)
            ->call('guardar')
            ->assertHasErrors(['observacion']);
    }

    // =========================================================================
    // 4. TAREAS: EXIGENCIA DE RESULTADO, OMISIÓN Y REPROGRAMACIÓN
    // =========================================================================

    public function test_tarea_realizada_exige_resultado_y_omitida_exige_motivo(): void
    {
        $this->actingAs($this->enfermeroAsignado);

        $plan = PlanCuidado::create([
            'cod_am' => $this->adulto->cod_am,
            'tipo_plan' => 'INICIAL',
            'version' => 1,
            'nivel_cuidado' => 'ESTANDAR',
            'estado' => 'ACTIVO',
            'origen' => 'ADMISION',
            'fecha_inicio' => today(),
        ]);

        $tarea = TareaPlanCuidado::create([
            'cod_plan' => $plan->cod_plan,
            'cod_am' => $this->adulto->cod_am,
            'cod_turno' => $this->turno->cod_turno,
            'area' => 'HIGIENE',
            'titulo' => 'Higiene matutina',
            'fecha_programada' => today(),
            'prioridad' => 'NORMAL',
            'estado' => 'PENDIENTE',
            'registrado_por' => $this->enfermeroAsignado->cod_usu,
        ]);

        // REALIZADA sin resultado suficiente
        Livewire::test(TareasPlanPanel::class)
            ->call('abrirResultado', $tarea->cod_tarea)
            ->set('estadoTarea', 'REALIZADA')
            ->set('resultado', '')
            ->call('guardarResultado')
            ->assertHasErrors(['resultado']);

        // OMITIDA sin motivo suficiente
        Livewire::test(TareasPlanPanel::class)
            ->call('abrirResultado', $tarea->cod_tarea)
            ->set('estadoTarea', 'OMITIDA')
            ->set('motivoOmision', 'No')
            ->call('guardarResultado')
            ->assertHasErrors(['motivoOmision']);
    }

    public function test_tarea_reprogramada_exige_fecha_hora_y_motivo(): void
    {
        $this->actingAs($this->enfermeroAsignado);

        $plan = PlanCuidado::create([
            'cod_am' => $this->adulto->cod_am,
            'tipo_plan' => 'INICIAL',
            'version' => 1,
            'nivel_cuidado' => 'ESTANDAR',
            'estado' => 'ACTIVO',
            'origen' => 'ADMISION',
            'fecha_inicio' => today(),
        ]);

        $tarea = TareaPlanCuidado::create([
            'cod_plan' => $plan->cod_plan,
            'cod_am' => $this->adulto->cod_am,
            'cod_turno' => $this->turno->cod_turno,
            'area' => 'SIGNOS',
            'titulo' => 'Control vespertino',
            'fecha_programada' => today(),
            'prioridad' => 'NORMAL',
            'estado' => 'PENDIENTE',
            'registrado_por' => $this->enfermeroAsignado->cod_usu,
        ]);

        // REPROGRAMADA sin nueva hora o sin motivo
        Livewire::test(TareasPlanPanel::class)
            ->call('abrirResultado', $tarea->cod_tarea)
            ->set('estadoTarea', 'REPROGRAMADA')
            ->set('fechaProgramada', today()->addDay()->toDateString())
            ->set('horaProgramada', '') // Falta hora
            ->set('motivoOmision', '')   // Falta motivo
            ->call('guardarResultado')
            ->assertHasErrors(['horaProgramada', 'motivoOmision']);

        Livewire::test(TareasPlanPanel::class)
            ->call('abrirResultado', $tarea->cod_tarea)
            ->set('estadoTarea', 'REPROGRAMADA')
            ->set('fechaProgramada', today()->addDay()->toDateString())
            ->set('horaProgramada', '14:30')
            ->set('motivoOmision', 'Se reprograma por indicación del turno.')
            ->call('guardarResultado')
            ->assertHasNoErrors();

        $this->assertSame('REPROGRAMADA', $tarea->fresh()->estado);
        $reprogramada = TareaPlanCuidado::where('cod_tarea', '!=', $tarea->cod_tarea)->sole();
        $this->assertSame('PENDIENTE', $reprogramada->estado);
        $this->assertSame('14:30:00', $reprogramada->hora_programada);
    }

    public function test_mis_pacientes_valida_y_evita_doble_seguimiento_del_turno(): void
    {
        $this->actingAs($this->enfermeroAsignado);
        $this->turno->update(['hora_inicio' => '00:00', 'hora_fin' => '23:59']);

        Livewire::test(MisPacientes::class)
            ->call('abrirRegistrarSeguimiento', $this->adulto->cod_am)
            ->set('segEstado', 'DESCONOCIDO')
            ->call('guardarSeguimiento')
            ->assertHasErrors(['segEstado']);

        Livewire::test(MisPacientes::class)
            ->call('abrirRegistrarSeguimiento', $this->adulto->cod_am)
            ->set('segObs', 'Residente estable y colaborador durante el turno.')
            ->call('guardarSeguimiento')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('seguimientos_diarios', 1);

        Livewire::test(MisPacientes::class)
            ->call('abrirRegistrarSeguimiento', $this->adulto->cod_am)
            ->set('segObs', 'Segundo registro para el mismo turno y residente.')
            ->call('guardarSeguimiento')
            ->assertHasErrors(['segObs']);

        $this->assertDatabaseCount('seguimientos_diarios', 1);
    }

    public function test_dashboard_administra_la_hora_prescrita_y_evita_dosis_duplicada(): void
    {
        $this->actingAs($this->enfermeroAsignado);
        $this->turno->update(['hora_inicio' => '00:00', 'hora_fin' => '23:59']);

        $med = MedicacionAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'nombre_medicamento' => 'Losartán 50 mg',
            'dosis' => '1 tableta',
            'via_administracion' => 'ORAL',
            'frecuencia' => 'Cada 24 horas',
            'hora_programada' => '08:00',
            'fecha_inicio' => today(),
            'estado' => 'ACTIVO',
            'registrado_por' => $this->enfermeroAsignado->cod_usu,
        ]);

        Livewire::test(DashboardTurno::class)
            ->call('administrarMed', $med->cod_med_adulto, $this->adulto->cod_am)
            ->assertHasNoErrors()
            ->call('administrarMed', $med->cod_med_adulto, $this->adulto->cod_am)
            ->assertHasErrors(['cod_med_adulto']);

        $this->assertDatabaseCount('administracion_medicacion', 1);
        $administracion = AdministracionMedicacion::sole();
        $this->assertSame('08:00', $administracion->hora_programada->format('H:i'));
    }

    public function test_las_validaciones_globales_se_muestran_en_espanol(): void
    {
        app()->setLocale('es');

        $mensajes = Validator::make(
            ['estado_general' => ''],
            ['estado_general' => 'required']
        )->errors();

        $this->assertStringContainsString('obligatorio', $mensajes->first('estado_general'));
        $this->assertStringNotContainsString('required', $mensajes->first('estado_general'));
    }

    // =========================================================================
    // 5. PASE DE TURNO: AUTO-PASE, VIGILANCIA Y DUPLICADOS
    // =========================================================================

    public function test_pase_turno_rechaza_autopase_y_vigilancia_sin_motivo(): void
    {
        $this->actingAs($this->enfermeroAsignado);

        // Intento de entregarse el turno a sí mismo
        Livewire::test(PaseTurnoPanel::class)
            ->call('abrirGenerar', $this->adulto->cod_am)
            ->set('turnoSalienteId', $this->turno->cod_turno)
            ->set('turnoEntranteId', $this->turnoTarde->cod_turno)
            ->set('enfermeroEntranteId', $this->enfermeroAsignado->cod_usu) // Mismo enfermero
            ->set('resumenTurno', 'Se entrega guardia con todas las novedades del turno.')
            ->call('generarPase')
            ->assertHasErrors(['enfermeroEntranteId']);

        // Vigilancia especial sin motivo
        Livewire::test(PaseTurnoPanel::class)
            ->call('abrirGenerar', $this->adulto->cod_am)
            ->set('turnoSalienteId', $this->turno->cod_turno)
            ->set('turnoEntranteId', $this->turnoTarde->cod_turno)
            ->set('enfermeroEntranteId', $this->enfermeroReceptor->cod_usu)
            ->set('requiereVigilanciaEspecial', true)
            ->set('motivoVigilancia', '') // Vacío
            ->set('resumenTurno', 'Se entrega guardia con todas las novedades del turno.')
            ->call('generarPase')
            ->assertHasErrors(['motivoVigilancia']);
    }

    public function test_pase_turno_impide_duplicado_y_solo_receptor_confirma(): void
    {
        $this->actingAs($this->enfermeroAsignado);

        // Generar pase legítimo
        Livewire::test(PaseTurnoPanel::class)
            ->call('abrirGenerar', $this->adulto->cod_am)
            ->set('turnoSalienteId', $this->turno->cod_turno)
            ->set('turnoEntranteId', $this->turnoTarde->cod_turno)
            ->set('enfermeroEntranteId', $this->enfermeroReceptor->cod_usu)
            ->set('resumenTurno', 'Se entrega guardia completa con novedades normales.')
            ->call('generarPase')
            ->assertHasNoErrors();

        $pase = PaseTurno::sole();

        // Intento de generar segundo pase duplicado para el mismo paciente y turno
        Livewire::test(PaseTurnoPanel::class)
            ->call('abrirGenerar', $this->adulto->cod_am)
            ->set('turnoSalienteId', $this->turno->cod_turno)
            ->set('turnoEntranteId', $this->turnoTarde->cod_turno)
            ->set('enfermeroEntranteId', $this->enfermeroReceptor->cod_usu)
            ->set('resumenTurno', 'Intento de segundo pase en la misma fecha y turno.')
            ->call('generarPase')
            ->assertHasErrors(['codAm']);

        // El enfermero saliente (o un tercero) NO puede confirmar la recepción (403)
        Livewire::test(PaseTurnoPanel::class)
            ->call('recibirPase', $pase->cod_pase)
            ->assertStatus(403);

        // El enfermero receptor SÍ puede confirmar la recepción
        $this->actingAs($this->enfermeroReceptor);
        Carbon::setTestNow('2026-09-11 16:00:00');
        RecepcionTurno::create([
            'cod_turno' => $this->turnoTarde->cod_turno,
            'cod_usuario' => $this->enfermeroReceptor->cod_usu,
            'fecha_hora_recepcion' => now(),
        ]);
        Livewire::test(PaseTurnoPanel::class)
            ->call('recibirPase', $pase->cod_pase)
            ->assertHasNoErrors();

        $this->assertSame('RECIBIDO', $pase->fresh()->estado);
    }

    // =========================================================================
    // 6. BARTHEL: PUNTUACIONES OFICIALES Y CÁLCULO ESTRICTO
    // =========================================================================

    public function test_barthel_calcula_total_y_clasificacion_sin_modificar_riesgo_caida(): void
    {
        $this->actingAs($this->enfermeroAsignado);

        $modal = Livewire::test(ValoracionBarthelModal::class)
            ->call('abrir', $this->adulto->cod_am)
            ->set('alimentacion', 10)
            ->set('bano', 5)
            ->set('aseo_personal', 5)
            ->set('vestido', 10)
            ->set('control_intestinal', 10)
            ->set('control_vesical', 10)
            ->set('uso_retrete', 10)
            ->set('traslados', 15)
            ->set('deambulacion', 15)
            ->set('escaleras', 10) // Suma = 100
            ->set('riesgo_caida', 'BAJO')
            ->call('guardar')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('valoracion_funcional_adulto', [
            'cod_am' => $this->adulto->cod_am,
            'indice_barthel' => 100,
            'nivel_dependencia' => 'Independiente',
            'riesgo_caida' => 'BAJO',
        ]);
    }

    // =========================================================================
    // 7. ENFERMERO NO ASIGNADO: RECHAZO EN TODAS LAS ACCIONES BACKEND
    // =========================================================================

    public function test_enfermero_no_asignado_recibe_403_en_todas_las_mutaciones(): void
    {
        $this->actingAs($this->enfermeroNoAsignado);

        // 1. Signos vitales
        Livewire::test(RegistroSignosVitalesModal::class)
            ->call('abrir', $this->adulto->cod_am)
            ->assertForbidden();

        // 2. Ficha 360
        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->assertForbidden();

        // 3. Seguimiento
        Livewire::test(SeguimientoDiarioPanel::class)
            ->call('abrirCrear')
            ->set('codAm', $this->adulto->cod_am)
            ->set('codTurno', $this->turno->cod_turno)
            ->set('observacion', 'Intento no autorizado de seguimiento.')
            ->call('guardar')
            ->assertForbidden();

        // 4. Pase de turno
        Livewire::test(PaseTurnoPanel::class)
            ->call('abrirGenerar', $this->adulto->cod_am)
            ->assertForbidden();
    }
}
