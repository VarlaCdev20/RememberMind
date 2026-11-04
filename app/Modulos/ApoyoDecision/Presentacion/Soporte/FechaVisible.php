<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Soporte;

use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;

final class FechaVisible
{
    public static function mostrar(?string $valor, string $sinDato = 'Fecha no disponible'): string
    {
        if ($valor === null || trim($valor) === '') {
            return $sinDato;
        }

        // Algunas instantáneas ya conservan la fecha en formato local, incluso con microsegundos y zona.
        if (preg_match('/^(\d{2}\/\d{2}\/\d{4})(?:[ T](\d{2}:\d{2}))?/', $valor, $partes) === 1) {
            return $partes[1].(isset($partes[2]) ? ' '.$partes[2] : '');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}(?:[T ]|$)/', $valor) === 1) {
            try {
                return CarbonImmutable::parse($valor)->format(strlen($valor) === 10 ? 'd/m/Y' : 'd/m/Y H:i');
            } catch (InvalidFormatException) {
                return $sinDato;
            }
        }

        return $sinDato;
    }
}
