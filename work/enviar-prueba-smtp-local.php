<?php

// Envío manual único; destinatario autorizado por la propietaria en esta sesión.
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$destinatario = $argv[1] ?? '';
if (! filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "DESTINATARIO_INVALIDO\n");
    exit(2);
}
try {
    if (config('mail.default') !== 'smtp' || config('mail.mailers.smtp.host') !== 'smtp.gmail.com') {
        throw new RuntimeException('Transporte inesperado.');
    }
    app(App\Compartido\Infraestructura\Notificaciones\ExigirTransporteCorreoReal::class)->verificar();
    $mailer = app('mailer');
    $transport = $mailer->getSymfonyTransport();
    $transport->getStream()->setTimeout(20);
    $transport->setRequireTls(true);
    $sent = $mailer->raw(
        "Hola.\n\nEste es un mensaje técnico de prueba enviado desde RememberMind para comprobar la conexión del correo saliente.\n\nNo contiene expedientes, contraseñas ni información clínica. No requiere abrir enlaces ni responder.\n\nRememberMind",
        fn ($message) => $message->to($destinatario)->subject('RememberMind — Prueba de conexión del correo')
    );
    if ($sent === null) {
        fwrite(STDERR, "ENVIO_NO_CONFIRMADO\n");
        exit(1);
    }
    echo "MENSAJE_ACEPTADO_POR_SMTP\n";
    $transport->stop();
} catch (Throwable $e) {
    fwrite(STDERR, "ENVIO_FALLIDO_NO_CONFIRMADO\n");
    exit(1);
}
