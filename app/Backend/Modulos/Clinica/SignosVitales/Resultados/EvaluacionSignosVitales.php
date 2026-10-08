<?php

namespace App\Backend\Modulos\Clinica\SignosVitales\Resultados;

use App\Backend\Modulos\Clinica\SignosVitales\Tipos\SeveridadClinica;

final readonly class EvaluacionSignosVitales
{
    /** @param list<ResultadoReglaClinica> $resultados */
    public function __construct(public array $resultados, public array $contextoHistorico = [], public array $erroresCaptura = []) {}

    public function severidadGlobal(): ?SeveridadClinica
    {
        $evaluados = array_filter($this->resultados, fn (ResultadoReglaClinica $resultado) => $resultado->severidad !== null);

        if ($evaluados === []) {
            return null;
        }

        usort($evaluados, fn (ResultadoReglaClinica $a, ResultadoReglaClinica $b) => $b->severidad->prioridad() <=> $a->severidad->prioridad());

        return $evaluados[0]->severidad;
    }

    public function toArray(): array
    {
        return [
            'severidad_global' => $this->severidadGlobal()?->value,
            'resultados' => array_map(fn (ResultadoReglaClinica $resultado) => $resultado->toArray(), $this->resultados),
            'contexto_historico' => $this->contextoHistorico,
            'errores_captura' => $this->erroresCaptura,
        ];
    }
}
