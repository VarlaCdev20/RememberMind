<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Aplicacion\DTOs;

<<<<<<< HEAD
use MotorExperto\dominio\conocimiento\reglas\EvaluadorObservaciones;

=======
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
/** Contrato de lectura: no normaliza, interpreta ni reconstruye inferencias. */
final class ConsultaCognitivaEnfermeria
{
    public static function texto(string $texto): string
    {
        return str_replace(['O.R.I.O.N.', 'RememberMind'], ['el sistema', 'el centro'], $texto);
    }

    public function presentar(array $datos, array $informe, ?array $perfil): array
    {
        $actual = $informe['actual'];
        $principal = $informe['areas']['COG-MEM'] ?? $actual;
        $sin = $datos['evaluacion'] === null;
        $integridad = ! empty($datos['perfil']['COG-MEM']['integridad'] ?? $datos['detalle']['integridad'] ?? []);
<<<<<<< HEAD
        $titulo = $integridad ? 'No se puede mostrar el resultado' : ($sin ? 'Sin evaluación clínica conservada' : self::texto($informe['narrativa']['conclusion']));
        $resumen = $integridad ? 'La evaluación presenta información inconsistente. No se puede mostrar una conclusión verificada.'
            : ($sin ? 'Todavía no hay un resultado clínico publicable para este residente. Debajo puede consultar los hallazgos del seguimiento cognitivo y los controles originales disponibles.' : self::texto($informe['narrativa']['resumen']));
=======
        $titulo = $integridad ? 'No se puede mostrar el resultado' : ($sin ? 'Sin conclusión experta disponible' : self::texto($informe['narrativa']['conclusion']));
        $resumen = $integridad ? 'La evaluación presenta información inconsistente. No se puede mostrar una conclusión verificada.'
            : ($sin ? 'Todavía no se dispone de una conclusión experta para este residente. Puedes consultar los controles cognitivos registrados.' : self::texto($informe['narrativa']['resumen']));
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
        if (! $sin && ! $integridad && str_contains(mb_strtolower($principal['estado']), 'insuficiente')) {
            $titulo = 'Información insuficiente para concluir';
            $resumen = 'La información disponible no permite determinar un resultado para el área seleccionada.';
        }
        $componentes = [];
        $aplicaciones = array_column($perfil['capturas_originales']['capturas'] ?? [], null, 'cod_aplicacion');
        foreach ($perfil['dominios'] ?? [] as $codigo => $dominio) {
            foreach ($dominio['componentes'] as $id => $c) {
                $componentes[$codigo][] = ['id' => $id, 'nombre' => $c['nombre'], 'estado' => $c['estado_medicion'] === 'MEDICIONES_DISPONIBLES' ? 'Mediciones disponibles' : 'Sin medición normalizada',
                    'definicion' => $c['concepto']['definicion'] ?? 'No disponible', 'limite' => $c['concepto']['limites'] ?? 'No hay una interpretación clínica integrada para este componente.',
                    'observaciones' => array_map(fn ($o) => ['fecha' => $o['fecha'], 'autor' => $o['autor'], 'texto' => $o['codigo'] === 'DIFICULTAD_DOCUMENTADA' ? 'Dificultad documentada' : 'Sin dificultad documentada en ese registro'], $c['observaciones'] ?? []),
                    'mediciones' => array_map(fn ($m) => ['fecha' => $m['fecha'], 'metodo' => $m['metodo'], 'referencia' => $m['referencia'],
                        'instrumento' => $aplicaciones[$m['aplicacion']]['nombre_instrumento'] ?? 'Instrumento no disponible',
                        'version' => $aplicaciones[$m['aplicacion']]['version_instrumento'] ?? 'Versión no disponible',
                        'autor' => $aplicaciones[$m['aplicacion']]['autor'] ?? 'Autor no disponible',
                        'valor_bruto' => $m['resultado']['puntuacion_bruta'] ?? null,
                        'valor' => $m['resultado']['z'] ?? null, 'unidad' => 'Puntuación z', 'estado' => $m['resultado']['estado']], $c['mediciones'])];
            }
        }

        return ['titulo' => $titulo, 'resumen' => $resumen, 'integridad' => $integridad, 'sin_evaluacion' => $sin,
            'componentes' => $componentes, 'areas' => $informe['areas'], 'actual' => $actual,
            'area_principal' => isset($datos['simulacion_local']) ? 'Perfil cognitivo de demostración' : LenguajeResultadosExperto::CRITERIOS['COG-MEM']['nombre'],
            'estado_principal' => isset($datos['simulacion_local']) ? 'Resultado conservado' : $principal['estado']];
    }

<<<<<<< HEAD
    /** Expone la lectura observacional con su alcance; no la convierte en resultado DCL. */
    public function presentarObservaciones(?array $lectura): ?array
    {
        if ($lectura === null) {
            return null;
        }

        $guardada = $lectura['persistido'] === true;
        $registros = $lectura['registros'];
        $total = $registros->total();
        $visibles = $registros->count();
        $unidad = $total === 1 ? 'control' : 'controles';
        $items = [];
        foreach ($lectura['resumen'] as $codigo => $regla) {
            $items[] = [
                'nombre' => $regla['nombre'] ?? EvaluadorObservaciones::REGLAS[$codigo]['nombre'],
                'estado' => match ($regla['estado']) {
                    'CUMPLE' => 'Observado en los controles revisados',
                    'NO_CUMPLE' => 'No señalado en los controles revisados',
                    default => 'Sin información suficiente',
                },
                'hallazgo' => $regla['hallazgo'],
            ];
        }

        return [
            'titulo' => $total === 0 ? 'Todavía no hay controles cognitivos para analizar' : 'Hallazgos del seguimiento cognitivo',
            'guardada' => $guardada,
            'fecha' => $guardada ? $lectura['fecha'] : $lectura['fecha_corte'],
            'alcance' => $guardada
                ? "Evaluación observacional guardada con {$total} {$unidad}."
                : "Consulta actual de {$visibles} de {$total} {$unidad}; esta lectura no está guardada como evaluación histórica.",
            'items' => $items,
            'limite' => $lectura['limite'],
        ];
    }

=======
>>>>>>> origin/feature/apoyo-decision-informe-neurocognitivo
    /** Solo agrupa medidas ya normalizadas de idéntica procedencia instrumental y referencia. */
    public function series(?array $capturas, string $area, array $componentes = []): array
    {
        $series = [];
        foreach ($capturas['capturas'] ?? [] as $a) {
            $e = $a['extraccion'];
            if (! $e || $a['motivos'] !== []) {
                continue;
            }
            foreach ($e['normalizacion']['componentes'] as $componente => $resultado) {
                if (! str_starts_with($componente, substr($area, 4).'-')) {
                    continue;
                }
                foreach ($resultado['medidas'] ?? [$resultado] as $medida) {
                    $z = $medida['z'] ?? null;
                    if (($medida['estado'] ?? '') !== 'NORMALIZADO' || ! is_numeric($z) || ! is_finite((float) $z)) {
                        continue;
                    }
                    $referencia = $e['normalizacion']['referencia'];
                    $id = hash('sha256', json_encode([$a['cod_instrumento'], $a['version_instrumento'], $e['perfil'], $e['metodo'], $referencia, $componente, $medida['medida'] ?? $componente], JSON_THROW_ON_ERROR));
                    $nombreComponente = $componentes[$componente]['nombre'] ?? LenguajeResultadosExperto::CRITERIOS[$area]['nombre'];
                    $series[$id] ??= ['id' => $id, 'nombre' => $a['nombre_instrumento'].' · '.$nombreComponente, 'instrumento' => $a['nombre_instrumento'],
                        'version' => $a['version_instrumento'], 'metodo' => $e['metodo'], 'referencia' => is_string($referencia) ? $referencia : json_encode($referencia, JSON_UNESCAPED_UNICODE),
                        'unidad' => 'Puntuación z', 'puntos' => []];
                    $series[$id]['puntos'][] = ['id' => hash('sha256', $a['cod_aplicacion'].$id), 'fecha' => $a['fecha_hora'], 'valor' => (float) $z,
                        'autor' => $a['autor'], 'instrumento' => $a['nombre_instrumento'], 'version' => $a['version_instrumento'],
                        'referencia' => $series[$id]['referencia'], 'unidad' => 'Puntuación z', 'interpretacion' => 'Medición instrumental normalizada; no equivale a un diagnóstico.',
                        'fuente' => 'Aplicación instrumental actual', 'resultado_bruto' => $medida['puntuacion_bruta'] ?? null];
                }
            }
        }
        foreach ($series as &$serie) {
            usort($serie['puntos'], fn ($a, $b) => $a['fecha'] <=> $b['fecha']);
        }

        return array_values($series);
    }
}
