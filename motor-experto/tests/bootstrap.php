<?php

declare(strict_types=1);

// Solo los ejecutores de pruebas incluyen este archivo. El producto no carga fixtures.
spl_autoload_register(static function (string $clase): void {
    $prefijo = 'MotorExperto\\';
    if (! str_starts_with($clase, $prefijo)) {
        return;
    }
    $archivo = __DIR__.'/fixtures/'.str_replace('\\', '/', substr($clase, strlen($prefijo))).'.php';
    if (is_file($archivo)) {
        require_once $archivo;
    }
}, true, true);
