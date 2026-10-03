<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminDashboardHubTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            
            RolesAndPermissionsSeeder::class,
        ]);
    }

    private function crearSuperadmin(): User
    {
        $user = User::factory()->create([
            'estado' => 'ACTIVO',
        ]);
        $user->assignRole('SUPERADMINISTRADOR');
        return $user;
    }

    public function test_dashboard_superadmin_muestra_supervision_global(): void
    {
        $superadmin = $this->crearSuperadmin();

        $response = $this->actingAs($superadmin)->get(route('dashboard'));
        $response->assertOk();

        $response->assertSee('Superadministración');
        $response->assertDontSee('bajo supervisión global');
        $response->assertSee('rm-superadmin-metrics');
        $response->assertSee('rm-metric-card');
        $response->assertSee('rm-superadmin-metric--quiet');
        $response->assertDontSee('rm-superadmin-kpi--residents');
        $response->assertSee('Residentes activos');
        $response->assertSee('Camas disponibles');
        $response->assertSee('Preadmisiones por estado');
        $response->assertSee('Auditoría');
        $response->assertSee('Alertas prioritarias');
        $response->assertSee('Incidentes por tipo');
        $response->assertDontSee('Agenda de Cuidados');
        $response->assertDontSee('Valoraciones Médicas');
    }

    public function test_superadmin_puede_acceder_a_las_rutas_del_hub_sin_403(): void
    {
        $superadmin = $this->crearSuperadmin();
        $this->actingAs($superadmin);

        $rutas = [
            'admin.usuarios.index',
            'admin.roles-permisos.index',
            'admin.bitacora.index',
        ];

        foreach ($rutas as $ruta) {
            $this->get(route($ruta))->assertOk();
        }
    }
}
