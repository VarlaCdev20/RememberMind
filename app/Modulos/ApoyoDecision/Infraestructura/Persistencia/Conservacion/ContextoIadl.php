<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion;

use DomainException;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

/** La evaluación conserva una sola funcionalidad; un hecho sin IADL no la proyecta. */
final class ContextoIadl
{
    public static function consulta(Connection $db): Builder
    {
        $origen = $db->table('hechos_instantanea_experta as h')
            ->join('evaluaciones_expertas as e', 'e.cod_evaluacion_experta', '=', 'h.cod_evaluacion_experta')
            ->select('h.*')->selectRaw('CASE WHEN h.cod_evidencia_iadl_instantanea IS NOT NULL THEN e.cod_funcionalidad_instantanea END AS iadl_cod_funcionalidad_instantanea');

        return $db->query()->fromSub($origen, 'hechos_instantanea_experta');
    }

    public static function preparar(Connection $db, array $datos): array
    {
        if (isset($datos['iadl_cod_funcionalidad_instantanea'])) {
            $canonica = $db->table('evaluaciones_expertas')->where('cod_evaluacion_experta', $datos['cod_evaluacion_experta'] ?? null)
                ->value('cod_funcionalidad_instantanea');
            if (! isset($datos['cod_evidencia_iadl_instantanea']) || $canonica !== $datos['iadl_cod_funcionalidad_instantanea']) {
                throw new DomainException('La caracterización IADL no corresponde a la funcionalidad de su evaluación.');
            }
        }
        unset($datos['iadl_cod_funcionalidad_instantanea']);

        return $datos;
    }
}
