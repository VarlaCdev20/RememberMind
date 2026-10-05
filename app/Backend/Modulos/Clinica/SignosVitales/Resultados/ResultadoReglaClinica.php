<?php

namespace App\Backend\Modulos\Clinica\SignosVitales\Resultados;

use App\Backend\Modulos\Clinica\SignosVitales\Tipos\ComportamientoAlerta;
use App\Backend\Modulos\Clinica\SignosVitales\Tipos\SeveridadClinica;

final readonly class ResultadoReglaClinica
{
    public function __construct(
        public string $variable,
        public string $valor,
        public string $unidad,
        public ?SeveridadClinica $severidad,
        public string $codigoRegla,
        public ?string $referenciaUtilizada,
        public ?string $rangoOUmbral,
        public string $explicacion,
        public ?string $recomendacion,
        public ComportamientoAlerta $comportamientoAlerta,
        public string $fuenteEvaluacion = 'REFERENCIA_GENERAL',
    ) {}

    public function toArray(): array
    {
        return [
            'variable' => $this->variable,
            'valor' => $this->valor,
            'unidad' => $this->unidad,
            'severidad' => $this->severidad?->value,
            'codigo_regla' => $this->codigoRegla,
            'referencia_utilizada' => $this->referenciaUtilizada,
            'rango_o_umbral' => $this->rangoOUmbral,
            'explicacion' => $this->explicacion,
            'recomendacion' => $this->recomendacion,
            'comportamiento_alerta' => $this->comportamientoAlerta->value,
            'fuente_evaluacion' => $this->fuenteEvaluacion,
        ];
    }
}
