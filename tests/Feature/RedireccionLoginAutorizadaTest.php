<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RedireccionLoginAutorizadaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function usuario(string $rol): User
    {
        $user = User::factory()->create(['estado' => 'ACTIVO']);
        $user->assignRole($rol);

        return $user;
    }

    public function test_gerencia_no_hereda_el_inicio_de_enfermeria_de_una_sesion_anterior(): void
    {
        $user = $this->usuario('GERENTE');
        $this->withSession(['url.intended' => route('admin.enfermeria.dashboard')])
            ->post('/login', ['correo' => $user->correo, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->can('enfermeria.ver_dashboard'));
        $this->get(route('admin.enfermeria.dashboard'))->assertForbidden();
    }

    public function test_enfermeria_conserva_un_destino_autorizado_y_sus_filtros(): void
    {
        $user = $this->usuario('ENFERMEROS');
        $destino = route('admin.enfermeria.pacientes').'?vista=listado';
        $this->withSession(['url.intended' => $destino])
            ->post('/login', ['correo' => $user->correo, 'password' => 'password'])
            ->assertRedirect($destino);
    }

    public static function destinosInvalidos(): array
    {
        return [
            ['https://externo.example.test/admin/enfermeria/dashboard'],
            ['//externo.example.test/dashboard'],
            ['http://localhost:9999/dashboard'],
            ['/login'], ['/logout'], [['ruta' => '/dashboard']],
        ];
    }

    #[DataProvider('destinosInvalidos')]
    public function test_un_destino_no_autorizado_no_se_utiliza_tras_login(mixed $destino): void
    {
        $user = $this->usuario('GERENTE');
        $this->withSession(['url.intended' => $destino])
            ->post('/login', ['correo' => $user->correo, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_una_ruta_obsoleta_no_envia_al_usuario_a_otro_error(): void
    {
        $user = $this->usuario('ENFERMEROS');
        $this->withSession(['url.intended' => url('/ruta-que-ya-no-existe')])
            ->post('/login', ['correo' => $user->correo, 'password' => 'password'])
            ->assertRedirect(route('admin.enfermeria.dashboard'));
    }

    public function test_error_403_de_cuenta_activa_ofrece_su_inicio_sin_abrir_permisos(): void
    {
        config(['app.name' => 'Laravel']);
        $user = $this->usuario('GERENTE');
        $this->actingAs($user)->get(route('admin.enfermeria.dashboard'))->assertForbidden()
            ->assertSee('RememberMind')->assertDontSee('LARAVEL')
            ->assertSee('Volver a mi inicio')->assertSee('href="'.route('dashboard').'"', false);
    }

    public function test_cuenta_inactiva_conserva_salida_publica_y_no_entra_en_bucle(): void
    {
        $user = $this->usuario('GERENTE');
        $user->update(['estado' => 'INACTIVO']);
        $this->actingAs($user)->get(route('dashboard'))->assertForbidden()
            ->assertSee('href="'.route('welcome').'"', false)->assertDontSee('Volver a mi inicio');
    }
}
