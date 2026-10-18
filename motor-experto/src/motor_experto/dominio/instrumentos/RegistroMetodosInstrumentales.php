<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

use DomainException;

/** Métodos explícitos investigados; no identifica el instrumento aplicado por similitud de nombres. */
final class RegistroMetodosInstrumentales
{
    public function metodos(): array
    {
        return [NormalizadorHvltBolivia2015::METODO => ['instrumento' => 'HVLT-R', 'forma' => 5, 'idioma' => 'es',
            'componentes' => ['MEM-S01', 'MEM-S02'], 'fuente' => NormalizadorHvltBolivia2015::FUENTE,
            'edad_minima' => 18, 'edad_maxima' => 95, 'escolaridad_minima' => 1,
            'lectoescritura_requerida' => true, 'reproduce_reactivos' => false, 'revision_profesional' => 'PENDIENTE']];
    }

    public function ejecutar(string $metodo, array $captura): array
    {
        return match ($metodo) {
            NormalizadorHvltBolivia2015::METODO => (new NormalizadorHvltBolivia2015)->normalizar($captura),
            default => throw new DomainException('No existe un método instrumental registrado con esa identidad.'),
        };
    }
}
