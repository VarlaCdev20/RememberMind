<?php

namespace App\Backend\Modulos\Clinica\SignosVitales\Tipos;

enum SeveridadClinica: string
{
    case NORMAL = 'NORMAL';
    case OBJETIVO_PERSONALIZADO = 'OBJETIVO_PERSONALIZADO';
    case ADVERTENCIA = 'ADVERTENCIA';
    case ALTO = 'ALTO';
    case CRITICO = 'CRITICO';

    public function prioridad(): int
    {
        return match ($this) {
            self::NORMAL, self::OBJETIVO_PERSONALIZADO => 0,
            self::ADVERTENCIA => 1,
            self::ALTO => 2,
            self::CRITICO => 3,
        };
    }
}
