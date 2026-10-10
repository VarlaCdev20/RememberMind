<?php

namespace App\Backend\Modulos\SistemaExperto\Evaluadores;

/** CTX-MEM-01: comprobaciones explícitas documentadas, sin número mínimo de registros. */
final class EvaluadorContextoCOGMEM
{
    public function evaluar(array $contexto): array
    {
        $faltantes = [];
        foreach (['componente_validado', 'condiciones_aplicacion_conocidas', 'confusores_revisados'] as $requisito) {
            if (($contexto[$requisito] ?? false) !== true) {
                $faltantes[] = $requisito;
            }
        }

        return ['suficiente' => ! $faltantes, 'faltantes' => $faltantes,
            'invalidacion_necesaria' => ($contexto['invalidacion_necesaria'] ?? false) === true,
            'modificador' => ($contexto['limitacion_no_invalidante'] ?? false) === true ? 'MOD-CM-1' : null,
            'basal_disponible' => ($contexto['basal_disponible'] ?? false) === true];
    }
}
