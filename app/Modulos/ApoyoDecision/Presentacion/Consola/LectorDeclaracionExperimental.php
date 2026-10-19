<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Consola;

use DomainException;

/** Lee datos declarativos locales acotados; nunca ejecuta código del archivo. */
final class LectorDeclaracionExperimental
{
    public static function leer(mixed $archivo): array
    {
        $ruta = is_string($archivo) ? realpath($archivo) : false;
        if ($ruta === false || ! is_file($ruta) || ! is_readable($ruta) || filesize($ruta) > 1048576) {
            throw new DomainException('Indique un archivo local declarativo de hasta 1 MiB.');
        }
        $contenido = file_get_contents($ruta);
        if ($contenido === false || strlen($contenido) > 1048576) {
            throw new DomainException('No se pudo leer el archivo declarativo dentro del límite permitido.');
        }
        $datos = json_decode($contenido, true, 64, JSON_THROW_ON_ERROR);
        if (! is_array($datos) || array_is_list($datos)) {
            throw new DomainException('El archivo debe contener un objeto declarativo.');
        }

        return $datos;
    }
}
