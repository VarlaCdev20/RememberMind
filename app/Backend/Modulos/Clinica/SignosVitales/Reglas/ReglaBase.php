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
            'La medición de '.$valor.' '.$unidad.' no tiene una clasificación automática aprobada aplicable. El sistema no puede marcarla como normal ni crítica con las reglas disponibles.');
    }

    protected function recomendacionCritica(): string
    {
        return 'Comprueba la técnica utilizada y repite la medición según el protocolo institucional. Registra la lectura confirmada y sigue el protocolo de atención del centro.';
    }
}
