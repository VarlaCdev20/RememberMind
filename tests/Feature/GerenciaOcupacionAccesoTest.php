<?php

namespace Tests\Feature;

use App\Models\Cama;
use App\Models\Contacto;
use App\Models\Habitacion;
use App\Models\Preadmision;
use App\Models\User;
use App\Modulos\Admisiones\Aplicacion\Acciones\FormalizarAdmision;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GerenciaOcupacionAccesoTest extends TestCase
{
    use RefreshDatabase;

    private User $gerente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->gerente = User::factory()->create(['estado' => 'ACTIVO']);
        $this->gerente->assignRole('GERENTE');
        $this->actingAs($this->gerente);
    }

    public function test_el_acceso_publicado_abre_ocupacion_sin_otorgar_dashboard_administrativo(): void
    {
        $this->assertFalse($this->gerente->can('admisiones.ver_dashboard'));
        $this->assertFalse($this->gerente->can('habitaciones.crear'));
        $this->assertFalse($this->gerente->can('ocupaciones_cama.gestionar'));
        $this->get(route('dashboard'))->assertOk()->assertSee(route('admin.habitaciones.index'), false);

        $this->followingRedirects()->get(route('admin.habitaciones.index'))
            ->assertOk()->assertSeeText('Ocupación')->assertSeeText('Mapa de alojamiento')
            ->assertDontSee('Nueva habitación')->assertDontSee('Añadir cama')
            ->assertDontSee('elegir-residente-para-cama', false)
            ->assertDontSee(route('admin.administracion.ocupacion', ['tab' => 'historial']), false);

        $this->get(route('admin.administracion.habitaciones'))->assertForbidden();
        $this->assertDatabaseCount('habitaciones', 0);
        $this->assertDatabaseCount('ocupaciones_cama', 0);
        $this->assertFalse($this->gerente->fresh()->can('admisiones.ver_dashboard'));
    }

    public function test_filtros_vistas_paginacion_y_detalle_permanecen_en_la_ruta_autorizada(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $habitacion = Habitacion::create(['codigo' => 'Sala de prueba '.$i, 'piso' => '1', 'estado' => 'ACTIVA']);
            Cama::create(['codigo' => 'Cama de prueba '.$i, 'cod_habitacion' => $habitacion->getKey(), 'estado' => 'DISPONIBLE']);
        }

        $respuesta = $this->get(route('admin.habitaciones.index', ['piso' => '1']))
            ->assertOk()->assertViewHas('rutaExplorador', 'admin.habitaciones.index')
            ->assertSee('action="'.route('admin.habitaciones.index').'"', false);
        $this->assertStringStartsWith(route('admin.habitaciones.index'), $respuesta->viewData('habitaciones')->url(2));
        $this->get($respuesta->viewData('habitaciones')->url(2))->assertOk();

        $cama = Cama::query()->firstOrFail();
        $this->get(route('admin.habitaciones.index', ['vista' => 'tabla', 'piso' => '1']))
            ->assertOk()->assertViewHas('vista', 'tabla')->assertSeeText($cama->codigo);
        $this->get(route('admin.habitaciones.index', ['cama' => $cama->getKey()]))
            ->assertOk()->assertSeeText('Volver a ocupación')
            ->assertDontSee('Preparar ingreso')->assertDontSee('Elegir residente');

        $this->assertDatabaseCount('habitaciones', 12);
        $this->assertDatabaseCount('camas', 12);
        $this->assertDatabaseCount('ocupaciones_cama', 0);
    }

    public function test_sin_permiso_de_habitaciones_se_deniega_la_consulta(): void
    {
        Role::findByName('GERENTE', 'web')->revokePermissionTo('habitaciones.ver');
        $this->get(route('admin.habitaciones.index'))->assertForbidden();
        $this->assertDatabaseCount('ocupaciones_cama', 0);
    }

    public function test_detalle_ocupado_respeta_identidad_autorizada_y_no_ofrece_ficha_administrativa_prohibida(): void
    {
        $admin = User::factory()->create(['estado' => 'ACTIVO']);
        $admin->assignRole('ADMINISTRADOR');
        $this->actingAs($admin);
        $habitacion = Habitacion::create(['codigo' => 'Habitación de prueba', 'estado' => 'ACTIVA']);
        $cama = Cama::create(['codigo' => 'Cama de prueba', 'cod_habitacion' => $habitacion->getKey(), 'estado' => 'DISPONIBLE']);
        $contacto = Contacto::create(['nombres' => 'Contacto', 'apellido_paterno' => 'Sintetico', 'estado' => 'ACTIVO']);
        $solicitud = Preadmision::create([
            'cod_contacto' => $contacto->getKey(), 'cod_usuario_registro' => $admin->getKey(),
            'nombres' => 'Persona', 'apellido_paterno' => 'Sintetica', 'fecha_nacimiento' => '1945-05-05',
            'motivo_ingreso' => 'Escenario de prueba', 'fecha_solicitud' => now()->subDay(), 'estado' => 'APROBADA',
        ]);
        $residente = app(FormalizarAdmision::class)->ejecutar($solicitud, [
            'cod_cama' => $cama->getKey(), 'fecha_hora_admision' => now()->subDay()->toDateTimeString(),
        ], $admin);

        $this->actingAs($this->gerente)->get(route('admin.habitaciones.index', ['cama' => $cama->getKey()]))
            ->assertOk()->assertSeeText('Persona Sintetica')
            ->assertDontSee('Ver residente y alojamiento');

        Role::findByName('GERENTE', 'web')->revokePermissionTo('residentes.ver');
        $respuesta = $this->get(route('admin.habitaciones.index', ['cama' => $cama->getKey()]))
            ->assertOk()->assertViewHas('verPersonas', false)->assertDontSee('Persona Sintetica');
        $this->assertNull($respuesta->viewData('detalle')->ocupante);
        $this->assertNull($respuesta->viewData('detalle')->cod_residente);
        $this->assertTrue($respuesta->viewData('historial')->isEmpty());
        $this->assertDatabaseCount('residentes', 1);
        $this->assertDatabaseHas('ocupaciones_cama', ['cod_residente' => $residente->getKey(), 'estado' => 'ACTIVA']);
    }

    public function test_la_cuenta_inactiva_no_consulta_aunque_conserve_permiso(): void
    {
        $this->gerente->update(['estado' => 'INACTIVO']);
        $this->get(route('admin.habitaciones.index'))->assertForbidden();
    }

    public function test_familiar_no_obtiene_mapa_institucional_con_permiso_aislado(): void
    {
        $familiar = User::factory()->create(['estado' => 'ACTIVO']);
        $familiar->assignRole('FAMILIAR');
        $familiar->givePermissionTo('habitaciones.ver');
        $this->actingAs($familiar)->get(route('admin.habitaciones.index'))->assertForbidden();
        $this->assertDatabaseCount('ocupaciones_cama', 0);
    }
}
