<?php

namespace App\Backend\Modulos\Clinica\SignosVitales\Reglas;

use App\Backend\Modulos\Clinica\SignosVitales\Resultados\ResultadoReglaClinica;
use App\Backend\Modulos\Clinica\SignosVitales\Tipos\SeveridadClinica as Nivel;

final class ReglaFrecuenciaRespiratoria extends ReglaBase
{
    public function evaluar(array $mediciones): ?ResultadoReglaClinica
    {
        $valor = $mediciones['frecuencia_respiratoria'] ?? null;
        if ($valor === null) return null;
        $c = config('signos_vitales.respiracion');
        $nivel = match (true) {
            $valor <= $c['critico_bajo'], $valor > $c['alto_alta'] => Nivel::CRITICO,
            $valor <= $c['advertencia_baja'] => Nivel::ADVERTENCIA,
            $valor > $c['normal_alta'] => Nivel::ALTO,
            default => Nivel::NORMAL,
        };
        return $this->resultado('frecuencia_respiratoria', (string) $valor, 'rpm', $nivel,
            'FR_'.$nivel->value, 'Referencia general', '12–20 rpm',
            $nivel === Nivel::NORMAL ? 'Dentro de la referencia general utilizada.' : 'Valor fuera de la referencia general utilizada.',
            $nivel === Nivel::CRITICO ? $this->recomendacionCritica() : null);
    }
}
