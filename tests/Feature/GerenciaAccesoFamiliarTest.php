<?php

namespace Tests\Feature;

use App\Models\Contacto;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\GestionarAccesoFamiliar;
use App\Modulos\Social\Aplicacion\Acciones\GestionarRedApoyo;
use App\Notifications\ResetPasswordNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class GerenciaAccesoFamiliarTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    private Contacto $contacto;

    private ResidenteContacto $vinculo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        Notification::fake();
        config(['mail.from.address' => 'remembermind.health@gmail.com', 'mail.default' => 'array']);
        $this->actor = User::factory()->create();
        $this->actor->assignRole('GERENTE');
        $this->actingAs($this->actor);
        $this->contacto = app(GestionarRedApoyo::class)->guardarContacto($this->actor, ['nombres' => 'Familiar de prueba', 'apellido_paterno' => 'Sintético', 'correo' => 'contacto@example.test']);
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);
        $this->vinculo = app(GestionarRedApoyo::class)->vincular($this->actor, $residente->getKey(), $this->contacto->getKey(), ['parentesco' => 'Hija', 'responsable_principal' => false, 'contacto_emergencia' => false]);
    }

    public function test_alta_sin_permiso_informativo_no_crea_usuario_ni_publica_fuentes(): void
    {
        $antes = User::count();
        try {
            app(GestionarAccesoFamiliar::class)->crear($this->actor, $this->contacto->getKey(), 'acceso@example.test');
            $this->fail('Falta soporte de acceso.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('correo', $e->errors());
        }
        $this->assertSame($antes, User::count());
        $this->assertNull($this->contacto->fresh()->cod_usuario);
        Notification::assertNothingSent();
    }

    public function test_cuenta_con_vinculo_autorizado_envia_enlace_de_un_uso_sin_personal(): void
    {
        $this->vinculo->update(['autoriza_informacion' => true]);
        $cuenta = app(GestionarAccesoFamiliar::class)->crear($this->actor, $this->contacto->getKey(), 'acceso@example.test');
        $this->assertSame(['FAMILIAR'], $cuenta->getRoleNames()->all());
        $this->assertNull($cuenta->personal);
        $this->assertSame($cuenta->getKey(), $this->contacto->fresh()->cod_usuario);
        $this->assertSame('contacto@example.test', $this->contacto->fresh()->correo);
        $token = null;
        Notification::assertSentTo($cuenta, ResetPasswordNotification::class, function ($n) use (&$token) {
            $token = $n->token;

            return true;
        });
        $this->assertTrue(Password::broker(config('fortify.passwords'))->tokenExists($cuenta, $token));
        auth()->logout();
        $this->post(route('password.update'), ['token' => $token, 'correo' => $cuenta->correo, 'password' => 'PersonalFamiliar#123', 'password_confirmation' => 'PersonalFamiliar#123'])->assertRedirect();
        $this->assertFalse(Password::broker(config('fortify.passwords'))->tokenExists($cuenta, $token));
        $this->assertDatabaseCount('personal', 0);
    }

    public function test_recuperacion_y_suspension_no_modifican_vinculos_y_reintento_se_rechaza(): void
    {
        $this->vinculo->update(['autoriza_informacion' => true]);
        $servicio = app(GestionarAccesoFamiliar::class);
        $cuenta = $servicio->crear($this->actor, $this->contacto->getKey(), 'acceso@example.test');
        $antes = $this->vinculo->fresh()->getAttributes();
        $servicio->cambiarEstado($this->actor, $this->contacto->getKey(), 'ACTIVO');
        $this->assertSame('SUSPENDIDO', $cuenta->fresh()->estado);
        $this->assertSame($antes, $this->vinculo->fresh()->getAttributes());
        try {
            $servicio->recuperar($this->actor, $this->contacto->getKey());
            $this->fail('No reactiva.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('acceso', $e->errors());
        }
        $this->assertSame('SUSPENDIDO', $cuenta->fresh()->estado);
        Notification::assertSentToTimes($cuenta, ResetPasswordNotification::class, 1);
    }

    public function test_cuenta_ajena_multirrol_y_actor_revocado_no_se_gestionan(): void
    {
        $ajena = User::factory()->create();
        $ajena->assignRole(['FAMILIAR', 'GERENTE']);
        $this->contacto->update(['cod_usuario' => $ajena->getKey()]);
        $this->expectException(HttpException::class);
        try {
            app(GestionarAccesoFamiliar::class)->cambiarEstado($this->actor, $this->contacto->getKey(), 'ACTIVO');
        } finally {
            $this->assertSame('ACTIVO', $ajena->fresh()->estado);
            Notification::assertNothingSent();
        }
    }

    public function test_fallo_del_vinculo_de_cuenta_revierte_solo_el_alta_nueva(): void
    {
        $this->vinculo->update(['autoriza_informacion'=>true]);
        $cuentas=User::count();$fallar=true;
        \Illuminate\Support\Facades\Event::listen('eloquent.updating: '.Contacto::class,function ($contacto) use (&$fallar) {if($fallar && $contacto->isDirty('cod_usuario')) throw new \RuntimeException('Fallo sintético al asociar la cuenta');});
        try {app(GestionarAccesoFamiliar::class)->crear($this->actor,$this->contacto->getKey(),'fallo@example.test');$this->fail('Debe revertir la asociación y la cuenta nueva.');}
        catch(\RuntimeException $error){$this->assertSame('Fallo sintético al asociar la cuenta',$error->getMessage());}
        finally{$fallar=false;}
        $this->assertSame($cuentas,User::count());$this->assertNull($this->contacto->fresh()->cod_usuario);
        $this->assertSame('ACTIVO',$this->vinculo->fresh()->estado);$this->assertTrue($this->vinculo->fresh()->autoriza_informacion);Notification::assertNothingSent();
    }

    public function test_cambiar_correo_de_acceso_invalida_token_sin_reescribir_correo_de_contacto(): void
    {
        $this->vinculo->update(['autoriza_informacion'=>true]);$a=app(GestionarAccesoFamiliar::class);
        $cuenta=$a->crear($this->actor,$this->contacto->getKey(),'anterior@example.test');$token=null;
        Notification::assertSentTo($cuenta,ResetPasswordNotification::class,function($n) use(&$token){$token=$n->token;return true;});
        $a->actualizarCorreo($this->actor,$this->contacto->getKey(),'nuevo@example.test');
        $this->assertSame('nuevo@example.test',$cuenta->fresh()->correo);$this->assertSame('contacto@example.test',$this->contacto->fresh()->correo);
        $this->assertFalse(Password::broker(config('fortify.passwords'))->tokenExists($cuenta,$token));
        $this->assertDatabaseCount('password_reset_tokens',0);
    }
}
