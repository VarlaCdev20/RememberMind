<?php
namespace Tests\Feature;

use App\Livewire\Cuidados\{TareasPlanPanel, PaseTurnoPanel};
use App\Models\{AdultoMayor, User, TurnoEnfermeria, PlanCuidado, TareaPlanCuidado, PaseTurno, AlertaAdulto, MedicacionAdulto, AdministracionMedicacion, SeguimientoDiario, AsignacionTurnoAdulto, RecepcionTurno};
use Database\Seeders\{EstadoAdultoSeeder, RolesAndPermissionsSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CuidadosFlujoTest extends TestCase
{
    use RefreshDatabase;

    public function test_tarea_pendiente_genera_alerta_resultado_y_pase_conservan_historial(): void
    {
        Carbon::setTestNow('2026-09-11 10:00:00');
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($user);
        $adulto = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);
        $saliente = TurnoEnfermeria::create(['orden' => 1, 'nombre' => 'Mañana', 'hora_inicio' => '08:00', 'hora_fin' => '16:00', 'estado' => 'ACTIVO']);
        $entrante = TurnoEnfermeria::create(['orden' => 2, 'nombre' => 'Tarde', 'hora_inicio' => '16:00', 'hora_fin' => '23:00', 'estado' => 'ACTIVO']);
        $salienteUser = User::factory()->create(['estado' => 'ACTIVO']);
        $salienteUser->assignRole('ENFERMEROS');
        $entranteUser = User::factory()->create(['estado' => 'ACTIVO']);
        $entranteUser->assignRole('ENFERMEROS');
        foreach ([[$salienteUser, $saliente], [$entranteUser, $entrante]] as [$enfermero, $turno]) {
            AsignacionTurnoAdulto::create(['cod_am' => $adulto->cod_am, 'cod_turno' => $turno->cod_turno, 'cod_usu_enfermero' => $enfermero->cod_usu, 'fecha_inicio' => today(), 'estado' => 'ACTIVA', 'nivel_supervision' => 'ESTANDAR']);
        }
        RecepcionTurno::create(['cod_turno' => $saliente->cod_turno, 'cod_usuario' => $salienteUser->cod_usu, 'fecha_hora_recepcion' => now()]);
        $plan = PlanCuidado::create(['cod_am' => $adulto->cod_am, 'tipo_plan' => 'INICIAL', 'version' => 1, 'nivel_cuidado' => 'ESTANDAR', 'estado' => 'ACTIVO', 'origen' => 'ADMISION', 'fecha_inicio' => today()]);
        Livewire::test(TareasPlanPanel::class)->call('abrirCrear', $plan->cod_plan)
            ->set('codTurno', $saliente->cod_turno)->set('area', 'HIDRATACION')
            ->set('titulo', 'Revisar hidratación')->set('fechaProgramada', today()->subDay()->format('Y-m-d'))
            ->call('guardarTarea')->assertHasNoErrors()->set('search', 'hidratación')->assertSee('Revisar hidratación');
        $tarea = TareaPlanCuidado::sole();
        $this->assertSame($plan->cod_plan, $tarea->cod_plan);
        $this->assertDatabaseHas('alertas_adulto', ['cod_am' => $adulto->cod_am, 'origen' => 'PLAN']);
        Livewire::test(TareasPlanPanel::class)->call('abrirResultado', $tarea->cod_tarea)
            ->set('resultado', 'Control efectuado y registrado.')->call('guardarResultado')->assertHasNoErrors();
        $this->assertSame('REALIZADA', $tarea->fresh()->estado);
        $this->actingAs($salienteUser);
        Livewire::test(PaseTurnoPanel::class)->call('abrirGenerar')
            ->set('codAm', $adulto->cod_am)->set('turnoSalienteId', $saliente->cod_turno)
            ->set('turnoEntranteId', $entrante->cod_turno)->set('enfermeroEntranteId', $entranteUser->cod_usu)
            ->set('resumenTurno', 'Se entrega seguimiento del residente y sus alertas pendientes.')
            ->call('generarPase')->assertHasNoErrors();
        $pase = PaseTurno::sole();
        $this->assertCount(1, $pase->alertas_activas_json);
        Livewire::test(PaseTurnoPanel::class)->call('abrirVer', $pase->cod_pase)
            ->assertSee('TAREA PENDIENTE U OMITIDA');
        Carbon::setTestNow('2026-09-11 16:30:00');
        RecepcionTurno::create(['cod_turno' => $entrante->cod_turno, 'cod_usuario' => $entranteUser->cod_usu, 'fecha_hora_recepcion' => now()]);
        $this->actingAs($entranteUser);
        Livewire::test(PaseTurnoPanel::class)->call('recibirPase', $pase->cod_pase)->assertHasNoErrors();
        $this->assertSame('RECIBIDO', $pase->fresh()->estado);
        $this->assertNotNull($pase->fresh()->fecha_recibido);
        Carbon::setTestNow();
    }

    public function test_omision_y_solicitud_medica_generan_alertas_con_origen_real(): void
    {
        $this->seed(EstadoAdultoSeeder::class);
        $adulto = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);
        $turno = TurnoEnfermeria::create(['orden' => 1, 'nombre' => 'Mañana', 'hora_inicio' => '08:00', 'hora_fin' => '16:00', 'estado' => 'ACTIVO']);
        $med = MedicacionAdulto::create(['cod_am' => $adulto->cod_am, 'nombre_medicamento' => 'Medicación de prueba', 'dosis' => 'Según orden', 'frecuencia' => 'Diaria', 'fecha_inicio' => today()]);
        AdministracionMedicacion::create(['cod_am' => $adulto->cod_am, 'cod_med_adulto' => $med->cod_med_adulto, 'fecha' => today(), 'hora_programada' => '08:00', 'administrado' => false, 'motivo_omision' => 'Rechazo registrado']);
        SeguimientoDiario::create(['cod_am' => $adulto->cod_am, 'cod_turno' => $turno->cod_turno, 'fecha' => today(), 'requiere_medico' => true, 'observacion' => 'Solicita revisión médica por cambio observado.']);
        $this->assertDatabaseHas('alertas_adulto', ['cod_am' => $adulto->cod_am, 'origen' => 'MEDICACION']);
        $this->assertDatabaseHas('alertas_adulto', ['cod_am' => $adulto->cod_am, 'origen' => 'SOLICITUD_MEDICA']);
        $this->assertSame(2, AlertaAdulto::count());
        $this->assertSame(1, MedicacionAdulto::count());
        $this->assertSame(1, AdministracionMedicacion::count());
    }
}


