<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

use DomainException;

/**
 * COMPONENTE DEL SISTEMA EXPERTO: Referencias instrumentales de Bolivia.
 *
 * Implementa medidas de seis publicaciones originales de NeuroRehabilitation (2015).
 * Conserva el bruto, las condiciones y la dirección del indicador. Los modelos
 * comparan rendimiento; no convierten una puntuación en alteración de dominio o DCL.
 * No contiene estímulos, láminas, respuestas correctas ni rúbricas de administración.
 */
final class NormalizadorBateriaBolivia2015
{
    public const CONTEXTO = ['escolaridad_anios' => 'ENTERO', 'espanol_nativo' => 'LOGICO',
        'nacimiento_bolivia' => 'LOGICO', 'residencia_bolivia' => 'LOGICO',
        'lectoescritura' => 'LOGICO', 'administracion_estandarizada' => 'LOGICO'];

    /** Metadatos de cálculo; no son bindings a instrumentos o preguntas de residentes. */
    public function metodos(): array
    {
        $definiciones = [
            'BTA-BO-2015-NL' => ['BTA', 'Schretlen-1997-NL', 'ATE-S03', '151283', 'Tabla 2',
                ['forma_n_aciertos' => 'ENTERO', 'forma_l_aciertos' => 'ENTERO', 'audicion_compatible' => 'LOGICO']],
            'STROOP-BO-2015-GOLDEN' => ['STROOP', 'Golden-2007-45s', 'EJE-S01', '151281', 'Tabla A35',
                ['palabras_correctas' => 'ENTERO', 'colores_correctos' => 'ENTERO', 'palabra_color_correctas' => 'ENTERO',
                    'segundos_palabras' => 'NUMERO', 'segundos_colores' => 'NUMERO', 'segundos_palabra_color' => 'NUMERO',
                    'condiciones_sensoriales_motoras_compatibles' => 'LOGICO']],
            'TMT-B-BO-2015' => ['TMT', 'Parte-B-25-300s', 'EJE-S02', '151284', 'Tabla 4',
                ['segundos_parte_b' => 'NUMERO', 'errores_observados' => 'ENTERO', 'finalizada' => 'LOGICO',
                    'condiciones_sensoriales_motoras_compatibles' => 'LOGICO']],
            'BNT-BO-2015-60' => ['BNT', 'Kaplan-1983-60', 'LEN-S01', '151278', 'Tabla 2',
                ['aciertos_espontaneos' => 'ENTERO', 'aciertos_clave_semantica' => 'ENTERO',
                    'condiciones_sensoriales_motoras_compatibles' => 'LOGICO']],
            'FLUIDEZ-BO-2015-FAS-ANIMALES' => ['FLUIDEZ', 'FAS-Animales-60s', 'LEN-S02', '151279', 'Tablas 2, 4, 6 y 8',
                ['f_validas' => 'ENTERO', 'f_segundos' => 'NUMERO', 'a_validas' => 'ENTERO', 'a_segundos' => 'NUMERO',
                    's_validas' => 'ENTERO', 's_segundos' => 'NUMERO', 'animales_validas' => 'ENTERO', 'animales_segundos' => 'NUMERO']],
            'ROCF-BO-2015-COPIA-A' => ['ROCF', 'Rey-2009-A-Copia', 'VIS-S02', '151285', 'Tabla 2',
                ['puntaje_copia' => 'NUMERO', 'elementos_revisados' => 'ENTERO',
                    'condiciones_sensoriales_motoras_compatibles' => 'LOGICO']],
        ];
        $metodos = [];
        foreach ($definiciones as $id => [$instrumento, $edicion, $componente, $doi, $tabla, $entradas]) {
            $metodos[$id] = ['instrumento' => $instrumento, 'edicion_protocolo' => $edicion, 'idioma' => 'es',
                'identidad' => ['instrumento' => $instrumento, 'edicion_protocolo' => $edicion, 'idioma' => 'es'],
                'componentes' => [$componente], 'fuente' => 'https://doi.org/10.3233/NRE-'.$doi, 'localizador' => $tabla,
                'edad_minima' => 18, 'edad_maxima' => 95, 'escolaridad_minima' => 1, 'lectoescritura_requerida' => true,
                'entradas' => $entradas + self::CONTEXTO, 'reproduce_reactivos' => false, 'revision_profesional' => 'PENDIENTE'];
        }

        return $metodos;
    }

    public function normalizar(string $metodo, array $captura): array
    {
        $meta = $this->metodos()[$metodo] ?? throw new DomainException('Método de la batería no registrado.');
        $motivos = [];
        foreach (['instrumento' => $meta['instrumento'], 'edicion_protocolo' => $meta['edicion_protocolo'], 'idioma' => 'es', 'protocolo' => $metodo] as $campo => $valor) {
            if (($captura[$campo] ?? null) !== $valor) {
                $motivos[] = 'IDENTIDAD_NO_VERIFICADA:'.$campo;
            }
        }
        foreach ($meta['entradas'] as $campo => $tipo) {
            if ($tipo === 'LOGICO' && ($captura[$campo] ?? null) !== true) {
                $motivos[] = 'APLICABILIDAD_NO_ACREDITADA:'.$campo;
            }
        }
        $edad = $captura['edad_anios'] ?? null;
        $educacion = $captura['escolaridad_anios'] ?? null;
        if (! is_int($edad) || $edad < 18 || $edad > 95) {
            $motivos[] = 'EDAD_FUERA_DE_MUESTRA_O_DESCONOCIDA';
        }
        if (! is_int($educacion) || $educacion < 1) {
            $motivos[] = 'ESCOLARIDAD_FUERA_DE_MUESTRA_O_DESCONOCIDA';
        }
        // Valores de cálculo solo tras validar el contexto; no rellenan la captura original.
        $e = is_int($educacion) && $educacion > 12 ? 1 : 0;
        $a = is_int($edad) ? $edad : 0;
        $r = match ($metodo) {
            'BTA-BO-2015-NL' => $this->bta($captura, $motivos, 14.942 - .059 * $a + 1.943 * $e),
            'STROOP-BO-2015-GOLDEN' => $this->stroop($captura, $motivos),
            'TMT-B-BO-2015' => $this->tmt($captura, $motivos, 57.619 + 2.448 * $a - 54.711 * $e),
            'BNT-BO-2015-60' => $this->bnt($captura, $motivos, 45.102 + 3.746 * $e),
            'FLUIDEZ-BO-2015-FAS-ANIMALES' => $this->fluidez($captura, $motivos, $a, $e),
            'ROCF-BO-2015-COPIA-A' => $this->rocf($captura, $motivos, 33.123 - .147 * $a + 5.212 * $e),
        };

        return ['metodo' => $metodo, 'referencia' => $meta['fuente'], 'localizador' => $meta['localizador'],
            'bruto' => array_intersect_key($captura, $meta['entradas']), 'contexto_aplicacion' => $captura,
            'componentes' => [$meta['componentes'][0] => $r], 'revision_profesional' => 'PENDIENTE', 'diagnostico' => null,
            'limite' => 'Referencia comunitaria de adultos alfabetizados. No acredita por sí sola alteración de un dominio, independencia funcional ni DCL.'];
    }

    private function entero(mixed $valor, int $maximo = PHP_INT_MAX): bool
    {
        return is_int($valor) && $valor >= 0 && $valor <= $maximo;
    }

    private function numero(mixed $valor, float $minimo, float $maximo): bool
    {
        return (is_int($valor) || is_float($valor)) && is_finite((float) $valor) && $valor >= $minimo && $valor <= $maximo;
    }

    private function resultado(string $medida, mixed $bruto, array $motivos, ?float $esperado = null, ?float $de = null, string $direccion = 'MAYOR_ES_MEJOR'): array
    {
        $r = ['medida' => $medida, 'estado' => $motivos === [] ? 'NORMALIZADO' : 'NO_NORMALIZABLE', 'motivos' => $motivos,
            'puntuacion_bruta' => $bruto, 'esperado' => null, 'de_residual' => null, 'z' => null,
            'direccion' => $direccion, 'clasificacion_clinica' => null];
        if ($motivos === [] && $esperado !== null && $de !== null) {
            $r['esperado'] = $esperado;
            $r['de_residual'] = $de;
            $r['z'] = ($bruto - $esperado) / $de;
        }

        return $r;
    }

    private function bta(array $c, array $motivos, float $esperado): array
    {
        $n = $c['forma_n_aciertos'] ?? null;
        $l = $c['forma_l_aciertos'] ?? null;
        $valido = $this->entero($n, 10) && $this->entero($l, 10);
        if (! $valido) {
            $motivos[] = 'REQUIERE_DOS_FORMAS_CON_ACIERTOS_ENTEROS_DE_0_A_10';
        }

        return $this->resultado('BTA_TOTAL_N_L', $valido ? $n + $l : null, $motivos, $esperado, 3.786);
    }

    private function tmt(array $c, array $motivos, float $esperado): array
    {
        $segundos = $c['segundos_parte_b'] ?? null;
        if (! $this->numero($segundos, 0, 300) || $segundos === 0 || $segundos === 0.0) {
            $motivos[] = 'TIEMPO_B_REQUIERE_SEGUNDOS_POSITIVOS_HASTA_300';
        }
        if (! $this->entero($c['errores_observados'] ?? null)) {
            $motivos[] = 'ERRORES_NO_DOCUMENTADOS_O_INVALIDOS';
        }
        // Un tiempo censurado no se publica como si midiera la finalización.
        if (($c['finalizada'] ?? null) !== true) {
            $motivos[] = 'TAREA_NO_FINALIZADA_NO_EQUIVALE_A_TIEMPO_EXACTO';
        }

        return $this->resultado('TMT_B_SEGUNDOS', $segundos, $motivos, $esperado, 69.661, 'MENOR_ES_MEJOR');
    }

    private function bnt(array $c, array $motivos, float $esperado): array
    {
        $espontaneos = $c['aciertos_espontaneos'] ?? null;
        $semanticos = $c['aciertos_clave_semantica'] ?? null;
        $valido = $this->entero($espontaneos, 60) && $this->entero($semanticos, 60) && $espontaneos + $semanticos <= 60;
        if (! $valido) {
            $motivos[] = 'REQUIERE_ACIERTOS_ESPONTANEOS_Y_SEMANTICOS_CON_TOTAL_HASTA_60';
        }

        return $this->resultado('BNT_60_TOTAL', $valido ? $espontaneos + $semanticos : null, $motivos, $esperado, 8.732);
    }

    private function fluidez(array $c, array $motivos, int $edad, int $e): array
    {
        $medidas = [];
        foreach (['f' => [10.600, -.046, 2.630, 3.248, 'Tabla 2'], 'a' => [10.651, -.050, 3.435, 3.441, 'Tabla 4'],
            's' => [9.453, -.046, 3.404, 3.405, 'Tabla 6'], 'animales' => [18.013, -.100, 4.398, 4.552, 'Tabla 8']] as $condicion => [$b, $ba, $be, $de, $tabla]) {
            $faltantes = $motivos;
            $bruto = $c[$condicion.'_validas'] ?? null;
            if (! $this->entero($bruto)) {
                $faltantes[] = 'RESPUESTAS_VALIDAS_NO_MEDIDAS_O_INVALIDAS:'.$condicion;
            }
            if (! $this->numero($c[$condicion.'_segundos'] ?? null, 60, 60)) {
                $faltantes[] = 'CONDICION_REQUIERE_60_SEGUNDOS:'.$condicion;
            }
            $medidas[$condicion] = $this->resultado('FLUIDEZ_'.strtoupper($condicion), $bruto, $faltantes, $b + $ba * $edad + $be * $e, $de)
                + ['localizador' => $tabla];
        }
        $normalizadas = count(array_filter($medidas, fn ($m) => $m['estado'] === 'NORMALIZADO'));

        return ['estado' => $normalizadas === 4 ? 'NORMALIZADO' : ($normalizadas > 0 ? 'PARCIALMENTE_NORMALIZADO' : 'NO_NORMALIZABLE'),
            'motivos' => $motivos, 'medidas' => $medidas, 'puntuacion_bruta' => null, 'esperado' => null, 'z' => null,
            'clasificacion_clinica' => null];
    }

    private function rocf(array $c, array $motivos, float $esperado): array
    {
        $puntaje = $c['puntaje_copia'] ?? null;
        if (! $this->numero($puntaje, 0, 36) || fmod((float) $puntaje * 2, 1) !== 0.0) {
            $motivos[] = 'COPIA_REQUIERE_PUNTUACION_DE_0_A_36_EN_MEDIOS_PUNTOS';
        }
        if (($c['elementos_revisados'] ?? null) !== 18) {
            $motivos[] = 'REQUIERE_CORRECCION_DE_LOS_18_ELEMENTOS';
        }

        return $this->resultado('ROCF_A_COPIA', $puntaje, $motivos, $esperado, 6.728);
    }

    private function stroop(array $c, array $motivos): array
    {
        $w = $c['palabras_correctas'] ?? null;
        $color = $c['colores_correctos'] ?? null;
        $wc = $c['palabra_color_correctas'] ?? null;
        $valido = $this->entero($w) && $this->entero($color) && $this->entero($wc) && $w + $color > 0;
        if (! $valido) {
            $motivos[] = 'REQUIERE_TRES_RECUENTOS_Y_DENOMINADOR_NO_NULO';
        }
        foreach (['segundos_palabras', 'segundos_colores', 'segundos_palabra_color'] as $campo) {
            if (! $this->numero($c[$campo] ?? null, 45, 45)) {
                $motivos[] = 'CONDICION_REQUIERE_45_SEGUNDOS:'.$campo;
            }
        }
        // Golden: WC - W*C/(W+C). La errata P+W del artículo se contrasta en el documento de métodos.
        $indice = $valido ? $wc - ($w * $color) / ($w + $color) : null;
        $r = $this->resultado('STROOP_INTERFERENCIA_GOLDEN', $indice, $motivos);
        $r['percentil_intervalo'] = null;
        if ($motivos === []) {
            // Pares de datos de A35, no regresión, interpolación o extrapolación.
            $tabla = [[-16.9, 5], [-10.6, 10], [-9.0, 15], [-7.2, 20], [-3.8, 30], [0.0, 40], [2.4, 50],
                [5.1, 60], [8.2, 70], [12.1, 80], [14.0, 85], [18.5, 90], [23.9, 95]];
            $inferior = $superior = null;
            foreach ($tabla as [$umbral, $percentil]) {
                if (abs($indice - $umbral) < 1e-9) {
                    $inferior = $superior = $percentil;
                    break;
                }
                if ($indice > $umbral) {
                    $inferior = $percentil;
                } else {
                    $superior = $percentil;
                    break;
                }
            }
            $r['percentil_intervalo'] = ['inferior' => $inferior, 'superior' => $superior, 'tipo' => 'APROXIMACION_TABLA_REDONDEADA_A35'];
        }

        return $r;
    }
}
