<?php

declare(strict_types=1);

namespace MotorExperto\dominio\instrumentos;

/** Comprobación de alcance 60+ por edad en la aplicación; no es un clasificador de rendimiento. */
final class AplicabilidadGeriatrica
{
    public function evaluar(array $metodo, array $captura): array
    {
        $edad = $captura['edad_anios'] ?? null;
        $edad = is_int($edad) && $edad >= 0 ? $edad : null;
        $minima = $metodo['edad_minima'] ?? null;
        $maxima = $metodo['edad_maxima'] ?? null;
        $motivos = [];
        if ($edad === null) {
            $motivos[] = 'EDAD_EN_APLICACION_DESCONOCIDA';
        } elseif ($edad < 60) {
            $motivos[] = 'APLICACION_ANTERIOR_A_LOS_60_ANIOS';
        }
        if (! is_int($minima) || ! is_int($maxima)) {
            $motivos[] = 'SIN_RANGO_ETARIO_NORMATIVO_IDENTIFICADO';
        } elseif ($edad !== null && ($edad < $minima || $edad > $maxima)) {
            $motivos[] = 'EDAD_FUERA_DE_REFERENCIA';
        }
        if ($edad !== null && in_array($edad, $metodo['edades_excluidas_por_conflicto'] ?? [], true)) {
            $motivos[] = 'EDAD_CON_AMBIGUEDAD_EN_FUENTE';
        }

        return ['poblacion_objetivo' => '60_ANIOS_O_MAS', 'edad_en_aplicacion' => $edad,
            'pertenece_a_poblacion_objetivo' => $edad === null ? null : $edad >= 60,
            'rango_edad_referencia' => ['minima' => $minima, 'maxima' => $maxima],
            'alcance_etario_compatible' => $motivos === [], 'motivos' => $motivos,
            'escolaridad_original' => $captura['escolaridad_anios'] ?? null,
            'idioma_declarado' => $captura['idioma'] ?? null, 'fuente' => $metodo['fuente'] ?? null,
            'requiere_verificar' => ['identidad_y_edicion', 'escolaridad', 'idioma_y_contexto_cultural', 'condiciones_de_aplicacion'],
            'autoriza_interpretacion_clinica' => false];
    }
}
