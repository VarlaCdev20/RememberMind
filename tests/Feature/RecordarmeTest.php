<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RecordarmeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Route::middleware('web')->get('/_ensayo-recordarme', fn () => response()->json([
            'cuenta' => Auth::id(), 'recordado' => Auth::viaRemember(),
        ]));
    }

    private function migracion(): \Illuminate\Database\Migrations\Migration
    {
        return require base_path('database/migrations/2026_10_26_000001_add_remember_token_to_usuarios.php');
    }

    private function activar(): void
    {
        if (! Schema::hasColumn('usuarios', 'remember_token')) {
            $this->migracion()->up();
        }
        config(['auth.remember_enabled' => true]);
        Auth::forgetGuards();
    }

    private function ingresar(User $usuario, bool $recordar = true): \Illuminate\Testing\TestResponse
    {
        return $this->post('/login', ['correo' => $usuario->correo, 'password' => 'password', 'remember' => $recordar]);
    }

    private function recordar(string $cookie): \Illuminate\Testing\TestResponse
    {
        $nombre = Auth::guard()->getRecallerName();
        $this->app['session.store']->flush();
        Auth::forgetGuards();

        return $this->withCredentials()->withCookie($nombre, $cookie)->getJson('/_ensayo-recordarme');
    }

    public function test_sin_extension_no_se_habilita_ni_escribe_columna_inexistente(): void
    {
        config(['auth.remember_enabled' => false]);
        $this->migracion()->down();
        $this->assertFalse(Schema::hasColumn('usuarios', 'remember_token'));
        $usuario = User::factory()->create();
        $this->get('/login')->assertDontSee('name="remember"', false);
        $usuario->setRememberToken('token-sintetico');
        $this->assertNull($usuario->getRememberToken());
        $this->ingresar($usuario)->assertRedirect();
        $this->post('/logout')->assertRedirect();
    }

    public function test_migracion_poblada_y_reversion_conservan_cuentas_y_contrasenas(): void
    {
        $this->migracion()->down();
        $usuario = User::factory()->create();
        $antes = DB::table('usuarios')->where('cod_usuario', $usuario->getKey())->first();
        $this->activar();
        $this->assertTrue(Schema::hasColumn('usuarios', 'remember_token'));
        $this->assertNull($usuario->fresh()->getRememberToken());
        $this->ingresar($usuario)->assertRedirect();
        $this->assertNotEmpty($usuario->fresh()->getRememberToken());
        config(['auth.remember_enabled' => false]);
        $this->migracion()->down();
        $this->assertFalse(Schema::hasColumn('usuarios', 'remember_token'));
        $this->assertEquals($antes, DB::table('usuarios')->where('cod_usuario', $usuario->getKey())->first());
    }

    public function test_recordar_restaura_sesion_sin_contrasena_y_oculta_token(): void
    {
        $this->activar();
        $usuario = User::factory()->create();
        $this->get('/login')->assertSee('name="remember"', false);
        $respuesta = $this->ingresar($usuario)->assertRedirect();
        $cookie = $respuesta->getCookie(Auth::guard()->getRecallerName());
        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
        $this->assertEqualsWithDelta(now()->addDays(30)->getTimestamp(), $cookie->getExpiresTime(), 5);
        $this->assertNotEmpty($usuario->fresh()->getRememberToken());
        $this->assertArrayNotHasKey('remember_token', $usuario->fresh()->toArray());
        $this->recordar($cookie->getValue())->assertOk()->assertJsonPath('cuenta', $usuario->getKey())->assertJsonPath('recordado', true);
    }

    public function test_sin_marcar_no_crea_cookie_persistente(): void
    {
        $this->activar();
        $usuario = User::factory()->create();
        $this->ingresar($usuario, false)->assertRedirect()->assertCookieMissing(Auth::guard()->getRecallerName());
        $this->assertNull($usuario->fresh()->getRememberToken());
    }

    public function test_credenciales_incorrectas_no_crean_cookie_ni_token(): void
    {
        $this->activar();
        $usuario = User::factory()->create();
        $this->post('/login', ['correo' => $usuario->correo, 'password' => 'incorrecta', 'remember' => true])
            ->assertSessionHasErrors()->assertCookieMissing(Auth::guard()->getRecallerName());
        $this->assertGuest();
        $this->assertNull($usuario->fresh()->getRememberToken());
    }

    public function test_restablecimiento_de_contrasena_invalida_cookie_anterior(): void
    {
        $this->activar();
        $usuario = User::factory()->create();
        $cookie = $this->ingresar($usuario)->getCookie(Auth::guard()->getRecallerName())->getValue();
        app(\App\Modulos\Identidad\Aplicacion\Acciones\Fortify\ResetUserPassword::class)->reset($usuario->fresh(), [
            'password' => 'Restablecida123!', 'password_confirmation' => 'Restablecida123!',
        ]);
        $this->recordar($cookie)->assertJsonPath('cuenta', null);
    }

    public function test_cookie_respeta_https_y_desactivacion_no_resucita_al_reactivar(): void
    {
        $this->activar();
        config(['session.secure' => true]);
        Auth::forgetGuards();
        $usuario = User::factory()->create();
        $cookie = $this->ingresar($usuario)->getCookie(Auth::guard()->getRecallerName());
        $this->assertTrue($cookie->isSecure());
        $usuario->fresh()->update(['estado' => 'INACTIVO']);
        $usuario->fresh()->update(['estado' => 'ACTIVO']);
        $this->recordar($cookie->getValue())->assertJsonPath('cuenta', null);
    }

    public function test_logout_invalida_la_cookie_anterior(): void
    {
        $this->activar();
        $usuario = User::factory()->create();
        $cookie = $this->ingresar($usuario)->getCookie(Auth::guard()->getRecallerName())->getValue();
        $antes = $usuario->fresh()->getRememberToken();
        $this->post('/logout')->assertRedirect();
        $this->assertNotSame($antes, $usuario->fresh()->getRememberToken());
        $this->recordar($cookie)->assertJsonPath('cuenta', null)->assertJsonPath('recordado', false);
    }

    public function test_cambio_de_contrasena_invalida_cookie_y_no_registra_token_en_auditoria(): void
    {
        $this->activar();
        $usuario = User::factory()->create();
        $cookie = $this->ingresar($usuario)->getCookie(Auth::guard()->getRecallerName())->getValue();
        $antes = $usuario->fresh()->getRememberToken();
        app(\App\Modulos\Identidad\Aplicacion\Acciones\Fortify\UpdateUserPassword::class)->update($usuario->fresh(), [
            'current_password' => 'password', 'password' => 'NuevaSegura123!', 'password_confirmation' => 'NuevaSegura123!',
        ]);
        $despues = $usuario->fresh()->getRememberToken();
        $this->assertNotSame($antes, $despues);
        $this->recordar($cookie)->assertJsonPath('cuenta', null);
        foreach (\Spatie\Activitylog\Models\Activity::all() as $actividad) {
            $this->assertStringNotContainsString($despues, $actividad->properties->toJson());
            $this->assertStringNotContainsString('remember_token', $actividad->properties->toJson());
        }
    }

    public function test_restauracion_no_admite_cuenta_desactivada_ni_cookie_manipulada(): void
    {
        $this->activar();
        $usuario = User::factory()->create();
        $cookie = $this->ingresar($usuario)->getCookie(Auth::guard()->getRecallerName())->getValue();
        // SQL directo demuestra que el proveedor comprueba estado incluso sin evento de modelo.
        DB::table('usuarios')->where('cod_usuario', $usuario->getKey())->update(['estado' => 'INACTIVO']);
        $this->recordar($cookie)->assertJsonPath('cuenta', null);
        DB::table('usuarios')->where('cod_usuario', $usuario->getKey())->update(['estado' => 'ACTIVO']);
        $this->recordar('usuario-ajeno|token-inventado|huella-inventada')->assertJsonPath('cuenta', null);
    }
}
