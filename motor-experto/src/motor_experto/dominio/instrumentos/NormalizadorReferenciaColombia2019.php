<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

use DomainException;

/**
 * Comparaciones descriptivas con las tablas 10–11 del registro API colombiano.
 * La DE es del grupo, no un error residual; no se supone distribución normal.
 * No adopta estas referencias antioqueñas como baremos clínicos de Bolivia.
 */
final class NormalizadorReferenciaColombia2019
{
    public const RAVEN = 'RAVEN-A-CO-API-2019';

    public const CERAD = 'CERAD-RECONOCIMIENTO-CO-API-2019';

    public const CANCELACION = 'CANCELACION-A-CO-API-2019';

    public const FUENTE = 'https://pmc.ncbi.nlm.nih.gov/articles/PMC6926163/';

    /** La identidad del procedimiento colombiano impide reutilizar normas WAIS, HVLT o Ruff. */
    public function metodos(): array
    {
        $definiciones = [
            self::RAVEN => ['Raven', 'API-COLOMBIA-2019-PARTE-A-12', 'EJE-S05',
                ['aciertos' => 'ENTERO', 'elementos_puntuados' => 'ENTERO']],
            self::CERAD => ['CERAD-Col', 'API-COLOMBIA-2019-RECONOCIMIENTO-20', 'MEM-S04',
                ['aciertos_objetivos' => 'ENTERO', 'falsas_alarmas' => 'ENTERO', 'objetivos_presentados' => 'ENTERO',
                    'distractores_presentados' => 'ENTERO', 'demora_minutos' => 'NUMERO',
                    'aprendizaje_tres_ensayos_documentado' => 'LOGICO']],
            self::CANCELACION => ['Cancelacion-A', 'API-COLOMBIA-2019-16', 'ATE-S01',
                ['aciertos' => 'ENTERO', 'objetivos_presentados' => 'ENTERO', 'tiempo_segundos' => 'NUMERO']],
        ];
        $resultado = [];
        foreach ($definiciones as $metodo => [$instrumento, $edicion, $componente, $entradas]) {
            $resultado[$metodo] = ['tipo' => 'NORMALIZACION_REFERENCIA', 'instrumento' => $instrumento,
                'identidad' => ['instrumento' => $instrumento, 'edicion_protocolo' => $edicion, 'idioma' => 'es'],
                'componentes' => [$componente], 'fuente' => self::FUENTE, 'localizador' => 'Métodos y tablas 10–11',
                'edad_minima' => 61, 'edad_maxima' => 86, 'edades_excluidas_por_conflicto' => [65],
                'escolaridad_minima' => 1, 'escolaridad_maxima' => 25,
                'entradas' => $entradas + ['escolaridad_anios' => 'ENTERO', 'espanol_nativo' => 'LOGICO',
                    'lectoescritura' => 'LOGICO', 'contexto_normativo_antioquia_documentado' => 'LOGICO',
                    'condiciones_sensoriales_compatibles' => 'LOGICO', 'administracion_estandarizada' => 'LOGICO'],
                'revision_profesional' => 'PENDIENTE', 'adopcion_institucional' => 'NO_CONFIGURADA',
                'reproduce_reactivos' => false];
        }

        return $resultado;
    }

    /** El solapamiento editorial de los 65 años y las celdas vacías no se resuelven por suposición. */
    public function normalizar(string $metodo, array $captura): array
    {
        $meta = $this->metodos()[$metodo] ?? throw new DomainException('Referencia colombiana no registrada.');
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
        $educacion = $captura['escolaridad_anios'] ?? null;
        if (! $this->entero($edad, 61, 86)) {
            $motivos[] = 'EDAD_FUERA_DE_REFERENCIA_O_DESCONOCIDA';
        } elseif ($edad === 65) {
            $motivos[] = 'SOLAPAMIENTO_PUBLICADO_ENTRE_TABLAS_A_LOS_65_ANIOS';
        }
        if (! $this->entero($educacion, 1, 25)) {
            $motivos[] = 'ESCOLARIDAD_FUERA_DE_MUESTRA_O_DESCONOCIDA';
        }
        [$bruto, $errores] = $this->medicion($metodo, $captura);
        $motivos = array_merge($motivos, $errores);
        $m = ['medida' => $metodo, 'tipo' => 'NORMALIZACION_REFERENCIA', 'estado' => 'NO_NORMALIZABLE',
            'puntuacion_bruta' => $bruto, 'tipo_comparacion' => 'MEDIA_GRUPO', 'media_referencia' => null,
            'de_referencia' => null, 'de_residual' => null, 'z' => null, 'percentil' => null,
            'direccion' => 'MAYOR_ES_MEJOR', 'clasificacion_clinica' => null, 'estrato' => null,
            'advertencias' => ['REFERENCIA_ANTIOQUENA_NO_ADOPTADA_AUTOMATICAMENTE',
                'GRUPOS_GERIATRICOS_PEQUENOS', 'NO_SE_SUPONE_DISTRIBUCION_NORMAL', 'NO_ES_DIAGNOSTICO']];
        if ($motivos === []) {
            $tabla = $edad < 65 ? 10 : 11;
            $baja = $educacion < 5;
            // Las cuatro parejas de cada medida siguen: tabla 10 baja/alta, tabla 11 baja/alta.
            $datos = match ($metodo) {
                self::RAVEN => [[8.43, 1.94], [8.16, 2.00], [8.06, 2.11], [7.71, 2.26]],
                self::CERAD => [[9.76, .54], [9.45, 1.09], [9.35, 1.17], [9.37, 1.30]],
                self::CANCELACION => [[12.00, 2.83], [13.67, 2.31], [null, null], [13.83, 1.94]],
            };
            [$media, $de] = $datos[($tabla === 10 ? 0 : 2) + ($baja ? 0 : 1)];
            $m['estrato'] = ['tabla' => $tabla, 'edad_publicada' => $tabla === 10 ? '61–65' : '65 y más',
                'escolaridad_publicada' => $baja ? '0–4' : '5 y más',
                'n_grupo_educativo' => $tabla === 10 ? ($baja ? 22 : 32) : ($baja ? 17 : 24)];
            if ($media === null || $de === null) {
                $motivos[] = 'CELDA_NORMATIVA_SIN_DATOS_PUBLICADOS';
            } else {
                $m = array_replace($m, ['estado' => 'NORMALIZADO', 'media_referencia' => $media,
                    'de_referencia' => $de, 'z' => ($bruto - $media) / $de]);
            }
        }
        $m['motivos'] = $motivos;

        return ['metodo' => $metodo, 'referencia' => self::FUENTE, 'localizador' => $meta['localizador'],
            'bruto' => array_intersect_key($captura, $meta['entradas']), 'contexto_aplicacion' => $captura,
            'componentes' => [$meta['componentes'][0] => $m], 'diagnostico' => null,
            'revision_profesional' => 'PENDIENTE', 'adopcion_institucional' => 'NO_CONFIGURADA',
            'limite' => 'Distancia descriptiva respecto de un grupo colombiano pequeño; sin percentil gaussiano, clasificación de dominio ni DCL.'];
    }

    /** La corrección de reconocimiento CERAD sí trunca en cero; no se traslada al HVLT-R. */
    private function medicion(string $metodo, array $c): array
    {
        if ($metodo === self::CERAD) {
            if (! $this->entero($c['aciertos_objetivos'] ?? null, 0, 10) || ! $this->entero($c['falsas_alarmas'] ?? null, 0, 10)) {
                return [null, ['CONTEOS_DE_RECONOCIMIENTO_INVALIDOS']];
            }
            $errores = ($c['objetivos_presentados'] ?? null) === 10 && ($c['distractores_presentados'] ?? null) === 10
                ? [] : ['REQUIERE_10_OBJETIVOS_Y_10_DISTRACTORES'];
            if (! $this->numero($c['demora_minutos'] ?? null) || (float) $c['demora_minutos'] !== 7.0) {
                $errores[] = 'DIFERIDO_PREVIO_REQUIERE_7_MINUTOS';
            }

            return [max(0, $c['aciertos_objetivos'] - $c['falsas_alarmas']), $errores];
        }
        $maximo = $metodo === self::RAVEN ? 12 : 16;
        $bruto = $c['aciertos'] ?? null;
        $errores = $this->entero($bruto, 0, $maximo) ? [] : ['ACIERTOS_INVALIDOS'];
        $campo = $metodo === self::RAVEN ? 'elementos_puntuados' : 'objetivos_presentados';
        if (($c[$campo] ?? null) !== $maximo) {
            $errores[] = 'FORMA_INCOMPLETA_O_DISTINTA:'.$campo;
        }
        if ($metodo === self::CANCELACION && (! $this->numero($c['tiempo_segundos'] ?? null) || $c['tiempo_segundos'] <= 0)) {
            $errores[] = 'TIEMPO_DE_CANCELACION_NO_DOCUMENTADO';
        }

        return [$this->entero($bruto, 0, $maximo) ? $bruto : null, $errores];
    }

    /** Cero es una medición; ausencia, texto y fracciones no son recuentos originales. */
    private function entero(mixed $valor, int $minimo, int $maximo): bool
    {
        return is_int($valor) && $valor >= $minimo && $valor <= $maximo;
    }

    /** Acepta segundos y minutos numéricos finitos sin convertir cadenas o booleanos. */
    private function numero(mixed $valor): bool
    {
        return (is_int($valor) || is_float($valor)) && is_finite((float) $valor);
    }
}
