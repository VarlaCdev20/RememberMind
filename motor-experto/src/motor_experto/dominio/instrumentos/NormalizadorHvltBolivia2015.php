<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Normalización instrumental.
 *
 * Modelo boliviano publicado por Arango-Lasprilla et al. (2015), tablas 2 y 4.
 * DOI 10.3233/NRE-151286. Solo aprendizaje total y recuerdo libre, HVLT-R forma 5.
 * No contiene reactivos, cortes diagnósticos, inferencias de DCL ni firmas profesionales.
 */
final class NormalizadorHvltBolivia2015
{
    public const METODO = 'HVLT-R-BO-2015-F5';

    public const FUENTE = 'https://doi.org/10.3233/NRE-151286';

    /**
     * El llamador aporta mediciones y contexto documentados; nunca se deducen de una etiqueta nominal.
     * NULL o contexto no acreditado conserva el bruto y enumera las causas de no normalización.
     */
    public function normalizar(array $captura): array
    {
        $contexto = [];
        foreach (['instrumento', 'forma', 'idioma', 'protocolo', 'edad_anios', 'escolaridad_anios',
            'espanol_nativo', 'nacimiento_bolivia', 'residencia_bolivia', 'lectoescritura', 'administracion_estandarizada'] as $campo) {
            $contexto[$campo] = $captura[$campo] ?? null;
        }
        $motivos = [];
        foreach (['instrumento' => 'HVLT-R', 'forma' => 5, 'idioma' => 'es', 'protocolo' => self::METODO] as $campo => $esperado) {
            if (($captura[$campo] ?? null) !== $esperado) {
                $motivos[] = 'IDENTIDAD_NO_VERIFICADA:'.$campo;
            }
        }
        foreach (['espanol_nativo', 'nacimiento_bolivia', 'residencia_bolivia', 'lectoescritura', 'administracion_estandarizada'] as $campo) {
            if (($captura[$campo] ?? null) !== true) {
                $motivos[] = 'APLICABILIDAD_NO_ACREDITADA:'.$campo;
            }
        }
        $edad = $captura['edad_anios'] ?? null;
        $escolaridad = $captura['escolaridad_anios'] ?? null;
        if (! is_int($edad) || $edad < 18 || $edad > 95) {
            $motivos[] = 'EDAD_FUERA_DE_MUESTRA_O_DESCONOCIDA';
        }
        if (! is_int($escolaridad) || $escolaridad < 1) {
            $motivos[] = 'ESCOLARIDAD_FUERA_DE_MUESTRA_O_DESCONOCIDA';
        }
        $bruto = ['ensayos' => $captura['ensayos'] ?? null, 'recuerdo_diferido' => $captura['recuerdo_diferido'] ?? null,
            'demora_minutos' => $captura['demora_minutos'] ?? null];
        $ensayos = $captura['ensayos'] ?? null;
        $ensayosValidos = is_array($ensayos) && array_is_list($ensayos) && count($ensayos) === 3
            && count(array_filter($ensayos, fn ($n) => is_int($n) && $n >= 0 && $n <= 12)) === 3;
        $libre = $captura['recuerdo_diferido'] ?? null;
        $demora = $captura['demora_minutos'] ?? null;
        $libreValido = is_int($libre) && $libre >= 0 && $libre <= 12;
        $demoraValida = (is_int($demora) || is_float($demora)) && is_finite((float) $demora) && $demora >= 20 && $demora <= 25;
        $componentes = [];
        foreach (['MEM-S01', 'MEM-S02'] as $codigo) {
            $faltantes = $motivos;
            if ($codigo === 'MEM-S01' && ! $ensayosValidos) {
                $faltantes[] = 'REQUIERE_TRES_ENSAYOS_CON_ACIERTOS_DE_0_A_12';
            }
            if ($codigo === 'MEM-S02' && (! $libreValido || ! $demoraValida)) {
                $faltantes[] = ! $libreValido ? 'RECUERDO_DIFERIDO_NO_MEDIDO_O_INVALIDO' : 'DEMORA_NO_ACREDITADA_ENTRE_20_Y_25_MINUTOS';
            }
            $resultado = ['estado' => 'NO_NORMALIZABLE', 'motivos' => $faltantes, 'puntuacion_bruta' => null,
                'esperado' => null, 'z' => null, 'clasificacion_clinica' => null];
            if ($faltantes === []) {
                $puntuacion = $codigo === 'MEM-S01' ? array_sum($ensayos) : $libre;
                $esperado = $codigo === 'MEM-S01' ? 24.553 - 0.112 * $edad + 3.264 * ($escolaridad > 12 ? 1 : 0)
                    : 9.702 - 0.064 * $edad;
                $deResidual = $codigo === 'MEM-S01' ? 4.213 : 2.424;
                $resultado = array_replace($resultado, ['estado' => 'NORMALIZADO', 'puntuacion_bruta' => $puntuacion,
                    'esperado' => $esperado, 'de_residual' => $deResidual, 'z' => ($puntuacion - $esperado) / $deResidual]);
            }
            $componentes[$codigo] = $resultado;
        }

        return ['metodo' => self::METODO, 'referencia' => self::FUENTE, 'bruto' => $bruto, 'contexto_aplicacion' => $contexto, 'componentes' => $componentes,
            'revision_profesional' => 'PENDIENTE', 'diagnostico' => null,
            'limite' => 'La normalización no determina por sí sola alteración del dominio ni DCL. No cubre claves o reconocimiento.'];
    }
}
