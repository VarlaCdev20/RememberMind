<?php

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia;

use App\Modulos\ApoyoDecision\Dominio\Puertos\FuenteConocimientoCognitivo;
use RuntimeException;

final class ArchivoConocimientoCognitivo implements FuenteConocimientoCognitivo
{
    public function leer(): array
    {
        $contenido = file_get_contents(__DIR__.'/datos/conceptos-cognitivos.json');
        if ($contenido === false) {
            throw new RuntimeException('No se pudo leer la base conceptual cognitiva.');
        }

        return json_decode($contenido, true, 512, JSON_THROW_ON_ERROR);
    }
}
