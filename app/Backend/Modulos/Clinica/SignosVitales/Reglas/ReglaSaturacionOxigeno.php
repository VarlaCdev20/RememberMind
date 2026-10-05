<?php

namespace App\Backend\Modulos\Clinica\SignosVitales\Reglas;

use App\Backend\Modulos\Clinica\SignosVitales\Resultados\ResultadoReglaClinica;

final class ReglaSaturacionOxigeno extends ReglaBase
{
    public function evaluar(array $mediciones): ?ResultadoReglaClinica
    {
        $valor = $mediciones['saturacion_oxigeno'] ?? null;
        return $valor === null ? null : $this->sinRegla('saturacion_oxigeno', (string) $valor, '%');
    }
}
