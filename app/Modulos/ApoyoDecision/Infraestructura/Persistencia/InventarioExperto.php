<?php

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia;

use DomainException;
use Illuminate\Database\Connection;

/** Inventario físico D-137, 4.19.8; no representa tablas ya instaladas. */
final class InventarioExperto
{
    public const TABLAS = [
        'versiones_modelo_experto',
        'nodos_semanticos',
        'relaciones_semanticas',
        'dominios_valores_expertos',
        'valores_semanticos',
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
        'evaluaciones_reglas',
        'evaluaciones_condiciones_regla',
        'evidencias_soporte_condicion',
    ];

    public const CONSERVACION = [
        'hechos_instantanea_experta',
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

    /** Solo metadata y conteos opcionales; no lee valores clínicos ni decide qué tablas eliminar. */
    public static function diagnosticar(Connection $conexion, bool $contarFilas = false): array
    {
        if (! in_array($conexion->getDriverName(), ['pgsql', 'sqlite'], true)) {
            throw new DomainException('El inventario admite los motores verificados PostgreSQL y SQLite.');
        }
        $esquema = $conexion->getSchemaBuilder();
        $tablas = array_column($esquema->getTables($conexion->getDriverName() === 'pgsql' ? 'public' : null), 'name');
        sort($tablas, SORT_STRING);
        $grupos = array_fill_keys(self::TABLAS, 'D137')
            + array_fill_keys(self::CONSERVACION, 'CONSERVACION')
            + array_fill_keys(self::REVISION_PROFESIONAL, 'REVISION_PROFESIONAL');
        $entrantes = [];
        foreach ($tablas as $tabla) {
            foreach ($esquema->getForeignKeys($tabla) as $fk) {
                if (isset($grupos[$fk['foreign_table']])
                    && ($conexion->getDriverName() !== 'pgsql' || ($fk['foreign_schema'] ?? 'public') === 'public')) {
                    $entrantes[$fk['foreign_table']][] = ['tabla' => $tabla, 'columnas' => $fk['columns'],
                        'columnas_destino' => $fk['foreign_columns']];
                }
            }
        }
        $detalle = [];
        foreach ($grupos as $tabla => $grupo) {
            $presente = in_array($tabla, $tablas, true);
            $detalle[] = ['tabla' => $tabla, 'grupo' => $grupo, 'presente' => $presente,
                'filas' => $presente && $contarFilas ? $conexion->table($tabla)->count() : null,
                'referencias_entrantes' => $entrantes[$tabla] ?? []];
        }

        return ['motor' => $conexion->getDriverName(), 'conteo_filas_solicitado' => $contarFilas,
            'tablas_fisicas' => count($tablas), 'expertas_esperadas' => count($grupos),
            'expertas_presentes' => count(array_intersect(array_keys($grupos), $tablas)),
            'expertas_ausentes' => array_values(array_diff(array_keys($grupos), $tablas)),
            'otras_tablas_fisicas' => array_values(array_diff($tablas, array_keys($grupos))), 'detalle' => $detalle];
    }
}
