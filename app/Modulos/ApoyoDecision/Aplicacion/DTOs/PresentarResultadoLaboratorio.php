<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

/** Traduce salidas existentes. No infiere hallazgos ni resuelve contradicciones. */
final class PresentarResultadoLaboratorio
{
    public static function texto(mixed $valor): string
    {
        if ($valor === null) {
            return 'No disponible';
        }
        if (is_bool($valor)) {
            return $valor ? 'Sí' : 'No';
        }
        if (str_starts_with((string) $valor, 'SIN_SOPORTE_')) {
            return 'No hay evidencia de soporte para '.substr((string) $valor, 12);
        }

        return [
            'CUMPLE' => 'Cumplida', 'NO_CUMPLE' => 'No cumplida', 'NO_EVALUADA' => 'No evaluada',
            'FINALIZADA' => 'Finalizada', 'ADMISIBLE' => 'Admitida', 'ADMISIBLE_CON_ADVERTENCIA' => 'Admitida con advertencia',
            'EV-CM-0' => 'No interpretable', 'EV-CM-1' => 'Información insuficiente', 'EV-CM-2' => 'Evaluable',
            'DIFICULTAD_EVIDENCIADA' => 'Dificultad específica evidenciada',
            'SIN_DIFICULTAD_EVIDENCIADA' => 'Sin dificultad acreditada en la evidencia examinada',
            'SIN_RESULTADO' => 'Sin resultado descriptivo', 'EVIDENCIA_CONTRADICTORIA' => 'Evidencias contradictorias',
            'MULTIPLES_HALLAZGOS_PENDIENTES_DE_RESOLUCION' => 'Múltiples hallazgos pendientes de resolución',
            'INFORMACION_INSUFICIENTE' => 'Información insuficiente', 'NO_INTERPRETABLE' => 'No interpretable',
            'A' => 'Alteración objetiva declarada en la simulación', 'P' => 'Evaluación simulada válida sin alteración objetiva',
            'U' => 'Desconocido o sin soporte suficiente', 'I' => 'No interpretable', 'X' => 'Contradictorio',
            'S' => 'Satisfecho en la simulación', 'B' => 'Bloqueado', 'N' => 'No satisfecho',
            'TAREA_NO_SOLICITADA' => 'Tarea no solicitada', 'CONTRATO_DE_TAREA_PENDIENTE' => 'Contrato de tarea pendiente',
            'CONTRADICCION_DOCUMENTADA' => 'Contradicción documentada', 'FUENTE_NO_ADMISIBLE' => 'Fuente no admisible',
            'COMPUERTA_NO_HABILITA' => 'La evaluabilidad no habilita la emisión',
            'CONDICION_POSITIVA_NO_CUMPLIDA' => 'Condición positiva no cumplida', 'CONDICION_DESCONOCIDA' => 'Condición desconocida',
            'REQUISITO_O_DOMINIO_NO_INTERPRETABLE' => 'Uno o más requisitos o dominios no tienen información interpretable suficiente',
            'SOLAPAMIENTO_SOPORTES' => 'Los soportes se solapan y requieren revisión',
            'CONTRADICCION_NO_ADJUDICADA' => 'Hay evidencias contradictorias pendientes de revisión',
            'DCL_C04_DIFERENCIAL_NO_RESUELTO' => 'La evaluación diferencial está pendiente de resolución',
            'DCL_C02_INCOHERENTE_CON_DOMINIOS' => 'El requisito de alteración objetiva y los estados de dominio son incompatibles',
            'CRITERIO_GENERAL_NO_ACREDITADO' => 'No se acreditó al menos un requisito general',
            'DCL_C02_SIN_SOPORTE_OBJETIVO' => 'El requisito de alteración objetiva carece de soporte',
            'PATRON_PETERSEN_SIMULADO' => 'Patrón candidato sustentado únicamente por referencias sintéticas',
            'REQUISITOS_NO_RESUELTOS' => 'Quedan requisitos pendientes de resolución',
        ][(string) $valor] ?? (string) $valor;
    }

    public function construir(array $ejecucion): array
    {
        $s = $ejecucion['salida'];
        $r = $s['resultado'];
        $dominios = [];
        foreach ($s['dominios'] as $codigo => $d) {
            $memoria = $codigo === 'COG-MEM';
            $evaluabilidad = $d['evaluabilidad'];
            $informe = $d['informe'];
            $reglas = $memoria ? $r['reglas_examinadas']['memoria'] : $d['reglas'];
            $admitidas = $memoria ? $r['evidencias_admitidas']['memoria']
                : $r['perfil_cinco_dominios'][$codigo]['salida']['resultado']['evidencias_admitidas'];
            $excluidas = $memoria ? $r['evidencias_excluidas']['memoria']
                : $r['perfil_cinco_dominios'][$codigo]['salida']['resultado']['excluidas'];
            $dominios[$codigo] = array_intersect_key($d, array_flip(['nombre', 'evaluabilidad', 'resultado', 'referencia', 'subcomponentes'])) + [
                'evaluabilidad_texto' => $memoria ? self::texto($evaluabilidad)
                    : (str_ends_with($evaluabilidad, '-2') ? 'Evaluable' : (str_ends_with($evaluabilidad, '-1') ? 'Información insuficiente' : 'No interpretable')),
                'resultado_texto' => $informe['titulo'] ?? self::texto($d['resultado']),
                'interpretacion' => $informe['interpretacion'] ?? 'El resultado descriptivo de memoria corresponde únicamente a las fuentes examinadas; no acredita sus cuatro subcomponentes ni determina el estado Petersen.',
                'admitidas' => count($admitidas), 'excluidas' => count($excluidas),
                'reglas_detalle' => $this->seguro($reglas),
                'reglas_con_soporte' => count(array_filter($reglas, static fn ($g) => $g['estado'] === 'CUMPLE')),
                'confusores' => $this->seguro($r['confusores'][$codigo]),
                'traza' => $this->seguro($r['traza_tecnica'][$memoria ? 'memoria' : 'g09'][$memoria ? 'rutas' : $codigo] ?? []),
            ];
        }
        $evidencias = [];
        foreach ($r['hechos_instrumentales'] as $h) {
            $evidencias[] = ['id' => $h['id'], 'origen' => $h['fuente'].' / '.$h['registro_original'],
                'fecha' => $h['fecha_original'], 'componente' => $h['perfil_mapeo'], 'rol' => $h['rol'],
                'estado' => $h['admitida'] ? 'Admitida' : 'Excluida', 'mapeo' => $h['valor_semantico'] ?? 'Sin mapeo admitido',
                'dominio' => $h['criterio'], 'motivos' => $h['motivos'], 'detalle' => $this->seguro($h)];
        }
        foreach ($r['fuentes_examinadas']['memoria'] as $h) {
            $evidencias[] = ['id' => $h['evidencia'], 'origen' => $h['fuente']['tabla'].' / '.$h['fuente']['registro'],
                'fecha' => $h['fuente']['fecha'], 'componente' => 'Memoria: componente integrado', 'rol' => 'Evidencia sintética de memoria',
                'estado' => self::texto($h['admisibilidad']), 'mapeo' => $h['valor_semantico'] ?? 'Sin mapeo admitido',
                'dominio' => 'COG-MEM', 'motivos' => $h['motivos'], 'detalle' => $this->seguro($h)];
        }
        $reglas = $r['reglas_examinadas']['g09'];
        foreach ($r['reglas_examinadas']['memoria'] as $regla) {
            $reglas[$regla['regla']] = $regla + ['dominio' => 'COG-MEM', 'version' => $r['manifiesto_versiones']['COG-MEM']];
        }

        return ['ejecucion' => $ejecucion['id'], 'fecha' => $ejecucion['fecha'], 'escenario' => $s['escenario'],
            'caso' => $r['caso'], 'evaluacion' => $r['evaluacion'], 'corte' => $r['fecha_corte'],
            'estado' => self::texto($r['estado_tecnico']), 'versiones' => $r['manifiesto_versiones'],
            'titulo' => $r['resumen']['petersen']['titulo'], 'interpretacion' => strtr($r['resumen']['petersen']['interpretacion'], [
                'memoria es A' => 'memoria cuenta con alteración objetiva declarada en la simulación',
                'memoria es P' => 'memoria dispone de evaluación simulada válida sin alteración objetiva',
                'con A' => 'con alteración objetiva declarada', 'se declararon S' => 'se declararon satisfechos',
            ] + array_combine($r['petersen']['motivos'], array_map(self::texto(...), $r['petersen']['motivos']))),
            'dominios' => $dominios, 'evidencias' => $evidencias, 'reglas' => $this->seguro($reglas),
            'petersen' => $r['petersen'], 'informe_petersen' => $r['resumen']['petersen'], 'requisitos' => $s['requisitos'],
            'soportes_petersen' => $this->seguro($r['fuentes_examinadas']['referencias_dcl'] ?? []),
            'limitaciones' => $r['limitaciones'], 'contradicciones' => $r['contradicciones'],
            'faltantes' => $this->seguro($r['evidencias_faltantes']), 'exclusiones' => $this->seguro($r['evidencias_excluidas']),
            'longitudinal' => $this->seguro($r['comparaciones_longitudinales']),
        ];
    }

    /** No envía estímulos/respuestas originales a Blade ni al navegador. */
    private function seguro(array $datos): array
    {
        $salida = [];
        foreach ($datos as $clave => $valor) {
            if (in_array($clave, ['valores_originales', 'valor_original', 'valor_tipado', 'valor_texto', 'respuestas_originales', 'respuestas', 'captura_original', 'pregunta', 'cod_pregunta'], true)) {
                continue;
            }
            $salida[$clave] = is_array($valor) ? $this->seguro($valor) : $valor;
        }

        return $salida;
    }
}
