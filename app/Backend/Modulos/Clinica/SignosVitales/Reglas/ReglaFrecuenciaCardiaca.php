<?php

namespace App\Backend\Modulos\Clinica\SignosVitales\Reglas;

use App\Backend\Modulos\Clinica\SignosVitales\Resultados\ResultadoReglaClinica;
use App\Backend\Modulos\Clinica\SignosVitales\Tipos\SeveridadClinica as Nivel;

final class ReglaFrecuenciaCardiaca extends ReglaBase
{
    public function evaluar(array $mediciones): ?ResultadoReglaClinica
    {
        $valor = $mediciones['frecuencia_cardiaca'] ?? null;
        if ($valor === null) return null;
        $c = config('signos_vitales.pulso');
        $nivel = match (true) {
            $valor <= $c['critico_bajo'], $valor > $c['alto_alta'] => Nivel::CRITICO,
            $valor > $c['advertencia_alta'] => Nivel::ALTO,
            $valor <= $c['advertencia_baja'], $valor > $c['normal_alta'] => Nivel::ADVERTENCIA,
            default => Nivel::NORMAL,
        };
        return $this->resultado('frecuencia_cardiaca', (string) $valor, 'lpm', $nivel,
            'FC_'.$nivel->value, 'Referencia operativa', '51–90 lpm',
            $nivel === Nivel::NORMAL ? 'Dentro de la referencia operativa utilizada.' : 'Valor fuera de la referencia operativa utilizada.',
            $nivel === Nivel::CRITICO ? $this->recomendacionCritica() : null);
    }
}
