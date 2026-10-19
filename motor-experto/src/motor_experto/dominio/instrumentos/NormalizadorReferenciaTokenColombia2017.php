<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

/**
 * Consulta tabulada de Token abreviado: tesis original UNAL de Duarte, 2017.
 * Conserva el nivel de evidencia, las lagunas editoriales y el ajuste tabulado.
 * No reconstruye reactivos ni inventa redondeos, percentiles finales o diagnósticos.
 */
final class NormalizadorReferenciaTokenColombia2017
{
    public const METODO = 'TOKEN-CO-UNAL-2017-TABLAS';

    public const FUENTE = 'https://repositorio.unal.edu.co/handle/unal/59359';

    // Tablas 23–30, pp. 103–106. Pares inclusivos en medios puntos; clave = escalar por edad.
    // Se omite la primera celda ambigua de la tabla 25 y no se rellenan huecos con vecinos.
    private const EDADES = [
        60 => [2 => [0, 24.5], 3 => [25, 26.5], 4 => [27, 27.5], 5 => [28, 28.5], 6 => [29, 30],
            7 => [30.5, 30.5], 8 => [31, 31.5], 9 => [32, 32.5], 10 => [33, 33.5],
            11 => [34, 34], 12 => [34.5, 34.5], 13 => [35, 35], 14 => [35.5, 35.5], 18 => [36, 36]],
        63 => [2 => [0, 25], 3 => [25.5, 26.5], 4 => [27, 27], 5 => [28, 28.5], 6 => [29, 29.5],
            7 => [30, 30.5], 8 => [31, 31.5], 9 => [32, 32.5], 10 => [33, 33.5],
            11 => [34, 34], 12 => [34.5, 34.5], 13 => [35, 35], 14 => [35.5, 35.5], 18 => [36, 36]],
        66 => [3 => [22.5, 24], 4 => [24.5, 25], 5 => [25.5, 27.5], 6 => [28, 29], 7 => [29.5, 30.5],
            8 => [31, 31.5], 9 => [32, 32.5], 10 => [33, 33.5], 12 => [34, 34.5], 14 => [35, 35.5], 18 => [36, 36]],
        69 => [2 => [0, 22], 3 => [22.5, 22.5], 4 => [23, 23.5], 5 => [24, 26], 6 => [26.5, 27.5],
            7 => [28, 29.5], 8 => [30, 30.5], 9 => [31, 31.5], 10 => [32, 32.5], 11 => [33, 33.5],
            12 => [34, 34], 13 => [34.5, 34.5], 14 => [35, 35.5], 18 => [36, 36]],
        72 => [2 => [0, 21.5], 3 => [22, 22], 4 => [22.5, 22.5], 5 => [23, 24], 6 => [24.5, 26],
            7 => [26.5, 28.5], 8 => [29, 30], 9 => [30.5, 31], 10 => [31.5, 32.5],
            11 => [33, 33.5], 13 => [34, 34.5], 14 => [35, 35.5], 18 => [36, 36]],
        75 => [2 => [0, 21.5], 3 => [22, 22], 4 => [22.5, 22.5], 5 => [23, 24], 6 => [24.5, 25.5],
            7 => [26, 27], 8 => [27.5, 29.5], 9 => [30, 30.5], 10 => [31, 32],
            11 => [32.5, 32.5], 12 => [33, 33.5], 13 => [34, 34.5], 15 => [35, 35.5], 18 => [36, 36]],
        78 => [3 => [0, 24], 5 => [24.5, 24.5], 6 => [25, 26], 7 => [26.5, 26.5], 8 => [27, 28.5],
            9 => [29, 30], 10 => [30.5, 31.5], 11 => [32, 32], 12 => [32.5, 33],
            13 => [33.5, 33.5], 14 => [34, 34.5], 15 => [35, 35], 16 => [35.5, 35.5], 18 => [36, 36]],
        81 => [4 => [0, 24], 5 => [24.5, 24.5], 6 => [25, 26], 7 => [26.5, 26.5], 8 => [27, 28],
            9 => [28.5, 29], 10 => [29.5, 30.5], 11 => [31, 31.5], 12 => [32, 32.5],
            13 => [33, 33.5], 14 => [34, 34], 15 => [34.5, 35.5], 18 => [36, 36]],
    ];

    // Tabla 32, p. 108: diferencias de cada columna escolar respecto de NSS(A), 0–18 años.
    // Se consultan celdas; no se deduce si los autores truncaron el ajuste o el resultado.
    private const AJUSTES_ESCOLARES = [2, 2, 2, 2, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, -1, -1, -1, -1];

    /** La tesis es referencia de investigación explícita, no manual oficial ni aprobación institucional. */
    public function metadatos(): array
    {
        return ['tipo' => 'NORMALIZACION_REFERENCIA', 'instrumento' => 'Token-Test',
            'identidad' => ['instrumento' => 'Token-Test', 'edicion_protocolo' => 'NN-CO-2017-DE-RENZI-FAGLIONI-36', 'idioma' => 'es'],
            'componentes' => ['LEN-S03'], 'fuente' => self::FUENTE, 'localizador' => 'Tablas 23–30 y 32, pp. 103–108',
            'nivel_evidencia' => 'TESIS_MAESTRIA_ORIGINAL', 'edad_minima' => 60, 'edad_maxima' => 90,
            'escolaridad_minima' => 0, 'escolaridad_maxima' => 18,
            'entradas' => ['puntaje_total' => 'NUMERO', 'instrucciones_del_protocolo' => 'ENTERO',
                'escolaridad_anios' => 'ENTERO', 'lectoescritura' => 'LOGICO',
                'contexto_normativo_neuronorma_colombia_documentado' => 'LOGICO',
                'audicion_vision_compatibles' => 'LOGICO', 'administracion_estandarizada' => 'LOGICO'],
            'revision_profesional' => 'PENDIENTE', 'adopcion_institucional' => 'NO_CONFIGURADA', 'reproduce_reactivos' => false];
    }

    /** Solo puntajes corregidos originales: las observaciones de cuidados no son respuestas Token. */
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
        $educacion = $captura['escolaridad_anios'] ?? null;
        $bruto = $captura['puntaje_total'] ?? null;
        if (! is_int($edad) || $edad < 60 || $edad > 90) {
            $motivos[] = 'EDAD_FUERA_DE_TABLAS_GERIATRICAS';
        }
        if (! is_int($educacion) || $educacion < 0 || $educacion > 18) {
            $motivos[] = 'ESCOLARIDAD_FUERA_DE_TABLA_O_DESCONOCIDA';
        }
        if ((! is_int($bruto) && ! is_float($bruto)) || ! is_finite((float) $bruto)
            || $bruto < 0 || $bruto > 36 || floor($bruto * 2) !== (float) ($bruto * 2)) {
            $motivos[] = 'PUNTAJE_REQUIERE_MEDIOS_PUNTOS_ENTRE_CERO_Y_36';
        }
        if (($captura['instrucciones_del_protocolo'] ?? null) !== 36) {
            $motivos[] = 'REQUIERE_TOKEN_ABREVIADO_DE_36_INSTRUCCIONES';
        }
        $m = ['medida' => 'TOKEN_ABREVIADO_NSS_EDAD_ESCOLARIDAD', 'tipo' => 'NORMALIZACION_REFERENCIA',
            'estado' => 'NO_NORMALIZABLE', 'puntuacion_bruta' => $bruto, 'escalar_edad' => null,
            'escalar_edad_escolaridad' => null, 'rango_percentil_edad' => null,
            'z' => null, 'percentil' => null, 'direccion' => 'MAYOR_ES_MEJOR', 'clasificacion_clinica' => null,
            'tabla_edad' => null, 'tabla_escolaridad' => 32,
            'advertencias' => ['REFERENCIA_DE_TESIS_NO_ADOPTADA_INSTITUCIONALMENTE',
                'AJUSTE_ESCOLAR_CONSULTADO_SIN_REDONDEO_INFERIDO', 'NO_ES_DIAGNOSTICO']];
        if ($motivos === []) {
            $inicio = $edad >= 81 ? 81 : 60 + intdiv($edad - 60, 3) * 3;
            $m['tabla_edad'] = 23 + intdiv($inicio - 60, 3);
            $coincidencias = array_keys(array_filter(self::EDADES[$inicio], fn ($r) => $bruto >= $r[0] && $bruto <= $r[1]));
            if ($inicio === 66 && $bruto <= 22) {
                $motivos[] = 'CELDA_PUBLICADA_AMBIGUA_TABLA_25';
            } elseif (count($coincidencias) !== 1) {
                $motivos[] = 'BRUTO_SIN_CORRESPONDENCIA_UNIVOCA_PUBLICADA';
            } else {
                $escalar = $coincidencias[0];
                $rangos = [2 => '<1', 3 => '1', 4 => '2', 5 => '3–5', 6 => '6–10', 7 => '11–18',
                    8 => '19–28', 9 => '29–40', 10 => '41–59', 11 => '60–71', 12 => '72–81',
                    13 => '82–89', 14 => '90–94', 15 => '95–97', 16 => '98', 17 => '99', 18 => '>99'];
                $m = array_replace($m, ['estado' => 'NORMALIZADO', 'escalar_edad' => $escalar,
                    'escalar_edad_escolaridad' => $escalar + self::AJUSTES_ESCOLARES[$educacion],
                    'rango_percentil_edad' => $rangos[$escalar]]);
            }
        }
        $m['motivos'] = $motivos;

        return ['metodo' => self::METODO, 'referencia' => self::FUENTE, 'localizador' => $meta['localizador'],
            'nivel_evidencia' => $meta['nivel_evidencia'], 'contexto_aplicacion' => $captura,
            'bruto' => array_intersect_key($captura, $meta['entradas']), 'componentes' => ['LEN-S03' => $m],
            'diagnostico' => null, 'revision_profesional' => 'PENDIENTE', 'adopcion_institucional' => 'NO_CONFIGURADA',
            'limite' => 'Tablas de una tesis colombiana; el rango percentilar corresponde solo al ajuste por edad. No aporta percentil final ni clasificación de dominio o DCL.'];
    }
}
