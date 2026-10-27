<?php

// Comprobación local de autenticación: no envía mensajes ni imprime credenciales.
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$transport = null;
try {
    if (config('mail.default') !== 'smtp' || config('mail.mailers.smtp.host') !== 'smtp.gmail.com'
        || (int) config('mail.mailers.smtp.port') !== 587) {
        echo "CONFIGURACION_SMTP_NO_ESPERADA\n";
        exit(2);
    }
    if (blank(config('mail.mailers.smtp.username')) || blank(config('mail.mailers.smtp.password'))) {
        echo "CREDENCIALES_INCOMPLETAS\n";
        exit(3);
    }
    app(App\Compartido\Infraestructura\Notificaciones\ExigirTransporteCorreoReal::class)->verificar();
    $transport = app('mailer')->getSymfonyTransport();
    $transport->getStream()->setTimeout(15);
    $transport->setRequireTls(true);
    $transport->start();
    echo "SMTP_AUTENTICADO_TLS_OK\n";
    echo config('mail.from.address') === config('mail.mailers.smtp.username')
        ? "REMITENTE_COINCIDE_CON_CUENTA\n" : "REMITENTE_DIFERENTE_REQUIERE_ALIAS_AUTORIZADO\n";
    $transport->stop();
} catch (Throwable $e) {
    $message = strtolower($e->getMessage());
    $category = str_contains($message, 'authenticat') ? 'AUTENTICACION_RECHAZADA'
        : ((str_contains($message, 'certificate') || str_contains($message, 'crypto')) ? 'VERIFICACION_TLS_FALLIDA' : 'CONEXION_SMTP_FALLIDA');
    echo $category."\n";
    foreach (['535' => 'GMAIL_535_CREDENCIALES_NO_ACEPTADAS', '534' => 'GMAIL_534_REQUIERE_AUTORIZACION_DE_APLICACION', '530' => 'GMAIL_530_REQUIERE_AUTENTICACION'] as $code => $label) {
        if (str_contains($message, (string) $code)) {
            echo $label."\n";
        }
    }
    exit(1);
}
