<?php

namespace Tests\Feature;

use App\Backend\Modulos\Identidad\Servicios\RolePreviewService;
use App\Backend\Modulos\Identidad\Servicios\SidebarService;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_solo_superadmin_puede_activar_preview(): void
    {
        $admin = $this->usuarioConRol('ADMINISTRADOR');

        $this->actingAs($admin)->post(route('role-preview.store'), ['role' => 'ENFERMEROS'])
            ->assertForbidden();
        $this->assertNull(session(RolePreviewService::SESSION_KEY));
    }

    public function test_preview_es_temporal_y_no_cambia_identidad_roles_ni_permisos(): void
    {
        $superadmin = $this->usuarioConRol('SUPERADMINISTRADOR');
        $codigo = $superadmin->cod_usuario;
        $roles = $superadmin->getRoleNames()->all();

        $this->actingAs($superadmin)->post(route('role-preview.store'), ['role' => 'ENFERMEROS'])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas(RolePreviewService::SESSION_KEY, 'ENFERMEROS');

        $this->assertSame($codigo, auth()->id());
        $this->assertSame($roles, auth()->user()->fresh()->getRoleNames()->all());
        $this->assertFalse(auth()->user()->can('administraciones_medicacion.crear'));
    }

    public function test_preview_cambia_sidebar_y_dashboard_y_muestra_banner_permanente(): void
    {
        $superadmin = $this->usuarioConRol('SUPERADMINISTRADOR');
        $this->actingAs($superadmin)->withSession([RolePreviewService::SESSION_KEY => 'ENFERMEROS']);

        $routes = collect(app(SidebarService::class)->getSidebar())
            ->flatMap(fn (array $section) => array_filter([
                $section['route'] ?? null,
                ...array_column($section['items'] ?? [], 'route'),
            ]))->values()->all();

        $this->assertContains('admin.enfermeria.pacientes', $routes);
        $this->assertNotContains('admin.usuarios.index', $routes);

        $this->followingRedirects()->get(route('dashboard'))->assertOk()
            ->assertSee('Modo previsualización')
            ->assertSee('livewire.js?rm_ui=20261003&id=', false)
            ->assertSee('Enfermería')
            ->assertSee('Este modo es solo lectura')
            ->assertSee('data-accent="nursing"', false)
            ->assertSee('class="rm-dashboard-header__role"><span aria-hidden="true"></span>Enfermería', false)
            ->assertSee('id="topbar-enfermeria"', false);
    }

    public function test_enlaces_visibles_de_enfermeria_abren_en_previsualizacion(): void
    {
        $superadmin = $this->usuarioConRol('SUPERADMINISTRADOR');
        $this->actingAs($superadmin)->withSession([RolePreviewService::SESSION_KEY => 'ENFERMEROS']);

        $routes = collect(app(SidebarService::class)->getSidebar())
            ->flatMap(fn (array $section) => array_filter([
                $section['route'] ?? null,
                ...array_column($section['items'] ?? [], 'route'),
            ]))->unique();

        foreach ($routes as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }

    public function test_preview_bloquea_escrituras_backend_y_permite_salir(): void
    {
        $superadmin = $this->usuarioConRol('SUPERADMINISTRADOR');
        $this->actingAs($superadmin)->withSession([RolePreviewService::SESSION_KEY => 'ADMINISTRADOR']);

        $this->postJson(route('admin.preadmisiones.store'), [])->assertForbidden();

        $this->delete(route('role-preview.destroy'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionMissing(RolePreviewService::SESSION_KEY);
    }

    public function test_cabecera_usa_el_rol_previsualizado_en_dashboard_generico_y_especializado(): void
    {
        $superadmin = $this->usuarioConRol('SUPERADMINISTRADOR');
        $this->actingAs($superadmin);

        foreach ([
            'GERENTE' => ['manager', 'Gerencia'],
            'PSICOLOGO/A' => ['psychology', 'Psicología'],
        ] as $rol => [$acento, $etiqueta]) {
            $response = $this->withSession([RolePreviewService::SESSION_KEY => $rol])
                ->followingRedirects()->get(route('dashboard'))->assertOk();

            $response->assertSee('data-role="'.$acento.'"', false)
                ->assertSee('data-accent="'.$acento.'"', false)
                ->assertSee('class="rm-dashboard-header__role"><span aria-hidden="true"></span>'.$etiqueta, false);
        }
    }

    public function test_manipular_sesion_no_concede_preview_a_otro_rol(): void
    {
        $admin = $this->usuarioConRol('ADMINISTRADOR');
        $this->actingAs($admin)->withSession([RolePreviewService::SESSION_KEY => 'SUPERADMINISTRADOR']);

        $this->followingRedirects()->get(route('dashboard'))->assertOk()->assertDontSee('Modo previsualización');
        $this->assertNull(session(RolePreviewService::SESSION_KEY));
        $this->assertFalse($admin->can('usuarios.gestionar'));
    }

    public function test_superadmin_medico_escribe_por_rol_profesional_y_conserva_identidad(): void
    {
        $usuario = $this->usuarioConRol('SUPERADMINISTRADOR');
        $usuario->assignRole('MEDICO GENERAL/GERIATRA');

        $this->assertTrue($usuario->can('prescripciones.crear'));
        $this->assertSame($usuario->cod_usuario, $usuario->getAuthIdentifier());
    }

    private function usuarioConRol(string $rol): User
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole($rol);

        return $user;
    }
}
