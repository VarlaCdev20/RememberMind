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
            return $base + ['estado' => 'DESCRIPTIVO_SIN_COMPARACION', 'posicion' => null,
                'texto' => 'Se calculó la diferencia entre aciertos y falsas alarmas. Falta una norma compatible para interpretar este reconocimiento.'];
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
