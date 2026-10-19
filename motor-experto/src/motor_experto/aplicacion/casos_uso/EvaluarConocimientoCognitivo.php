<?php

declare(strict_types=1);

namespace MotorExperto\aplicacion\casos_uso;

use DomainException;
use MotorExperto\dominio\conocimiento\AdmisibilidadFuenteCOGMEM;
use MotorExperto\dominio\conocimiento\CompiladorPerfilCognitivo;
use MotorExperto\dominio\conocimiento\MapeadorSemantico;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\conocimiento\reglas\EvaluadorReglasCognitivas;
use MotorExperto\dominio\explicacion\ConstructorTraza;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;

/**
 * Ejecución declarativa pura sobre hechos originales y un paquete completo de cinco dominios.
 * No crea fuentes, deduce A/P, agrega puntuaciones ni persiste evaluaciones.
 * La aplicación institucional debe autorizar el acceso y proporcionar su versión y extracción verificadas.
 * Las conclusiones son las consecuencias positivas exactas del conocimiento, no una interpretación clínica nueva.
 */
final class EvaluarConocimientoCognitivo
{
    public function ejecutar(PaqueteConocimiento $p, MemoriaTrabajo $m): array
    {
        if ($p->version !== $m->contexto->version) {
            throw new DomainException('El conocimiento y la memoria deben pertenecer a la misma revisión.');
        }
        $red = (new CompiladorPerfilCognitivo)->compilar($p);
        $memoria = $red['dominios']['COG-MEM'];
        $instrumentalesMemoria = array_fill_keys(array_column($m->paraCriterio($memoria), 'id'), true);
        foreach ($m->todas() as $e) {
            $original = (new MapeadorSemantico)->mapear($p, $m->contexto, $e->fuente, $e->mapeoVariable);
            if (isset($instrumentalesMemoria[$e->id])) {
                $original = (new AdmisibilidadFuenteCOGMEM)->validar($p, $original);
            }
            if (serialize($original) !== serialize($e)) {
                throw new DomainException('Un hecho no coincide con su fuente original y el mapeo de la revisión.');
            }
        }
        $perfil = [];
        foreach ($red['dominios'] as $codigo => $criterio) {
            $reglas = (new EvaluadorReglasCognitivas)->evaluar($p, $m, $criterio);
            $contradicciones = $valores = [];
            foreach ($m->paraCriterio($criterio) as $e) {
                if (! $e->utilizable()) {
                    continue;
                }
                $unidad = $m->soportesEfectivos([$e->id])[0];
                $valores[$unidad][$e->variable][$e->valor][] = $e->id;
            }
            foreach ($valores as $unidad => $variables) {
                foreach ($variables as $variable => $alternativas) {
                    if (count($alternativas) > 1) {
                        $contradicciones[] = ['unidad' => $unidad, 'variable' => $variable, 'valores_y_evidencias' => $alternativas];
                    }
                }
            }
            $conclusiones = [];
            if ($contradicciones === []) {
                foreach ($reglas as $regla) {
                    if ($regla['estado'] !== 'CUMPLE') {
                        continue;
                    }
                    $valor = $regla['consecuencia']['cod_valor_semantico'];
                    $ids = array_values(array_unique(array_merge(...array_column($regla['condiciones'], 'soportes'))));
                    $conclusiones[$valor]['codigo'] = $p->fila('valores_semanticos', $valor)['codigo_valor'];
                    $conclusiones[$valor]['reglas'][] = $regla['regla'];
                    $conclusiones[$valor]['evidencias'] = array_values(array_unique([...($conclusiones[$valor]['evidencias'] ?? []), ...$ids]));
                }
            }
            foreach ($conclusiones as &$conclusion) {
                sort($conclusion['reglas'], SORT_STRING);
                sort($conclusion['evidencias'], SORT_STRING);
                $conclusion['soportes_efectivos'] = $m->soportesEfectivos($conclusion['evidencias']);
            }
            unset($conclusion);
            ksort($conclusiones, SORT_STRING);
            $perfil[$codigo] = ['criterio' => $criterio, 'conclusiones_candidatas' => $conclusiones,
                'contradicciones' => $contradicciones,
                'traza' => (new ConstructorTraza)->construir($p, $m, $criterio,
                    ['alcance' => 'CONJUNCIONES_DECLARATIVAS', 'sin_interpretacion_A_P' => true], $reglas, null)];
        }

        return ['version' => $p->version, 'evaluacion' => $m->contexto->evaluacion, 'residente' => $m->contexto->residente,
            'fecha_corte' => $m->contexto->fechaCorte->format(DATE_ATOM), 'red' => $red, 'perfil' => $perfil,
            'alcance' => 'CONSECUENCIAS_DECLARATIVAS', 'clasificacion_dcl' => null, 'persistido' => false];
    }
}
