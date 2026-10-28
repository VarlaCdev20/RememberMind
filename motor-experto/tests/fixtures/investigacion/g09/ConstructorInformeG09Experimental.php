<?php

declare(strict_types=1);

namespace MotorExperto\investigacion\g09;

use DomainException;

/**
 * Informe legible generado SOLO de trazas sintéticas verificadas G09-EXP.
 * No redacta diagnóstico, clasificación de DCL ni orientación médica.
 */
final class ConstructorInformeG09Experimental
{
    public function construir(array $evaluacion, array $capturas = []): array
    {
        if (($evaluacion['uso'] ?? null) !== 'PRUEBA_TECNICA_NO_CLINICA'
            || ($evaluacion['version'] ?? null) !== CatalogoReglasG09Experimental::VERSION) {
            throw new DomainException('No se genera informe fuera de una prueba G09 aislada.');
        }

        $estado = $evaluacion['estado_tecnico'] ?? '';
        $fuentes = $this->explicarFuentes($evaluacion, $capturas);
        if (isset($evaluacion['inferencia_declarativa'])) {
            $d = $evaluacion['inferencia_declarativa'];
            $positivas = array_filter($d['reglas'], static fn ($r) => $r['estado'] === 'CUMPLE');

            return ['uso' => 'PRUEBA_TECNICA_NO_CLINICA', 'version' => $d['version_conocimiento'],
                'criterio' => $evaluacion['criterio'], 'estado_tecnico' => $estado,
                'titulo' => 'Evaluación de reglas declarativas de laboratorio',
                'interpretacion' => 'Estado calculado: '.$estado.'. Consecuencias: '.implode(', ', array_keys($d['consecuencias'])).'.',
                'como_se_llego' => 'D-130 examinó '.count($d['reglas']).' reglas elegibles; '.count($positivas).' cumplieron sus condiciones positivas. '.$fuentes['texto'],
                'trazas_positivas' => $positivas, 'reglas_examinadas' => $d['reglas'],
                'rutas_explicacion' => $fuentes['rutas'], 'rutas_condiciones_declarativas' => $d['rutas_fuente'],
                'limitaciones' => 'Conocimiento TEST_ONLY; fuentes sintéticas y soportes originales trazados. Sin interpretación clínica.'];
        }
        $trazas = $evaluacion['traza'] ?? [];
        $validas = [];
        foreach ($trazas as $t) {
            if (($t['cumple'] ?? false) !== true) {
                continue;
            }
            if (($t['condiciones']['C1'] ?? false) !== true
                || ($t['condiciones']['C2'] ?? false) !== true
                || ($t['condiciones']['C3'] ?? false) !== true
                || ! is_string($t['evidencia'] ?? null)) {
                throw new DomainException('No es posible explicar una regla sin condiciones y soporte.');
            }
            $regla = CatalogoReglasG09Experimental::obtener($t['regla']);
            if ($regla['criterio'] !== ($evaluacion['criterio'] ?? null)) {
                throw new DomainException('Traza fuera del criterio.');
            }
            $validas[$regla['consecuencia']][] = [
                'regla' => $t['regla'],
                'evidencia' => $t['evidencia'],
                'componente' => $regla['componente'],
                'proceso' => $regla['descripcion'],
                'condiciones' => ['C1', 'C2', 'C3'],
            ];
        }
        ksort($validas);
        $consecuencias = $evaluacion['consecuencias_candidatas'] ?? [];
        ksort($consecuencias);
        if ($estado === 'HALLAZGO_ACOTADO' && (count($validas) !== 1 || array_keys($validas) !== array_keys($consecuencias))) {
            throw new DomainException('Resultado no coincide con los soportes de la traza.');
        }
        $motivos = $evaluacion['motivos_g08'] ?? [];
        if ($estado === 'HALLAZGO_ACOTADO') {
            $ruta = current($validas)[0];
            $titulo = 'Hallazgo experimental en '.$ruta['proceso'];
            $interpretacion = 'Una tarea simulada produjo soporte para el subcomponente '.$ruta['componente'].'. Este resultado está limitado a la prueba técnica.';
            $fundamento = 'La regla '.$ruta['regla'].' se satisfizo porque se verificaron C1 (soporte específico de prueba), C2 (atribución de tarea) y C3 (contexto), con referencia a la evidencia '.$ruta['evidencia'].'.';
        } elseif ($estado === 'EVALUABLE_SIN_SOPORTE') {
            $titulo = 'Tarea experimental interpretable sin soporte positivo';
            $interpretacion = 'La tarea fue admisible y alcanzó evaluabilidad suficiente, pero no se satisfizo una regla positiva de dificultad. No equivale a preservación cognitiva.';
            $fallidas = [];
            foreach ($trazas as $t) {
                if (($t['cumple'] ?? false) !== true) {
                    $faltantes = [];
                    foreach ($t['condiciones'] ?? [] as $codigo => $cumple) {
                        if ($cumple !== true) {
                            $faltantes[] = $codigo;
                        }
                    }
                    $fallidas[] = ($t['regla'] ?? 'REGLA_SIN_ID').': '.implode(', ', $faltantes);
                }
            }
            $fundamento = $fallidas
                ? 'Condiciones positivas no satisfechas: '.implode('; ', $fallidas).'.'
                : 'No hay regla positiva satisfecha con evidencia admisible.';
        } elseif ($estado === 'MULTIPLES_HALLAZGOS_PENDIENTES_DE_RESOLUCION') {
            $titulo = 'Varios hallazgos experimentales sin resolución unitaria';
            $interpretacion = 'Se cumplieron reglas de diferentes subcomponentes; ninguna se prioriza automáticamente.';
            $fundamento = 'Conservar cada ruta positiva por separado y sus evidencias; la política clínica de resolución aún está pendiente.';
        } else {
            $titulo = 'Sin conclusión de componente en la prueba experimental';
            $interpretacion = 'La evidencia de esta ejecución no permite emitir un hallazgo del componente.';
            $fundamento = $motivos
                ? 'Motivos documentados por G08: '.implode('; ', $motivos).'.'
                : 'Revisar la evaluabilidad, las condiciones de las reglas y los soportes registrados.';
        }

        return [
            'uso' => 'PRUEBA_TECNICA_NO_CLINICA',
            'version' => $evaluacion['version'],
            'criterio' => $evaluacion['criterio'],
            'estado_tecnico' => $estado,
            'titulo' => $titulo,
            'interpretacion' => $interpretacion,
            'como_se_llego' => $fundamento.' '.$fuentes['texto'],
            'trazas_positivas' => $validas,
            'rutas_explicacion' => $fuentes['rutas'],
            'limitaciones' => 'Instrumentos G08 sin perfiles clínicos autorizados; datos exclusivamente sintéticos. Sin diagnóstico, severidad, pronóstico ni clasificación de DCL.',
        ];
    }

    /** Une únicamente capturas conservadas con sus condiciones calculadas; no interpreta valores. */
    private function explicarFuentes(array $evaluacion, array $capturas): array
    {
        $rutas = $textos = [];
        foreach ($capturas as $captura) {
            if (($captura['criterio'] ?? null) !== $evaluacion['criterio']
                || ! is_array($captura['perfil_instrumental'] ?? null)
                || ! is_array($captura['captura_original']['aplicacion'] ?? null)) {
                throw new DomainException('Captura ajena o incompleta para la explicación G09.');
            }
            $p = $captura['perfil_instrumental'];
            $original = $captura['captura_original'];
            $app = $original['aplicacion'];
            $trazas = array_values(array_filter($evaluacion['traza'], static fn ($t) => $t['regla'] === $captura['regla'] && in_array($t['evidencia'], $captura['evidencias_generadas'], true)));
            $ruta = ['regla_fuente' => $captura['regla'], 'perfil' => $p['codigo'],
                'instrumento' => $p['instrumento'], 'tarea' => $p['componente'], 'edicion' => $p['version_instrumento'],
                'edicion_original' => $original['instrumento']['version'] ?? null,
                'norma' => $p['norma'], 'idioma' => $p['idioma'], 'poblacion' => $p['poblacion'],
                'fuente' => 'aplicaciones_instrumento', 'registro_original' => $app['cod_aplicacion'],
                'fecha_original' => $app['fecha_hora'], 'autor' => $app['cod_personal'] ?? null,
                'respuestas_originales' => $original['respuestas'], 'evidencias' => $captura['evidencias_generadas'],
                'condiciones_calculadas' => $trazas, 'motivos_no_admision' => $captura['motivos_no_admision']];
            $ruta['controles_tarea'] = $captura['controles_tarea'] ?? null;
            $condiciones = [];
            foreach ($trazas as $traza) {
                foreach ($traza['condiciones'] as $codigo => $valor) {
                    $condiciones[] = $traza['evidencia'].'/'.$codigo.'='.($valor === null ? 'NULL' : ($valor ? 'true' : 'false'));
                }
            }
            $respuestas = [];
            foreach ($original['respuestas'] as $respuesta) {
                $respuestas[] = $respuesta['cod_respuesta'].'/'.$respuesta['cod_pregunta'].'/valor_texto='.var_export($respuesta['valor_texto'], true);
            }
            $textos[] = $ruta['regla_fuente'].': instrumento '.$ruta['instrumento'].', tarea '.$ruta['tarea']
                .', edición del perfil '.$ruta['edicion'].', edición original '.($ruta['edicion_original'] ?? 'NULL').', perfil '.$ruta['perfil'].', norma '.$ruta['norma']
                .', idioma '.$ruta['idioma'].', población '.$ruta['poblacion'].'. Fuente '.$ruta['fuente'].'/'
                .$ruta['registro_original'].' del '.$ruta['fecha_original'].'. Respuestas: '.implode('; ', $respuestas)
                .'. Condiciones: '.($condiciones ? implode('; ', $condiciones) : 'no evaluadas')
                .'. Motivos de exclusión: '.($ruta['motivos_no_admision'] ? implode('; ', $ruta['motivos_no_admision']) : 'ninguno registrado')
                .'. Advertencias de tarea: '.($ruta['controles_tarea'] === null ? 'declaración no disponible'
                    : ($ruta['controles_tarea']['advertencias'] ? implode('; ', $ruta['controles_tarea']['advertencias']) : 'ninguna declarada')).'.';
            $rutas[] = $ruta;
        }

        return ['rutas' => $rutas, 'texto' => implode(' ', $textos)];
    }
}
