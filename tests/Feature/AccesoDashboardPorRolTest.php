<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccesoDashboardPorRolTest extends TestCase
{
    use RefreshDatabase;

    public static function roles(): array
    {
        return array_map(fn ($rol) => [$rol], ['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR', 'MEDICO GENERAL/GERIATRA',
            'ENFERMEROS', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO', 'FAMILIAR']);
    }

    #[DataProvider('roles')]
    public function test_login_real_y_destino_del_rol_sin_error_500(string $rol): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['correo' => 'rol-sintetico@example.test', 'estado' => 'ACTIVO']);
        $usuario->assignRole($rol);
        $login = $this->post('/login', ['correo' => $usuario->correo, 'password' => 'password'])->assertRedirect();
        $this->assertAuthenticatedAs($usuario);
        $respuesta = $this->get($login->headers->get('Location'));
        if ($respuesta->isRedirect()) {
            $destino = $respuesta->headers->get('Location');
            $this->assertStringStartsWith(config('app.url'), $destino);
            $respuesta = $this->get($destino);
        }
        $respuesta->assertOk();
        $this->assertAuthenticatedAs($usuario);
        $this->assertSame([$rol], $usuario->fresh()->getRoleNames()->all());
        $this->assertDatabaseCount('residentes', 0);
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }

    public function test_cuenta_inactiva_no_inicia_sesion(): void
    {
        $usuario = User::factory()->create(['estado' => 'INACTIVO']);
        $hash = $usuario->contrasena;
        $this->post('/login', ['correo' => $usuario->correo, 'password' => 'password']);
        $this->assertGuest();
        $this->assertSame('INACTIVO', $usuario->fresh()->estado);
        $this->assertSame($hash, $usuario->fresh()->contrasena);
    }
}
