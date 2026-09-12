<?php
namespace Tests\Feature;

use App\Livewire\Cuidados\{AsignacionTurnoPanel, PlanCuidadoPanel, SeguimientoDiarioPanel};
use App\Models\{AdultoMayor, User, Habitacion, Cama, TurnoEnfermeria, AsignacionTurnoAdulto, PlanCuidado, SeguimientoDiario};
use Database\Seeders\{EstadoAdultoSeeder, RolesAndPermissionsSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OcupacionCuidadosTest extends TestCase
{
    use RefreshDatabase;

    public function test_ocupacion_impide_doble_asignacion_integra_plan_seguimiento_y_conserva_historia(): void
    {
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);
        $user = User::factory()->create();
        $user->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($user);
        $adulto = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);
        $otro = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);
        $habitacion = Habitacion::create(['nombre' => 'Habitación 101', 'codigo' => '101', 'tipo_habitacion' => 'INDIVIDUAL', 'capacidad' => 1, 'estado' => 'DISPONIBLE']);
        $cama = Cama::create(['cod_habitacion' => $habitacion->cod_habitacion, 'codigo' => '101-A', 'estado' => 'DISPONIBLE']);
        $turno = TurnoEnfermeria::create(['nombre' => 'Turno operativo', 'orden' => 1, 'hora_inicio' => '00:00', 'hora_fin' => '23:59', 'estado' => 'ACTIVO']);
        $panel = Livewire::test(AsignacionTurnoPanel::class)->call('abrirCrear')
            ->set('codAm', $adulto->cod_am)->set('codTurno', $turno->cod_turno)->set('codEnfermero', $user->cod_usu)
            ->set('codHabitacion', $habitacion->cod_habitacion)->set('codCama', $cama->cod_cama)
            ->set('motivoAsignacion', 'Asignación para seguimiento de cuidado.')
            ->call('guardar')->assertHasNoErrors();
        $this->assertSame($cama->cod_cama, $adulto->fresh()->cod_cama);
        $this->assertSame('OCUPADA', $cama->fresh()->estado);
        $panel->call('abrirCrear')->set('codAm', $otro->cod_am)->set('codTurno', $turno->cod_turno)
            ->set('codEnfermero', $user->cod_usu)->set('codHabitacion', $habitacion->cod_habitacion)
            ->set('codCama', $cama->cod_cama)->set('motivoAsignacion', 'Intento de segunda ocupación.')
            ->call('guardar')->assertHasErrors('codCama');
        $this->assertDatabaseCount('asignaciones_turno_adulto', 1);
        Livewire::test(PlanCuidadoPanel::class)->call('abrirCrear')->set('codAm', $adulto->cod_am)
            ->set('resumen', 'Plan de seguimiento del residente.')->set('estadoPlan', 'ACTIVO')
            ->call('guardar')->assertHasNoErrors();
        $plan = PlanCuidado::sole();
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');
        AsignacionTurnoAdulto::sole()->update(['cod_usu_enfermero' => $enfermero->cod_usu]);
        \App\Models\RecepcionTurno::create(['cod_turno' => $turno->cod_turno, 'cod_usuario' => $enfermero->cod_usu, 'fecha_hora_recepcion' => now()]);
        $this->actingAs($enfermero);
        Livewire::test(SeguimientoDiarioPanel::class)->call('abrirCrear')->set('codAm', $adulto->cod_am)
            ->set('codTurno', $turno->cod_turno)->set('codPlan', $plan->cod_plan)
            ->set('requiereMedico', true)->set('observacion', 'Cambio observado que requiere revisión profesional.')
            ->call('guardar')->assertHasNoErrors();
        $this->assertDatabaseHas('alertas_adulto', ['cod_am' => $adulto->cod_am, 'origen' => 'SOLICITUD_MEDICA']);
        $this->get(route('admin.adultos-mayores.show', $adulto))->assertOk();
        $this->get(route('admin.enfermeria.pacientes.ficha', $adulto))->assertOk();
        $this->actingAs($user);
        $panel->call('finalizarAsignacion', AsignacionTurnoAdulto::sole()->cod_asig_turno);
        $this->assertNull($adulto->fresh()->cod_cama);
        $this->assertSame('DISPONIBLE', $cama->fresh()->estado);
        $this->assertSame('FINALIZADA', AsignacionTurnoAdulto::sole()->estado);
        $this->assertDatabaseCount('seguimientos_diarios', 1);
    }
}


