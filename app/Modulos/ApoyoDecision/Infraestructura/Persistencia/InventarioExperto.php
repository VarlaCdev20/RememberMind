<?php

namespace App\Modulos\ApoyoDecision\Infraestructura\Persistencia;

use App\Compartido\Infraestructura\Persistencia\ContextosResidente;

use App\Models\AplicacionInstrumento;
use App\Models\Atencion;
use App\Models\Instrumento;
use App\Models\OpcionPregunta;
use App\Models\PreguntaInstrumento;
use App\Models\Residente;
use App\Models\ResidenteContacto;
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
        'declaraciones_nodo',
        'relaciones_semanticas',
        'dominios_valores_expertos',
        'valores_semanticos',
        'fuentes_datos_expertas',
        'mapeos_variables_fuente',
        'mapeos_valores_fuente',
        'reglas_expertas',
        'componentes_regla_experta',
        'evaluaciones_expertas',
        'evidencias_evaluacion',
        'relaciones_evidencias_evaluacion',
        'evaluacion_criterios',
        'evaluaciones_reglas',
        'vinculos_evidencia_experta',
    ];

    public const CONSERVACION = [
        'hechos_instantanea_experta',
        'motivos_dcl_instantanea',
        'reglas_aplicadas_dcl_instantanea',
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
            $relacionesSoporte[] = [$fk, $entidad->getTable(), match ($fk) {
                'cod_antecedente', 'cod_diagnostico' => $fk.'_ref',
                'cod_valoracion_psicologica' => $fk,
                'cod_documento_clinico', 'cod_control_cognitivo', 'cod_control_iadl' => $fk,
                default => $entidad->getKeyName(),
            }];
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
            'certifica_preparacion_clinica' => false, 'mapa_fuente_a_criterio' => ContratoFuentesPetersen::mapa(),
            'contexto_evaluacion' => self::contextoEvaluacion($esquema), 'soportes_admitidos' => $soportes, 'captura_original' => $captura];
    }

    /** Contratos de contexto separados de la existencia física y de los datos de cada caso. */
    private static function contextoEvaluacion(Builder $esquema): array
    {
        $atencion = self::describirEntrada($esquema, new Atencion,
            ['cod_residente', 'cod_personal', 'fecha_hora', ...Atencion::CONTEXTO_ESCOLARIDAD],
            [['cod_residente', 'residentes', 'cod_residente'], ['cod_personal', 'personal', 'cod_personal']]);
        $aplicacion = self::describirEntrada($esquema, new AplicacionInstrumento,
            ['cod_residente', 'cod_atencion'], [['cod_residente', 'residentes', 'cod_residente'], ['cod_atencion', 'atenciones', 'cod_atencion']]);
        $vinculo = self::describirEntrada($esquema, new ResidenteContacto,
            ['cod_residente', 'cod_contacto', 'parentesco', 'estado'],
            [['cod_residente', 'residentes', 'cod_residente'], ['cod_contacto', 'contactos', 'cod_contacto']]);
        $contexto = self::describirEntrada($esquema, new Atencion,
            ['cod_residente', ...Atencion::CONTEXTO_IDIOMA_INFORMANTE],
            [['cod_residente_contacto_informante', 'residentes_contactos', 'cod_residente_contacto']]);

        return ['alcance' => 'ESTRUCTURA_Y_CONTRATO_DE_CONTEXTO_SIN_DATOS_DEL_CASO',
            'datos_clinicos_consultados' => false, 'certifica_4fn_global' => false,
            'escolaridad' => ['fuente_por_episodio' => $atencion, 'vinculo_con_aplicacion' => $aplicacion,
                'estructura_de_referencia_verificada' => $atencion['estructura_verificada'] && $aplicacion['estructura_verificada'],
                'permiso_lectura_fuente' => 'atenciones.ver', 'se_copia_a_respuestas' => false, 'acredita_aplicabilidad_instrumental' => false],
            'idioma' => ['contrato_de_captura_episodica_definido' => true,
                'cardinalidad_estructurada' => 'UN_IDIOMA_PRINCIPAL_OPCIONAL', 'fuente_por_episodio' => $contexto,
                'idioma_del_material_acredita_idioma_del_caso' => false,
                'uso_automatico_en_inferencia' => false,
                'limite' => 'Otros idiomas e intérpretes se describen en notas para lectura profesional; no se extraen hechos automáticamente.'],
            'informantes' => ['vinculo_residente_contacto' => $vinculo, 'vinculo_maestro_acredita_participacion' => false,
                'vinculo_maestro_acredita_conocimiento_previo' => false, 'responsable_principal_se_elige_por_defecto' => false,
                'fuente_por_episodio' => $contexto, 'cardinalidad_estructurada' => 'UN_INFORMANTE_PRINCIPAL_OPCIONAL',
                'participacion_requiere_seleccion_explicita' => true, 'uso_automatico_en_inferencia' => false,
                'permisos_lectura_identidad' => ['atenciones.ver', 'contactos.ver', 'residentes_contactos.ver'],
                'limite' => 'La identidad se conserva mediante la FK del episodio. Otras fuentes y discrepancias siguen siendo relato profesional, no participantes estructurados.'],
            'limites' => ['La presencia de columnas no demuestra datos registrados ni preparación clínica.',
                'No se infiere idioma, alfabetización, informante o normalidad desde categorías, parentesco o notas.',
                'No se convierten listas de idiomas o informantes en columnas repetidas, JSON/EAV ni valores de una FK.',
                'Las notas y soportes clínicos conservan su propio grano y contrato; no sustituyen una relación de participantes.']];
    }

    /** Informa faltantes sin intentar crear, migrar o completar la estructura. */
    private static function describirEntrada(Builder $esquema, Model $modelo, array $campos, array $relaciones): array
    {
        $tabla = $modelo->getTable();
        $captura = $modelo instanceof RespuestaInstrumento && $esquema->hasTable('capturas_instrumento');
        $fisica = $captura ? 'capturas_instrumento' : $tabla;
        $presente = $captura ? self::proyeccionRespuestasDisponible($esquema) : $esquema->hasTable($tabla);
        $columnas = $presente ? $esquema->getColumnListing($tabla) : [];
        sort($columnas, SORT_STRING);
        $contextualizado = isset(ContextosResidente::CONTRATOS[$tabla]);
        $contextoDisponible = $contextualizado && ContextosResidente::estructuraDisponible($esquema->getConnection(), $tabla);
        $derivados = $contextoDisponible ? ['cod_residente'] : [];
        if ($captura && $presente) $derivados[] = 'cod_pregunta';
        $faltantes = array_values(array_diff([$modelo->getKeyName(), ...$campos], [...$columnas, ...$derivados]));
        $pk = $presente && collect($esquema->getIndexes($fisica))->contains(
            fn ($indice) => $indice['primary'] && $indice['columns'] === [$modelo->getKeyName()]);
        $fks = $presente ? $esquema->getForeignKeys($fisica) : [];
        $enlaces = [];
        foreach ($relaciones as [$campo, $destino, $clave]) {
            $origen = $captura && $campo === 'cod_pregunta' ? 'cod_pregunta_libre' : $campo;
            $verificada = collect($fks)->contains(static fn ($fk) => $fk['columns'] === [$origen]
                && $fk['foreign_table'] === $destino && $fk['foreign_columns'] === [$clave]
                && in_array($fk['foreign_schema'] ?? null, [null, 'main', 'public'], true));
            if ($captura && $campo === 'cod_pregunta') {
                $verificada = $verificada && collect($esquema->getForeignKeys('opciones_pregunta'))->contains(
                    fn ($fk) => $fk['columns'] === ['cod_pregunta'] && $fk['foreign_table'] === $destino
                        && $fk['foreign_columns'] === [$clave] && in_array($fk['foreign_schema'] ?? null, [null, 'main', 'public'], true));
            }
            if ($contextualizado && $campo === 'cod_residente') $verificada = $contextoDisponible;
            $enlaces[] = ['campo' => $campo, 'tabla_destino' => $destino, 'clave_destino' => $clave, 'verificada' => $verificada]
                + ($captura && $campo === 'cod_pregunta' ? ['tipo_vinculo'=>'PREGUNTA_LIBRE_O_DERIVADA_DE_OPCION','fk_directa_en_tabla'=>false] : [])
                + ($contextualizado && $campo === 'cod_residente' ? ['tipo_vinculo' => 'DERIVADO_DE_ATENCION_O_ASOCIACION_DIRECTA', 'fk_directa_en_tabla' => false] : []);
        }

        return ['tabla' => $tabla, 'clave_primaria' => $modelo->getKeyName(), 'tabla_presente' => $presente,
            'clave_primaria_verificada' => $pk, 'campos_existentes' => $columnas, 'campos_derivados_disponibles' => $derivados, 'campos_base_faltantes' => $faltantes,
            'relaciones' => $enlaces, 'estructura_verificada' => $presente && $pk && $faltantes === []
                && ! in_array(false, array_column($enlaces, 'verificada'), true)];
    }

    /** Verifica la consulta canónica sin ejecutar la vista ni leer respuestas de residentes. */
    private static function proyeccionRespuestasDisponible(Builder $esquema): bool
    {
        $db = $esquema->getConnection();
        $pg = $db->getDriverName() === 'pgsql';
        $ddl = $pg
            ? $db->scalar("SELECT pg_get_viewdef(c.oid,true) FROM pg_class c JOIN pg_namespace n ON n.oid=c.relnamespace WHERE c.relkind='v' AND n.nspname='public' AND c.relname='respuestas_instrumento'")
            : $db->scalar("SELECT sql FROM sqlite_master WHERE type='view' AND name='respuestas_instrumento'");
        if (!$ddl) return false;

        $fuente = $pg ? '' : 'CREATE VIEW respuestas_instrumento AS ';
        $e = $pg ? '' : 'e.';
        $campos = ['cod_respuesta','cod_aplicacion','cod_pregunta','cod_opcion','valor_numero','valor_texto','valor_logico','puntaje','observacion'];
        $proyeccion = array_map(fn ($campo) => $campo === 'cod_pregunta'
            ? "COALESCE({$e}cod_pregunta_libre,(SELECT p.cod_pregunta FROM opciones_pregunta p WHERE p.cod_opcion=e.cod_opcion)) AS cod_pregunta"
            : $e.$campo, $campos);
        $esperado = $fuente.'SELECT '.implode(',', $proyeccion).' FROM capturas_instrumento e';
        $firma = static fn ($sql) => strtolower(preg_replace('/\s+|::text|;/', '', $sql));
        return $firma($ddl) === $firma($esperado);
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
