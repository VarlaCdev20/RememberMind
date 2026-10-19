<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

/**
 * Conversión publicada de dos subpruebas TB-A con efecto techo (tabla 5).
 * Una escala extrema del artículo no se convierte en gravedad ni capacidad superior.
 * No aplica baremos TB-A a WAB, TB revisado o notas generales del expediente.
 */
final class NormalizadorReferenciaBarcelona2011
{
    public const METODO = 'TB-A-ES-NEURONORMA-2011-LENGUAJE';

    public const FUENTE = 'https://doi.org/10.1093/arclin/acq098';

    /** Ambas subpruebas pertenecen a la misma aplicación; conservan resultados independientes. */
    public function metadatos(): array
    {
        return ['tipo' => 'NORMALIZACION_REFERENCIA', 'instrumento' => 'TB-A',
            'identidad' => ['instrumento' => 'TB-A', 'edicion_protocolo' => 'NEURONORMA-2011-ABREVIADO', 'idioma' => 'es'],
            'componentes' => ['LEN-S04', 'LEN-S05'], 'fuente' => self::FUENTE,
            'localizador' => 'Resultados, tabla 5 y apéndice A; PDF de autores pp. 4, 6, 9–10',
            'edad_minima' => 50, 'edad_maxima' => 90,
            'entradas' => ['repeticion_palabras_aciertos' => 'ENTERO', 'repeticion_elementos_administrados' => 'ENTERO',
                'fluencia_gramatica' => 'ENTERO', 'muestra_discurso_documentada' => 'LOGICO',
                'tareas_habla_espontanea_completadas' => 'LOGICO', 'audicion_compatible' => 'LOGICO',
                'contexto_normativo_espana_documentado' => 'LOGICO', 'administracion_estandarizada' => 'LOGICO'],
            'revision_profesional' => 'PENDIENTE', 'adopcion_institucional' => 'NO_CONFIGURADA',
            'reproduce_reactivos' => false];
    }

    /** El contexto compartido invalida ambas; una subprueba ausente no borra la otra. */
    public function normalizar(array $captura): array
    {
        $meta = $this->metadatos();
        $comunes = [];
        foreach ($meta['identidad'] + ['protocolo' => self::METODO] as $campo => $valor) {
            if (($captura[$campo] ?? null) !== $valor) {
                $comunes[] = 'IDENTIDAD_NO_VERIFICADA:'.$campo;
            }
        }
        foreach (['audicion_compatible', 'contexto_normativo_espana_documentado', 'administracion_estandarizada'] as $campo) {
            if (($captura[$campo] ?? null) !== true) {
                $comunes[] = 'CONTEXTO_NO_ACREDITADO:'.$campo;
            }
        }
        $edad = $captura['edad_anios'] ?? null;
        if (! is_int($edad) || $edad < 50 || $edad > 90) {
            $comunes[] = 'EDAD_FUERA_DE_REFERENCIA_O_DESCONOCIDA';
        }
        $componentes = [];
        foreach (['LEN-S04' => 'repeticion_palabras_aciertos', 'LEN-S05' => 'fluencia_gramatica'] as $componente => $campo) {
            $motivos = $comunes;
            $bruto = $captura[$campo] ?? null;
            if (! is_int($bruto) || $bruto < 0 || $bruto > 10) {
                $motivos[] = 'PUNTUACION_ORIGINAL_INVALIDA:'.$campo;
            }
            if ($componente === 'LEN-S04' && ($captura['repeticion_elementos_administrados'] ?? null) !== 10) {
                $motivos[] = 'REPETICION_REQUIERE_DIEZ_ELEMENTOS_TB_A';
            }
            if ($componente === 'LEN-S05') {
                foreach (['muestra_discurso_documentada', 'tareas_habla_espontanea_completadas'] as $condicion) {
                    if (($captura[$condicion] ?? null) !== true) {
                        $motivos[] = 'TAREA_DE_DISCURSO_NO_ACREDITADA:'.$condicion;
                    }
                }
            }
            $valido = $motivos === [];
            // El texto de Resultados define máximo frente a cualquier fallo, sin gradación intermedia.
            $componentes[$componente] = ['medida' => $componente === 'LEN-S04' ? 'TB_A_REPETICION_PALABRAS' : 'TB_A_FLUENCIA_GRAMATICA',
                'tipo' => 'NORMALIZACION_REFERENCIA', 'estado' => $valido ? 'NORMALIZADO' : 'NO_NORMALIZABLE',
                'motivos' => $motivos, 'puntuacion_bruta' => $bruto, 'tabla' => 5,
                'tipo_comparacion' => 'TABLA_CON_EFECTO_TECHO',
                'escalar_publicado' => $valido ? ($bruto === 10 ? 18 : 2) : null,
                'rango_percentil_publicado' => $valido ? ($bruto === 10 ? '>99' : '<1') : null,
                'z' => null, 'direccion' => 'MAYOR_ES_MEJOR', 'clasificacion_clinica' => null,
                'advertencias' => ['EFECTO_TECHO_100_POR_CIENTO_EN_MAXIMO', 'NO_INTERPRETAR_MAXIMO_COMO_CAPACIDAD_SUPERIOR',
                    'NO_CLASIFICAR_DCL_POR_UN_ERROR', 'REFERENCIA_ESPANOLA_NO_ADOPTADA_AUTOMATICAMENTE']];
        }

        return ['metodo' => self::METODO, 'referencia' => self::FUENTE, 'localizador' => $meta['localizador'],
            'bruto' => array_intersect_key($captura, $meta['entradas']), 'contexto_aplicacion' => $captura,
            'componentes' => $componentes, 'diagnostico' => null, 'revision_profesional' => 'PENDIENTE',
            'adopcion_institucional' => 'NO_CONFIGURADA',
            'limite' => 'Conversión categórica publicada con efecto techo. No demuestra superioridad, gravedad clínica, preservación global del lenguaje ni DCL.'];
    }
}
