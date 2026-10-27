<?php

namespace Tests\Feature;

use App\Compartido\Infraestructura\Notificaciones\ExigirTransporteCorreoReal;
use App\Models\User;
use App\Modulos\Identidad\Presentacion\Http\Controllers\UsuarioController;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class TransporteCorreoRealTest extends TestCase
{
    public function test_el_evento_impide_que_un_correo_se_guarde_en_log_como_si_se_hubiera_enviado(): void
    {
        config(['mail.default' => 'log']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('El envío de correo no está conectado');

        Mail::raw('Mensaje sintético sin datos personales.', fn ($mensaje) => $mensaje->to('destino@example.test')->subject('Prueba'));
    }

    public function test_valida_el_mailer_elegido_expresamente(): void
    {
        config(['mail.default' => 'array']);

        $this->expectException(RuntimeException::class);
        Mail::mailer('log')->raw('Prueba sintética.', fn ($mensaje) => $mensaje->to('destino@example.test'));
    }

    public function test_no_permite_que_failover_oculte_un_fallo_smtp_usando_log(): void
    {
        config(['mail.default' => 'failover']);

        $this->expectException(RuntimeException::class);
        app(ExigirTransporteCorreoReal::class)->verificar();
    }

    public function test_el_transporte_array_solo_esta_disponible_en_pruebas(): void
    {
        $this->app->instance('env', 'local');

        $this->expectException(RuntimeException::class);
        app(ExigirTransporteCorreoReal::class)->verificar('array');
    }

    public function test_las_pruebas_pueden_capturar_correos_sin_red(): void
    {
        $resultado = Mail::mailer('array')->raw('Prueba sintética.', fn ($mensaje) => $mensaje->to('destino@example.test'));

        $this->assertNotNull($resultado);
    }

    #[DataProvider('credencialesIncompletas')]
    public function test_gmail_rechaza_credenciales_incompletas(?string $usuario, ?string $clave): void
    {
        config(['mail.mailers.smtp.host' => 'smtp.gmail.com', 'mail.mailers.smtp.username' => $usuario, 'mail.mailers.smtp.password' => $clave]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('contraseña de aplicación');
        app(ExigirTransporteCorreoReal::class)->verificar('smtp');
    }

    public static function credencialesIncompletas(): array
    {
        return [[null, null], ['cuenta@example.test', ''], ['', 'clave-sintetica']];
    }

    public function test_configuracion_gmail_completa_supera_la_validacion_sin_afirmar_entrega(): void
    {
        $this->expectNotToPerformAssertions();
        config(['mail.mailers.smtp.host' => 'smtp.gmail.com', 'mail.mailers.smtp.username' => ExigirTransporteCorreoReal::REMITENTE_INSTITUCIONAL, 'mail.mailers.smtp.password' => 'clave-sintetica']);

        app(ExigirTransporteCorreoReal::class)->handle(new MessageSending((new Email)->from(ExigirTransporteCorreoReal::REMITENTE_INSTITUCIONAL), ['mailer' => 'smtp']));
    }

    public function test_la_ficha_no_se_encola_si_no_hay_transporte_real(): void
    {
        Bus::fake();
        config(['mail.default' => 'log']);
        $usuario = new User(['correo' => 'destino@example.test']);

        $respuesta = app(UsuarioController::class)->enviarFichaCorreo($usuario);

        $this->assertSame(500, $respuesta->getStatusCode());
        $this->assertFalse($respuesta->getData(true)['success']);
        $this->assertStringNotContainsString('RuntimeException', $respuesta->getContent());
        Bus::assertNothingDispatched();
    }
}
