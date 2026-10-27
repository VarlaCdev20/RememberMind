<?php

namespace App\Compartido\Infraestructura\Notificaciones;

use Illuminate\Mail\Events\MessageSending;

final class ExigirTransporteCorreoReal
{
    // Dirección fijada expresamente por la propietaria; cambiarla requiere su autorización.
    public const REMITENTE_INSTITUCIONAL = 'remembermind.health@gmail.com';

    public function handle(MessageSending $evento): void
    {
        $this->verificar($evento->data['mailer'] ?? null);

        $remitentes = $evento->message->getFrom();
        if (count($remitentes) !== 1 || $remitentes[0]->getAddress() !== self::REMITENTE_INSTITUCIONAL
            || ($evento->message->getSender() !== null && $evento->message->getSender()->getAddress() !== self::REMITENTE_INSTITUCIONAL)
            || ($evento->message->getReturnPath() !== null && $evento->message->getReturnPath()->getAddress() !== self::REMITENTE_INSTITUCIONAL)) {
            throw new CorreoNoConfigurado('El remitente institucional está protegido. Se rechazó un mensaje con otra dirección de origen.');
        }
    }

    public function verificar(?string $mailer = null, array $visitados = []): void
    {
        if (config('mail.from.address') !== self::REMITENTE_INSTITUCIONAL) {
            throw new CorreoNoConfigurado('El remitente institucional está protegido y no puede sustituirse sin autorización de la propietaria.');
        }

        $mailer ??= config('mail.default');
        $configuracion = config('mail.mailers.'.$mailer, []);
        $transporte = $configuracion['transport'] ?? null;

        if (in_array($mailer, $visitados, true) || ! $transporte
            || $transporte === 'log'
            || ($transporte === 'array' && ! app()->environment('testing'))) {
            throw new CorreoNoConfigurado('El envío de correo no está conectado. Configura un servicio de correo real; el modo de registro local no entrega mensajes.');
        }

        if (in_array($transporte, ['failover', 'roundrobin'], true)) {
            if (empty($configuracion['mailers'])) {
                throw new CorreoNoConfigurado('No hay un servicio de correo configurado para enviar mensajes.');
            }
            foreach ($configuracion['mailers'] as $alternativa) {
                $this->verificar($alternativa, [...$visitados, $mailer]);
            }
        }

        if ($transporte === 'smtp' && ($configuracion['host'] ?? null) === 'smtp.gmail.com'
            && (blank($configuracion['username'] ?? null) || blank($configuracion['password'] ?? null))) {
            throw new CorreoNoConfigurado('Falta completar la cuenta de Gmail y su contraseña de aplicación en la configuración del correo.');
        }

        if ($transporte === 'smtp' && filled($configuracion['url'] ?? null)) {
            throw new CorreoNoConfigurado('No se permite una URL SMTP alternativa que sustituya la cuenta institucional protegida.');
        }

        if ($transporte === 'smtp' && ($configuracion['host'] ?? null) === 'smtp.gmail.com'
            && $configuracion['username'] !== self::REMITENTE_INSTITUCIONAL) {
            throw new CorreoNoConfigurado('La cuenta de Gmail debe ser la cuenta institucional protegida.');
        }
    }
}
