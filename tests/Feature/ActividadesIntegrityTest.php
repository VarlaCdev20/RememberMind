<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Administracion\Actividades\ActividadesPanel;
use App\Frontend\Livewire\Administracion\Actividades\ParticipacionPanel;
use App\Models\Actividad;
use App\Models\AdultoMayor;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ActividadesIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_participacion_usa_personal_y_area_asignados_al_usuario_actual(): void
    {
        User::factory()->create([
            'nombres' => 'Personal',
            'ap_paterno' => 'Ajeno',
        ]);
        $usuario = User::factory()->create([
            'nombres' => 'Ana',
            'ap_paterno' => 'Responsable',
        ]);
        $usuario->givePermissionTo(['actividades.ver', 'actividades.gestionar']);
        $personal = $usuario->personal()->firstOrFail();
        $area = Area::query()->create([
            'cod_area' => 'ARE_ACT_TEST',
            'nombre' => 'Terapia ocupacional',
            'estado' => 'ACTIVA',
        ]);
        $turno = Turno::query()->create([
            'cod_turno' => 'TUR_ACT_TEST',
            'nombre' => 'Turno de actividades',
            'hora_inicio' => '08:00',
            'hora_cierre' => '16:00',
            'orden' => 1,
            'estado' => 'ACTIVO',
        ]);
        $jornada = Jornada::query()->create([
            'cod_jornada' => 'JOR_ACT_TEST',
            'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);
        AsignacionPersonal::query()->create([
            'cod_asignacion_personal' => 'ASP_ACT_TEST',
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $personal->cod_personal,
            'cod_area' => $area->cod_area,
            'fecha_asignacion' => now(),
            'estado' => 'ACTIVA',
        ]);
        $residente = AdultoMayor::factory()->create();

        $this->actingAs($usuario);
        Livewire::test(ParticipacionPanel::class)
            ->set('codResidente', $residente->cod_residente)
            ->set('codTipoAct', 'COGNITIVA')
            ->set('fecha', today()->toDateString())
            ->set('hora', '10:00')
            ->set('estado', 'PROGRAMADA')
            ->call('guardarActividad')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('actividades', [
            'cod_personal' => $personal->cod_personal,
            'cod_area' => $area->cod_area,
            'tipo' => 'COGNITIVA',
        ]);

        $actividad = Actividad::query()->sole();
        Livewire::test(ParticipacionPanel::class)
            ->call('abrirEditar', $actividad->cod_actividad)
            ->assertSet('codResidente', $residente->cod_residente)
            ->call('cancelarActividad', $actividad->cod_actividad);

        $this->assertSame('CANCELADA', $actividad->fresh()->estado);
    }

    public function test_no_crea_actividad_con_el_primer_personal_si_falta_contexto_propio(): void
    {
        User::factory()->create([
            'nombres' => 'Personal',
            'ap_paterno' => 'Ajeno',
        ]);
        $usuario = User::factory()->create();
        $usuario->givePermissionTo(['actividades.ver', 'actividades.gestionar']);
        $residente = AdultoMayor::factory()->create();

        $this->actingAs($usuario);
        Livewire::test(ParticipacionPanel::class)
            ->set('codResidente', $residente->cod_residente)
            ->set('codTipoAct', 'SOCIAL')
            ->set('fecha', today()->toDateString())
            ->set('hora', '11:00')
            ->set('estado', 'PROGRAMADA')
            ->call('guardarActividad')
            ->assertHasErrors(['codTipoAct']);

        $this->assertDatabaseCount('actividades', 0);
    }

    public function test_permiso_de_lectura_no_autoriza_mutaciones_de_actividades(): void
    {
        $usuario = User::factory()->create();
        $usuario->givePermissionTo('actividades.ver');
        $residente = AdultoMayor::factory()->create();

        $this->actingAs($usuario);
        Livewire::test(ActividadesPanel::class)
            ->set('codResidente', $residente->cod_residente)
            ->set('codTipoAct', 'RECREATIVA')
            ->set('fecha', today()->toDateString())
            ->set('hora', '12:00')
            ->set('estado', 'PROGRAMADA')
            ->call('guardarActividad')
            ->assertForbidden();

        $this->assertDatabaseCount('actividades', 0);
    }
}
