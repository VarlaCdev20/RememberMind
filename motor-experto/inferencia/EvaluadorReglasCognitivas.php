<?php

declare(strict_types=1);

namespace MotorExperto\inferencia;

use DomainException;
use MotorExperto\conocimiento\PaqueteConocimiento;
use MotorExperto\conocimiento\PertenenciaVariableCognitiva;
use MotorExperto\hechos\MemoriaTrabajo;

/** Alcance de las conjunciones cognitivas sobre el AND D-130 existente. */
final class EvaluadorReglasCognitivas
{
    public function evaluar(PaqueteConocimiento $p, MemoriaTrabajo $m, string $criterio): array
    {
        $motor = new EvaluadorReglas;
        $reglas = $motor->evaluar($p, $m, $criterio);
        $unidades = [];
        foreach ($m->paraCriterio($criterio) as $e) {
            $unidad = $e->fuente->contexto['unidad_conjuncion'] ?? $e->fuente->contexto['unidad_soporte'] ?? $e->fuente->tabla.':'.$e->fuente->registro;
            if (! is_string($unidad) || trim($unidad) === '') {
                throw new DomainException('La unidad de soporte debe identificar una aplicación/fuente original.');
            }
            $unidades[$unidad][] = $e;
        }
        ksort($unidades, SORT_STRING);
        $porUnidad = [];
        foreach ($unidades as $unidad => $evidencias) {
            $local = new MemoriaTrabajo($m->contexto);
            foreach ($evidencias as $e) {
                $local->incorporar($e);
                $local->vincular($criterio, $e->id, $m->participaciones()[$criterio][$e->id]);
            }
            foreach ($motor->evaluar($p, $local, $criterio) as $r) {
                $porUnidad[$r['regla']][$unidad] = $r;
            }
        }
        foreach ($reglas as &$r) {
            $propietarios = [];
            foreach ($r['condiciones'] as $condicion) {
                if (! $p->variableParticipa($condicion['variable'], $criterio)) {
                    throw new DomainException('Condición sin participación inferencial explícita.');
                }
                $ruta = (new PertenenciaVariableCognitiva)->verificar($p->declaracion()['tablas'], $condicion['variable'], $criterio);
                $propietarios[$ruta['propietario']][] = $condicion['condicion'];
            }
            $r['alcance'] = count($propietarios) > 1 ? 'MULTICOMPONENTE_EXPLICITO' : 'MISMA_UNIDAD';
            $r['evaluaciones_unidad'] = $porUnidad[$r['regla']] ?? [];
            $resueltas = [];
            $r['unidades_por_componente'] = [];
            foreach ($propietarios as $propietario => $ids) {
                $candidatas = [];
                foreach ($r['evaluaciones_unidad'] as $unidad => $evaluacion) {
                    $condiciones = array_values(array_filter($evaluacion['condiciones'], static fn ($c) => in_array($c['condicion'], $ids, true)));
                    $candidatas[$unidad] = ['condiciones' => $condiciones,
                        'estado' => in_array('NO_CUMPLE', array_column($condiciones, 'estado'), true) ? 'NO_CUMPLE' : 'CUMPLE'];
                }
                $cumplen = array_filter($candidatas, static fn ($u) => $u['estado'] === 'CUMPLE');
                $r['unidades_por_componente'][$propietario] = array_keys($cumplen);
                if ($candidatas === []) {
                    foreach ($r['condiciones'] as $c) {
                        if (in_array($c['condicion'], $ids, true)) {
                            $resueltas[$c['condicion']] = $c;
                        }
                    }

                    continue;
                }
                // Una regla multicomponente también exige una conjunción completa por componente/aplicación.
                uasort($candidatas, static fn ($a, $b) => count(array_filter($b['condiciones'], static fn ($c) => $c['estado'] === 'CUMPLE'))
                    <=> count(array_filter($a['condiciones'], static fn ($c) => $c['estado'] === 'CUMPLE')));
                $seleccionadas = $cumplen ?: [reset($candidatas)];
                foreach ($seleccionadas as $u) {
                    foreach ($u['condiciones'] as $c) {
                        $anterior = $resueltas[$c['condicion']]['soportes'] ?? [];
                        $c['soportes'] = array_values(array_unique([...$anterior, ...$c['soportes']]));
                        sort($c['soportes'], SORT_STRING);
                        $resueltas[$c['condicion']] = $c;
                    }
                }
            }
            $r['condiciones'] = array_values($resueltas);
            $r['estado'] = in_array([], $r['unidades_por_componente'], true) ? 'NO_CUMPLE' : 'CUMPLE';
        }
        unset($r);

        return $reglas;
    }
}
