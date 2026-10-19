<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

/**
 * Totales descriptivos de FCSRT de palabras según el procedimiento NEURONORMA.
 * Cotejo: Calderón-Rubio et al., 2021, apartado 2.2.1. No reproduce estímulos,
 * no aplica baremos españoles a residentes ni intercambia palabras e imágenes.
 */
final class CalculadorRecuerdoConClavesFcsrt
{
    public const METODO = 'FCSRT-PALABRAS-NEURONORMA-BRUTO-1';

    public const FUENTE = 'https://doi.org/10.3390/ijerph182412977';

    /** La identidad se refiere al procedimiento aplicado, no al país del baremo. */
    public function metadatos(): array
    {
        return ['instrumento' => 'FCSRT', 'tipo' => 'CALCULO_DESCRIPTIVO',
            'identidad' => ['instrumento' => 'FCSRT', 'edicion_protocolo' => 'NEURONORMA-2009-PALABRAS', 'idioma' => 'es'],
            'componentes' => ['MEM-S03'], 'fuente' => self::FUENTE,
            'entradas' => ['libre_1' => 'ENTERO', 'adicional_claves_1' => 'ENTERO',
                'libre_2' => 'ENTERO', 'adicional_claves_2' => 'ENTERO',
                'libre_3' => 'ENTERO', 'adicional_claves_3' => 'ENTERO',
                'libre_diferido' => 'ENTERO', 'adicional_claves_diferido' => 'ENTERO',
                'demora_minutos' => 'NUMERO', 'codificacion_verificada' => 'LOGICO',
                'administracion_estandarizada' => 'LOGICO'],
            'reproduce_reactivos' => false, 'revision_profesional' => 'PENDIENTE'];
    }

    /** Comprueba cada pareja antes de sumar; un diferido ausente no borra el aprendizaje. */
    public function calcular(array $captura): array
    {
        $meta = $this->metadatos();
        $comunes = [];
        foreach ($meta['identidad'] + ['protocolo' => self::METODO] as $campo => $esperado) {
            if (($captura[$campo] ?? null) !== $esperado) {
                $comunes[] = 'IDENTIDAD_NO_VERIFICADA:'.$campo;
            }
        }
        foreach (['codificacion_verificada', 'administracion_estandarizada'] as $campo) {
            if (($captura[$campo] ?? null) !== true) {
                $comunes[] = 'CONDICION_NO_ACREDITADA:'.$campo;
            }
        }
        $aprendizaje = $comunes;
        $libres = 0;
        $adicionales = 0;
        foreach ([1, 2, 3] as $ensayo) {
            $errores = $this->validarPareja($captura, 'libre_'.$ensayo, 'adicional_claves_'.$ensayo);
            $aprendizaje = array_merge($aprendizaje, $errores);
            if ($errores === []) {
                $libres += $captura['libre_'.$ensayo];
                $adicionales += $captura['adicional_claves_'.$ensayo];
            }
        }
        $diferido = array_merge($comunes, $this->validarPareja($captura, 'libre_diferido', 'adicional_claves_diferido'));
        $demora = $captura['demora_minutos'] ?? null;
        if ((! is_int($demora) && ! is_float($demora)) || ! is_finite((float) $demora) || (float) $demora !== 30.0) {
            $diferido[] = 'DEMORA_NO_CORRESPONDE_A_30_MINUTOS';
        }

        return ['metodo' => self::METODO, 'referencia' => self::FUENTE,
            'localizador' => 'Apartado 2.2.1; procedimiento de palabras NEURONORMA, no pFCSRT+IR de imágenes',
            'bruto' => array_intersect_key($captura, $meta['entradas']), 'contexto_aplicacion' => $captura,
            'componentes' => ['MEM-S03' => ['medidas' => [
                $this->resultado('FCSRT_PALABRAS_ADICIONALES_CON_CLAVES', $aprendizaje, $aprendizaje === [] ? $adicionales : null),
                $this->resultado('FCSRT_PALABRAS_TOTAL_APRENDIZAJE', $aprendizaje, $aprendizaje === [] ? $libres + $adicionales : null)
                    + ['libre_total' => $aprendizaje === [] ? $libres : null],
                $this->resultado('FCSRT_PALABRAS_TOTAL_DIFERIDO', $diferido,
                    $diferido === [] ? $captura['libre_diferido'] + $captura['adicional_claves_diferido'] : null),
            ]]],
            'diagnostico' => null, 'revision_profesional' => 'PENDIENTE',
            'limite' => 'Totales descriptivos, sin norma adoptada. No demuestran déficit de almacenamiento, etiología ni DCL.'];
    }

    /** Las claves son aciertos adicionales: la pareja no puede superar los dieciséis elementos. */
    private function validarPareja(array $captura, string $libre, string $claves): array
    {
        foreach ([$libre, $claves] as $campo) {
            $valor = $captura[$campo] ?? null;
            if (! is_int($valor) || $valor < 0 || $valor > 16) {
                return ['RECUENTO_ORIGINAL_AUSENTE_O_INVALIDO:'.$campo];
            }
        }

        return $captura[$libre] + $captura[$claves] <= 16 ? [] : ['PAREJA_SUPERA_16:'.$libre];
    }

    /** No asigna z, percentil ni categoría clínica a una suma descriptiva. */
    private function resultado(string $medida, array $motivos, ?int $puntos): array
    {
        return ['medida' => $medida, 'estado' => $motivos === [] ? 'CALCULADO_SIN_NORMA' : 'NO_CALCULABLE',
            'motivos' => $motivos, 'puntuacion_bruta' => $puntos, 'unidad' => 'ACIERTOS',
            'esperado' => null, 'z' => null, 'clasificacion_clinica' => null,
            'advertencias' => ['SIN_NORMA_ADOPTADA', 'NO_EQUIVALENTE_A_FCSRT_IMAGENES']];
    }
}
