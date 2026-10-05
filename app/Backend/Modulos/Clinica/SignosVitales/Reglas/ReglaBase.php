<?php

namespace App\Backend\Modulos\Clinica\SignosVitales\Reglas;

use App\Backend\Modulos\Clinica\SignosVitales\Resultados\ResultadoReglaClinica;
use App\Backend\Modulos\Clinica\SignosVitales\Tipos\ComportamientoAlerta;
use App\Backend\Modulos\Clinica\SignosVitales\Tipos\SeveridadClinica;

abstract class ReglaBase implements ReglaClinica
{
    protected function resultado(
        string $variable,
        string $valor,
        string $unidad,
        ?SeveridadClinica $severidad,
        string $codigo,
        ?string $referencia,
        ?string $umbral,
        string $explicacion,
        ?string $recomendacion = null,
    ): ResultadoReglaClinica {
        return new ResultadoReglaClinica(
            $variable, $valor, $unidad, $severidad, $codigo, $referencia, $umbral,
            $explicacion, $recomendacion,
            $severidad === SeveridadClinica::CRITICO
                ? ComportamientoAlerta::AUTOMATICA_AL_CONFIRMAR
                : ComportamientoAlerta::NINGUNA,
        );
    }

    protected function sinRegla(string $variable, string $valor, string $unidad): ResultadoReglaClinica
    {
        return $this->resultado($variable, $valor, $unidad, null, 'SIN_REGLA_APROBADA', null, null,
            'Esta lectura no tiene una clasificación automática aprobada.');
    }

    protected function recomendacionCritica(): string
    {
        return 'Repetir la medición, verificar la técnica y seguir el protocolo institucional.';
    }
}
