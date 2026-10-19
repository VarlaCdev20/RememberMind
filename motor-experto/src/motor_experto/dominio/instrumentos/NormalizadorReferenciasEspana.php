<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

use DomainException;

/**
 * Regresiones españolas reconstruidas desde artículos originales y cotejo de autores.
 * Son referencias poblacionales explícitas, sin adopción clínica automática en Bolivia.
 * No incluyen umbrales, diagnósticos, estímulos ni la fórmula FCSRT-Del conflictiva.
 */
final class NormalizadorReferenciasEspana
{
    public const DSB = 'DSB-ES-SABIEX-2021-LONGITUD';

    public const LN = 'LN-ES-SABIEX-2021-LONGITUD';

    public const SPAN = 'SPAN-ES-SABIEX-2021-LONGITUDES';

    public const JLO = 'JLO-ES-SABIEX-2021-30';

    public const FCSRT = 'FCSRT-ES-SABIEX-2021-APRENDIZAJE';

    public const IMAGENES = 'PFCSRT-ES-2025-IMAGENES-A-LIBRE';

    /** Las condiciones describen el alcance de referencia; no exigen que el evaluado esté sano. */
    public function metodos(): array
    {
        $span = 'https://doi.org/10.3390/ijerph18199958';
        $memoria = 'https://doi.org/10.3390/ijerph182412977';
        $aprendizaje = ['libre_1' => 'ENTERO', 'adicional_claves_1' => 'ENTERO',
            'libre_2' => 'ENTERO', 'adicional_claves_2' => 'ENTERO',
            'libre_3' => 'ENTERO', 'adicional_claves_3' => 'ENTERO', 'codificacion_verificada' => 'LOGICO'];
        $definiciones = [
            self::DSB => ['WAIS-III', 'WAIS-III-ES-LONGITUD-INVERSA', 'EJE-S03', $span,
                '§2.3.1, tabla 4; calculadora de autores C9/E9', ['longitud_inversa' => 'ENTERO']],
            self::LN => ['WAIS-III', 'WAIS-III-ES-LONGITUD-LETRAS-NUMEROS', 'EJE-S03', $span,
                '§2.3.2, tabla 4; calculadora de autores C10/E10', ['longitud_letras_numeros' => 'ENTERO']],
            self::SPAN => ['WAIS-III', 'WAIS-III-ES-LONGITUDES-DSB-LN', 'EJE-S03', $span,
                '§2.3.1–2, tabla 4; calculadora C9/E9 y C10/E10',
                ['longitud_inversa' => 'ENTERO', 'longitud_letras_numeros' => 'ENTERO']],
            self::JLO => ['JLO', 'JLO-30-NEURONORMA', 'VIS-S01', $memoria,
                '§2.2.3, tabla 2; calculadora de autores C23/E23', ['aciertos' => 'ENTERO', 'elementos_puntuados' => 'ENTERO', 'sexo_norma' => 'ENTERO']],
            self::FCSRT => ['FCSRT', 'NEURONORMA-2009-PALABRAS', 'MEM-S03', $memoria,
                '§2.2.1, tabla 2 FCSRT-Imm; calculadora de autores C15/E15', $aprendizaje + ['sexo_norma' => 'ENTERO']],
            self::IMAGENES => ['pFCSRT+IR', 'AEC-IMAGENES-IR-A', 'MEM-S01', 'https://doi.org/10.1177/13872877251376906',
                'Página 433, tablas 4 y 5; recuerdo libre de aprendizaje',
                ['libre_1' => 'ENTERO', 'libre_2' => 'ENTERO', 'libre_3' => 'ENTERO',
                    'codificacion_verificada' => 'LOGICO', 'sexo_norma' => 'ENTERO']],
        ];
        $metodos = [];
        foreach ($definiciones as $id => [$instrumento, $edicion, $componente, $fuente, $localizador, $entradas]) {
            $imagenes = $id === self::IMAGENES;
            $contexto = ['escolaridad_anios' => 'ENTERO', 'contexto_normativo_espana_documentado' => 'LOGICO',
                'condiciones_sensoriales_compatibles' => 'LOGICO', 'administracion_estandarizada' => 'LOGICO'];
            if (! $imagenes) {
                $contexto += ['espanol_nativo' => 'LOGICO', 'nacimiento_crianza_espana' => 'LOGICO',
                    'actividad_cognitiva_universitaria' => 'LOGICO'];
            }
            $metodos[$id] = ['tipo' => 'NORMALIZACION_REFERENCIA', 'instrumento' => $instrumento,
                'identidad' => ['instrumento' => $instrumento, 'edicion_protocolo' => $edicion, 'idioma' => 'es'],
                'componentes' => [$componente], 'fuente' => $fuente, 'localizador' => $localizador,
                'edad_minima' => $imagenes ? 50 : 55, 'edad_maxima' => $imagenes ? 88 : 87,
                'escolaridad_minima' => $imagenes ? 0 : 3, 'escolaridad_maxima' => $imagenes ? 30 : 22,
                'entradas' => $entradas + $contexto, 'revision_profesional' => 'PENDIENTE',
                'adopcion_institucional' => 'NO_CONFIGURADA', 'reproduce_reactivos' => false];
        }

        return $metodos;
    }

    /** Ningún valor ausente se transforma en edad, escolaridad o sexo de referencia. */
    public function normalizar(string $metodo, array $captura): array
    {
        $meta = $this->metodos()[$metodo] ?? throw new DomainException('Referencia española no registrada.');
        $motivos = [];
        foreach ($meta['identidad'] + ['protocolo' => $metodo] as $campo => $valor) {
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
        $escolaridad = $captura['escolaridad_anios'] ?? null;
        if (! $this->entero($edad, $meta['edad_minima'], $meta['edad_maxima'])) {
            $motivos[] = 'EDAD_FUERA_DE_MUESTRA_O_DESCONOCIDA';
        }
        if (! $this->entero($escolaridad, $meta['escolaridad_minima'], $meta['escolaridad_maxima'])) {
            $motivos[] = 'ESCOLARIDAD_FUERA_DE_MUESTRA_O_DESCONOCIDA';
        }
        $sexo = $captura['sexo_norma'] ?? null;
        if (isset($meta['entradas']['sexo_norma']) && ! $this->entero($sexo, 0, 1)) {
            $motivos[] = 'SEXO_REQUERIDO_POR_MODELO_NO_DOCUMENTADO';
        }
        // Una aplicación puede contener ambas longitudes: conservar sus resultados por separado.
        $medidas = [];
        foreach ($metodo === self::SPAN ? [self::DSB, self::LN] : [$metodo] as $id) {
            $medidas[] = $this->normalizarMedida($id, $captura, $motivos);
        }

        return ['metodo' => $metodo, 'referencia' => $meta['fuente'], 'localizador' => $meta['localizador'],
            'fuente_calculadora' => $metodo === self::IMAGENES ? null : 'https://drive.google.com/file/d/1p-RDT6F85EsXPxALV-l6R8Sq-E4qGEr0/view',
            'bruto' => array_intersect_key($captura, $meta['entradas']), 'contexto_aplicacion' => $captura,
            'componentes' => [$meta['componentes'][0] => count($medidas) === 1 ? $medidas[0] : ['medidas' => $medidas]],
            'diagnostico' => null, 'revision_profesional' => 'PENDIENTE', 'adopcion_institucional' => 'NO_CONFIGURADA',
            'limite' => 'Comparación con una referencia española y sus condiciones explícitas. No establece alteración de dominio ni DCL.'];
    }

    /** Un bruto inválido afecta a su medida; los errores de contexto afectan a todas. */
    private function normalizarMedida(string $metodo, array $captura, array $motivos): array
    {
        [$bruto, $medida, $errores] = $this->medicion($metodo, $captura);
        $motivos = array_merge($motivos, $errores);
        $r = ['medida' => $medida, 'estado' => 'NO_NORMALIZABLE', 'motivos' => $motivos,
            'puntuacion_bruta' => $bruto, 'esperado' => null, 'de_residual' => null,
            'varianza_residual' => null, 'z' => null, 'direccion' => 'MAYOR_ES_MEJOR',
            'clasificacion_clinica' => null, 'tipo' => 'NORMALIZACION_REFERENCIA',
            'advertencias' => ['REFERENCIA_ESPANOLA_NO_ADOPTADA_AUTOMATICAMENTE', 'NO_ES_DIAGNOSTICO']];
        if ($motivos === []) {
            $edad = $captura['edad_anios'];
            $escolaridad = $captura['escolaridad_anios'];
            $sexo = $captura['sexo_norma'] ?? null;
            $e = $escolaridad - 3;
            [$predicho, $de] = match ($metodo) {
                self::DSB => [3.695 + .003 * $e ** 2, .854],
                self::LN => [5.014 - .048 * ($edad - 55), 1.075],
                self::JLO => [22.252 - 4.263 * $sexo + .014 * $e ** 2, 4.446],
                self::FCSRT => [40.31 + .288 * $e + 1.886 * $sexo, 3.865],
                self::IMAGENES => [$this->predichoImagenes($edad, $escolaridad, $sexo), null],
            };
            // Este estudio modela la varianza: no usar la DE de otro test o una constante.
            $varianza = $de === null ? 106.86 - 2.55 * $predicho : $de ** 2;
            if (! is_finite($predicho) || ! is_finite($varianza) || $varianza <= 0) {
                $r['motivos'][] = 'MODELO_NO_PRODUCE_VARIANZA_POSITIVA_FINITA';
            } else {
                $de ??= sqrt($varianza);
                $r = array_replace($r, ['estado' => 'NORMALIZADO', 'esperado' => $predicho,
                    'de_residual' => $de, 'varianza_residual' => $varianza, 'z' => ($bruto - $predicho) / $de]);
            }
        }

        return $r;
    }

    /** Longitudes, aciertos y totales tienen unidades distintas, aunque todos sean enteros. */
    private function medicion(string $metodo, array $captura): array
    {
        if ($metodo === self::FCSRT) {
            // Reutilizar la aritmética del mismo procedimiento; no cambiar la captura conservada.
            $calculado = (new CalculadorRecuerdoConClavesFcsrt)->calcular(array_replace($captura,
                ['protocolo' => CalculadorRecuerdoConClavesFcsrt::METODO]));
            $m = $calculado['componentes']['MEM-S03']['medidas'][1];

            return [$m['puntuacion_bruta'], 'FCSRT_PALABRAS_TOTAL_APRENDIZAJE_SABIEX', $m['motivos']];
        }
        if ($metodo === self::IMAGENES) {
            $total = 0;
            foreach ([1, 2, 3] as $ensayo) {
                if (! $this->entero($captura['libre_'.$ensayo] ?? null, 0, 16)) {
                    return [null, 'PFCSRT_IMAGENES_LIBRE_APRENDIZAJE', ['ENSAYO_LIBRE_AUSENTE_O_INVALIDO:'.$ensayo]];
                }
                $total += $captura['libre_'.$ensayo];
            }

            return [$total, 'PFCSRT_IMAGENES_LIBRE_APRENDIZAJE', []];
        }
        $campo = match ($metodo) {
            self::DSB => 'longitud_inversa', self::LN => 'longitud_letras_numeros', default => 'aciertos'
        };
        $valor = $captura[$campo] ?? null;
        $errores = $this->entero($valor, 0, $metodo === self::JLO ? 30 : 8) ? [] : ['MEDICION_ORIGINAL_INVALIDA:'.$campo];
        if ($metodo === self::JLO && ($captura['elementos_puntuados'] ?? null) !== 30) {
            $errores[] = 'JLO_REQUIERE_FORMA_COMPLETA_DE_30_ELEMENTOS';
        }

        return [$valor, $campo, $errores];
    }

    /** Interacción edad/sexo y centrados publicados en las tablas 4–5, sin redondeo previo. */
    private function predichoImagenes(int $edad, int $escolaridad, int $sexo): float
    {
        $a = $edad - 67.46;

        return 27.38 - .05 * $a + .17 * ($escolaridad - 10.30) + 1.23 * $sexo - .25 * $a * $sexo;
    }

    /** Distingue cero de ausencia y rechaza cadenas, booleanos, fracciones y no finitos. */
    private function entero(mixed $valor, int $minimo, int $maximo): bool
    {
        return is_int($valor) && $valor >= $minimo && $valor <= $maximo;
    }
}
