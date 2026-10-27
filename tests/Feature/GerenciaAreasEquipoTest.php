<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\PlanCuidado;
use App\Models\Residente;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarAreasInstitucionales;
use App\Modulos\Institucion\Presentacion\Livewire\Paginas\AreasInstitucionalesPanel;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GerenciaAreasEquipoTest extends TestCase
{
    use RefreshDatabase;

    public function test_area_muestra_equipo_por_fecha_sin_inventar_jefatura_permanente(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        $this->actingAs($actor);
        $persona = User::factory()->create(['nombres' => 'Persona Sintética', 'ap_paterno' => 'Prueba']);
        $persona->assignRole('ENFERMEROS');
        $area = Area::create(['nombre' => 'Área de prueba', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Mañana', 'hora_inicio' => '08:00:00', 'hora_cierre' => '16:00:00', 'estado' => 'ACTIVO', 'orden' => 1]);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => '2026-10-26', 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_personal' => $persona->personal->getKey(), 'cod_area' => $area->getKey(), 'cod_jornada' => $jornada->getKey(), 'estado' => 'ACTIVA']);
        $panel = Livewire::test(AreasInstitucionalesPanel::class)->assertSee('Áreas y equipo')->assertDontSee('Ranking')
            ->set('fechaEquipo', '2026-10-26')->call('verArea', $area->getKey())->assertSee('Persona Sintética Prueba')->assertSee('Mañana')
            ->set('fechaEquipo', '2026-10-27')->assertSee('Sin asignaciones para esta fecha');
        $panel->call('cerrarFicha')->call('editarArea', $area->getKey())->set('nombre', 'Área renombrada')->set('estado', 'INACTIVO')->call('guardarArea');
        $this->assertDatabaseHas('areas', ['cod_area' => $area->getKey(), 'nombre' => 'ÁREA RENOMBRADA', 'estado' => 'ACTIVA']);
        $this->assertDatabaseCount('asignaciones_personal', 1);
    }

    public function test_sin_lectura_de_areas_no_serializa_datos(): void
    {
        $this->actingAs(User::factory()->create());
        Livewire::test(AreasInstitucionalesPanel::class)->assertForbidden();
    }

    public function test_da01_bloquea_desactivar_con_asignaciones_y_permite_despues_de_cerrarlas(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        $this->actingAs($actor);
        $persona = User::factory()->create(['nombres' => 'Persona', 'ap_paterno' => 'Prueba']);
        $persona->assignRole('ENFERMEROS');
        $area = Area::create(['nombre' => 'Área con compromisos', 'estado' => 'ACTIVA']);
        $turno = Turno::create(['nombre' => 'Mañana', 'hora_inicio' => '08:00:00', 'hora_cierre' => '16:00:00', 'estado' => 'ACTIVO', 'orden' => 1]);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => '2026-10-26', 'estado' => 'ABIERTA']);
        $asignacion = AsignacionPersonal::create(['cod_personal' => $persona->personal->getKey(), 'cod_area' => $area->getKey(), 'cod_jornada' => $jornada->getKey(), 'estado' => 'ACTIVA']);
        Livewire::test(AreasInstitucionalesPanel::class)->call('toggleEstado', $area->getKey())->assertHasErrors('estado');
        $this->assertSame('ACTIVA', $area->fresh()->estado);
        $this->assertSame('ACTIVA', $asignacion->fresh()->estado);
        $asignacion->update(['estado' => 'INACTIVO']);
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $plan = PlanCuidado::create(['cod_residente' => $residente->getKey(), 'cod_personal' => $persona->personal->getKey(), 'cod_area' => $area->getKey(), 'nombre' => 'Plan de prueba', 'objetivo_general' => 'Seguimiento de prueba', 'estado' => 'ACTIVO']);
        Livewire::test(AreasInstitucionalesPanel::class)->call('toggleEstado', $area->getKey())->assertHasErrors('estado');
        $this->assertSame('ACTIVA', $area->fresh()->estado);
        $plan->update(['estado' => 'CERRADO', 'fecha_hora_cierre' => now()]);
        Livewire::test(AreasInstitucionalesPanel::class)->call('toggleEstado', $area->getKey())->assertHasNoErrors();
        $this->assertSame('INACTIVO', $area->fresh()->estado);
    }

    public function test_fallo_de_auditoria_revierte_area_y_reintento_no_duplica(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        $this->actingAs($actor);
        $accion = app(GestionarAreasInstitucionales::class);
        $datos = ['nombre' => ' Área de prueba ', 'descripcion' => 'Descripción original', 'estado' => 'ACTIVO', 'isEdit' => false];
        $auditorias = Activity::count();
        $dispatcher = Activity::getEventDispatcher();
        Activity::setEventDispatcher(clone $dispatcher);
        try {
            Activity::creating(fn () => throw new \RuntimeException('Fallo sintético de auditoría del área'));
            try {
                $accion->guardarArea($actor, $datos);
                $this->fail('El área no puede persistir después del fallo de la operación.');
            } catch (\RuntimeException $error) {
                $this->assertSame('Fallo sintético de auditoría del área', $error->getMessage());
            }
        } finally {
            Activity::setEventDispatcher($dispatcher);
        }
        $this->assertDatabaseCount('areas', 0);
        $this->assertSame($auditorias, Activity::count());
        $accion->guardarArea($actor, $datos);
        $this->assertDatabaseHas('areas', ['nombre' => 'ÁREA DE PRUEBA']);
        try {
            $accion->guardarArea($actor, [...$datos, 'nombre' => 'área de prueba']);
            $this->fail('La segunda alta normalizada debe rechazarse.');
        } catch (ValidationException $error) {
            $this->assertArrayHasKey('nombre', $error->errors());
        }
        $this->assertDatabaseCount('areas', 1);
        $this->assertSame($auditorias + 1, Activity::count());
    }

    public function test_revocar_lectura_de_equipo_abierto_no_mantiene_datos_residuales(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('GERENTE');
        $this->actingAs($actor);
        $persona = User::factory()->create(['nombres' => 'Equipo restringido', 'ap_paterno' => 'Prueba']);
        $persona->assignRole('ENFERMEROS');
        $area = Area::create(['nombre' => 'Área de prueba', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'Mañana', 'hora_inicio' => '08:00', 'hora_cierre' => '16:00', 'estado' => 'ACTIVO']);
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => '2026-10-26', 'estado' => 'ABIERTA']);
        AsignacionPersonal::create(['cod_personal' => $persona->personal->getKey(), 'cod_area' => $area->getKey(), 'cod_jornada' => $jornada->getKey(), 'estado' => 'ACTIVO']);
        $panel = Livewire::test(AreasInstitucionalesPanel::class)->set('fechaEquipo', '2026-10-26')
            ->call('verArea', $area->getKey())->assertSee('Equipo restringido Prueba');
        Role::findByName('GERENTE', 'web')->revokePermissionTo('asignaciones_personal.ver');
        $panel->call('$refresh')->assertDontSee('Equipo restringido Prueba');
        $this->assertNull($panel->viewData('equipo'));
        $this->assertDatabaseCount('asignaciones_personal', 1);
    }
}
