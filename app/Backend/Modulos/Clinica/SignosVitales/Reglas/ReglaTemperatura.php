<?php

namespace App\Backend\Modulos\Clinica\SignosVitales\Reglas;

use App\Backend\Modulos\Clinica\SignosVitales\Resultados\ResultadoReglaClinica;
use App\Backend\Modulos\Clinica\SignosVitales\Tipos\SeveridadClinica as Nivel;

final class ReglaTemperatura extends ReglaBase
{
    public function evaluar(array $mediciones): ?ResultadoReglaClinica
    {
        $valor = $mediciones['temperatura'] ?? null;
        if ($valor === null) return null;
        $c = config('signos_vitales.temperatura');
        if ($valor <= $c['critico_bajo'] || $valor >= $c['critico_alto']) {
            return $this->resultado('temperatura', (string) $valor, '°C', Nivel::CRITICO, 'TEMP_CRITICA',
                'Umbral crítico de seguridad', '≤'.$c['critico_bajo'].' o ≥'.$c['critico_alto'].' °C',
                'La temperatura supera un umbral crítico de seguridad.', $this->recomendacionCritica());
        }
        if ($valor >= $c['advertencia_alta']) {
            return $this->resultado('temperatura', (string) $valor, '°C', Nivel::ADVERTENCIA, 'TEMP_ELEVADA',
                'Referencia operativa geriátrica', $c['referencia_baja'].'–'.$c['referencia_alta'].' °C',
                'La elevación de temperatura puede ser relevante en una persona adulta mayor.');
        }
        if ($valor >= $c['referencia_baja'] && $valor <= $c['referencia_alta']) {
            return $this->resultado('temperatura', (string) $valor, '°C', Nivel::NORMAL, 'TEMP_REFERENCIA',
                'Referencia operativa geriátrica', $c['referencia_baja'].'–'.$c['referencia_alta'].' °C',
                'Dentro de la referencia operativa utilizada.');
        }
        return $this->sinRegla('temperatura', (string) $valor, '°C');
    }
}
