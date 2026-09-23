<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Identidad\SidebarService;
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
        $response->assertSee('GOBERNANZA CLÍNICA Y ASISTENCIAL');
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

    public function test_sidebar_superadmin_en_rutas_de_enfermeria_muestra_vista_completa_y_supervision(): void
    {
        $superadmin = User::factory()->create(['estado' => 'ACTIVO']);
        $superadmin->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($superadmin);

        // Simular que el superadministrador está en una ruta de enfermería
        $this->get(route('admin.enfermeria.dashboard'));

        $sidebarService = app(SidebarService::class);
        $sidebar = $sidebarService->getSidebar();

        // Debe contener el botón de retorno y las dos secciones organizadas
        $titles = array_column($sidebar, 'title');
        $this->assertContains('Volver a Áreas de Atención', $titles);
        $this->assertContains('Atención de Enfermería', $titles);
        $this->assertContains('Supervisión de Enfermería', $titles);

        // Verificar ítems de la sección operativa (vista completa)
        $secAtencion = collect($sidebar)->firstWhere('title', 'Atención de Enfermería');
        $this->assertNotNull($secAtencion);
        $labelsAtencion = array_column($secAtencion['items'], 'label');
        $this->assertContains('Mi turno activo', $labelsAtencion);
        $this->assertContains('Todos los residentes', $labelsAtencion);
        $this->assertContains('Ficha de cuidados', $labelsAtencion);
        $this->assertContains('Agenda de cuidados', $labelsAtencion);
        $this->assertContains('Medicación prescrita', $labelsAtencion);
        $this->assertContains('Kardex y administraciones', $labelsAtencion);
        $this->assertContains('Cuidados e incidentes', $labelsAtencion);
        $this->assertContains('Planes y tareas', $labelsAtencion);
        $this->assertContains('Valoraciones iniciales', $labelsAtencion);

        // Verificar ítems de la sección de supervisión
        $secSupervision = collect($sidebar)->firstWhere('title', 'Supervisión de Enfermería');
        $this->assertNotNull($secSupervision);
        $labelsSupervision = array_column($secSupervision['items'], 'label');
        $this->assertContains('Resumen global de guardia', $labelsSupervision);
        $this->assertContains('Turnos de enfermería', $labelsSupervision);
        $this->assertContains('Asignación de pacientes', $labelsSupervision);
        $this->assertContains('Pases y relevos de turno', $labelsSupervision);
        $this->assertContains('Alertas de guardia', $labelsSupervision);
        $this->assertContains('Reportes de enfermería', $labelsSupervision);
    }

    public function test_sidebar_principal_superadmin_incluye_centro_de_areas_de_atencion(): void
    {
        $superadmin = User::factory()->create(['estado' => 'ACTIVO']);
        $superadmin->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($superadmin);

        // En el dashboard principal
        $this->get(route('dashboard'));

        $sidebarService = app(SidebarService::class);
        $sidebar = $sidebarService->getSidebar();

        $secAreas = collect($sidebar)->firstWhere('title', 'Áreas de atención');
        $this->assertNotNull($secAreas);

        $routesAreas = array_column($secAreas['items'], 'route');
        $this->assertContains('admin.areas-atencion.index', $routesAreas);
        $this->assertContains('admin.enfermeria.dashboard', $routesAreas);
        $this->assertContains('admin.medico.dashboard', $routesAreas);
        $this->assertContains('admin.psicologia.dashboard', $routesAreas);
    }
}
