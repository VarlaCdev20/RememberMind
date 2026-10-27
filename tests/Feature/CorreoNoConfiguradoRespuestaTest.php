<?php

namespace Tests\Feature;

use App\Compartido\Infraestructura\Notificaciones\CorreoNoConfigurado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CorreoNoConfiguradoRespuestaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.debug' => true, 'mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'smtp.gmail.com',
            'mail.mailers.smtp.username' => 'remitente@example.test', 'mail.mailers.smtp.password' => null]);
        Notification::fake();
    }

    public function test_recuperacion_sin_configuracion_da_el_mismo_error_sin_crear_tokens(): void
    {
        $usuario = User::factory()->create(['correo' => 'cuenta@example.test', 'estado' => 'ACTIVO']);

        $existente = $this->postJson(route('password.email'), ['correo' => $usuario->correo]);
        $inexistente = $this->postJson(route('password.email'), ['correo' => 'ausente@example.test']);

        foreach ([$existente, $inexistente] as $respuesta) {
            $respuesta->assertStatus(503)->assertJsonPath('success', false)
                ->assertJsonPath('errors.correo.0', CorreoNoConfigurado::AVISO)
                ->assertDontSee('RuntimeException')->assertDontSee('MAIL_PASSWORD')->assertDontSee('trace');
        }
        $this->assertSame($existente->json(), $inexistente->json());
        $this->assertDatabaseCount(config('auth.passwords.'.config('fortify.passwords').'.table'), 0);
        Notification::assertNothingSent();
    }

    public function test_navegador_regresa_al_formulario_con_aviso_y_sin_falso_exito(): void
    {
        $respuesta = $this->followingRedirects()->from(route('password.request'))->post(route('password.email'), [
            'correo' => 'cuenta@example.test', 'password' => 'no-conservar-esta-entrada',
        ]);

        $respuesta->assertOk()->assertSee(CorreoNoConfigurado::AVISO)->assertSee('cuenta@example.test')
            ->assertDontSee('RuntimeException')->assertDontSee('Solicitud recibida')
            ->assertSessionMissing('status')->assertSessionMissing('_old_input.password');
    }

    public function test_excepcion_de_correo_fuera_de_recuperacion_tambien_oculta_detalles_con_debug(): void
    {
        Route::post('/_prueba-correo-no-configurado', fn () => throw new CorreoNoConfigurado('Detalle técnico interno de configuración.'));

        $this->postJson('/_prueba-correo-no-configurado')->assertStatus(503)
            ->assertJsonPath('errors.envio.0', CorreoNoConfigurado::AVISO)
            ->assertDontSee('Detalle técnico')->assertDontSee('trace');
    }
}
