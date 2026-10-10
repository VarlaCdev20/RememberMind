<?php

namespace Tests\Feature;

use App\Models\User;
use App\Backend\Modulos\Identidad\Servicios\SidebarService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AreasAtencionSuperadminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ RolesAndPermissionsSeeder::class]);
    }

    public function test_superadmin_puede_acceder_al_centro_de_areas_de_atencion(): void
    {
        $superadmin = User::factory()->create(['estado' => 'ACTIVO']);
        $superadmin->assignRole('SUPERADMINISTRADOR');

        $response = $this->actingAs($superadmin)->get(route('admin.areas-atencion.index'));

        $response->assertStatus(200);
        $response->assertSee('Centro de Mando de Áreas de Atención');
        $response->assertSee('Gobernanza Clínica y Asistencial');
        $response->assertSee('Área de Enfermería y Cuidados Continuos');
        $response->assertSee('Vista Completa de Enfermería • Atención Diaria y Cuidados');
        $response->assertSee('Supervisión y Gestión de Enfermería • Coordinación Institucional');
        $response->assertSee('Matriz de Gobernanza: Áreas de Atención vs. Roles Institucionales');
    }

    public function test_areas_de_atencion_organiza_por_roles_institucionales(): void
    {
        $superadmin = User::factory()->create(['estado' => 'ACTIVO']);
        $superadmin->assignRole('SUPERADMINISTRADOR');

        $response = $this->actingAs($superadmin)->get(route('admin.areas-atencion.index'));

        $response->assertStatus(200);
        $response->assertSee('ENFERMEROS');
        $response->assertSee('MEDICO GENERAL/GERIATRA');
        $response->assertSee('PSICOLOGO/A');
        $response->assertSee('NUTRICIONISTA');
        $response->assertSee('FISIOTERAPEUTA');
        $response->assertDontSee('VOLUNTARIO');
        $response->assertSee('ADMINISTRADOR');
    }

    public function test_sidebar_superadmin_en_rutas_de_enfermeria_permanece_global(): void
    {
        $superadmin = User::factory()->create(['estado' => 'ACTIVO']);
        $superadmin->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($superadmin);

        // Simular que el superadministrador está en una ruta de enfermería
        $this->get(route('admin.enfermeria.dashboard'));

        $sidebarService = app(SidebarService::class);
        $sidebar = $sidebarService->getSidebar();

        $titles = array_column($sidebar, 'title');
        $this->assertContains('Gestión del sistema', $titles);
        $this->assertContains('Gestión institucional', $titles);
        $this->assertContains('Gestión residencial', $titles);
        $this->assertContains('Expediente clínico', $titles);
        $this->assertNotContains('Atención de Enfermería', $titles);
        $this->assertNotContains('Supervisión de Enfermería', $titles);
    }

    public function test_sidebar_principal_superadmin_integra_clinica_sin_duplicar_dashboards_profesionales(): void
    {
        $superadmin = User::factory()->create(['estado' => 'ACTIVO']);
        $superadmin->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($superadmin);

        // En el dashboard principal
        $this->get(route('dashboard'));

        $sidebarService = app(SidebarService::class);
        $sidebar = $sidebarService->getSidebar();

        $routes = collect($sidebar)->flatMap(fn (array $section) => collect($section['items'] ?? [])->pluck('route'));
        $this->assertNotContains('admin.enfermeria.dashboard', $routes);
        $this->assertNotContains('admin.medico.dashboard', $routes);
        $this->assertNotContains('admin.psicologia.dashboard', $routes);
    }
}
