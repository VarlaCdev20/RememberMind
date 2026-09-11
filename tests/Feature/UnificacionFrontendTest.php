<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnificacionFrontendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);
    }

    public function test_vistas_extra_es_exclusiva_de_superadministracion(): void
    {
        $superadmin = User::factory()->create(['estado' => 'ACTIVO']);
        $superadmin->assignRole('SUPERADMINISTRADOR');

        $this->actingAs($superadmin)
            ->get(route('admin.vistas-extra'))
            ->assertOk()
            ->assertSee('Vistas extra')
            ->assertSee('Ninguna vista de este listado se elimina automáticamente.');

        $administrador = User::factory()->create(['estado' => 'ACTIVO']);
        $administrador->assignRole('ADMINISTRADOR');

        $this->actingAs($administrador)
            ->get(route('admin.vistas-extra'))
            ->assertForbidden();
    }

    public function test_rutas_de_salud_abren_la_seccion_solicitada(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('SUPERADMINISTRADOR');

        $this->actingAs($usuario)
            ->get(route('admin.salud-seguimiento.medicacion.index'))
            ->assertOk()
            ->assertSee('Gestión y Prescripción de Medicación');

        $this->actingAs($usuario)
            ->get(route('admin.salud-seguimiento.signos.index'))
            ->assertOk()
            ->assertSee('Signos Vitales');
    }

}
