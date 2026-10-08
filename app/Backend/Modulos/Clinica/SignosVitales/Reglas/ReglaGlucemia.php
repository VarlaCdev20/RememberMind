<?php

namespace App\Backend\Modulos\Clinica\SignosVitales\Reglas;

use App\Backend\Modulos\Clinica\SignosVitales\Resultados\ResultadoReglaClinica;
use App\Backend\Modulos\Clinica\SignosVitales\Tipos\SeveridadClinica as Nivel;
use App\Backend\Modulos\Clinica\SignosVitales\Tipos\ComportamientoAlerta;

final class ReglaGlucemia extends ReglaBase
{
    public function evaluar(array $mediciones): ?ResultadoReglaClinica
    {
        $valor = $mediciones['glucemia'] ?? null;
        if ($valor === null) return null;
        $c = config('signos_vitales.glucemia');
        if ($valor < $c['critico_bajo']) {
            return $this->resultado('glucemia', (string) $valor, 'mg/dL', Nivel::CRITICO, 'GLUCEMIA_BAJA_CRITICA',
                'Umbral bajo de seguridad', '<'.$c['critico_bajo'].' mg/dL',
                'Glucemia de '.$valor.' mg/dL, por debajo del límite crítico inferior (<'.$c['critico_bajo'].' mg/dL).', $this->recomendacionCritica());
        }
        if ($valor < $c['advertencia_baja']) {
            return $this->resultado('glucemia', (string) $valor, 'mg/dL', Nivel::ADVERTENCIA, 'GLUCEMIA_BAJA',
                'Umbral bajo de atención', '<'.$c['advertencia_baja'].' mg/dL',
                'Glucemia de '.$valor.' mg/dL, por debajo del límite de atención (<'.$c['advertencia_baja'].' mg/dL).');
        }
        if ($valor > $c['revision_alta']) {
            return new ResultadoReglaClinica('glucemia', (string) $valor, 'mg/dL', null,
                'GLUCEMIA_ALTA_REVISAR_CONTEXTO', 'Orientación para revisión',
                '>'.$c['revision_alta'].' mg/dL',
                'Glucemia de '.$valor.' mg/dL, superior a '.$c['revision_alta'].' mg/dL. Una lectura elevada aislada no establece por sí sola una urgencia ni un patrón persistente.',
                'Revisar las lecturas recientes, síntomas y objetivo individual con el profesional responsable.',
                ComportamientoAlerta::SUGERIR);
        }
        return $this->sinRegla('glucemia', (string) $valor, 'mg/dL');
    }
}
