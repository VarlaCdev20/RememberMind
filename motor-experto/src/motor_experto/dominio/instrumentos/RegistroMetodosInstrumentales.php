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
            'identidad' => ['instrumento' => 'HVLT-R', 'forma' => 5, 'idioma' => 'es'],
            'entradas' => NormalizadorHvltBolivia2015::ENTRADAS,
            'componentes' => ['MEM-S01', 'MEM-S02'], 'fuente' => NormalizadorHvltBolivia2015::FUENTE,
            'edad_minima' => 18, 'edad_maxima' => 95, 'escolaridad_minima' => 1,
            'lectoescritura_requerida' => true, 'reproduce_reactivos' => false, 'revision_profesional' => 'PENDIENTE']]
            + (new NormalizadorBateriaBolivia2015)->metodos();
    }

    public function ejecutar(string $metodo, array $captura): array
    {
        if (! isset($this->metodos()[$metodo])) {
            throw new DomainException('No existe un método instrumental registrado con esa identidad.');
        }

        return $metodo === NormalizadorHvltBolivia2015::METODO
            ? (new NormalizadorHvltBolivia2015)->normalizar($captura)
            : (new NormalizadorBateriaBolivia2015)->normalizar($metodo, $captura);
    }
}
