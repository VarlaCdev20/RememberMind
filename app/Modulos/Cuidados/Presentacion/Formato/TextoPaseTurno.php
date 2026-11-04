<?php

namespace App\Modulos\Cuidados\Presentacion\Formato;

use App\Compartido\Aplicacion\Formato\TextoVisible;

final class TextoPaseTurno
{
    public static function pendientes(?string $texto): string
    {
        $filas = json_decode($texto ?? '', true);
        if (! is_array($filas)) {
            return TextoVisible::sinCodigosInternos($texto);
        }

        return collect($filas)->filter(fn ($fila) => is_array($fila) && isset($fila['detalle']))
            ->map(fn ($fila) => TextoVisible::sinCodigosInternos(implode(' · ', array_filter([
                $fila['tipo'] ?? null, $fila['detalle'], $fila['estado'] ?? null,
            ]))))->implode("\n");
    }
}
