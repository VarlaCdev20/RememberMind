<?php

namespace App\Backend\Modulos\SistemaExperto\Evaluadores;

use App\Backend\Modulos\SistemaExperto\DTO\ContextoEjecucion;
use App\Backend\Modulos\SistemaExperto\DTO\FuenteBrutaCOGMEM;

final class EvaluadorAdmisibilidad
{
    /** Contrato mínimo D-111..116; no inventa criterios por ausencia de jornada/narrativa. */
    public function evaluar(FuenteBrutaCOGMEM $f, ContextoEjecucion $c): array
    {
        $motivos = [];
        if ($f->residente !== $c->residente) {
            $motivos[] = 'RESIDENTE_INCOMPATIBLE';
        }
        if (! $f->procedenciaVerificada || $f->registro === '' || $f->personal === null || $f->personal === '') {
            $motivos[] = 'PROCEDENCIA_NO_VERIFICADA';
        }
        if ($f->fecha === null || $f->fecha > $c->fechaCorte) {
            $motivos[] = 'FECHA_NO_UTILIZABLE';
        }
        if (! $f->estadoUtilizable) {
            $motivos[] = 'ESTADO_OPERACIONAL_NO_UTILIZABLE';
        }
        if ($motivos) {
            return ['estado' => 'NO_ADMISIBLE', 'motivos' => $motivos];
        }
        $advertencias = $f->contexto['advertencias_tecnicas'] ?? [];

        return ['estado' => $advertencias ? 'ADMISIBLE_CON_ADVERTENCIA' : 'ADMISIBLE', 'motivos' => $advertencias];
    }
}
