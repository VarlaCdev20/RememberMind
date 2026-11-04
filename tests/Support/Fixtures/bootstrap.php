<?php

declare(strict_types=1);

// Infraestructura histórica de regresión: no forma parte del autoload del producto.
spl_autoload_register(static function (string $clase): void {
    $prefijo = 'App\\';
    if (! str_starts_with($clase, $prefijo)) {
        return;
    }
    $archivo = __DIR__.'/app/'.str_replace('\\', '/', substr($clase, strlen($prefijo))).'.php';
    if (is_file($archivo)) {
        require_once $archivo;
    }
}, true, true);
