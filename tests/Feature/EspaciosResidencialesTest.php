<?php

namespace Tests\Feature;

use App\Backend\Modulos\Admisiones\Acciones\GuardarEspacioResidencial;
use App\Frontend\Livewire\Admisiones\HabitacionesPanel;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class EspaciosResidencialesTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['habitaciones.ver', 'habitaciones.crear', 'habitaciones.editar', 'camas.crear', 'camas.editar', 'admisiones.ver_dashboard', 'ocupaciones_cama.ver', 'residentes.ver'] as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }
        Role::findOrCreate('FAMILIAR', 'web');
        $this->usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $this->usuario->givePermissionTo(['habitaciones.ver', 'habitaciones.crear', 'habitaciones.editar', 'camas.crear', 'camas.editar', 'admisiones.ver_dashboard', 'ocupaciones_cama.ver', 'residentes.ver']);
        $this->actingAs($this->usuario);
    }

    public function test_modales_guardan_campos_v2_y_auditoria_sin_asignar_personas(): void
    {
        Livewire::test(HabitacionesPanel::class)->dispatch('crear-habitacion')
            ->set('codigo', ' hab-sintetica ')->set('piso', 'Planta prueba')->set('nombre', 'Sala sintética')->set('capacidad', '2')->set('tipo', 'Compartida')
            ->call('guardarHabitacion')->assertHasNoErrors()->assertSet('modalHabitacion', false)->assertDispatched('espacios-actualizados');
        $hab = Habitacion::where('codigo', 'HAB-SINTETICA')->sole();
        $this->assertSame('Planta prueba', $hab->piso);
        $this->assertSame('Compartida', $hab->tipo);
        Livewire::test(HabitacionesPanel::class)->dispatch('crear-cama', codHabitacion: $hab->getKey())
            ->set('codigo', ' cama-a ')->set('tipo', 'Articulada')->call('guardarCama')->assertHasNoErrors()->assertDispatched('espacios-actualizados');
        $this->assertDatabaseHas('camas', ['codigo' => 'CAMA-A', 'cod_habitacion' => $hab->getKey(), 'tipo' => 'Articulada', 'estado' => 'ACTIVA']);
        $this->assertSame(2, Activity::where('log_name', 'infraestructura')->where('causer_id', $this->usuario->getKey())->count());
        $this->assertDatabaseCount('ocupaciones_cama', 0);
        $this->assertDatabaseCount('residentes', 0);
    }

    public function test_capacidad_completa_y_reduccion_no_persisten_cambios(): void
    {
        $hab = Habitacion::create(['codigo' => 'H-PRUEBA', 'capacidad' => 2, 'estado' => 'ACTIVA']);
        foreach (['CA', 'CB'] as $codigo) {
            Cama::create(['codigo' => $codigo, 'cod_habitacion' => $hab->getKey(), 'estado' => 'ACTIVA']);
        }
        Livewire::test(HabitacionesPanel::class)->call('abrirCrearCama', $hab->getKey())->set('codigo', 'CC')->call('guardarCama')->assertHasErrors('codigo');
        Livewire::test(HabitacionesPanel::class)->call('abrirEditarHabitacion', $hab->getKey())->set('capacidad', '1')->call('guardarHabitacion')->assertHasErrors('capacidad');
        $this->assertSame(2, (int) $hab->fresh()->capacidad);
        $this->assertDatabaseCount('camas', 2);
        $this->assertSame(0, Activity::where('log_name', 'infraestructura')->count());
    }

    public function test_referencia_normalizada_duplicada_no_crea_otro_espacio(): void
    {
        Habitacion::create(['codigo' => 'H-PRUEBA', 'estado' => 'ACTIVA']);
        Livewire::test(HabitacionesPanel::class)->call('abrirCrearHabitacion')->set('codigo', ' h-prueba ')->call('guardarHabitacion')->assertHasErrors('codigo');
        $this->assertDatabaseCount('habitaciones', 1);
    }

    public function test_permiso_activo_y_policy_se_revalidan_al_guardar(): void
    {
        $form = Livewire::test(HabitacionesPanel::class)->call('abrirCrearHabitacion')->set('codigo', 'H-PRUEBA');
        $this->usuario->revokePermissionTo('habitaciones.crear');
        $form->call('guardarHabitacion')->assertForbidden();
        $this->usuario->givePermissionTo('habitaciones.crear');
        $this->usuario->update(['estado' => 'INACTIVO']);
        Livewire::test(HabitacionesPanel::class)->call('abrirCrearHabitacion')->assertForbidden();
        $this->usuario->update(['estado' => 'ACTIVO']);
        $this->usuario->assignRole('FAMILIAR');
        Livewire::test(HabitacionesPanel::class)->call('abrirCrearHabitacion')->assertForbidden();
        $this->assertDatabaseCount('habitaciones', 0);
    }

    public function test_editar_cama_no_permite_cambiar_su_habitacion(): void
    {
        $hab = Habitacion::create(['codigo' => 'H-A', 'estado' => 'ACTIVA']);
        $otra = Habitacion::create(['codigo' => 'H-B', 'estado' => 'ACTIVA']);
        $cama = Cama::create(['codigo' => 'C-A', 'cod_habitacion' => $hab->getKey(), 'estado' => 'ACTIVA']);
        $this->expectException(HttpException::class);
        try {
            app(GuardarEspacioResidencial::class)->cama(['codigo' => 'C-A', 'estado' => 'ACTIVA'], $this->usuario, $otra->getKey(), $cama->getKey());
        } finally {
            $this->assertSame($hab->getKey(), $cama->fresh()->cod_habitacion);
            $this->assertSame(0, Activity::where('log_name', 'infraestructura')->count());
        }
    }

    public function test_rutas_separan_inventario_mapa_y_redirigen_el_directorio_antiguo(): void
    {
        $this->get(route('admin.habitaciones.index'))->assertRedirect(route('admin.administracion.habitaciones'));
        $this->get(route('admin.administracion.habitaciones'))->assertOk()->assertSee('Nueva habitación')->assertSee('Inventario físico')->assertDontSee('Gestionar espacios');
        $this->get(route('admin.administracion.ocupacion'))->assertOk()->assertSee('Mapa de alojamiento')->assertDontSee('Nueva habitación')->assertDontSee('Añadir cama');
        $this->get(route('admin.administracion.ocupacion', ['tab' => 'historial']))->assertOk()->assertViewIs('pages.admin.administracion.operacion');
    }
}
