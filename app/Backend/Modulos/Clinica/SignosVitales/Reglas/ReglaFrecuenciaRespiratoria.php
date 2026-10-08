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
        $referencia = ($c['advertencia_baja'] + 1).'–'.$c['normal_alta'].' rpm';
        $explicacion = 'Frecuencia respiratoria de '.$valor.' respiraciones por minuto. '.match (true) {
            $valor <= $c['critico_bajo'] => 'Alcanza el límite crítico inferior de la regla institucional (≤'.$c['critico_bajo'].' rpm).',
            $valor > $c['alto_alta'] => 'Supera el límite crítico superior de la regla institucional (>'.$c['alto_alta'].' rpm).',
            $nivel === Nivel::NORMAL => 'Está dentro del intervalo de referencia de '.$referencia.'.',
            $valor <= $c['advertencia_baja'] => 'Está por debajo del intervalo de referencia de '.$referencia.'.',
            default => 'Está por encima del intervalo de referencia de '.$referencia.'.',
        };
        return $this->resultado('frecuencia_respiratoria', (string) $valor, 'rpm', $nivel,
            'FR_'.$nivel->value, 'Referencia general', $referencia, $explicacion,
            $nivel === Nivel::CRITICO ? $this->recomendacionCritica() : null);
    }
}
