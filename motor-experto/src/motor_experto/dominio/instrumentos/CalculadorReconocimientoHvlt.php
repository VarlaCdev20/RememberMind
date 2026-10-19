<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

/**
 * Diferencia descriptiva de reconocimiento HVLT-R; no es una norma boliviana.
 * FITBIR describe aciertos menos falsas alarmas. Su rango declarado 0–12
 * contradice los valores negativos posibles: se conserva la diferencia firmada
 * y se advierte el conflicto, sin truncar ni atribuir validez clínica al índice.
 */
final class CalculadorReconocimientoHvlt
{
    public const METODO = 'HVLT-R-F5-RECONOCIMIENTO-BRUTO-1';

    public const FUENTE = 'https://fitbir.nih.gov/dictionary/publicData/dataElementAction!view.action?dataElementName=HVLTRRecogDiscrimIndexRawScore&publicArea=true&style.key=fitbir-style';

    /** La identidad es independiente del perfil normativo de aprendizaje y recuerdo. */
    public function metadatos(): array
    {
        return ['instrumento' => 'HVLT-R', 'tipo' => 'CALCULO_DESCRIPTIVO',
            'identidad' => ['instrumento' => 'HVLT-R', 'forma' => 5, 'idioma' => 'es'],
            'componentes' => ['MEM-S04'], 'fuente' => self::FUENTE,
            'entradas' => ['aciertos_reconocimiento' => 'ENTERO', 'falsas_alarmas_relacionadas' => 'ENTERO',
                'falsas_alarmas_no_relacionadas' => 'ENTERO', 'demora_minutos' => 'NUMERO',
                'administracion_estandarizada' => 'LOGICO'],
            'reproduce_reactivos' => false, 'revision_profesional' => 'PENDIENTE'];
    }

    /** Recibe recuentos originales; un faltante nunca se reemplaza por cero. */
    public function calcular(array $captura): array
    {
        $meta = $this->metadatos();
        $motivos = [];
        foreach ($meta['identidad'] + ['protocolo' => self::METODO] as $campo => $esperado) {
            if (($captura[$campo] ?? null) !== $esperado) {
                $motivos[] = 'IDENTIDAD_NO_VERIFICADA:'.$campo;
            }
        }
        // Los tamaños de las listas proceden de NRE-151286, sección 1; no contiene sus estímulos.
        foreach (['aciertos_reconocimiento' => 12, 'falsas_alarmas_relacionadas' => 6, 'falsas_alarmas_no_relacionadas' => 6] as $campo => $maximo) {
            $valor = $captura[$campo] ?? null;
            if (! is_int($valor) || $valor < 0 || $valor > $maximo) {
                $motivos[] = 'RECUENTO_AUSENTE_O_INVALIDO:'.$campo;
            }
        }
        $demora = $captura['demora_minutos'] ?? null;
        if ((! is_int($demora) && ! is_float($demora)) || ! is_finite((float) $demora) || $demora < 20 || $demora > 25) {
            $motivos[] = 'DEMORA_NO_ACREDITADA_ENTRE_20_Y_25_MINUTOS';
        }
        if (($captura['administracion_estandarizada'] ?? null) !== true) {
            $motivos[] = 'ADMINISTRACION_NO_ACREDITADA';
        }
        $alarmas = $motivos === [] ? $captura['falsas_alarmas_relacionadas'] + $captura['falsas_alarmas_no_relacionadas'] : null;
        $diferencia = $alarmas !== null ? $captura['aciertos_reconocimiento'] - $alarmas : null;

        return ['metodo' => self::METODO, 'referencia' => self::FUENTE,
            'referencia_estructura' => NormalizadorHvltBolivia2015::FUENTE,
            'localizador' => 'FITBIR v1.2, Guidelines/Instructions; NRE-151286, secciones 1 y 2.2',
            'bruto' => array_intersect_key($captura, $meta['entradas']), 'contexto_aplicacion' => $captura,
            'componentes' => ['MEM-S04' => ['medida' => 'DIFERENCIA_ACIERTOS_FALSAS_ALARMAS',
                'estado' => $motivos === [] ? 'CALCULADO_SIN_NORMA' : 'NO_CALCULABLE', 'motivos' => $motivos,
                'puntuacion_bruta' => $diferencia, 'falsas_alarmas_total' => $alarmas,
                'esperado' => null, 'z' => null, 'clasificacion_clinica' => null,
                'advertencias' => ['SIN_NORMA_BOLIVIANA_DE_RECONOCIMIENTO', 'CONFLICTO_RANGO_DECLARADO_FITBIR'],
                'fuera_rango_declarado_fuente' => $diferencia !== null ? $diferencia < 0 : null]],
            'revision_profesional' => 'PENDIENTE', 'diagnostico' => null,
            'limite' => 'Diferencia aritmética descriptiva. No equivale a puntuación normativa, normalidad, alteración objetiva ni DCL.'];
    }
}
