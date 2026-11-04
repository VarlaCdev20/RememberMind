<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Conservacion;

use DomainException;
use Illuminate\Database\Connection;

/** Preflight técnico de lectura. Devuelve conteos y comparación, nunca filas clínicas. */
final class EvaluarReduccionConservacion
{
    public function evaluar(Connection $db): array
    {
        if ($db->transactionLevel() !== 0 || ! in_array($db->getDriverName(), ['pgsql', 'sqlite'], true)) {
            throw new DomainException('El preflight requiere una transacción propia en PostgreSQL o SQLite.');
        }

        return $db->transaction(function () use ($db): array {
            if ($db->getDriverName() === 'pgsql') {
                $db->statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ, READ ONLY');
                $externas = $db->select("SELECT 1 FROM pg_constraint c
                    JOIN pg_class origen ON origen.oid=c.conrelid JOIN pg_namespace ns ON ns.oid=origen.relnamespace
                    JOIN pg_class destino ON destino.oid=c.confrelid JOIN pg_namespace nd ON nd.oid=destino.relnamespace
                    WHERE c.contype='f' AND nd.nspname='public' AND ns.nspname<>'public' AND destino.relname IN (?,?,?,?) LIMIT 1",
                    [ProyeccionReduccionExperta::COGNITIVAS, ProyeccionReduccionExperta::IADL,
                        ProyeccionReduccionExperta::FUNCIONALIDAD, ProyeccionReduccionExperta::SINTETICOS]);
                if ($externas !== []) {
                    throw new DomainException('Existe una FK entrante desde otro esquema fuera del mapa de reducción.');
                }
            }

            return $this->validarEnTransaccion($db);
        });
    }

    /** Lectura reutilizada por la migración dentro de su propia transacción. */
    public function validarEnTransaccion(Connection $db): array
    {
        $plan = new ProyeccionReduccionExperta;
        $reducida = $db->getSchemaBuilder()->hasColumn(ProyeccionReduccionExperta::HECHOS, 'cognitiva_dominio');
        if (! $reducida) {
            $permitidas = [ProyeccionReduccionExperta::COGNITIVAS => [], ProyeccionReduccionExperta::IADL => [],
                ProyeccionReduccionExperta::SINTETICOS => [], ProyeccionReduccionExperta::FUNCIONALIDAD => [ProyeccionReduccionExperta::IADL, ProyeccionReduccionExperta::CLASIFICACIONES]];
            foreach ($db->getSchemaBuilder()->getTableListing() as $tabla) {
                $nombre = str_contains($tabla, '.') ? substr($tabla, strrpos($tabla, '.') + 1) : $tabla;
                foreach ($db->getSchemaBuilder()->getForeignKeys($tabla) as $fk) {
                    if (isset($permitidas[$fk['foreign_table']]) && (! in_array($nombre, $permitidas[$fk['foreign_table']], true)
                        || $fk['foreign_columns'] !== ['cod_funcionalidad_instantanea'])) {
                        throw new DomainException('La consolidación tiene una FK entrante fuera del mapa previsto.');
                    }
                }
            }
        }
        $tablas = $reducida ? [ProyeccionReduccionExperta::HECHOS, ProyeccionReduccionExperta::CLASIFICACIONES, ProyeccionReduccionExperta::VINCULOS] : ProyeccionReduccionExperta::TABLAS;
        $conteos = [];
        $t = [];
        foreach ($tablas as $tabla) {
            $conteos[$tabla] = $db->table($tabla)->count();
        }
        if (array_sum($conteos) > 100000) {
            throw new DomainException('El preflight en memoria admite hasta 100000 filas; se requiere un cotejo por lotes para este volumen.');
        }
        foreach ($tablas as $tabla) {
            $t[$tabla] = array_map(static fn ($fila) => (array) $fila, $db->table($tabla)->get()->all());
        }
        if ($reducida) {
            $t = $plan->restaurar($t);
            $conteos = array_map(count(...), $t);
        }
        $plan = new ProyeccionReduccionExperta;
        $proyeccion = $plan->proyectar($t);
        $criterios = $db->table('evaluacion_criterios')->whereIn('cod_evaluacion_criterio', array_unique(array_column($proyeccion[ProyeccionReduccionExperta::VINCULOS], 'cod_evaluacion_criterio')))
            ->get(['cod_evaluacion_criterio', 'cod_evaluacion_experta', 'tipo_registro'])->keyBy('cod_evaluacion_criterio');
        $evidencias = $db->table('evidencias_evaluacion')->whereIn('cod_evidencia_evaluacion', array_filter(array_column($proyeccion[ProyeccionReduccionExperta::VINCULOS], 'cod_evidencia_evaluacion')))
            ->get(['cod_evidencia_evaluacion', 'cod_evaluacion_experta'])->keyBy('cod_evidencia_evaluacion');
        $hechos = array_column($t[ProyeccionReduccionExperta::HECHOS], null, 'cod_hecho_instantanea');
        $ordenCognitivo = $ordenIadl = [];
        foreach ($t[ProyeccionReduccionExperta::COGNITIVAS] as $v) {
            $ordenCognitivo[$hechos[$v['cod_hecho_instantanea']]['cod_evaluacion_experta']][] = $v['orden'];
        }
        foreach ($t[ProyeccionReduccionExperta::IADL] as $v) {
            $ordenIadl[$v['cod_funcionalidad_instantanea']][] = $v['orden'];
        }
        $this->ordenesContinuos($ordenCognitivo);
        $this->ordenesContinuos($ordenIadl);
        $porEvidencia = [];
        foreach ($hechos as $h) {
            if ($h['cod_evidencia_evaluacion'] !== null) {
                $porEvidencia[$h['cod_evidencia_evaluacion']][] = $h;
            }
        }
        $ordenes = $destinos = [];
        foreach ($proyeccion[ProyeccionReduccionExperta::VINCULOS] as $v) {
            $criterio = $criterios->get($v['cod_evaluacion_criterio']) ?? throw new DomainException('Soporte con criterio inexistente.');
            if ($v['cod_hecho_instantanea'] !== null) {
                $h = $hechos[$v['cod_hecho_instantanea']];
                if ($h['cod_evaluacion_experta'] !== $criterio->cod_evaluacion_experta || $criterio->tipo_registro === 'D137') {
                    throw new DomainException('Soporte sintético fuera de su evaluación o contrato.');
                }
                $identidad = $h['cod_hecho_instantanea'];
            } else {
                $e = $evidencias->get($v['cod_evidencia_evaluacion']) ?? throw new DomainException('Vínculo sin evidencia original.');
                if ($e->cod_evaluacion_experta !== $criterio->cod_evaluacion_experta) {
                    throw new DomainException('El vínculo cruza evaluaciones.');
                }
                if ($criterio->tipo_registro === 'D137') {
                    continue;
                }
                $coinciden = $porEvidencia[$v['cod_evidencia_evaluacion']] ?? [];
                if (count($coinciden) !== 1 || $coinciden[0]['cod_evaluacion_experta'] !== $criterio->cod_evaluacion_experta) {
                    throw new DomainException('El soporte original no identifica un único hecho de la evaluación.');
                }
                $identidad = $coinciden[0]['cod_hecho_instantanea'];
            }
            if (isset($destinos[$v['cod_evaluacion_criterio']][$identidad])) {
                throw new DomainException('Un criterio repite el mismo hecho por dos vías.');
            }
            if ($v['rol_en_criterio'] !== 'SOPORTE_EXPERIMENTAL' || $v['estado_participacion'] !== 'PRUEBA_TECNICA') {
                throw new DomainException('Rol o participación incompatible con el soporte histórico.');
            }
            $destinos[$v['cod_evaluacion_criterio']][$identidad] = true;
            $ordenes[$v['cod_evaluacion_criterio']][] = $v['orden_conservacion'];
        }
        $this->ordenesContinuos($ordenes);
        $original = $plan->canonizar($t);
        $recuperado = $plan->canonizar($plan->restaurar($proyeccion));
        if ($original !== $recuperado) {
            throw new DomainException('La proyección no reconstruye exactamente las filas anteriores.');
        }

        return ['estado' => $reducida ? 'PASS_RECONSTRUCCION_MODELO_REDUCIDO' : 'PASS_PROYECCION_EN_MEMORIA', 'filas_por_tabla' => $conteos,
            'igualdad_filas' => true, 'huella_filas' => hash('sha256', serialize($original)),
            'tablas_retiradas' => $reducida ? 4 : 0, 'ddl_ejecutado' => false, 'dto_tras_migracion_verificado' => false,
            'recalculo_institucional_completo_verificado' => false];
    }

    private function ordenesContinuos(array $grupos): void
    {
        foreach ($grupos as $orden) {
            foreach ($orden as $valor) {
                if (! is_int($valor) && (! is_string($valor) || ! preg_match('/^(0|[1-9][0-9]*)$/D', $valor))) {
                    throw new DomainException('Orden ausente o de tipo incompatible.');
                }
            }
            $orden = array_map(intval(...), $orden);
            sort($orden, SORT_NUMERIC);
            if ($orden !== range(0, count($orden) - 1)) {
                throw new DomainException('El orden de la colección tiene saltos o duplicados.');
            }
        }
    }
}
