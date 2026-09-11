<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Identidad\SidebarService;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SidebarServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);
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
        $this->assertContains('admin.adultos-mayores.index', $routes);
        $this->assertContains('admin.admisiones.preadmisiones', $routes);
        $this->assertContains('admin.habitaciones.index', $routes);
        $this->assertContains('admin.familia-social.resumen', $routes);
        $this->assertContains('admin.personal-institucional', $routes);
        $this->assertContains('admin.turnos-asignaciones.index', $routes);
        $this->assertContains('admin.turnos-enfermeria.index', $routes);
        $this->assertContains('admin.asignacion-turno.index', $routes);
        $this->assertContains('admin.administracion.dashboard', $routes);
        $this->assertContains('admin.medico.dashboard', $routes);
        $this->assertContains('admin.enfermeria.dashboard', $routes);
        $this->assertContains('admin.psicologia.dashboard', $routes);
        $this->assertContains('admin.alertas-clinicas.index', $routes);
        $this->assertContains('admin.reportes.institucional.preview', $routes);
        $this->assertContains('admin.reportes.adultos.preview', $routes);
        $this->assertContains('admin.reportes.salud.preview', $routes);
        $this->assertContains('admin.reportes.equipo.preview', $routes);
        $this->assertContains('admin.bitacora.index', $routes);
        $this->assertContains('admin.vistas-extra', $routes);

        // "Actividades" retirada de reportes en Superadmin
        $this->assertNotContains('admin.reportes.actividades.preview', $routes);

        // Fisioterapia y Nutrición ocultas en Áreas de atención
        $this->assertNotContains('admin.fisioterapia.dashboard', $routes);
        $this->assertNotContains('admin.nutricion.dashboard', $routes);
    }

    public function test_superadmin_supervision_modulos_profesionales(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($user);

        // Simular navegación en enfermería
        $requestEnfermeria = Request::create('/admin/enfermeria/pacientes', 'GET');
        app()->instance('request', $requestEnfermeria);

        $sidebar = app(SidebarService::class)->getSidebar();
        $titles = array_column($sidebar, 'title');
        $this->assertContains('Volver a Administración', $titles);
        $this->assertContains('Enfermería', $titles);

        // Simular navegación en médico
        $requestMedico = Request::create('/admin/medico/dashboard', 'GET');
        app()->instance('request', $requestMedico);

        $sidebarMedico = app(SidebarService::class)->getSidebar();
        $titlesMedico = array_column($sidebarMedico, 'title');
        $this->assertContains('Volver a Administración', $titlesMedico);
        $this->assertContains('Pacientes', $titlesMedico);

        // Simular navegación en psicología
        $requestPsico = Request::create('/admin/psicologia/dashboard', 'GET');
        app()->instance('request', $requestPsico);

        $sidebarPsico = app(SidebarService::class)->getSidebar();
        $titlesPsico = array_column($sidebarPsico, 'title');
        $this->assertContains('Volver a Administración', $titlesPsico);
        $this->assertContains('Valoraciones', $titlesPsico);

        // Simular navegación en administración (panel completo del administrador)
        $requestAdmin = Request::create('/admin/administracion/dashboard', 'GET');
        app()->instance('request', $requestAdmin);

        $sidebarAdmin = app(SidebarService::class)->getSidebar();
        $titlesAdmin = array_column($sidebarAdmin, 'title');
        $this->assertContains('Volver a Superadministrador', $titlesAdmin);
        $this->assertContains('Residentes', $titlesAdmin);
        $this->assertContains('Admisiones', $titlesAdmin);
        $this->assertContains('Personal y turnos', $titlesAdmin);
        $this->assertContains('Usuarios', $titlesAdmin);
        $this->assertContains('Alertas', $titlesAdmin);
        $this->assertContains('Actividades y comunidad', $titlesAdmin);
        $this->assertContains('Reportes', $titlesAdmin);
    }

    public function test_administrador_sidebar_estructura_y_sin_duplicados(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('ADMINISTRADOR');
        $this->actingAs($user);

        $sidebar = app(SidebarService::class)->getSidebar();
        $routes = $this->extractAllRoutes($sidebar);

        $this->assertSame(count($routes), count(array_unique($routes)));
        $this->assertContains('dashboard', $routes);
        $this->assertContains('admin.adultos-mayores.index', $routes);
        $this->assertContains('admin.familia-social.resumen', $routes);
        $this->assertContains('admin.admisiones.preadmisiones', $routes);
        $this->assertContains('admin.personal-institucional', $routes);
        $this->assertContains('admin.turnos-asignaciones.index', $routes);
        $this->assertContains('admin.usuarios.index', $routes);
        $this->assertContains('admin.reportes.institucional.preview', $routes);
        $this->assertContains('admin.reportes.adultos.preview', $routes);
        $this->assertContains('admin.reportes.equipo.preview', $routes);

        // No debe tener roles-permisos ni bitacora
        $this->assertNotContains('admin.roles-permisos.index', $routes);
        $this->assertNotContains('admin.bitacora.index', $routes);

        // Si se le otorga permiso de habitaciones, aparece dinámicamente
        $user->givePermissionTo('habitaciones.ver');
        $sidebarConHabitaciones = app(SidebarService::class)->getSidebar();
        $routesConHabitaciones = $this->extractAllRoutes($sidebarConHabitaciones);
        $this->assertContains('admin.habitaciones.index', $routesConHabitaciones);
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

    public function test_nutricionista_sidebar_muestra_inicio_y_rutas_funcionales(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('NUTRICIONISTA');
        $this->actingAs($user);

        $sidebar = app(SidebarService::class)->getSidebar();
        $routes = $this->extractAllRoutes($sidebar);

        $this->assertSame(count($routes), count(array_unique($routes)));
        $this->assertSame([
            'dashboard',
            'admin.nutricion.valoracion',
            'admin.nutricion.seguimiento',
        ], $routes);

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
            'admin.reportes.adultos.preview',
        ], $routes);
    }

    public function test_voluntario_sidebar_muestra_solo_inicio(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('VOLUNTARIO');
        $this->actingAs($user);

        $sidebar = app(SidebarService::class)->getSidebar();
        $routes = $this->extractAllRoutes($sidebar);

        $this->assertSame(['dashboard'], $routes);
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

        // El sidebar debe ser fixed left-0 top-0 (no empuja verticalmente el contenido)
        $response->assertSee('sidebar-institucional fixed left-0 top-0 z-50 flex h-screen w-[240px]', false);

        // El navbar y main deben tener el margen desktop correcto para alinearse al lado
        $response->assertSee('lg:ml-[240px]', false);

        // El accordion tiene abierta la sección Usuarios y accesos (índice 1)
        $response->assertSee('openSection: 1', false);
        $response->assertSee('border-l-4 border-boton-acento', false);
    }

    public function test_layout_desktop_y_accordion_en_superadmin_residentes(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('SUPERADMINISTRADOR');

        $response = $this->actingAs($user)->get(route('admin.adultos-mayores.index'));
        $response->assertOk();

        $response->assertSee('sidebar-institucional fixed left-0 top-0 z-50 flex h-screen w-[240px]', false);
        // Sección Residentes es índice 2
        $response->assertSee('openSection: 2', false);
    }

    public function test_layout_desktop_en_administrador(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('ADMINISTRADOR');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();

        $response->assertSee('sidebar-institucional fixed left-0 top-0 z-50 flex h-screen w-[240px]', false);
        $response->assertSee('lg:ml-[240px]', false);
    }

    public function test_layout_desktop_en_enfermero(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('ENFERMEROS');

        $response = $this->actingAs($user)->get(route('admin.enfermeria.dashboard'));
        $response->assertOk();

        $response->assertSee('sidebar-institucional fixed left-0 top-0 z-50 flex h-screen w-[240px]', false);
        $response->assertSee('lg:ml-[240px]', false);
    }

    public function test_superadmin_accede_a_admin_dashboard_y_sidebar_muestra_administracion_en_areas_de_atencion(): void
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole('SUPERADMINISTRADOR');

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();

        $response->assertSee('Áreas de atención', false);
        $response->assertSee('Administración', false);
        $response->assertSee(route('admin.administracion.dashboard'), false);

        // Al acceder a administración, se despliega el panel completo del administrador
        $responseAdmin = $this->actingAs($user)->get(route('admin.administracion.dashboard'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Volver a Superadministrador', false);
        $responseAdmin->assertSee('Personal y turnos', false);
    }
}
