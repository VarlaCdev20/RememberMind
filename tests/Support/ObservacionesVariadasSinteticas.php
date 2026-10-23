<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Modulos\Cuidados\Dominio\ObservacionCognitiva as Contrato;

/** Ejemplos inventados para ingeniería; no corresponden a Elena ni a otro residente real. */
final class ObservacionesVariadasSinteticas
{
    public const MARCA = 'OBS-20-SINTETICO-V1';

    public static function registros(): array
    {
        $ejemplos = [
            ['2026-09-01 08:10:00', 'Durante conversación breve responde a la consigna y no repite preguntas en este episodio.', ['sigue_instrucciones' => true, 'repite_preguntas' => false]],
            ['2026-09-03 10:25:00', 'Pregunta dos veces dónde comienza la actividad, después de haber recibido la misma indicación.', ['repite_preguntas' => true, 'olvida_indicaciones' => true]],
            ['2026-09-05 14:40:00', 'Interrumpe la tarea cuando hay conversación alrededor; vuelve a ella tras repetir la consigna.', ['atencion' => 'FLUCTUANTE', 'sigue_instrucciones' => true]],
            ['2026-09-08 17:20:00', 'Se consulta el día de la semana, pero no se obtiene una respuesta suficiente para registrar orientación temporal.', []],
            ['2026-09-10 09:15:00', 'Tras una pausa recuerda el paso que estaba realizando; no se observa olvido de la indicación en esta tarea.', ['olvida_indicaciones' => false, 'sigue_instrucciones' => true]],
            ['2026-09-12 11:30:00', 'Hace pausas al nombrar un objeto durante la conversación y completa la frase después de esperar.', ['lenguaje' => 'LIMITADO']],
            ['2026-09-15 15:45:00', 'Solicita nuevamente la instrucción dada minutos antes y continúa con acompañamiento verbal.', ['olvida_indicaciones' => true, 'sigue_instrucciones' => true]],
            ['2026-09-17 18:05:00', 'Identifica a una persona habitual y el espacio de la actividad; estos indicadores se observaron directamente.', ['reconoce_personas' => true, 'reconoce_entorno' => true]],
            ['2026-09-20 08:35:00', 'Repite una pregunta respondida al inicio de la interacción; no se dispone de información sobre otros indicadores.', ['repite_preguntas' => true]],
            ['2026-09-22 10:50:00', 'La actividad se interrumpe porque solicita descansar; no se valoran memoria ni seguimiento de instrucciones.', []],
            ['2026-09-25 14:15:00', 'Durante una secuencia conocida omite un paso y pide que le recuerden qué debía hacer.', ['olvida_indicaciones' => true]],
            ['2026-09-27 17:40:00', 'Conversación comprensible; no se registra repetición de preguntas durante esta interacción.', ['lenguaje' => 'CONSERVADO', 'repite_preguntas' => false]],
            ['2026-09-30 09:05:00', 'Se observa desorganización transitoria durante la actividad y se señala un cambio respecto al seguimiento previo del escenario.', ['confusion' => true, 'cambio_cognitivo' => true]],
            ['2026-10-02 11:20:00', 'La persona participa en conversación breve; no se realiza una valoración de orientación o memoria.', ['lenguaje' => 'CONSERVADO']],
            ['2026-10-05 15:35:00', 'Necesita repetir una indicación de dos pasos; completa el primer paso después de la reiteración.', ['olvida_indicaciones' => true, 'sigue_instrucciones' => true]],
            ['2026-10-08 18:10:00', 'Pregunta nuevamente por el horario ya explicado; se documenta un cambio observado en este episodio.', ['repite_preguntas' => true, 'cambio_cognitivo' => true]],
            ['2026-10-11 08:45:00', 'Reconoce el entorno durante una actividad habitual; el resto de los indicadores no se explora.', ['reconoce_entorno' => true]],
            ['2026-10-14 10:15:00', 'Sigue una instrucción sencilla; no se observa confusión durante este episodio.', ['sigue_instrucciones' => true, 'confusion' => false]],
            ['2026-10-17 14:30:00', 'Reitera una pregunta y solicita que se repita una indicación; se señala el cambio para seguimiento.', ['repite_preguntas' => true, 'olvida_indicaciones' => true, 'cambio_cognitivo' => true]],
            ['2026-10-20 17:55:00', 'La actividad finaliza antes de explorar los indicadores cognitivos; se conserva explícitamente que no fueron observados.', []],
        ];

        return array_map(static fn ($e, $i) => ['alias' => 'Residente ficticio OBS-20', 'sintetico' => true,
            'episodio' => sprintf('OBS-20-%02d', $i + 1), 'fecha_hora' => $e[0],
            'datos' => array_replace(array_fill_keys([...Contrato::TEXTOS, ...Contrato::SENALES], null), $e[2],
                ['observacion' => self::MARCA.' · EJEMPLO INVENTADO PARA PRUEBAS. '.$e[1]])], $ejemplos, array_keys($ejemplos));
    }

    /** Exporta datos de prueba, nunca un archivo de importación al expediente institucional. */
    public static function exportar(string $directorio): void
    {
        if (! is_dir($directorio)) {
            throw new \LogicException('El directorio de evidencia debe existir.');
        }
        $csv = fopen($directorio.'/observaciones-sinteticas.csv', 'wb');
        if ($csv === false) {
            throw new \LogicException('No se pudo crear el CSV sintético.');
        }
        $md = ['# 20 observaciones sintéticas con fechas y situaciones variadas', '',
            '**Datos inventados exclusivamente para probar el software. No corresponden a Elena ni a otro residente real.**', '',
            'Alias: Residente ficticio OBS-20. Sin firma ni autoría atribuida a profesionales reales. Las fechas son simuladas.', '',
            'Este archivo no se incorpora al expediente operativo. La prueba automatizada registra estos datos mediante las Actions existentes en una BDD desechable.', ''];
        try {
            $cabecera = ['alias', 'sintetico', 'episodio', 'fecha_hora', ...Contrato::TEXTOS, ...Contrato::SENALES, 'observacion'];
            fputcsv($csv, $cabecera, ',', '"', '');
            foreach (self::registros() as $r) {
                $fila = array_merge(array_values(array_intersect_key($r, array_flip(['alias', 'sintetico', 'episodio', 'fecha_hora']))), array_values($r['datos']));
                // NULL no se confunde con FALSE al exportar para inspección humana.
                fputcsv($csv, array_map(static fn ($v) => $v === null ? 'NO_OBSERVADO' : (is_bool($v) ? ($v ? 'TRUE' : 'FALSE') : $v), $fila), ',', '"', '');
                $md[] = '## '.substr($r['fecha_hora'], 8, 2).'/'.substr($r['fecha_hora'], 5, 2).'/'.substr($r['fecha_hora'], 0, 4).' · '.substr($r['fecha_hora'], 11, 5).' — Simulado';
                $md[] = '';
                $md[] = $r['datos']['observacion'];
                $md[] = '';
            }
        } finally {
            fclose($csv);
        }
        $md[] = 'Estas observaciones permiten probar hallazgos descriptivos. No son mediciones instrumentales ni acreditan una clasificación de DCL.';
        if (file_put_contents($directorio.'/OBSERVACIONES_SINTETICAS.md', implode(PHP_EOL, $md).PHP_EOL) === false) {
            throw new \LogicException('No se pudo crear la presentación de los ejemplos.');
        }
    }
}
