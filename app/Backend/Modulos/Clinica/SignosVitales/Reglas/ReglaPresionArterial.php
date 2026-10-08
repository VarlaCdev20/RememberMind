<?php

namespace App\Backend\Modulos\Clinica\SignosVitales\Reglas;

use App\Backend\Modulos\Clinica\SignosVitales\Resultados\ResultadoReglaClinica;
use App\Backend\Modulos\Clinica\SignosVitales\Tipos\SeveridadClinica as Nivel;

final class ReglaPresionArterial extends ReglaBase
{
    public function evaluar(array $mediciones): ?ResultadoReglaClinica
    {
        $sis = $mediciones['presion_sistolica'] ?? null;
        $dia = $mediciones['presion_diastolica'] ?? null;
        if ($sis === null && $dia === null) return null;
        if ($sis === null || $dia === null) return $this->sinRegla('presion_arterial', ($sis ?? '—').'/'.($dia ?? '—'), 'mmHg');

        $c = config('signos_vitales.presion');
        $valor = $sis.'/'.$dia;
        $causas = [];
        if ($sis > $c['sistolica_critica_alta']) $causas[] = 'la sistólica supera '.$c['sistolica_critica_alta'].' mmHg';
        if ($dia > $c['diastolica_critica_alta']) $causas[] = 'la diastólica supera '.$c['diastolica_critica_alta'].' mmHg';
        if ($causas !== []) {
            return $this->resultado('presion_arterial', $valor, 'mmHg', Nivel::CRITICO, 'PA_CRITICA',
                'Umbral crítico de seguridad', 'Sistólica >'.$c['sistolica_critica_alta'].' o diastólica >'.$c['diastolica_critica_alta'].' mmHg',
                'Presión arterial de '.$valor.' mmHg. '.ucfirst(implode(' y ', $causas)).': se supera el límite crítico de la regla institucional.', $this->recomendacionCritica());
        }
        if ($sis <= $c['sistolica_advertencia_baja']) {
            return $this->resultado('presion_arterial', $valor, 'mmHg', Nivel::ADVERTENCIA, 'PA_SISTOLICA_BAJA',
                'Umbral de atención', 'Sistólica ≤'.$c['sistolica_advertencia_baja'].' mmHg',
                'Presión arterial de '.$valor.' mmHg. La presión sistólica de '.$sis.' mmHg alcanza el límite bajo de atención (≤'.$c['sistolica_advertencia_baja'].' mmHg).');
        }
        if ($sis < $c['sistolica_referencia_alta'] && $dia < $c['diastolica_referencia_alta']) {
            return $this->resultado('presion_arterial', $valor, 'mmHg', Nivel::NORMAL, 'PA_REFERENCIA',
                'Referencia general', '<'.$c['sistolica_referencia_alta'].' / <'.$c['diastolica_referencia_alta'].' mmHg',
                'Presión arterial de '.$valor.' mmHg. La presión sistólica y la diastólica están dentro de los límites de referencia utilizados.');
        }

        return $this->sinRegla('presion_arterial', $valor, 'mmHg');
    }
}
