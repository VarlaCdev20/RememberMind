<?php

namespace Tests\Feature;

use App\Livewire\Cuidados\FichaPaciente;
use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AsignacionAdultoMayor;
use App\Models\AsignacionTurnoAdulto;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\MedicacionAdulto;
use App\Models\PlanCuidado;
use App\Models\SignosVitalesAdulto;
use App\Models\TareaPlanCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);

        $this->enfermero = User::factory()->create([
            'cod_usu' => 'USU_0099',
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
            'cod_am' => 'AM100',
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

        AsignacionAdultoMayor::create([
            'cod_am' => $this->adulto->cod_am,
            'cod_habitacion' => $this->habitacion->cod_habitacion,
            'cod_cama' => $this->cama->cod_cama,
            'fecha_asignacion' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        AsignacionTurnoAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'cod_turno' => $this->turno->cod_turno,
            'cod_usu_enfermero' => $this->enfermero->cod_usu,
            'cod_usu' => $this->enfermero->cod_usu,
            'fecha_inicio' => today()->toDateString(),
            'motivo_asignacion' => 'Turno activo',
            'asignado_por' => $this->enfermero->cod_usu,
            'fecha' => today()->toDateString(),
            'nivel_supervision' => 'MEDIO',
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_modal_registrar_signos_vitales_completo(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
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

        $this->assertDatabaseHas('signos_vitales_adulto', [
            'cod_am' => $this->adulto->cod_am,
            'presion_arterial' => '125/80',
            'frecuencia_cardiaca' => 74,
            'temperatura' => 36.6,
        ]);
    }

    public function test_modal_administrar_medicacion_completo(): void
    {
        $this->actingAs($this->enfermero);

        $med = MedicacionAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'nombre_medicamento' => 'Metformina 850mg',
            'dosis' => '850 mg',
            'via_administracion' => 'Oral',
            'frecuencia' => 'Cada 12 horas',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVO',
        ]);

        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->call('abrirModalMedicacion', $med->cod_med)
            ->assertSet('modalMed', true)
            ->set('medAccion', 'ADMINISTRAR')
            ->set('medEfectoObs', 'Tolerancia digestiva adecuada')
            ->call('guardarMedicacion')
            ->assertHasNoErrors()
            ->assertSet('modalMed', false);

        $this->assertDatabaseHas('administracion_medicacion', [
            'cod_am' => $this->adulto->cod_am,
            'administrado' => true,
        ]);
    }

    public function test_modal_tarea_cuidados_completar(): void
    {
        $this->actingAs($this->enfermero);

        $plan = PlanCuidado::create([
            'cod_am' => $this->adulto->cod_am,
            'diagnostico_enfermeria' => 'Riesgo de deterioro de la integridad cutánea',
            'objetivo' => 'Mantener piel intacta',
            'fecha_inicio' => today()->toDateString(),
            'estado' => 'ACTIVO',
            'creado_por' => $this->enfermero->cod_usu,
        ]);

        $tarea = TareaPlanCuidado::create([
            'cod_plan' => $plan->cod_plan,
            'cod_am' => $this->adulto->cod_am,
            'cod_turno' => $this->turno->cod_turno,
            'titulo' => 'Cambio postural decúbito lateral',
            'area' => 'PIEL',
            'prioridad' => 'ALTA',
            'fecha_programada' => today()->toDateString(),
            'hora_programada' => '10:00:00',
            'estado' => 'PENDIENTE',
            'registrado_por' => $this->enfermero->cod_usu,
        ]);

        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
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

        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
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

        $this->assertDatabaseHas('seguimientos_diarios', [
            'cod_am' => $this->adulto->cod_am,
            'estado_general' => 'ESTABLE',
            'alimentacion' => 'COMPLETA',
        ]);
    }

    public function test_modal_reportar_incidente_genera_alerta(): void
    {
        $this->actingAs($this->enfermero);

        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->call('abrirModalIncidente')
            ->assertSet('modalIncidente', true)
            ->set('incidenteTipo', 'CAIDA')
            ->set('incidenteNivel', 'ALTO')
            ->set('incidenteMotivo', 'Tropiezo al incorporarse de la cama sin asistencia')
            ->call('guardarIncidente')
            ->assertHasNoErrors()
            ->assertSet('modalIncidente', false);

        $this->assertDatabaseHas('alertas_adulto', [
            'cod_am' => $this->adulto->cod_am,
            'tipo_alerta' => 'CAIDA',
            'nivel' => 'ALTO',
            'estado' => 'ABIERTA',
        ]);
    }

    public function test_atender_y_cerrar_alerta(): void
    {
        $this->actingAs($this->enfermero);

        $alerta = AlertaAdulto::create([
            'cod_am' => $this->adulto->cod_am,
            'origen' => 'ENFERMERIA',
            'tipo_alerta' => 'CLINICA',
            'nivel' => 'ALTO',
            'motivo' => 'Fiebre persistente 38.5°C',
            'estado' => 'ABIERTA',
            'registrado_por' => $this->enfermero->cod_usu,
        ]);

        // Atender alerta -> pasa a EN_ATENCION
        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->call('abrirModalAtenderAlerta', $alerta->cod_alerta)
            ->assertSet('modalAtenderAlerta', true)
            ->set('accionTomadaAlerta', 'Medios físicos aplicados y aviso a médico de guardia')
            ->call('guardarAtenderAlerta')
            ->assertHasNoErrors()
            ->assertSet('modalAtenderAlerta', false);

        $this->assertSame('EN_ATENCION', $alerta->fresh()->estado);

        // Cerrar alerta -> pasa a CERRADA
        Livewire::test(FichaPaciente::class, ['adulto' => $this->adulto->cod_am])
            ->call('abrirModalCerrarAlerta', $alerta->cod_alerta)
            ->assertSet('modalCerrarAlerta', true)
            ->set('observacionCierreAlerta', 'Temperatura normalizada en 36.8°C tras medicación')
            ->call('guardarCerrarAlerta')
            ->assertHasNoErrors()
            ->assertSet('modalCerrarAlerta', false);

        $this->assertSame('CERRADA', $alerta->fresh()->estado);
    }
}
