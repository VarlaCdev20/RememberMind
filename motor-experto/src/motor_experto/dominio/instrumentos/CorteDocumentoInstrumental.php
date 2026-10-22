<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

use DateTimeImmutable;
use DateTimeInterface;

/** La constancia documental debe existir en el corte que pretende respaldar. */
final class CorteDocumentoInstrumental
{
    public static function admite(?array $documento, DateTimeInterface $corte): bool
    {
        $valor = $documento['fecha_hora'] ?? null;
        if (! is_string($valor)) {
            return false;
        }
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:s.uP', $valor);
        $errores = DateTimeImmutable::getLastErrors();

        return $fecha !== false && ($errores === false || ($errores['warning_count'] === 0 && $errores['error_count'] === 0))
            && $fecha <= $corte;
    }
}
