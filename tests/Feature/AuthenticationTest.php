<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Entrar al Portal');
        $response->assertSee("correo: ''", false);
        $response->assertSee("recoverCorreo: ''", false);
        $this->assertMatchesRegularExpression('/<div x-show="\s*panel === \'login\'/u', $response->getContent());
    }

    public function test_route_source_does_not_prefix_script_responses_with_a_bom(): void
    {
        $this->assertStringStartsWith('<?php', file_get_contents(base_path('routes/web.php')));
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'correo' => $user->correo,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'correo' => $user->correo,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_known_legacy_passwords_do_not_bypass_authentication(): void
    {
        $user = User::factory()->create([
            'correo' => 'admincasaamandita@gmail.com',
            'contrasena' => 'ClaveActualSegura123!',
        ]);

        $this->post('/login', [
            'correo' => $user->correo,
            'password' => 'CasaAmandita123',
        ]);

        $this->assertGuest();
        $this->assertTrue(Hash::check('ClaveActualSegura123!', $user->fresh()->getAuthPassword()));
    }

    public function test_plaintext_passwords_are_rejected_instead_of_migrated_during_login(): void
    {
        DB::table('usuarios')->insert([
            'cod_usuario' => 'USU_PLAINTEXT',
            'correo' => 'plaintext@example.test',
            'contrasena' => 'ClaveSinHash123!',
            'estado' => 'ACTIVO',
        ]);

        $this->post('/login', [
            'correo' => 'plaintext@example.test',
            'password' => 'ClaveSinHash123!',
        ]);

        $this->assertGuest();
        $this->assertSame('ClaveSinHash123!', DB::table('usuarios')->where('cod_usuario', 'USU_PLAINTEXT')->value('contrasena'));
    }
}
