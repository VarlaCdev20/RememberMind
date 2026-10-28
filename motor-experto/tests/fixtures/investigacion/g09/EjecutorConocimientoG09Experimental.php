<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\g09;

use DateTimeImmutable;
use DomainException;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorReglasCognitivas;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\objetos_valor\ContextoEjecucion;
use MotorExperto\dominio\objetos_valor\EvidenciaExperta;
use MotorExperto\dominio\objetos_valor\FuenteBrutaCOGMEM;

/** D-130 es la autoridad de reglas declarativas; G09 aporta solo sus hechos/compuerta de prueba. */
final class EjecutorConocimientoG09Experimental
{
    public function evaluar(PaqueteConocimiento $p, array $base, array $resultadoFuente): array
    {
        if (! $p->soloPruebasTecnicas || ($base['uso'] ?? null) !== 'TEST_ONLY' || ($base['aislado'] ?? null) !== true) {
            throw new DomainException('El conocimiento declarativo G09 es exclusivamente TEST_ONLY.');
        }
        (new EvaluadorG09Experimental)->validarResultadoFuente($base, $resultadoFuente);
        $codigoCriterio = $resultadoFuente['criterio'];
        $criterios = array_values(array_filter($p->tabla('nodos_semanticos'),
            static fn ($n) => $n['codigo_semantico'] === $codigoCriterio && $n['tipo_nodo'] === 'CRITERIO'));
        if (count($criterios) !== 1) {
            throw new DomainException('El conocimiento exige un criterio G09 único.');
        }
        $criterio = $criterios[0]['cod_nodo_semantico'];
        $variables = [];
        foreach ($p->tabla('variables_expertas') as $v) {
            $variables[$p->red->nodo($v['cod_nodo_semantico'])['codigo_semantico']] = $v['cod_variable_experta'];
        }
        $valores = array_values(array_filter($p->tabla('valores_semanticos'), static fn ($v) => $v['codigo_valor'] === 'SIMULADO_TRUE'));
        if (count($valores) !== 1) {
            throw new DomainException('Falta el literal técnico positivo único de G09.');
        }
        $corte = $base['fecha_corte'].(strlen($base['fecha_corte']) === 10 ? 'T23:59:59-04:00' : '-04:00');
        $c = new ContextoEjecucion($base['evaluacion'], $base['residente_ficticio'], $p->version, new DateTimeImmutable($corte));
        $m = new MemoriaTrabajo($c);
        $originales = [];
        foreach ($resultadoFuente['traza'] as $traza) {
            foreach ($traza['condiciones'] as $condicion => $valor) {
                if ($valor !== true) {
                    continue;
                }
                $codigoVariable = 'VAR-'.$traza['regla'].'-'.$condicion;
                $variable = $variables[$codigoVariable] ?? throw new DomainException('La revisión no conserva una variable fuente G09.');
                $id = 'SIM-D-'.substr(hash('sha256', serialize([$traza['evidencia'], $condicion])), 0, 14);
                $fuente = new FuenteBrutaCOGMEM('SIM-G08', 'aplicaciones_instrumento', $condicion, $traza['evidencia'],
                    $c->residente, new DateTimeImmutable($traza['fecha_fuente'].'-04:00'), true, true, null, true, true,
                    contexto: ['unidad_soporte' => $traza['evidencia']]);
                $m->incorporar(new EvidenciaExperta($id, $c, $fuente, 'SIM-MAP-'.$codigoVariable, null,
                    $variable, $valores[0]['cod_valor_semantico'], 'MAPEADO', 'ADMISIBLE', []));
                $m->vincular($criterio, $id, 'INSTRUMENTAL_TEST_ONLY');
                $originales[$id] = ['evidencia_original' => $traza['evidencia'], 'condicion_fuente' => $condicion,
                    'regla_fuente' => $traza['regla'], 'episodio_original' => $traza['episodio_fuente']];
            }
        }
        $reglas = str_ends_with($resultadoFuente['evaluabilidad'], '-2') ? (new EvaluadorReglasCognitivas)->evaluar($p, $m, $criterio) : [];
        $consecuencias = $detalles = $gruposConsecuencia = [];
        foreach ($reglas as $regla) {
            $definicion = $p->fila('reglas_expertas', $regla['regla']);
            $valorResultado = $p->fila('valores_semanticos', $regla['consecuencia']['cod_valor_semantico']);
            $codigo = $definicion['codigo_regla'] ?? $regla['regla'];
            $soportes = $grupos = [];
            foreach ($regla['condiciones'] as $condicion) {
                foreach ($condicion['soportes'] as $id) {
                    $origen = $originales[$id];
                    $soportes[] = $origen['evidencia_original'];
                    // Corroboraciones de una misma tarea/episodio no son soportes independientes.
                    // Se conservan todas las rutas de condición y todos los originales.
                    $clave = $origen['episodio_original'].'|'.$origen['regla_fuente'];
                    $grupos[$clave][$origen['evidencia_original']] = true;
                }
            }
            $soportes = array_values(array_unique($soportes));
            ksort($grupos);
            $efectivos = [];
            foreach ($grupos as $clave => $ids) {
                $ids = array_keys($ids);
                sort($ids);
                $grupos[$clave] = $ids;
                $efectivos[] = $ids[0];
                if ($regla['estado'] === 'CUMPLE') {
                    foreach ($ids as $id) {
                        $gruposConsecuencia[$valorResultado['codigo_valor']][$clave][$id] = true;
                    }
                }
            }
            $detalles[$codigo] = ['id_regla' => $regla['regla'], 'codigo' => $codigo, 'estado' => $regla['estado'],
                'nombre' => $definicion['nombre'] ?? $codigo, 'fundamento' => $definicion['observacion'] ?? null,
                'consecuencia' => $valorResultado['codigo_valor'], 'condiciones' => $regla['condiciones'],
                'soportes_originales' => $soportes, 'soportes_efectivos' => $efectivos,
                'unidad_soporte' => 'EPISODIO_Y_TAREA', 'grupos_soporte' => $grupos];
            $detalles[$codigo]['alcance'] = $regla['alcance'];
            $detalles[$codigo]['evaluaciones_unidad'] = $regla['evaluaciones_unidad'];
        }
        foreach ($gruposConsecuencia as $consecuencia => $grupos) {
            ksort($grupos);
            foreach ($grupos as $ids) {
                $ids = array_keys($ids);
                sort($ids);
                $consecuencias[$consecuencia][] = $ids[0];
            }
        }
        ksort($consecuencias);
        if ($resultadoFuente['estado_tecnico'] === 'EVIDENCIA_CONTRADICTORIA') {
            $consecuencias = [];
        }
        $estado = $resultadoFuente['estado_tecnico'];
        if (str_ends_with($resultadoFuente['evaluabilidad'], '-2') && $estado !== 'EVIDENCIA_CONTRADICTORIA') {
            $estado = match (count($consecuencias)) {
                0 => 'EVALUABLE_SIN_SOPORTE', 1 => 'HALLAZGO_ACOTADO', default => 'MULTIPLES_HALLAZGOS_PENDIENTES_DE_RESOLUCION',
            };
        }

        return ['uso' => 'TEST_ONLY', 'version_conocimiento' => $p->version, 'estado_tecnico' => $estado,
            'consecuencias' => $consecuencias, 'reglas' => $detalles, 'rutas_fuente' => $originales];
    }
}
