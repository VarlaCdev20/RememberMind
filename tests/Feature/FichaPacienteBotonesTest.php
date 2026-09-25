<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Compartido\Clinica\FichaPaciente;
use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\Alerta;
use App\Models\OcupacionCama;
use App\Models\AsignacionResidenteJornada;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\Prescripcion;
use App\Models\PlanCuidado;
use App\Models\AsignacionPersonal;
use App\Models\SignoVital;
use App\Models\EjecucionCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class FichaPacienteBotonesTest extends TestCase
{
    use RefreshDatabase;

    protected User $enfermero;
    protected AdultoMayor $adulto;
    protected TurnoEnfermeria $turno;
    protected Habitacion $habitacion;
    protected Cama $cama;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-11 10:00:00');

        $this->seed([ RolesAndPermissionsSeeder::class]);

        $this->enfermero = User::factory()->create([
            'cod_usuario' => 'USU_0099',
            'nombres' => 'Elena',
            'ap_paterno' => 'Salazar',
            'estado' => 'ACTIVO',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');

        $this->turno = TurnoEnfermeria::create([
            'nombre' => 'Turno Mañana',
            'hora_inicio' => '07:00:00',
            'hora_fin' => '15:00:00',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);

        $this->habitacion = Habitacion::create([
            'nombre' => 'Habitación 202',
            'codigo' => 'H-202',
            'numero' => '202',
            'tipo' => 'INDIVIDUAL',
            'capacidad' => 1,
            'estado' => 'ACTIVA',
        ]);

        $this->cama = Cama::create([
            'cod_habitacion' => $this->habitacion->cod_habitacion,
            'codigo' => 'C-202-A',
            'numero' => 'A',
            'estado' => 'OCUPADA',
        ]);

        $this->adulto = AdultoMayor::factory()->create([
            'cod_residente' => 'AM100',
            'nombres' => 'Bernardo',
            'ap_paterno' => 'Pinto',
            'ap_materno' => 'Rios',
            'ci' => '4455667',
            'fecha_nac' => '1940-02-20',
            'genero' => 'MASCULINO',
            'alergias' => 'Ibuprofeno',
            'cod_habitacion' => $this->habitacion->cod_habitacion,
            'cod_cama' => $this->cama->cod_cama,
            'cod_est_adul' => 'EST_001',
        ]);

        OcupacionCama::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_habitacion' => $this->habitacion->cod_habitacion,
            'cod_cama' => $this->cama->cod_cama,
            'fecha_asignacion' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        AsignacionResidenteJornada::create([
            'cod_residente' => $this->adulto->cod_residente,
            'cod_turno' => $this->turno->cod_turno,
            'cod_usu_enfermero' => $this->enfermero->cod_usuario,
            'cod_usuario' => $this->enfermero->cod_usuario,
            'fecha_inicio' => today()->toDateString(),
            'motivo_asignacion' => 'Turno activo',
            'asignado_por' => $this->enfermero->cod_usuario,
            'fecha' => today()->toDateString(),
            'nivel_supervision' => 'MEDIO',
            'estado' => 'ACTIVO',
        ]);
        AsignacionPersonal::create([
            'cod_turno' => $this->turno->cod_turno,
            'cod_usuario' => $this->enfermero->cod_usuario,
            'fecha_hora_recepcion' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_modal_registrar_signos_vitales_completo(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_residente])
            ->call('abrirModalSignos')
            ->assertSet('modalSignos', true)
            ->set('signoPA', '125/80')
            ->set('signoFC', '74')
            ->set('signoFR', '16')
            ->set('signoTemp', '36.6')
            ->set('signoSat', '98')
            ->set('signoGlucosa', '110')
            ->set('signoDolor', 1)
            ->set('signoObs', 'Control matutino sin novedades')
            ->call('guardarSignos')
            ->assertHasNoErrors()
            ->assertSet('modalSignos', false);

        $this->assertDatabaseHas('signos_vitales', [
            'cod_residente' => $this->adulto->cod_residente,
            'presion_sistolica' => 125,
            'presion_diastolica' => 80,
            'frecuencia_cardiaca' => 74,
            'temperatura' => 36.6,
        ]);
    }

    public function test_modal_administrar_medicacion_completo(): void
    {
        $this->actingAs($this->enfermero);

        $med = Prescripcion::create([
            'cod_residente' => $this->adulto->cod_residente,
            'nombre_medicamento' => 'Metformina 850mg',
            'dosis' => '850 mg',
            'via_administracion' => 'Oral',
            'frecuencia' => 'Cada 12 horas',
            'hora_programada' => '08:00',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_residente])
            ->call('abrirModalMedicacion', $med->cod_med)
            ->assertSet('modalMed', true)
            ->set('medAccion', 'ADMINISTRAR')
            ->set('medEfectoObs', 'Tolerancia digestiva adecuada')
            ->call('guardarMedicacion')
            ->assertHasNoErrors()
            ->assertSet('modalMed', false);

        $this->assertDatabaseHas('administraciones_medicacion', [
            'cod_residente' => $this->adulto->cod_residente,
            'resultado' => 'ADMINISTRADA',
        ]);
    }

    public function test_modal_tarea_cuidados_completar(): void
    {
        $this->actingAs($this->enfermero);

        $plan = PlanCuidado::create([
            'cod_residente' => $this->adulto->cod_residente,
            'diagnostico_enfermeria' => 'Riesgo de deterioro de la integridad cutánea',
            'objetivo' => 'Mantener piel intacta',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVO',
            'creado_por' => $this->enfermero->cod_usuario,
        ]);

        $tarea = EjecucionCuidado::create([
            'cod_plan' => $plan->cod_plan,
            'cod_residente' => $this->adulto->cod_residente,
            'cod_turno' => $this->turno->cod_turno,
            'titulo' => 'Cambio postural decúbito lateral',
            'area' => 'PIEL',
            'prioridad' => 'ALTA',
            'fecha_programada' => today()->toDateString(),
            'hora_programada' => '10:00:00',
            'estado' => 'PENDIENTE',
            'registrado_por' => $this->enfermero->cod_usuario,
        ]);

        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_residente])
            ->call('abrirModalTarea', $tarea->cod_tarea)
            ->assertSet('modalTarea', true)
            ->set('tareaEstadoAccion', 'REALIZADA')
            ->set('tareaResultado', 'Cambio realizado a decúbito izquierdo')
            ->call('guardarTarea')
            ->assertHasNoErrors()
            ->assertSet('modalTarea', false);

        $this->assertSame('REALIZADA', $tarea->fresh()->estado);
    }

    public function test_modal_seguimiento_diario_completo(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_residente])
            ->call('abrirModalSeguimiento')
            ->assertSet('modalSeguimiento', true)
            ->set('segEstado', 'ESTABLE')
            ->set('segAlimentacion', 'COMPLETA')
            ->set('segMovilidad', 'INDEPENDIENTE')
            ->set('segSueno', 'NORMAL')
            ->set('segObs', 'Desayuno completo, deambula por el patio')
            ->call('guardarSeguimiento')
            ->assertHasNoErrors()
            ->assertSet('modalSeguimiento', false);

        $this->assertDatabaseHas('atenciones', [
            'cod_residente' => $this->adulto->cod_residente,
            'tipo_atencion' => 'SEGUIMIENTO_DIARIO',
        ]);
    }

    public function test_modal_reportar_incidente_genera_alerta(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_residente])
            ->call('abrirModalIncidente')
            ->assertSet('modalIncidente', true)
            ->set('incidenteTipo', 'CAIDA')
            ->set('incidenteNivel', 'ALTO')
            ->set('incidenteMotivo', 'Tropiezo al incorporarse de la cama sin asistencia')
            ->call('guardarIncidente')
            ->assertHasNoErrors()
            ->assertSet('modalIncidente', false);

        $this->assertDatabaseHas('alertas', [
            'cod_residente' => $this->adulto->cod_residente,
            'prioridad' => 'ALTO',
            'estado' => 'ABIERTA',
        ]);
    }

    public function test_atender_y_cerrar_alerta(): void
    {
        $this->actingAs($this->enfermero);

        $alerta = Alerta::create([
            'cod_residente' => $this->adulto->cod_residente,
            'origen' => 'ENFERMERIA',
            'tipo_alerta' => 'CLINICA',
            'nivel' => 'ALTO',
            'motivo' => 'Fiebre persistente 38.5°C',
            'estado' => 'ABIERTA',
            'registrado_por' => $this->enfermero->cod_usuario,
        ]);

        // Atender alerta -> pasa a EN_ATENCION
        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_residente])
            ->call('abrirModalAtenderAlerta', $alerta->cod_alerta)
            ->assertSet('modalAtenderAlerta', true)
            ->set('accionTomadaAlerta', 'Medios físicos aplicados y aviso a médico de guardia')
            ->call('guardarAtenderAlerta')
            ->assertHasNoErrors()
            ->assertSet('modalAtenderAlerta', false);

        $this->assertSame('EN_ATENCION', $alerta->fresh()->estado);

        // Cerrar alerta -> pasa a CERRADA
        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_residente])
            ->call('abrirModalCerrarAlerta', $alerta->cod_alerta)
            ->assertSet('modalCerrarAlerta', true)
            ->set('observacionCierreAlerta', 'Temperatura normalizada en 36.8°C tras medicación')
            ->call('guardarCerrarAlerta')
            ->assertHasNoErrors()
            ->assertSet('modalCerrarAlerta', false);

        $this->assertSame('CERRADA', $alerta->fresh()->estado);
    }
}
