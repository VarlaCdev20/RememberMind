<?php

namespace Tests\Feature;

use App\Compartido\Infraestructura\Notificaciones\CorreoNoConfigurado;
use App\Compartido\Infraestructura\Notificaciones\ExigirTransporteCorreoReal;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RemitenteInstitucionalProtegidoTest extends TestCase
{
    public function test_el_entorno_no_puede_sustituir_el_remitente_institucional(): void
    {
        $entorno = Env::getRepository();
        $anterior = $entorno->get('MAIL_FROM_ADDRESS');
        $entorno->set('MAIL_FROM_ADDRESS', 'otra-cuenta@example.test');
        try {
            $configuracion = require config_path('mail.php');
            $this->assertSame(ExigirTransporteCorreoReal::REMITENTE_INSTITUCIONAL, $configuracion['from']['address']);
        } finally {
            $anterior === null ? $entorno->clear('MAIL_FROM_ADDRESS') : $entorno->set('MAIL_FROM_ADDRESS', $anterior);
        }
    }

    public function test_bloquea_sustitucion_de_configuracion_en_ejecucion(): void
    {
        config(['mail.from.address' => 'otra-cuenta@example.test']);

        $this->expectException(CorreoNoConfigurado::class);
        app(ExigirTransporteCorreoReal::class)->verificar('array');
    }

    public function test_bloquea_otra_cuenta_smtp_de_gmail(): void
    {
        config(['mail.mailers.smtp.host' => 'smtp.gmail.com', 'mail.mailers.smtp.username' => 'otra-cuenta@example.test',
            'mail.mailers.smtp.password' => 'clave-sintetica', 'mail.mailers.smtp.url' => null]);

        $this->expectException(CorreoNoConfigurado::class);
        $this->expectExceptionMessage('cuenta institucional protegida');
        app(ExigirTransporteCorreoReal::class)->verificar('smtp');
    }

    public function test_bloquea_dsn_que_puede_sustituir_la_autenticacion_smtp(): void
    {
        config(['mail.mailers.smtp.url' => 'smtp://cuenta-sintetica:clave-sintetica@localhost:2525']);

        $this->expectException(CorreoNoConfigurado::class);
        app(ExigirTransporteCorreoReal::class)->verificar('smtp');
    }

    #[DataProvider('cabecerasDeOrigen')]
    public function test_el_mensaje_no_puede_sustituir_cabeceras_de_origen(string $cabecera): void
    {
        $this->expectException(CorreoNoConfigurado::class);
        Mail::mailer('array')->raw('Prueba sin datos personales.', function ($mensaje) use ($cabecera) {
            $mensaje->to('destino@example.test');
            $mensaje->{$cabecera}('otra-cuenta@example.test');
        });
    }

    public static function cabecerasDeOrigen(): array
    {
        return [['from'], ['sender'], ['returnPath']];
    }

    public function test_mensaje_conserva_remitente_y_destinatario_independientes(): void
    {
        $enviado = Mail::mailer('array')->raw('Prueba sin datos personales.', fn ($mensaje) => $mensaje->to('destino@example.test'));
        $original = $enviado->getSymfonySentMessage()->getOriginalMessage();

        $this->assertSame(ExigirTransporteCorreoReal::REMITENTE_INSTITUCIONAL, $original->getFrom()[0]->getAddress());
        $this->assertSame('destino@example.test', $original->getTo()[0]->getAddress());
    }
}
