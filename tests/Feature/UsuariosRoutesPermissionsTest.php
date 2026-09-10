<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UsuariosRoutesPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['usuarios.ver', 'usuarios.crear', 'usuarios.editar'] as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }
    }

    public function test_invitado_no_puede_acceder_a_las_rutas_de_usuarios(): void
    {
        $usuario = User::factory()->create();

        foreach (['index', 'create', 'show', 'edit'] as $action) {
            $parameters = in_array($action, ['show', 'edit']) ? ['usuario' => $usuario] : [];

            $this->get(route('admin.usuarios.'.$action, $parameters))
                ->assertRedirect(route('login'));
        }

        $this->post(route('admin.usuarios.store'))->assertRedirect(route('login'));
        $this->put(route('admin.usuarios.update', $usuario))->assertRedirect(route('login'));
        $this->patch(route('admin.usuarios.update', $usuario))->assertRedirect(route('login'));
        $this->delete(route('admin.usuarios.destroy', $usuario))->assertRedirect(route('login'));
    }

    public function test_usuario_con_solo_permiso_ver_puede_listar(): void
    {
        $this->actingAs($this->userWithOnlyPermission('usuarios.ver'))
            ->get(route('admin.usuarios.index'))
            ->assertOk()
            ->assertViewIs('pages.usuarios.index');
    }

    public function test_usuario_con_solo_permiso_ver_no_puede_abrir_create(): void
    {
        $this->actingAs($this->userWithOnlyPermission('usuarios.ver'))
            ->get(route('admin.usuarios.create'))
            ->assertForbidden();
    }

    public function test_usuario_con_solo_permiso_ver_no_puede_abrir_edit(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($this->userWithOnlyPermission('usuarios.ver'))
            ->get(route('admin.usuarios.edit', $usuario))
            ->assertForbidden();
    }

    public function test_usuario_con_permiso_crear_puede_abrir_create(): void
    {
        $this->actingAs($this->userWithOnlyPermission('usuarios.crear'))
            ->get(route('admin.usuarios.create'))
            ->assertOk()
            ->assertViewIs('pages.usuarios.create');
    }

    public function test_usuario_con_permiso_editar_puede_abrir_edit(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($this->userWithOnlyPermission('usuarios.editar'))
            ->get(route('admin.usuarios.edit', $usuario))
            ->assertOk()
            ->assertViewIs('pages.usuarios.edit');
    }

    private function userWithOnlyPermission(string $permission): User
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->givePermissionTo($permission);

        return $user;
    }
}
