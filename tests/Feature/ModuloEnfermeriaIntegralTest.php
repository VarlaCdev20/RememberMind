<?php

namespace Tests\Feature;

use App\Livewire\Cuidados\AgendaEnfermeria;
use App\Livewire\Cuidados\FichaPaciente;
use App\Livewire\Cuidados\RegistrosEnfermeria;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AsignacionTurnoAdulto;
use App\Models\MedicacionAdulto;
use App\Models\LesionResidente;
use App\Models\PlanCuidado;
use App\Models\TareaPlanCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Enfermeria\AgendaTurnoService;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class ModuloEnfermeriaIntegralTest extends TestCase
{
    use RefreshDatabase;

    private User $enfermero;
    private AdultoMayor $residente;
    private TurnoEnfermeria $turno;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-11 10:00:00');
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);
        $this->enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $this->enfermero->assignRole('ENFERMEROS');
        $this->turno = TurnoEnfermeria::create(['orden' => 1, 'nombre' => 'Mañana', 'hora_inicio' => '07:00', 'hora_fin' => '15:00', 'estado' => 'ACTIVO']);
        $this->residente = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001', 'nombres' => 'Rosa', 'ap_paterno' => 'Mamani']);
        AsignacionTurnoAdulto::create([
            'cod_am' => $this->residente->cod_am, 'cod_turno' => $this->turno->cod_turno,
            'cod_usu_enfermero' => $this->enfermero->cod_usu, 'fecha_inicio' => today(),
            'nivel_supervision' => 'ESTANDAR', 'estado' => 'ACTIVA', 'motivo_asignacion' => 'Prueba integral',
            'asignado_por' => $this->enfermero->cod_usu,
        ]);
        $this->actingAs($this->enfermero);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_agenda_prioriza_alertas_medicacion_y_tareas_y_permite_recibir_turno(): void
    {
        MedicacionAdulto::create([
            'cod_am' => $this->residente->cod_am, 'nombre_medicamento' => 'Losartán', 'dosis' => '50 mg',
            'frecuencia' => 'CADA 12 HORAS', 'intervalo_horas' => 12, 'via_administracion' => 'ORAL',
            'hora_programada' => '08:00', 'fecha_inicio' => today(), 'estado' => 'ACTIVO',
        ]);
        $plan = PlanCuidado::create(['cod_am' => $this->residente->cod_am, 'tipo_plan' => 'INICIAL', 'version' => 1, 'nivel_cuidado' => 'ESTANDAR', 'estado' => 'ACTIVO', 'origen' => 'ADMISION', 'fecha_inicio' => today()]);
        TareaPlanCuidado::create(['cod_plan' => $plan->cod_plan, 'cod_am' => $this->residente->cod_am, 'cod_turno' => $this->turno->cod_turno, 'area' => 'HIGIENE', 'titulo' => 'Higiene matutina', 'fecha_programada' => today(), 'hora_programada' => '09:00', 'prioridad' => 'MEDIA', 'estado' => 'PENDIENTE']);
        AlertaAdulto::create(['cod_am' => $this->residente->cod_am, 'origen' => 'SIGNOS', 'tipo_alerta' => 'SATURACIÓN BAJA', 'nivel' => 'CRITICO', 'motivo' => 'Requiere control inmediato.', 'estado' => 'ABIERTA']);

        $agenda = app(AgendaTurnoService::class)->generar($this->enfermero);
        $this->assertSame('ALERTA', $agenda->first()['tipo']);
        $this->assertTrue($agenda->contains(fn ($i) => $i['tipo'] === 'MEDICACION' && $i['estado'] === 'VENCIDA'));
        $this->assertTrue($agenda->contains(fn ($i) => $i['tipo'] === 'TAREA' && $i['estado'] === 'VENCIDA'));

        Livewire::test(AgendaEnfermeria::class)->assertSee('Rosa')->call('recibirTurno');
        $this->get(route('admin.enfermeria.reportes'))->assertOk()->assertSee('Reportes de Enfermería');
        $this->assertDatabaseHas('recepciones_turno', ['cod_turno' => $this->turno->cod_turno, 'cod_usuario' => $this->enfermero->cod_usu]);
    }

    public function test_estado_fuera_del_centro_suspende_rutinas_sin_ocultar_alertas(): void
    {
        MedicacionAdulto::create(['cod_am' => $this->residente->cod_am, 'nombre_medicamento' => 'Vitamina D', 'dosis' => '1 cápsula', 'frecuencia' => 'DIARIA', 'via_administracion' => 'ORAL', 'hora_programada' => '09:00', 'fecha_inicio' => today(), 'estado' => 'ACTIVO']);
        AlertaAdulto::create(['cod_am' => $this->residente->cod_am, 'origen' => 'MANUAL', 'tipo_alerta' => 'SEGUIMIENTO', 'nivel' => 'MEDIO', 'motivo' => 'Seguimiento pendiente.', 'estado' => 'ABIERTA']);

        Livewire::test(RegistrosEnfermeria::class, ['adulto' => $this->residente->cod_am])
            ->set('codAm', $this->residente->cod_am)->set('estadoOperativo', 'HOSPITALIZADO')->set('motivoEstado', 'Derivación hospitalaria de emergencia.')
            ->call('cambiarEstadoOperativo')->assertHasNoErrors();

        $this->assertSame('HOSPITALIZADO', $this->residente->fresh()->estado_operativo);

        $agenda = app(AgendaTurnoService::class)->generar($this->enfermero);
        $this->assertTrue($agenda->contains(fn ($i) => $i['tipo'] === 'ALERTA'));
        $this->assertFalse($agenda->contains(fn ($i) => $i['tipo'] === 'MEDICACION'));
        $this->assertDatabaseHas('historial_estado_operativo', ['cod_am' => $this->residente->cod_am, 'estado_nuevo' => 'HOSPITALIZADO']);
    }

    public function test_cuidado_firmado_valida_baja_ingesta_y_genera_alerta_por_cambio_basal(): void
    {
        $componente = Livewire::test(RegistrosEnfermeria::class, ['adulto' => $this->residente->cod_am])
            ->set('codAm', $this->residente->cod_am)->set('tipo', 'ALIMENTACION')->set('subtipo', 'DESAYUNO')->set('porcentaje', 25)
            ->call('guardarCuidado')->assertHasErrors(['motivo']);

        $componente->set('motivo', 'Rechazo persistente de alimentos.')
            ->set('cambioBasal', 'PEOR')->call('guardarCuidado')->assertHasNoErrors();

        $this->assertDatabaseHas('registros_cuidados', ['cod_am' => $this->residente->cod_am, 'tipo' => 'ALIMENTACION', 'estado' => 'FIRMADO', 'porcentaje' => 25]);
        $this->assertDatabaseHas('alertas_adulto', ['cod_am' => $this->residente->cod_am, 'tipo_alerta' => 'CAMBIO RESPECTO AL ESTADO BASAL', 'estado' => 'ABIERTA']);
    }

    public function test_caida_crea_incidente_lesion_y_alerta_con_datos_obligatorios(): void
    {
        Livewire::test(RegistrosEnfermeria::class, ['adulto' => $this->residente->cod_am])
            ->set('codAm', $this->residente->cod_am)->set('tipoIncidente', 'CAIDA')->set('lugarIncidente', 'Baño')
            ->set('descripcionIncidente', 'Residente encontrado en el piso durante la higiene.')
            ->set('presenciado', true)->set('testigo', 'Auxiliar de turno')
            ->set('hayLesion', true)->set('tipoLesion', 'HEMATOMA')->set('zonaLesion', 'Brazo izquierdo')
            ->set('medicoInformado', true)->call('guardarIncidente')->assertHasNoErrors();

        $this->assertDatabaseHas('incidentes_residente', ['cod_am' => $this->residente->cod_am, 'tipo' => 'CAIDA', 'medico_informado' => true]);
        $this->assertDatabaseHas('lesiones_residente', ['cod_am' => $this->residente->cod_am, 'tipo' => 'HEMATOMA']);
        $this->assertDatabaseHas('alertas_adulto', ['cod_am' => $this->residente->cod_am, 'tipo_alerta' => 'CAIDA', 'nivel' => 'ALTO']);

        $lesion = LesionResidente::where('cod_am', $this->residente->cod_am)->firstOrFail();
        Livewire::test(RegistrosEnfermeria::class)->set('codAm', $this->residente->cod_am)
            ->set('lesionId', $lesion->cod_lesion)->set('largoLesion', 3.2)->set('anchoLesion', 1.5)
            ->set('aspectoLesion', 'Hematoma violáceo sin sangrado activo.')
            ->set('accionLesion', 'Aplicación de frío local y vigilancia.')
            ->call('guardarSeguimientoLesion')->assertHasNoErrors();
        $this->assertDatabaseHas('seguimientos_lesion', ['cod_lesion' => $lesion->cod_lesion, 'largo_cm' => 3.2]);
    }

    public function test_prn_exige_valoracion_y_programa_reevaluacion_sin_aparecer_como_dosis_vencida(): void
    {
        $plan = PlanCuidado::create(['cod_am' => $this->residente->cod_am, 'tipo_plan' => 'INICIAL', 'version' => 1, 'nivel_cuidado' => 'ESTANDAR', 'estado' => 'ACTIVO', 'origen' => 'ADMISION', 'fecha_inicio' => today()]);
        $med = MedicacionAdulto::create([
            'cod_am' => $this->residente->cod_am, 'nombre_medicamento' => 'Paracetamol', 'dosis' => '500 mg',
            'frecuencia' => 'PRN', 'es_prn' => true, 'condicion_prn' => 'Dolor igual o mayor a 5/10',
            'via_administracion' => 'ORAL', 'fecha_inicio' => today(), 'estado' => 'ACTIVO',
        ]);

        $this->assertFalse(app(AgendaTurnoService::class)->generar($this->enfermero)->contains(fn ($i) => $i['tipo'] === 'MEDICACION'));

        $ficha = Livewire::test(FichaPaciente::class, ['adulto' => $this->residente->cod_am])
            ->call('abrirAdministrarMed', $med->cod_med_adulto)
            ->call('guardarMedicacion')->assertHasErrors(['medMotivoPrn', 'medValoracionPrevia']);
        $ficha->set('medMotivoPrn', 'Dolor lumbar persistente.')
            ->set('medValoracionPrevia', 'Dolor EVA 7 sin signos neurológicos asociados.')
            ->set('medIntensidadPrevia', 7)->call('guardarMedicacion')->assertHasNoErrors();

        $this->assertDatabaseHas('administracion_medicacion', ['cod_med_adulto' => $med->cod_med_adulto, 'resultado' => 'ADMINISTRADO', 'requiere_reevaluacion' => true]);
        $this->assertDatabaseHas('tareas_plan_cuidado', ['cod_plan' => $plan->cod_plan, 'area' => 'REEVALUACION', 'estado' => 'PENDIENTE']);
    }
}
