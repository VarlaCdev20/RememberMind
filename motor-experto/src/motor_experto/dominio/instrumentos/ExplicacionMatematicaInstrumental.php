<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

/** Explica la comparación matemática sin cortes clínicos, agregación ni diagnóstico. */
final class ExplicacionMatematicaInstrumental
{
    /** Añade explicación a cada medida sin modificar puntuaciones, estados ni fuentes. */
    public function agregar(array $salida): array
    {
        foreach ($salida['componentes'] as &$componente) {
            if (isset($componente['medidas'])) {
                foreach ($componente['medidas'] as &$medida) {
                    $medida['interpretacion_matematica'] = $this->explicar($medida);
                }
                unset($medida);
            } else {
                $componente['interpretacion_matematica'] = $this->explicar($componente);
            }
        }
        unset($componente);

        return $salida;
    }

    /** El signo expresa posición respecto del predicho, nunca gravedad clínica. */
    private function explicar(array $medida): array
    {
        $base = ['contrato' => 'EXPLICACION-MATEMATICA-1', 'clasificacion_clinica' => null,
            'limite' => 'La comparación instrumental no establece por sí sola una alteración de dominio ni DCL.'];
        if (($medida['estado'] ?? null) === 'CALCULADO_SIN_NORMA') {
            // La explicación depende de la operación real; no atribuye reconocimiento a lenguaje.
            $texto = match ($medida['medida'] ?? null) {
                'DIFERENCIA_ACIERTOS_FALSAS_ALARMAS' => 'Se calculó la diferencia entre aciertos y falsas alarmas. Falta una norma compatible para interpretar este reconocimiento.',
                'WAB_ES_2000_REPETICION_ESCALA_0_10' => 'El total original de repetición se convirtió a la escala de 0 a 10 de esta edición. Esta conversión no determina alteración cognitiva.',
                'WAB_ES_2000_HABLA_ESPONTANEA_TOTAL' => 'Se sumaron contenido de la información y fluidez corregidos en la tarea de habla espontánea. El total conserva ambas puntuaciones y no determina DCL.',
                'FCSRT_PALABRAS_ADICIONALES_CON_CLAVES' => 'Se sumaron los aciertos adicionales con claves de los tres ensayos. Este recuento no incluye los aciertos libres y no establece un déficit de almacenamiento.',
                'FCSRT_PALABRAS_TOTAL_APRENDIZAJE' => 'Se sumaron los aciertos libres y adicionales con claves de los tres ensayos, sin contarlos de nuevo. Falta una comparación normativa adoptada para interpretar este total.',
                'FCSRT_PALABRAS_TOTAL_DIFERIDO' => 'Se sumaron los aciertos libres y adicionales con claves del diferido a treinta minutos. Este total no demuestra por sí solo una alteración de memoria ni DCL.',
                default => 'Se dispone de una puntuación descriptiva, sin comparación normativa compatible ni clasificación clínica.',
            };

            return $base + ['estado' => 'DESCRIPTIVO_SIN_COMPARACION', 'posicion' => null,
                'texto' => $texto];
        }
        if (($medida['estado'] ?? null) !== 'NORMALIZADO') {
            return $base + ['estado' => 'INFORMACION_INSUFICIENTE', 'posicion' => null,
                'texto' => 'No se dispone de una comparación normativa interpretable para esta medida. Revisar los motivos y los datos originales.'];
        }
        if (isset($medida['percentil_intervalo'])) {
            return $base + ['estado' => 'INTERVALO_PERCENTILAR', 'posicion' => null,
                'texto' => 'La interferencia se sitúa mediante los puntos percentilares publicados. Los límites abiertos no se extrapolan; no se asigna una categoría clínica.'];
        }
        $z = $medida['z'] ?? null;
        $direccion = $medida['direccion'] ?? null;
        if ((! is_int($z) && ! is_float($z)) || ! is_finite((float) $z)
            || ! in_array($direccion, ['MAYOR_ES_MEJOR', 'MENOR_ES_MEJOR'], true)) {
            return $base + ['estado' => 'INFORMACION_INSUFICIENTE', 'posicion' => null,
                'texto' => 'Falta una puntuación estandarizada finita o la dirección documentada de esta medida.'];
        }
        $posicion = $z < 0 ? 'INFERIOR_AL_PREDICHO' : ($z > 0 ? 'SUPERIOR_AL_PREDICHO' : 'IGUAL_AL_PREDICHO');
        $texto = $direccion === 'MENOR_ES_MEJOR'
            ? match ($posicion) {
                'INFERIOR_AL_PREDICHO' => 'El tiempo es menor que el predicho por la referencia: ejecución más rápida en esta tarea.',
                'SUPERIOR_AL_PREDICHO' => 'El tiempo es mayor que el predicho por la referencia: ejecución más lenta en esta tarea.',
                default => 'El tiempo coincide con el predicho por la referencia.',
            }
        : match ($posicion) {
            'INFERIOR_AL_PREDICHO' => 'La puntuación es menor que la predicha por la referencia para esta medida.',
            'SUPERIOR_AL_PREDICHO' => 'La puntuación es mayor que la predicha por la referencia para esta medida.',
            default => 'La puntuación coincide con la predicha por la referencia.',
        };

        return $base + ['estado' => 'COMPARACION_CON_PREDICHO', 'posicion' => $posicion, 'texto' => $texto];
    }
}
