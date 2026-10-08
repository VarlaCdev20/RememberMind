<?php

namespace App\Backend\Modulos\Clinica\SignosVitales\Reglas;

use App\Backend\Modulos\Clinica\SignosVitales\Resultados\ResultadoReglaClinica;

final class ReglaSaturacionOxigeno extends ReglaBase
{
    public function evaluar(array $mediciones): ?ResultadoReglaClinica
    {
        $valor = $mediciones['saturacion_oxigeno'] ?? null;
        return $valor === null ? null : $this->resultado(
            'saturacion_oxigeno', (string) $valor, '%', null, 'SIN_REGLA_APROBADA',
            'Sin objetivo médico vigente', null,
            'SpO₂ medida: '.$valor.' %. No hay un objetivo médico individual vigente para comparar esta lectura. Su clasificación automática queda pendiente; este estado no indica que la saturación sea normal.',
            'La comparación automática necesita un objetivo médico individual vigente. Puedes registrar el valor medido y su contexto sin asignarle una clasificación automática.',
        );
    }
}
