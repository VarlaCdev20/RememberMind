<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\Fortify\ResetUserPassword;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordInicialPersonalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (require base_path('database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php'))->up();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_cuenta_nueva_completa_password_desde_el_enlace_y_conserva_su_rol(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        config(['mail.default' => 'array', 'mail.mailers.array.transport' => 'array',
            'mail.from.address' => 'remembermind.health@gmail.com']);
        $usuario = User::factory()->create(['password' => 'TemporalDePrueba#123']);
        $usuario->assignRole('GERENTE');
        $usuario->forceFill(['cambio_password_pendiente' => true, 'fecha_hora_alta' => now()])->save();
        $this->actingAs($usuario)->get(route('dashboard'))->assertRedirect(route('password.inicial'));
        $this->getJson(route('admin.personal-institucional'))->assertForbidden();
        $this->get(route('password.inicial'))->assertOk()->assertSeeText('Configura tu acceso desde el correo')
            ->assertDontSee('name="current_password"', false);
        $this->post(route('password.inicial.guardar'), ['correo' => 'ajeno@example.test',
            'password' => 'NoDebeGuardarse#123'])->assertRedirect(route('password.request'));
        $this->assertGuest();
        $this->assertTrue($usuario->fresh()->cambio_password_pendiente);
        $this->assertTrue(Hash::check('TemporalDePrueba#123', $usuario->fresh()->getAuthPassword()));
        $token = null;
        \Illuminate\Support\Facades\Notification::assertSentTo($usuario, \App\Notifications\ResetPasswordNotification::class,
            function ($notificacion) use (&$token) { $token = $notificacion->token; return true; });
        $this->actingAs($usuario)->get(route('password.reset', ['token' => $token, 'correo' => $usuario->correo]))->assertOk();
        $this->assertGuest();
        $this->post('/reset-password', ['token' => $token, 'correo' => $usuario->correo,
            'password' => 'PersonalNueva#1234', 'password_confirmation' => 'PersonalNueva#1234'])->assertSessionHasNoErrors();
        $this->assertFalse($usuario->fresh()->cambio_password_pendiente);
        $this->assertTrue(Hash::check('PersonalNueva#1234', $usuario->fresh()->getAuthPassword()));
        $this->assertTrue($usuario->fresh()->hasRole('GERENTE'));
        $this->actingAs($usuario->fresh())->get(route('dashboard'))->assertOk();
        $this->get(route('password.inicial'))->assertRedirect(route('dashboard'));
    }

    public function test_correo_no_disponible_no_cambia_password_ni_declara_envio(): void
    {
        config(['mail.default' => 'log', 'mail.from.address' => 'remembermind.health@gmail.com']);
        $usuario = User::factory()->create(['password' => 'TemporalDePrueba#123']);
        $usuario->assignRole('GERENTE');
        $usuario->forceFill(['cambio_password_pendiente' => true])->save();
        $this->actingAs($usuario)->from(route('password.inicial'))->post(route('password.inicial.guardar'))
            ->assertRedirect(route('password.inicial'))->assertSessionHasErrors('correo');
        $this->assertAuthenticatedAs($usuario);
        $this->assertTrue($usuario->fresh()->cambio_password_pendiente);
        $this->assertTrue(Hash::check('TemporalDePrueba#123', $usuario->fresh()->getAuthPassword()));
    }
    public function test_historia_desconocida_no_se_interpreta_como_cambio_real_y_reset_valido_resuelve_obligacion(): void
    {
        $usuario = User::factory()->create(['password' => 'TemporalDePrueba#123']);
        $usuario->assignRole('GERENTE');
        $this->assertNull($usuario->fresh()->cambio_password_pendiente);
        $this->actingAs($usuario)->get(route('dashboard'))->assertOk();
        $usuario->forceFill(['cambio_password_pendiente' => true])->save();
        app(ResetUserPassword::class)->reset($usuario, ['password' => 'OtraPersonal#1234', 'password_confirmation' => 'OtraPersonal#1234']);
        $this->assertFalse($usuario->fresh()->cambio_password_pendiente);
        $this->assertNull($usuario->fresh()->fecha_hora_alta);
    }
}
