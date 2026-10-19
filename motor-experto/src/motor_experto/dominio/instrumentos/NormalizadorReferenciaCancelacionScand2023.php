<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

/**
 * Eficiencia en cancelación P/W de SCAND: cálculo original y tablas 7–8.
 * Consulta celdas publicadas; no inventa la conversión fuera del soporte observado.
 * La tarea no es Ruff, cancelación A ni una segunda evidencia de atención sostenida.
 */
final class NormalizadorReferenciaCancelacionScand2023
{
    public const METODO = 'LC-PW-ES-SCAND-2023-EFICIENCIA';

    public const FUENTE = 'https://doi.org/10.1080/13854046.2023.2173304';

    // Tabla 8, columnas de eficiencia: niveles escolares 1 (<6), 2 (6–11), 3 (12–15), 4 (>15).
    // Se incorporan solo los años geriátricos desde 60; no se interpolan ni extrapolan.
    private const AJUSTES = [
        60 => [1, 0, -1, -2], 61 => [1, 0, -1, -2], 62 => [1, 0, -1, -2],
        63 => [1, 0, -1, -1], 64 => [1, 0, -1, -1], 65 => [1, 0, 0, -1],
        66 => [1, 1, 0, -1], 67 => [2, 1, 0, -1], 68 => [2, 1, 0, -1],
        69 => [2, 1, 0, -1], 70 => [2, 1, 0, -1], 71 => [2, 1, 0, -1],
        72 => [2, 1, 0, -1], 73 => [2, 1, 0, -1], 74 => [2, 1, 0, 0],
        75 => [2, 1, 1, 0], 76 => [2, 2, 1, 0], 77 => [2, 2, 1, 0],
        78 => [3, 2, 1, 0], 79 => [3, 2, 1, 0], 80 => [3, 2, 1, 0],
        81 => [3, 2, 1, 0], 82 => [3, 2, 1, 0], 83 => [3, 2, 1, 0],
        84 => [3, 2, 1, 1], 85 => [3, 2, 2, 1], 86 => [3, 3, 2, 1],
        87 => [3, 3, 2, 1], 88 => [4, 3, 2, 1], 89 => [4, 3, 2, 1],
        90 => [4, 3, 2, 1], 91 => [4, 3, 2, 1], 92 => [4, 3, 2, 1],
        93 => [4, 3, 2, 1], 94 => [4, 3, 2, 2], 95 => [4, 3, 3, 2],
        96 => [4, 3, 3, 2], 97 => [4, 4, 3, 2], 98 => [5, 4, 3, 2],
    ];

    // Tabla 7: eficiencia mínima, máxima y rango percentilar antes del ajuste demográfico.
    private const ESCALARES = [
        2 => [-42, -31, null], 3 => [-30, -25, '1–2'], 4 => [-24, -15, '2–3'],
        5 => [-14, -6, '4–6'], 6 => [-5, 0, '7–12'], 7 => [1, 3, '13–18'],
        8 => [4, 6, '21–30'], 9 => [7, 9, '34–43'], 10 => [10, 12, '47–56'],
        11 => [13, 14, '63–67'], 12 => [15, 16, '72–77'], 13 => [17, 18, '82–86'],
        14 => [19, 20, '89–92'], 15 => [21, 22, '95–96'], 16 => [23, 23, '98'],
        17 => [24, 24, '99'], 18 => [25, 28, '>99'],
    ];

    /** El nivel escolar conserva la categoría publicada, sin atribuir escolaridad por edad o nombre. */
    public function metadatos(): array
    {
        return ['tipo' => 'NORMALIZACION_REFERENCIA', 'instrumento' => 'Cancelacion-PW',
            'identidad' => ['instrumento' => 'Cancelacion-PW', 'edicion_protocolo' => 'ELES-SCAND-2023-26X30-65', 'idioma' => 'es'],
            'componentes' => ['ATE-S02'], 'fuente' => self::FUENTE, 'localizador' => 'Instrumentos p. 1771 y tablas 7–8 pp. 1778–1779',
            'edad_minima' => 60, 'edad_maxima' => 98,
            'entradas' => ['aciertos' => 'ENTERO', 'omisiones_corregidas' => 'ENTERO', 'comisiones' => 'ENTERO',
                'objetivos_del_protocolo' => 'ENTERO', 'filas_del_protocolo' => 'ENTERO', 'columnas_del_protocolo' => 'ENTERO',
                'segundos' => 'NUMERO', 'nivel_educativo_scand' => 'ENTERO',
                'zona_explorada_cotejada' => 'LOGICO', 'practica_comprendida' => 'LOGICO', 'lectoescritura' => 'LOGICO', 'espanol_nativo' => 'LOGICO',
                'contexto_normativo_espana_documentado' => 'LOGICO', 'condiciones_sensoriales_motoras_compatibles' => 'LOGICO',
                'administracion_estandarizada' => 'LOGICO'],
            'revision_profesional' => 'PENDIENTE', 'adopcion_institucional' => 'NO_CONFIGURADA', 'reproduce_reactivos' => false];
    }

    /** Aciertos menos omisiones y comisiones; negativos se conservan y no pasan por el extractor como brutos negativos. */
    public function normalizar(array $captura): array
    {
        $meta = $this->metadatos();
        $motivos = [];
        foreach ($meta['identidad'] + ['protocolo' => self::METODO] as $campo => $valor) {
            if (($captura[$campo] ?? null) !== $valor) {
                $motivos[] = 'IDENTIDAD_NO_VERIFICADA:'.$campo;
            }
        }
        foreach ($meta['entradas'] as $campo => $tipo) {
            if ($tipo === 'LOGICO' && ($captura[$campo] ?? null) !== true) {
                $motivos[] = 'CONTEXTO_NO_ACREDITADO:'.$campo;
            }
        }
        $edad = $captura['edad_anios'] ?? null;
        $nivel = $captura['nivel_educativo_scand'] ?? null;
        if (! $this->entero($edad, 60, 98)) {
            $motivos[] = 'EDAD_FUERA_DE_TABLAS_GERIATRICAS';
        }
        if (! $this->entero($nivel, 1, 4)) {
            $motivos[] = 'NIVEL_EDUCATIVO_SCAND_NO_DOCUMENTADO';
        }
        foreach (['objetivos_del_protocolo' => 65, 'filas_del_protocolo' => 26, 'columnas_del_protocolo' => 30] as $campo => $valor) {
            if (($captura[$campo] ?? null) !== $valor) {
                $motivos[] = 'FORMA_DISTINTA:'.$campo;
            }
        }
        $segundos = $captura['segundos'] ?? null;
        if ((! is_int($segundos) && ! is_float($segundos)) || ! is_finite((float) $segundos) || (float) $segundos !== 60.0) {
            $motivos[] = 'REQUIERE_SESENTA_SEGUNDOS';
        }
        $a = $captura['aciertos'] ?? null;
        $o = $captura['omisiones_corregidas'] ?? null;
        $c = $captura['comisiones'] ?? null;
        $conteos = $this->entero($a, 0, 65) && $this->entero($o, 0, 65) && $this->entero($c, 0, 715);
        if (! $conteos) {
            $motivos[] = 'CONTEOS_ORIGINALES_INVALIDOS';
        } elseif ($a + $o > 65) {
            $motivos[] = 'ACIERTOS_Y_OMISIONES_EXCEDEN_OBJETIVOS';
        }
        $bruto = $conteos ? $a - ($o + $c) : null;
        $m = ['medida' => 'LC_PW_EFICIENCIA', 'tipo' => 'NORMALIZACION_REFERENCIA', 'estado' => 'NO_NORMALIZABLE',
            'motivos' => [], 'puntuacion_bruta' => $bruto, 'escalar_base' => null,
            'ajuste_demografico' => null, 'escalar_ajustado' => null, 'rango_percentil_sin_ajuste' => null,
            'z' => null, 'percentil' => null, 'direccion' => 'MAYOR_ES_MEJOR', 'clasificacion_clinica' => null,
            'advertencias' => ['REFERENCIA_ESPANOLA_NO_ADOPTADA_AUTOMATICAMENTE',
                'NO_EXTRAPOLAR_FUERA_DE_BRUTOS_PUBLICADOS', 'NO_ES_DIAGNOSTICO']];
        if ($motivos === []) {
            $filas = array_keys(array_filter(self::ESCALARES, fn ($r) => $bruto >= $r[0] && $bruto <= $r[1]));
            if (count($filas) !== 1) {
                $motivos[] = 'EFICIENCIA_FUERA_DEL_SOPORTE_TABULADO';
            } else {
                $base = $filas[0];
                $ajuste = self::AJUSTES[$edad][$nivel - 1];
                $m = array_replace($m, ['estado' => 'NORMALIZADO', 'escalar_base' => $base,
                    'ajuste_demografico' => $ajuste, 'escalar_ajustado' => $base + $ajuste,
                    'rango_percentil_sin_ajuste' => self::ESCALARES[$base][2]]);
                if ($base === 2) {
                    $m['advertencias'][] = 'RANGO_PERCENTILAR_DE_PRIMERA_FILA_NO_ASIGNADO';
                }
            }
        }
        $m['motivos'] = $motivos;

        return ['metodo' => self::METODO, 'referencia' => self::FUENTE, 'localizador' => $meta['localizador'],
            'bruto' => array_intersect_key($captura, $meta['entradas']), 'contexto_aplicacion' => $captura,
            'componentes' => ['ATE-S02' => $m], 'diagnostico' => null, 'revision_profesional' => 'PENDIENTE',
            'adopcion_institucional' => 'NO_CONFIGURADA',
            'limite' => 'Escalar de eficiencia y ajuste etario/escolar de la tabla SCAND. Sin percentil final, clasificación de atención, dominio o DCL.'];
    }

    /** Los recuentos son enteros documentados; no se convierten cadenas, ausencia o booleanos. */
    private function entero(mixed $valor, int $minimo, int $maximo): bool
    {
        return is_int($valor) && $valor >= $minimo && $valor <= $maximo;
    }
}
