<?php

namespace App\Backend\Modulos\SistemaExperto\Conocimiento;

/** Inventario físico D-137, 4.19.8; no representa tablas ya instaladas. */
final class InventarioExperto
{
    public const TABLAS = [
        'versiones_modelo_experto',
        'nodos_semanticos',
        'relaciones_semanticas',
        'dominios_valores_expertos',
        'valores_semanticos',
        'variables_expertas',
        'fuentes_datos_expertas',
        'mapeos_variables_fuente',
        'mapeos_valores_fuente',
        'criterios_dominios_resultado',
        'reglas_expertas',
        'condiciones_regla_experta',
        'consecuencias_regla_experta',
        'evaluaciones_expertas',
        'evidencias_evaluacion',
        'relaciones_evidencias_evaluacion',
        'evaluacion_criterios',
        'evidencias_criterio_evaluacion',
        'resultados_criterio',
        'trazas_inferencia',
        'evaluaciones_reglas',
        'evaluaciones_condiciones_regla',
        'evidencias_soporte_condicion',
    ];

    /** Excluye solamente nombres aprobados; las tablas inesperadas siguen visibles. */
    public static function excluirDelInventarioOperativo(array $tablas): array
    {
        return array_values(array_diff($tablas, self::TABLAS));
    }
}
