<?php

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia;

use App\Models\Instrumento;
use App\Models\OpcionPregunta;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\RespuestaInstrumento;
use App\Models\SoporteValoracionPetersen;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratoFuentesPetersen;
use DomainException;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;

/** Inspección del esquema experto y sus entradas; no certifica datos ni preparación clínica. */
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

    /** Contrato físico de entrada: solo esquema y permisos declarados, sin leer filas clínicas. */
    public static function entradas(Connection $conexion): array
    {
        if (! in_array($conexion->getDriverName(), ['pgsql', 'sqlite'], true)) {
            throw new DomainException('El inventario admite los motores verificados PostgreSQL y SQLite.');
        }
        $esquema = $conexion->getSchemaBuilder();
        $soportes = [];
        foreach (ContratoFuentesPetersen::FUENTES as $fk => [$modelo, $permiso]) {
            $entidad = new $modelo;
            $fecha = $fk === 'cod_antecedente' ? 'fecha_referencia' : 'fecha_hora';
            $estado = $fk === 'cod_valoracion_revisada' ? 'estado_original' : 'estado';
            $soportes[$fk] = self::describirEntrada($esquema, $entidad,
                ['cod_residente', 'cod_personal', $fecha, $estado],
                [['cod_residente', 'residentes', 'cod_residente']])
                + ['permiso_lectura' => $permiso, 'campo_fecha' => $fecha, 'campo_estado' => $estado];
        }
        $relacionesSoporte = [['cod_valoracion_petersen', 'valoraciones_petersen_originales', 'cod_valoracion_petersen']];
        foreach (ContratoFuentesPetersen::FUENTES as $fk => [$modelo]) {
            $entidad = new $modelo;
            $relacionesSoporte[] = [$fk, $entidad->getTable(), $entidad->getKeyName()];
        }
        $captura = [];
        foreach ([
            [new Residente, ['fecha_nacimiento', 'estado'], []],
            [new SoporteValoracionPetersen, ['cod_valoracion_petersen', 'tipo_soporte', 'orden', ...array_keys(ContratoFuentesPetersen::FUENTES)], $relacionesSoporte],
            [new Instrumento, ['codigo', 'version', 'tipo', 'estado'], []],
            [new PreguntaInstrumento, ['cod_instrumento', 'tipo_respuesta', 'estado'], [['cod_instrumento', 'instrumentos', 'cod_instrumento']]],
            [new OpcionPregunta, ['cod_pregunta', 'puntaje', 'estado'], [['cod_pregunta', 'preguntas_instrumento', 'cod_pregunta']]],
            [new RespuestaInstrumento, ['cod_aplicacion', 'cod_pregunta', 'cod_opcion', 'valor_numero', 'valor_texto', 'valor_logico', 'puntaje'],
                [['cod_aplicacion', 'aplicaciones_instrumento', 'cod_aplicacion'], ['cod_pregunta', 'preguntas_instrumento', 'cod_pregunta'], ['cod_opcion', 'opciones_pregunta', 'cod_opcion']]],
        ] as [$entidad, $campos, $relaciones]) {
            $captura[$entidad->getTable()] = self::describirEntrada($esquema, $entidad, $campos, $relaciones);
        }

        return ['alcance' => 'ESTRUCTURA_DE_ENTRADAS_SIN_DATOS_CLINICOS', 'requiere_residentes' => false,
            'certifica_preparacion_clinica' => false, 'mapa_fuente_a_criterio' => ContratoFuentesPetersen::mapa(), 'soportes_admitidos' => $soportes, 'captura_original' => $captura];
    }

    /** Informa faltantes sin intentar crear, migrar o completar la estructura. */
    private static function describirEntrada(Builder $esquema, Model $modelo, array $campos, array $relaciones): array
    {
        $tabla = $modelo->getTable();
        $presente = $esquema->hasTable($tabla);
        $columnas = $presente ? $esquema->getColumnListing($tabla) : [];
        sort($columnas, SORT_STRING);
        $faltantes = array_values(array_diff([$modelo->getKeyName(), ...$campos], $columnas));
        $pk = $presente && collect($esquema->getIndexes($tabla))->contains(
            fn ($indice) => $indice['primary'] && $indice['columns'] === [$modelo->getKeyName()]);
        $fks = $presente ? $esquema->getForeignKeys($tabla) : [];
        $enlaces = [];
        foreach ($relaciones as [$campo, $destino, $clave]) {
            $verificada = collect($fks)->contains(static fn ($fk) => $fk['columns'] === [$campo]
                && $fk['foreign_table'] === $destino && $fk['foreign_columns'] === [$clave]
                && in_array($fk['foreign_schema'] ?? null, [null, 'main', 'public'], true));
            $enlaces[] = ['campo' => $campo, 'tabla_destino' => $destino, 'clave_destino' => $clave, 'verificada' => $verificada];
        }

        return ['tabla' => $tabla, 'clave_primaria' => $modelo->getKeyName(), 'tabla_presente' => $presente,
            'clave_primaria_verificada' => $pk, 'campos_existentes' => $columnas, 'campos_base_faltantes' => $faltantes,
            'relaciones' => $enlaces, 'estructura_verificada' => $presente && $pk && $faltantes === []
                && ! in_array(false, array_column($enlaces, 'verificada'), true)];
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
