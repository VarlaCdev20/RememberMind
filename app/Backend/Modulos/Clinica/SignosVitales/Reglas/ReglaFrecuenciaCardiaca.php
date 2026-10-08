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
        $referencia = ($c['advertencia_baja'] + 1).'–'.$c['normal_alta'].' lpm';
        $explicacion = 'Frecuencia cardíaca de '.$valor.' latidos por minuto. '.match (true) {
            $valor <= $c['critico_bajo'] => 'Alcanza el límite crítico inferior de la regla institucional (≤'.$c['critico_bajo'].' lpm).',
            $valor > $c['alto_alta'] => 'Supera el límite crítico superior de la regla institucional (>'.$c['alto_alta'].' lpm).',
            $nivel === Nivel::NORMAL => 'Está dentro del intervalo de referencia de '.$referencia.'.',
            $valor <= $c['advertencia_baja'] => 'Está por debajo del intervalo de referencia de '.$referencia.'.',
            default => 'Está por encima del intervalo de referencia de '.$referencia.'.',
        };
        return $this->resultado('frecuencia_cardiaca', (string) $valor, 'lpm', $nivel,
            'FC_'.$nivel->value, 'Referencia operativa', $referencia, $explicacion,
            $nivel === Nivel::CRITICO ? $this->recomendacionCritica() : null);
    }
}
