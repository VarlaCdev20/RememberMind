<?php

declare(strict_types=1);

namespace MotorExperto\aplicacion\casos_uso;

use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\dominio\inferencia\EvaluadorEvaluabilidadCOGMEM;
use MotorExperto\dominio\inferencia\MemoriaTrabajo;
use MotorExperto\dominio\inferencia\ResolvedorSoportesCOGMEM;

/** Orquesta A–D desde hechos originales mapeados, sin reglas de laboratorio ni resultados precargados. */
final class EvaluarPerfilInstitucional
{
    public function ejecutar(PaqueteConocimiento $p, MemoriaTrabajo $m): array
    {
        // Verifica todos los originales antes de aplicar la compuerta local de cada tarea.
        $salida = (new EvaluarConocimientoCognitivo)->ejecutar($p, $m);
        $observaciones = $contexto = $aplicacionesInstrumentales = [];
        foreach ($m->todas() as $e) {
            $v = $p->fila('variables_expertas', $e->variable);
            $nodo = $p->red->nodo($v['cod_nodo_semantico']);
            $aplicacion = $e->fuente->contexto['unidad_conjuncion'] ?? $e->fuente->registro;
            if ($nodo['codigo_semantico'] === 'VAR-MEM-INST-01' && $e->utilizable()) {
                $aplicacionesInstrumentales[$aplicacion] = true;
            }
            if (str_starts_with($nodo['codigo_semantico'], 'VAR-CTX-MEM-')) {
                $contexto[$aplicacion][$nodo['codigo_semantico']][] = $e->utilizable()
                    && $p->fila('valores_semanticos', $e->valor)['codigo_valor'] === 'ACREDITADA';
            }
            if (! str_starts_with($nodo['codigo_semantico'], 'VAR-OBS-')) {
                continue;
            }
            $componente = $p->red->nodo($v['cod_nodo_propietario_primario'])['codigo_semantico'];
            $observaciones[$componente][] = ['evidencia' => $e->id, 'variable' => $v['cod_variable_experta'],
                'componente' => $componente, 'dominio' => 'COG-'.substr($componente, 0, 3), 'aplicacion' => $e->fuente->registro,
                'pregunta' => $e->fuente->contexto['cod_pregunta'] ?? null, 'respuesta' => $e->fuente->contexto['cod_respuesta'] ?? null,
                'opcion' => $e->fuente->contexto['opcion_original']['cod_opcion'] ?? null, 'literal_original' => $e->fuente->valorOriginal,
                'codigo' => $e->utilizable() ? $p->fila('valores_semanticos', $e->valor)['codigo_valor'] : null,
                'representacion' => $e->representacion, 'admisibilidad' => $e->admisibilidad, 'motivos' => $e->motivos,
                'autor' => $e->fuente->personal, 'fecha' => $e->fuente->fecha?->format('Y-m-d\TH:i:s.uP'),
                'atencion' => $e->fuente->atencion, 'reglas_instrumentales_acreditadas' => []];
        }
        $validas = [];
        foreach (array_keys($aplicacionesInstrumentales) as $aplicacion) {
            $validas[$aplicacion] = true;
            foreach (['COMPONENTE', 'CONDICIONES', 'CONFUSORES'] as $codigo) {
                $hechos = $contexto[$aplicacion]['VAR-CTX-MEM-'.$codigo] ?? [];
                $validas[$aplicacion] = $validas[$aplicacion] && $hechos !== [] && ! in_array(false, $hechos, true);
            }
        }
        $evaluables = new MemoriaTrabajo($m->contexto);
        $descartadas = [];
        foreach ($m->todas() as $e) {
            $v = $p->fila('variables_expertas', $e->variable);
            $codigo = $p->red->nodo($v['cod_nodo_semantico'])['codigo_semantico'];
            $aplicacion = $e->fuente->contexto['unidad_conjuncion'] ?? $e->fuente->registro;
            if ($codigo === 'VAR-MEM-INST-01' && $e->utilizable() && ! ($validas[$aplicacion] ?? false)) {
                $descartadas[] = ['evidencia' => $e->id, 'aplicacion' => $e->fuente->registro, 'motivo' => 'CONTEXTO_DE_TAREA_INSUFICIENTE'];

                continue;
            }
            $evaluables->incorporar($e);
            foreach ($m->participaciones() as $criterio => $participaciones) {
                if (isset($participaciones[$e->id])) {
                    $evaluables->vincular($criterio, $e->id, $participaciones[$e->id]);
                }
            }
        }
        foreach ($m->relaciones() as $r) {
            $idsDescartados = array_column($descartadas, 'evidencia');
            if (! in_array($r['origen'], $idsDescartados, true) && ! in_array($r['destino'], $idsDescartados, true)) {
                $evaluables->relacionar($r['origen'], $r['destino'], $r['tipo'], $r['justificacion']);
            }
        }
        if ($descartadas !== []) {
            $salida['perfil']['COG-MEM'] = (new EvaluarConocimientoCognitivo)->ejecutar($p, $evaluables)['perfil']['COG-MEM'];
        }
        $memoria = &$salida['perfil']['COG-MEM'];
        $hayValidas = in_array(true, $validas, true);
        $ctx = ['componente_validado' => $hayValidas, 'condiciones_aplicacion_conocidas' => $hayValidas, 'confusores_revisados' => $hayValidas];
        $compuerta = (new EvaluadorEvaluabilidadCOGMEM)->evaluar($p, $evaluables, $memoria['criterio'], $ctx);
        $hayObservacionMemoria = array_filter($observaciones, static fn ($filas, $codigo) => str_starts_with($codigo, 'MEM-')
            && count(array_filter($filas, static fn ($f) => $f['codigo'] !== null)) > 0, ARRAY_FILTER_USE_BOTH) !== [];
        if ($compuerta['estado'] === 'EV-CM-0' && ($hayObservacionMemoria || $aplicacionesInstrumentales !== [])) {
            $compuerta['estado'] = 'EV-CM-1';
        }
        $memoria['compuerta_institucional'] = $compuerta;
        $memoria['antecedentes_descartados_por_contexto'] = $descartadas;
        $memoria['resultado_institucional'] = null;
        if ($compuerta['habilita_resultado'] && $memoria['contradicciones'] === [] && $memoria['conclusiones_candidatas'] !== []) {
            $memoria['resultado_institucional'] = (new ResolvedorSoportesCOGMEM)->resolver($p, $evaluables, $memoria['criterio'], $compuerta, $memoria['traza']['reglas']);
        }
        unset($memoria);
        foreach ($salida['perfil'] as $codigo => &$dominio) {
            foreach ($dominio['traza']['reglas'] as &$regla) {
                $regla['codigo_regla'] = $p->fila('reglas_expertas', $regla['regla'])['codigo_regla'];
                foreach ($regla['condiciones'] as &$condicion) {
                    $variable = $p->fila('variables_expertas', $condicion['variable']);
                    $condicion['variable_codigo'] = $p->red->nodo($variable['cod_nodo_semantico'])['codigo_semantico'];
                    $condicion['valor_codigo'] = $p->fila('valores_semanticos', $condicion['valor'])['codigo_valor'];
                }
                unset($condicion);
            }
            unset($regla);
            $dominio['estado_institucional'] = $dominio['contradicciones'] !== [] ? 'EVIDENCIA_CONTRADICTORIA'
                : ($dominio['conclusiones_candidatas'] === [] ? 'SIN_ANTECEDENTES_INSTRUMENTALES_SUFICIENTES' : 'HALLAZGOS_CANDIDATOS_CON_SOPORTE');
            if ($codigo !== 'COG-MEM') {
                $dominio['resultado_institucional'] = null;
            } elseif (! $dominio['compuerta_institucional']['habilita_resultado']) {
                $dominio['estado_institucional'] = 'SIN_ANTECEDENTES_INSTRUMENTALES_SUFICIENTES';
            }
        }
        unset($dominio);
        $salida['contrato_institucional'] = 'ORION-AD-20261019';
        $salida['observaciones_por_componente'] = $observaciones;

        return $salida;
    }
}
