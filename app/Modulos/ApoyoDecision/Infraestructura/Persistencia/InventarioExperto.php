<?php

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia;

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

    public const CONSERVACION = [
        'hechos_instantanea_experta',
        'evidencias_cognitivas_instantanea',
        'soportes_sinteticos_criterio',
        'funcionalidad_instantanea_experta',
        'evidencias_iadl_instantanea',
        'clasificaciones_dcl_instantanea',
        'motivos_dcl_instantanea',
        'reglas_aplicadas_dcl_instantanea',
        'condiciones_regla_instantanea',
    ];

    /** Dictámenes humanos separados, extensión aprobada el 17/10/2026. */
    public const REVISION_PROFESIONAL = ['revisiones_evaluacion_experta'];

    /** Excluye solamente nombres aprobados; las tablas inesperadas siguen visibles. */
    public static function excluirDelInventarioOperativo(array $tablas): array
    {
        return array_values(array_diff($tablas, self::TABLAS, self::CONSERVACION, self::REVISION_PROFESIONAL));
    }
}
