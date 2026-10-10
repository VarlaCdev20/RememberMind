<?php

namespace App\Backend\Modulos\SistemaExperto\Inferencia;

use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteConocimiento;
use App\Backend\Modulos\SistemaExperto\MemoriaTrabajo\MemoriaTrabajo;
use DomainException;

/** D-130: conjunción de condiciones positivas existenciales, sin prioridad ni puntuación. */
final class EvaluadorReglas
{
    public function evaluar(PaqueteConocimiento $p, MemoriaTrabajo $m, string $criterio): array
    {
        if ($p->version !== $m->contexto->version || $p->red->nodo($criterio)['tipo_nodo'] !== 'CRITERIO') {
            throw new DomainException('Criterio/versión incompatible con la ejecución.');
        }
        $dominio = $p->dominioCriterio($criterio);
        if ($p->tipoReglaResultado === null || $dominio['estado'] !== 'ACTIVO'
            || $p->red->nodo($criterio)['estado'] !== 'ACTIVO'
            || $p->fila('dominios_valores_expertos', $dominio['cod_dominio_valores'])['estado'] !== 'ACTIVO') {
            throw new DomainException('Criterio/dominio o tipo de regla no habilitados para ejecución.');
        }
        foreach ($m->paraCriterio($criterio) as $e) {
            if (! $p->variableParticipa($e->variable, $criterio)) {
                throw new DomainException('Participación de variable sin relación semántica autorizada al criterio.');
            }
        }
        $resultados = [];
        $reglas = $p->tabla('reglas_expertas');
        usort($reglas, fn ($a, $b) => strcmp($a['cod_regla_experta'], $b['cod_regla_experta']));
        foreach ($reglas as $r) {
            if ($r['estado'] !== 'ACTIVO' || $r['tipo_regla'] !== $p->tipoReglaResultado) {
                continue;
            }
            $consecuencias = array_values(array_filter($p->tabla('consecuencias_regla_experta'), fn ($c) => $c['cod_regla_experta'] === $r['cod_regla_experta']
                && $c['cod_criterio_dominio_resultado'] === $dominio['cod_criterio_dominio_resultado']));
            if (! $consecuencias) {
                continue;
            }
            $condiciones = array_values(array_filter($p->tabla('condiciones_regla_experta'), fn ($c) => $c['cod_regla_experta'] === $r['cod_regla_experta']));
            if (! $condiciones || count($consecuencias) !== 1) {
                throw new DomainException('Regla de resultado incompleta o ambigua.');
            }
            $salida = $p->fila('valores_semanticos', $consecuencias[0]['cod_valor_semantico']);
            if ($salida['estado'] !== 'ACTIVO' || $salida['estado_aprobacion'] !== 'APROBADO') {
                throw new DomainException('Consecuencia no autorizada.');
            }
            usort($condiciones, fn ($a, $b) => strcmp($a['cod_condicion_regla'], $b['cod_condicion_regla']));
            $cumple = true;
            $evaluadas = [];
            foreach ($condiciones as $c) {
                $variable = $p->fila('variables_expertas', $c['cod_variable_experta']);
                $valor = $p->fila('valores_semanticos', $c['cod_valor_semantico']);
                if ($variable['estado'] !== 'ACTIVO' || $valor['estado'] !== 'ACTIVO' || $valor['estado_aprobacion'] !== 'APROBADO'
                    || $p->red->nodo($variable['cod_nodo_semantico'])['estado'] !== 'ACTIVO'
                    || $p->fila('dominios_valores_expertos', $valor['cod_dominio_valores'])['estado'] !== 'ACTIVO') {
                    throw new DomainException('Dependencia de condición no autorizada.');
                }
                $soportes = [];
                foreach ($m->paraCriterio($criterio) as $e) {
                    if ($e->utilizable() && $e->variable === $c['cod_variable_experta'] && $e->valor === $c['cod_valor_semantico']) {
                        $soportes[] = $e->id;
                    }
                }
                $estado = $soportes ? 'CUMPLE' : 'NO_CUMPLE';
                $cumple = $cumple && (bool) $soportes;
                $evaluadas[] = ['condicion' => $c['cod_condicion_regla'], 'variable' => $c['cod_variable_experta'],
                    'valor' => $c['cod_valor_semantico'], 'estado' => $estado, 'soportes' => $soportes];
            }
            $resultados[] = ['regla' => $r['cod_regla_experta'], 'estado' => $cumple ? 'CUMPLE' : 'NO_CUMPLE',
                'consecuencia' => $consecuencias[0], 'condiciones' => $evaluadas];
        }

        return $resultados;
    }
}
