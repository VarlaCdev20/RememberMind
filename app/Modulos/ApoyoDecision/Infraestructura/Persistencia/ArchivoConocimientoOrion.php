<?php

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia;

use App\Modulos\ApoyoDecision\Dominio\Puertos\FuenteConocimientoOrion;
use RuntimeException;

final class ArchivoConocimientoOrion implements FuenteConocimientoOrion
{
    public function leer(): array
    {
        $contenido = file_get_contents(__DIR__.'/datos/orion-v1-conceptos.json');
        if ($contenido === false) {
            throw new RuntimeException('No se pudo leer la fuente documental O.R.I.O.N.');
        }

        return json_decode($contenido, true, 512, JSON_THROW_ON_ERROR);
    }
}
