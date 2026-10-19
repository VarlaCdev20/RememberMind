<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

/**
 * Consulta de percentiles publicados TOL-F, GHS alemán, únicamente de 60 a 79 años.
 * No convierte TOL-DX, años escolares bolivianos ni percentiles redondeados en z.
 */
final class NormalizadorReferenciaTolF2019
{
    public const METODO = 'TOL-F-DE-GHS-2019-24';

    public const FUENTE = 'https://doi.org/10.1017/S1355617718001248';

    // Tablas 5 (mujeres) y 6 (varones), pp. 527–528. Clave: sexo-nivel-edad inferior.
    // Cada vector corresponde a aciertos 1–24. No hay fila publicada para cero.
    private const TABLAS = [
        '0-2-60' => ['n' => 313, 'p' => [0, 0, 0, 1, 1, 2, 4, 8, 11, 19, 26, 35, 44, 56, 68, 79, 86, 93, 96, 99, 100, 100, 100, 100]],
        '0-2-65' => ['n' => 312, 'p' => [1, 1, 1, 1, 2, 4, 7, 12, 18, 27, 37, 46, 58, 71, 82, 88, 95, 97, 98, 100, 100, 100, 100, 100]],
        '0-2-70' => ['n' => 357, 'p' => [0, 1, 1, 3, 5, 8, 13, 18, 24, 34, 46, 59, 71, 80, 87, 93, 98, 99, 99, 100, 100, 100, 100, 100]],
        '0-2-75' => ['n' => 247, 'p' => [1, 2, 3, 5, 7, 12, 18, 26, 33, 43, 53, 63, 72, 81, 88, 94, 97, 99, 100, 100, 100, 100, 100, 100]],
        '0-4-60' => ['n' => 251, 'p' => [0, 0, 0, 0, 1, 2, 3, 3, 6, 10, 15, 24, 33, 48, 60, 70, 82, 90, 94, 97, 100, 100, 100, 100]],
        '0-4-65' => ['n' => 216, 'p' => [0, 0, 0, 1, 2, 2, 6, 10, 15, 19, 24, 32, 47, 55, 65, 77, 87, 93, 97, 99, 100, 100, 100, 100]],
        '0-4-70' => ['n' => 175, 'p' => [0, 0, 0, 0, 0, 2, 7, 13, 22, 30, 38, 49, 66, 76, 83, 92, 95, 98, 99, 100, 100, 100, 100, 100]],
        '0-4-75' => ['n' => 140, 'p' => [1, 1, 1, 4, 4, 8, 17, 24, 31, 41, 52, 63, 73, 78, 86, 89, 96, 99, 100, 100, 100, 100, 100, 100]],
        '1-2-60' => ['n' => 398, 'p' => [0, 0, 0, 1, 2, 6, 9, 13, 19, 29, 39, 55, 65, 78, 83, 89, 94, 97, 99, 99, 100, 100, 100, 100]],
        '1-2-65' => ['n' => 395, 'p' => [0, 0, 1, 2, 4, 7, 10, 16, 24, 33, 47, 58, 71, 78, 88, 93, 96, 98, 99, 100, 100, 100, 100, 100]],
        '1-2-70' => ['n' => 413, 'p' => [0, 1, 2, 4, 6, 12, 19, 26, 36, 48, 62, 74, 81, 90, 93, 96, 98, 99, 100, 100, 100, 100, 100, 100]],
        '1-2-75' => ['n' => 254, 'p' => [0, 1, 3, 6, 10, 18, 27, 36, 47, 55, 70, 81, 88, 95, 97, 99, 100, 100, 100, 100, 100, 100, 100, 100]],
        '1-4-60' => ['n' => 123, 'p' => [0, 0, 0, 1, 1, 2, 7, 11, 15, 21, 31, 41, 55, 66, 76, 86, 92, 94, 98, 99, 100, 100, 100, 100]],
        '1-4-65' => ['n' => 104, 'p' => [0, 0, 0, 2, 3, 3, 7, 11, 16, 25, 38, 49, 63, 71, 84, 90, 94, 98, 98, 98, 99, 100, 100, 100]],
        '1-4-70' => ['n' => 83, 'p' => [1, 1, 1, 2, 4, 4, 11, 13, 18, 29, 39, 52, 64, 76, 87, 93, 95, 98, 99, 100, 100, 100, 100, 100]],
        '1-4-75' => ['n' => 50, 'p' => [0, 2, 2, 6, 6, 10, 12, 26, 36, 42, 56, 70, 74, 80, 88, 94, 96, 100, 100, 100, 100, 100, 100, 100]],
    ];

    /** El nivel educativo es el código GHS documentado, no el número de años escolares. */
    public function metadatos(): array
    {
        return ['tipo' => 'NORMALIZACION_REFERENCIA', 'instrumento' => 'TOL-F',
            'identidad' => ['instrumento' => 'TOL-F', 'edicion_protocolo' => 'GHS-2019-TACTIL-24', 'idioma' => 'de'],
            'componentes' => ['EJE-S04'], 'fuente' => self::FUENTE, 'localizador' => 'Tablas 5–6, pp. 527–528',
            'edad_minima' => 60, 'edad_maxima' => 79,
            'entradas' => ['aciertos_minimos_movimientos' => 'ENTERO', 'problemas_del_protocolo' => 'ENTERO',
                'sexo_norma' => 'ENTERO', 'nivel_educativo_ghs' => 'ENTERO',
                'contexto_normativo_alemania_documentado' => 'LOGICO', 'modalidad_tactil' => 'LOGICO',
                'comprension_instrucciones_verificada' => 'LOGICO', 'condiciones_sensoriales_motoras_compatibles' => 'LOGICO',
                'administracion_estandarizada' => 'LOGICO'],
            'revision_profesional' => 'PENDIENTE', 'adopcion_institucional' => 'NO_CONFIGURADA',
            'reproduce_reactivos' => false];
    }

    /** Devuelve el entero publicado; cero y cien son percentiles redondeados, sin inversión gaussiana. */
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
        $sexo = $captura['sexo_norma'] ?? null;
        $nivel = $captura['nivel_educativo_ghs'] ?? null;
        $bruto = $captura['aciertos_minimos_movimientos'] ?? null;
        if (! $this->entero($edad, 60, 79)) {
            $motivos[] = 'EDAD_FUERA_DE_TABLAS_GERIATRICAS';
        }
        if (! $this->entero($sexo, 0, 1)) {
            $motivos[] = 'SEXO_REQUERIDO_POR_TABLA_NO_DOCUMENTADO';
        }
        if (! $this->entero($nivel, 2, 5)) {
            $motivos[] = 'NIVEL_EDUCATIVO_GHS_SIN_REFERENCIA';
        }
        if (! $this->entero($bruto, 0, 24)) {
            $motivos[] = 'ACIERTOS_INVALIDOS';
        } elseif ($bruto === 0) {
            $motivos[] = 'TABLAS_NO_PUBLICAN_FILA_PARA_CERO_ACIERTOS';
        }
        if (($captura['problemas_del_protocolo'] ?? null) !== 24) {
            $motivos[] = 'REQUIERE_PROTOCOLO_TOL_F_DE_24_PROBLEMAS';
        }
        $m = ['medida' => 'TOL_F_PROBLEMAS_RESUELTOS_EN_MINIMO_MOVIMIENTOS',
            'tipo' => 'NORMALIZACION_REFERENCIA', 'estado' => 'NO_NORMALIZABLE', 'puntuacion_bruta' => $bruto,
            'percentil_publicado' => null, 'z' => null, 'de_residual' => null,
            'direccion' => 'MAYOR_ES_MEJOR', 'clasificacion_clinica' => null, 'estrato' => null,
            'advertencias' => ['REFERENCIA_ALEMANA_NO_ADOPTADA_AUTOMATICAMENTE',
                'PERCENTILES_PUBLICADOS_REDONDEADOS', 'NO_ES_DIAGNOSTICO']];
        if ($motivos === []) {
            $inicio = intdiv($edad, 5) * 5;
            $grupo = $nivel <= 3 ? 2 : 4;
            $fila = self::TABLAS[$sexo.'-'.$grupo.'-'.$inicio];
            $m = array_replace($m, ['estado' => 'NORMALIZADO', 'percentil_publicado' => $fila['p'][$bruto - 1],
                'estrato' => ['tabla' => $sexo === 1 ? 5 : 6, 'edad_inferior' => $inicio, 'edad_superior' => $inicio + 4,
                    'nivel_ghs_agrupado' => $grupo === 2 ? '2–3' : '4–5', 'n' => $fila['n']]]);
            if ($fila['n'] < 100) {
                $m['advertencias'][] = 'CELDA_CON_MENOS_DE_CIEN_PARTICIPANTES';
            }
        }
        $m['motivos'] = $motivos;

        return ['metodo' => self::METODO, 'referencia' => self::FUENTE, 'localizador' => $meta['localizador'],
            'bruto' => array_intersect_key($captura, $meta['entradas']), 'contexto_aplicacion' => $captura,
            'componentes' => ['EJE-S04' => $m], 'diagnostico' => null, 'revision_profesional' => 'PENDIENTE',
            'adopcion_institucional' => 'NO_CONFIGURADA',
            'limite' => 'Posición en la tabla alemana de la tarea táctil TOL-F. No interpreta TOL-DX, mayores de 79 años ni establece DCL.'];
    }

    /** Evita asignar una celda a edades, sexo o niveles desconocidos o de tipo incorrecto. */
    private function entero(mixed $valor, int $minimo, int $maximo): bool
    {
        return is_int($valor) && $valor >= $minimo && $valor <= $maximo;
    }
}
