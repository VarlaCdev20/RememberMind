<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class RecuperacionContrasenaFlujoTest extends TestCase
{
    use RefreshDatabase;

    private User $usuario;

    private string $claveAnterior = 'AnteriorSegura123!';

    private string $claveNueva = 'NuevaSegura456!';

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->usuario = User::factory()->create(['correo' => 'recuperacion@example.test', 'password' => $this->claveAnterior, 'estado' => 'ACTIVO']);
    }

    private function token(): string
    {
        return Password::broker(config('fortify.passwords'))->createToken($this->usuario);
    }

    private function enlace(string $token): string
    {
        return route('password.reset', ['token' => $token, 'correo' => $this->usuario->correo]);
    }

    private function datos(string $token): array
    {
        return ['token' => $token, 'correo' => $this->usuario->correo, 'password' => $this->claveNueva, 'password_confirmation' => $this->claveNueva];
    }

    public function test_solicitud_y_nueva_contrasena_comparten_identidad_visual_sin_cambiar_campos(): void
    {
        foreach ([route('password.request'), $this->enlace($this->token())] as $url) {
            $this->get($url)->assertOk()->assertSee('data-auth-photo', false)
                ->assertSee('Cuidado, memoria<br>y dignidad.', false)
                ->assertSee('rm-auth-form-panel', false)->assertSee('name="correo"', false)
                ->assertDontSee('role="tablist"', false);
        }
    }

    public function test_solicitud_uniforme_existente_inexistente_y_repetida_sin_revelar_cuenta(): void
    {
        $url = route('password.email');
        $existente = $this->postJson($url, ['correo' => $this->usuario->correo])->assertOk();
        $repetida = $this->postJson($url, ['correo' => $this->usuario->correo])->assertOk();
        $inexistente = $this->postJson($url, ['correo' => 'noexiste@example.test'])->assertOk();
        $this->assertSame($existente->json(), $repetida->json());
        $this->assertSame($existente->json(), $inexistente->json());
        $this->assertStringContainsString('Si el correo corresponde', $existente->json('message'));
        Notification::assertSentToTimes($this->usuario, ResetPasswordNotification::class, 1);
    }

    public function test_solicitud_web_conserva_correo_y_ayuda_sin_afirmar_entrega(): void
    {
        $this->from(route('password.request'))->post(route('password.email'), ['correo' => $this->usuario->correo])
            ->assertRedirect(route('password.request'))->assertSessionHas('recovery_requested', true);
        $this->get(route('password.request'))->assertOk()->assertSee('Solicitud recibida')->assertSee('Spam')->assertSee('60 segundos')->assertSee($this->usuario->correo)
            ->assertDontSee('Te hemos enviado');
    }

    public function test_correo_invalido_es_error_del_campo_y_no_envia(): void
    {
        $this->postJson(route('password.email'), ['correo' => 'invalido'])->assertUnprocessable()->assertJsonValidationErrors('correo');
        Notification::assertNothingSent();
    }

    public function test_correo_original_incluye_enlace_real_y_vigencia_del_broker(): void
    {
        $this->postJson(route('password.email'), ['correo' => $this->usuario->correo])->assertOk();
        Notification::assertSentTo($this->usuario, ResetPasswordNotification::class, function ($notification) {
            $mensaje = $notification->toMail($this->usuario);
            $this->assertStringContainsString('correo=recuperacion%40example.test', $mensaje->actionUrl);
            $this->assertStringContainsString('/reset-password/'.$notification->token, $mensaje->actionUrl);
            $this->assertStringContainsString('60 minutos', implode(' ', $mensaje->outroLines));

            return true;
        });
    }

    public function test_enlace_valido_muestra_politica_real_sin_consumir_token_ni_cambiar_password(): void
    {
        $token = $this->token();
        $antes = Activity::count();
        $this->get($this->enlace($token))->assertOk()->assertSee('Crear una contraseña nueva')->assertSee('Entre 12 y 64 caracteres.')
            ->assertSee('data-reset-password-form', false)->assertSee('Incluye mayúscula, minúscula, número, símbolo.')
            ->assertHeader('Referrer-Policy', 'no-referrer')->assertHeader('Cache-Control', 'no-store, private');
        $this->assertTrue(Password::broker(config('fortify.passwords'))->tokenExists($this->usuario, $token));
        $this->assertTrue(Hash::check($this->claveAnterior, $this->usuario->refresh()->getAuthPassword()));
        $this->assertSame($antes, Activity::count());
    }

    public function test_enlace_vencido_muestra_accion_de_recuperacion_sin_formulario(): void
    {
        $this->freezeTime();
        $token = $this->token();
        $this->travel(61)->minutes();
        $this->get($this->enlace($token))->assertOk()->assertSee('Necesitas un enlace nuevo')->assertSee('Solicitar un enlace nuevo')
            ->assertDontSee('data-reset-password-form', false)->assertDontSee('Este token');
        $this->from($this->enlace($token))->post(route('password.update'), $this->datos($token))
            ->assertSessionHasErrors('correo')->assertSessionHas('password_reset_link_invalid', true);
        $this->assertTrue(Hash::check($this->claveAnterior, $this->usuario->refresh()->getAuthPassword()));
        $this->assertSame(0, Activity::where('description', 'Contraseña restablecida.')->count());
    }

    public function test_token_invalido_correo_ajeno_o_ausente_no_revelan_la_causa(): void
    {
        $token = $this->token();
        foreach ([$this->enlace('invalido'), route('password.reset', ['token' => $token, 'correo' => 'otra@example.test']), route('password.reset', ['token' => $token])] as $url) {
            $this->get($url)->assertOk()->assertSee('Necesitas un enlace nuevo')->assertDontSee('data-reset-password-form', false);
        }
        $invalido = $this->postJson(route('password.update'), $this->datos('invalido'))->assertUnprocessable()->assertJsonValidationErrors('correo');
        $ajeno = $this->postJson(route('password.update'), [...$this->datos($token), 'correo' => 'otra@example.test'])->assertUnprocessable()->assertJsonValidationErrors('correo');
        $this->assertSame($invalido->json('message'), $ajeno->json('message'));
        $this->assertStringContainsString('Solicita un enlace nuevo', $invalido->json('message'));
    }

    public function test_cambio_confirmado_lleva_al_login_y_token_no_se_puede_reutilizar(): void
    {
        $token = $this->token();
        $this->from($this->enlace($token))->post(route('password.update'), $this->datos($token))
            ->assertRedirect(route('login'))->assertSessionHasNoErrors()->assertSessionHas('password_reset_completed', true);
        $this->get(route('login'))->assertOk()->assertSee('data-password-reset-confirmation', false)->assertSee('Ya puedes iniciar sesión con la nueva contraseña.');
        $this->assertTrue(Hash::check($this->claveNueva, $this->usuario->refresh()->getAuthPassword()));
        $this->assertFalse(Hash::check($this->claveAnterior, $this->usuario->getAuthPassword()));
        $this->assertGuest();
        $this->assertFalse(Password::broker(config('fortify.passwords'))->tokenExists($this->usuario, $token));
        $this->assertSame(1, Activity::where('description', 'Contraseña restablecida.')->count());
        $this->postJson(route('password.update'), $this->datos($token))->assertUnprocessable()->assertJsonValidationErrors('correo');
        $this->get($this->enlace($token))->assertSee('Necesitas un enlace nuevo')->assertDontSee('data-reset-password-form', false);
    }

    public function test_confirmacion_incorrecta_debil_y_password_repetido_no_cambian_cuenta(): void
    {
        $token = $this->token();
        $this->postJson(route('password.update'), [...$this->datos($token), 'password_confirmation' => 'Diferente123!'])
            ->assertUnprocessable()->assertJsonValidationErrors('password')->assertJsonPath('errors.password.0', 'La confirmación de la nueva contraseña no coincide. Repite la misma contraseña en ambos campos.');
        $this->postJson(route('password.update'), [...$this->datos($token), 'password' => 'debil', 'password_confirmation' => 'debil'])->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->postJson(route('password.update'), [...$this->datos($token), 'password' => $this->claveAnterior, 'password_confirmation' => $this->claveAnterior])
            ->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->assertTrue(Hash::check($this->claveAnterior, $this->usuario->refresh()->getAuthPassword()));
        $this->assertTrue(Password::broker(config('fortify.passwords'))->tokenExists($this->usuario, $token));
    }

    public function test_cuenta_desactivada_despues_de_solicitar_no_cambia_ni_se_reactiva(): void
    {
        $token = $this->token();
        $this->usuario->update(['estado' => 'INACTIVO']);
        $this->get($this->enlace($token))->assertSee('Necesitas un enlace nuevo')->assertDontSee('data-reset-password-form', false);
        $this->postJson(route('password.update'), $this->datos($token))->assertUnprocessable()->assertJsonValidationErrors('correo');
        $this->assertSame('INACTIVO', $this->usuario->refresh()->estado);
        $this->assertTrue(Hash::check($this->claveAnterior, $this->usuario->getAuthPassword()));
        $this->assertSame(0, Activity::where('description', 'Contraseña restablecida.')->count());
    }

    public function test_parametros_manipulados_son_validacion_y_no_error_interno(): void
    {
        $token = $this->token();
        $this->postJson(route('password.update'), [...$this->datos($token), 'token' => ['invalido']])->assertUnprocessable()->assertJsonValidationErrors('token');
        $this->postJson(route('password.update'), [...$this->datos($token), 'password' => ['invalido']])->assertUnprocessable()->assertJsonValidationErrors('password');
        $this->postJson(route('password.update'), [...$this->datos($token), 'correo' => ['invalido']])->assertUnprocessable()->assertJsonValidationErrors('correo');
        $this->get(route('password.reset', ['token' => $token, 'correo' => ['invalido']]))->assertOk()->assertSee('Necesitas un enlace nuevo');
        $this->assertTrue(Hash::check($this->claveAnterior, $this->usuario->refresh()->getAuthPassword()));
    }

    public function test_cambio_json_confirma_y_no_guarda_claves_o_token_en_auditoria(): void
    {
        $token = $this->token();
        $this->postJson(route('password.update'), $this->datos($token))->assertOk()->assertJsonPath('login_url', route('login'));
        $historia = Activity::where('log_name', 'Seguridad')->get()->toJson();
        $this->assertStringNotContainsString($this->claveNueva, $historia);
        $this->assertStringNotContainsString($token, $historia);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $this->usuario->correo]);
    }

    public function test_un_enlace_nuevo_invalida_el_anterior_y_tokens_se_conservan_con_hash(): void
    {
        $anterior = $this->token();
        $nuevo = $this->token();
        $this->assertNotSame($anterior, $nuevo);
        $this->get($this->enlace($anterior))->assertSee('Necesitas un enlace nuevo');
        $this->get($this->enlace($nuevo))->assertSee('Crear una contraseña nueva');
        $guardado = DB::table('password_reset_tokens')->where('email', $this->usuario->correo)->value('token');
        $this->assertNotSame($nuevo, $guardado);
        $this->assertTrue(Hash::check($nuevo, $guardado));
    }

    public function test_despues_del_cambio_se_inicia_sesion_con_clave_nueva_y_no_con_anterior(): void
    {
        $this->postJson(route('password.update'), $this->datos($this->token()))->assertOk();
        $this->post(route('login'), ['correo' => $this->usuario->correo, 'password' => $this->claveAnterior])->assertSessionHasErrors('correo');
        $this->assertGuest();
        session()->forget('errors');
        $this->post(route('login'), ['correo' => $this->usuario->correo, 'password' => $this->claveNueva])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertAuthenticatedAs($this->usuario);
    }
}
