<?php

namespace Tests\Feature;

use App\Models\User;
use App\Backend\Modulos\Identidad\Servicios\SidebarService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ RolesAndPermissionsSeeder::class]);
    }

    private function extractAllRoutes(array $sidebar): array
    {
        $routes = [];
        foreach ($sidebar as $section) {
            if (!empty($section['route'])) {
                $routes[] = $section['route'];
            }
            if (!empty($section['items'])) {
                foreach ($section['items'] as $item) {
                    if (!empty($item['route'])) {
                        $routes[] = $item['route'];
                    }
                }
            }
        }
        return $routes;
    }

    public function test_superadministrador_sidebar_estructura_y_sin_duplicados(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($user);

        $sidebar = app(SidebarService::class)->getSidebar();
        $routes = $this->extractAllRoutes($sidebar);

        // Sin duplicados
        $this->assertSame(count($routes), count(array_unique($routes)));

        // Rutas clave presentes
        $this->assertContains('dashboard', $routes);
        $this->assertContains('admin.usuarios.index', $routes);
        $this->assertContains('admin.roles-permisos.index', $routes);
        $this->assertContains('admin.bitacora.index', $routes);
        $this->assertContains('admin.areas-institucionales.index', $routes);
        $this->assertNotContains('admin.enfermeria.dashboard', $routes);
        $this->assertNotContains('admin.medico.dashboard', $routes);
        $this->assertNotContains('admin.administracion.dashboard', $routes);
    }

    public function test_superadmin_conserva_sidebar_global_en_cualquier_contexto(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($user);

        $sidebar = app(SidebarService::class)->getSidebar();
        $titles = array_column($sidebar, 'title');
        $this->assertContains('Dashboard', $titles);
        $this->assertContains('Gestión del sistema', $titles);
        $this->assertContains('Gestión institucional', $titles);
        $this->assertContains('Gestión residencial', $titles);
        $this->assertContains('Expediente clínico', $titles);
    }

    public function test_administrador_sidebar_estructura_y_sin_duplicados(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('ADMINISTRADOR');
        $this->actingAs($user);

        $sidebar = app(SidebarService::class)->getSidebar();
        $routes = $this->extractAllRoutes($sidebar);

        $this->assertSame([
            'Inicio',
            'Admisión',
            'Residentes',
            'Operación diaria',
            'Documentación',
            'Seguimiento',
            'Reportes',
        ], array_column($sidebar, 'title'));
        $this->assertSame(['Preadmisiones', 'Admisiones'], array_column($sidebar[1]['items'], 'label'));
        $this->assertSame(['Residentes', 'Habitaciones y camas', 'Ocupación'], array_column($sidebar[2]['items'], 'label'));
        $this->assertSame(['Jornadas', 'Asignaciones', 'Actividades', 'Visitas'], array_column($sidebar[3]['items'], 'label'));
        $this->assertSame(['Contactos y responsables', 'Documentos', 'Consentimientos', 'Seguros'], array_column($sidebar[4]['items'], 'label'));
        $this->assertSame(['Alertas', 'Incidentes'], array_column($sidebar[5]['items'], 'label'));
        $this->assertSame(count($routes), count(array_unique($routes)));
        $this->assertContains('admin.administracion.dashboard', $routes);
        $this->assertContains('admin.administracion.residentes', $routes);
        $this->assertContains('admin.administracion.contactos', $routes);
        $this->assertContains('admin.admisiones.preadmisiones', $routes);
        $this->assertContains('admin.administracion.admisiones', $routes);
        $this->assertContains('admin.administracion.jornadas', $routes);
        $this->assertContains('admin.administracion.asignaciones', $routes);
        $this->assertContains('admin.administracion.reportes', $routes);
        $this->assertNotContains('admin.personal-institucional', $routes);
        $this->assertNotContains('admin.turnos-asignaciones.index', $routes);
        $this->assertNotContains('admin.usuarios.index', $routes);

        // No debe tener roles-permisos ni bitacora
        $this->assertNotContains('admin.roles-permisos.index', $routes);
        $this->assertNotContains('admin.bitacora.index', $routes);

        $this->assertContains('admin.administracion.habitaciones', $routes);
        $this->assertNotContains('admin.adultos-mayores.index', $routes);
        $this->assertNotContains('admin.turnos-enfermeria.index', $routes);
    }

    public function test_medico_sidebar_estructura(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('MEDICO GENERAL/GERIATRA');
        $this->actingAs($user);

        $sidebar = app(SidebarService::class)->getSidebar();
        $routes = $this->extractAllRoutes($sidebar);

        $this->assertSame(count($routes), count(array_unique($routes)));
        $this->assertSame([
            'admin.medico.dashboard',
            'admin.medico.pacientes.observacion',
            'admin.medico.valoraciones',
            'admin.medico.interconsultas',
            'admin.medico.signos-vitales',
            'admin.salud-seguimiento.ficha.index',
            'admin.salud-seguimiento.medicacion.index',
            'admin.salud-seguimiento.alertas',
            'admin.salud-seguimiento.reportes',
        ], $routes);
    }

    public function test_psicologo_sidebar_muestra_valoraciones_y_oculta_placeholders(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('PSICOLOGO/A');
        $this->actingAs($user);

        $sidebar = app(SidebarService::class)->getSidebar();
        $routes = $this->extractAllRoutes($sidebar);

        $this->assertSame(count($routes), count(array_unique($routes)));
        $this->assertContains('admin.psicologia.dashboard', $routes);
        $this->assertContains('admin.psicologia.evaluaciones', $routes);
        $this->assertContains('admin.psicologia.evaluacion.cognitiva', $routes);
        $this->assertContains('admin.psicologia.evaluacion.afectiva', $routes);
        $this->assertContains('admin.psicologia.evaluacion.funcionamiento', $routes);
        $this->assertContains('admin.psicologia.evaluacion.entorno', $routes);

        // Placeholders deben estar ocultos
        $this->assertNotContains('admin.psicologia.seguimiento', $routes);
        $this->assertNotContains('admin.psicologia.alertas', $routes);
        $this->assertNotContains('admin.psicologia.reportes', $routes);
        $this->assertNotContains('admin.psicologia.pacientes.derivados', $routes);
        $this->assertNotContains('admin.psicologia.pacientes.historial', $routes);
    }

    public function test_fisioterapeuta_sidebar_muestra_solo_inicio(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('FISIOTERAPEUTA');
        $this->actingAs($user);

        $sidebar = app(SidebarService::class)->getSidebar();
        $routes = $this->extractAllRoutes($sidebar);

        $this->assertSame(['dashboard'], $routes);
    }

    public function test_nutricionista_sidebar_muestra_solo_inicio(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('NUTRICIONISTA');
        $this->actingAs($user);

        $sidebar = app(SidebarService::class)->getSidebar();
        $routes = $this->extractAllRoutes($sidebar);

        $this->assertSame(['dashboard'], $routes);

        // Placeholders de nutrición no deben estar presentes
        $this->assertNotContains('admin.nutricion.dashboard', $routes);
        $this->assertNotContains('admin.nutricion.pacientes.derivados', $routes);
        $this->assertNotContains('admin.nutricion.plan', $routes);
        $this->assertNotContains('admin.nutricion.control.peso', $routes);
        $this->assertNotContains('admin.nutricion.control.hidratacion', $routes);
        $this->assertNotContains('admin.nutricion.alertas', $routes);
        $this->assertNotContains('admin.nutricion.reportes', $routes);
    }

    public function test_pedagogo_sidebar_estructura(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('PEDAGOGO');
        $this->actingAs($user);

        $sidebar = app(SidebarService::class)->getSidebar();
        $routes = $this->extractAllRoutes($sidebar);

        $this->assertSame(count($routes), count(array_unique($routes)));
        $this->assertSame([
            'dashboard',
            'admin.adultos-mayores.index',
        ], $routes);
    }

    public function test_familiar_sidebar_muestra_solo_inicio(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('FAMILIAR');
        $this->actingAs($user);

        $sidebar = app(SidebarService::class)->getSidebar();
        $routes = $this->extractAllRoutes($sidebar);

        $this->assertSame(['dashboard'], $routes);
    }

    public function test_layout_desktop_sidebar_fixed_y_accordion_en_superadmin_usuarios(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('SUPERADMINISTRADOR');

        $response = $this->actingAs($user)->get(route('admin.usuarios.index'));
        $response->assertOk();

        // El ancho y la posición ahora pertenecen a los tokens CSS del shell.
        $response->assertSee('id="sidebar"', false);
        $response->assertSee('class="rm-sidebar"', false);
        $response->assertSee('data-rm-main', false);

        // El acordeón abre Gestión del sistema y destaca sólo la página actual.
        $response->assertSee('openSection: 1', false);
        $response->assertSee('aria-current="page"', false);
        $response->assertSee('id="rm-sidebar-trigger-1" class="rm-sidebar__item is-current-section"', false);
        $response->assertDontSee('class="rm-sidebar__item is-active"', false);
        $response->assertSee('x-collapse.duration.200ms', false);
        $this->assertSame(1, preg_match_all('/class="rm-sidebar__subitem\s+rm-nav-item\s+is-active"/', $response->getContent()));
    }

    public function test_layout_superadmin_en_consulta_de_residentes_conserva_sidebar_tecnico(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('SUPERADMINISTRADOR');

        $response = $this->actingAs($user)->get(route('admin.adultos-mayores.index'));
        $response->assertOk();

        $response->assertSee('class="rm-sidebar"', false);
        $response->assertSee('Gestión del sistema', false);
        $response->assertSee('Gestión residencial', false);
        $response->assertDontSee('Atención de Enfermería', false);
    }

    public function test_layout_desktop_en_administrador(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('ADMINISTRADOR');

        $response = $this->actingAs($user)->get(route('admin.administracion.dashboard'));
        $response->assertOk();

        $response->assertSee('class="rm-sidebar"', false);
        $response->assertSee('data-rm-main', false);
    }

    public function test_layout_desktop_en_enfermero(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('ENFERMEROS');

        $response = $this->actingAs($user)->get(route('admin.enfermeria.dashboard'));
        $response->assertOk();
        $response->assertSee('id="sidebar-enfermeria"', false);
        $response->assertSee('class="rm-sidebar"', false);
        $response->assertSee('data-rm-main', false);
    }

    public function test_superadmin_puede_consultar_dashboard_operativo_de_administracion(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('SUPERADMINISTRADOR');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();

        $responseAdmin = $this->actingAs($user)->get(route('admin.administracion.dashboard'));
        $responseAdmin->assertOk();
    }
}
