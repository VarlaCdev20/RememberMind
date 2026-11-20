<?php

declare(strict_types=1);

namespace App\Compartido\Infraestructura\Persistencia;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\ValidationException;

/** Sujeto original de un registro con atención opcional, conservado una sola vez. */
final class ContextosResidente
{
    public const CONTRATOS = [
        'mediciones_clinicas' => ['cod_registro_medicion', 'residentes_mediciones_clinicas'],
        'registros_observaciones_cuidado' => ['cod_observacion_cuidado', 'residentes_observaciones_cuidado'],
        'aplicaciones_instrumento' => ['cod_aplicacion', 'residentes_aplicaciones_instrumento'],
        'controles_cognitivos' => ['cod_control_cognitivo', 'residentes_controles_cognitivos'],
        'controles_funcionales_iadl' => ['cod_control_iadl', 'residentes_controles_funcionales_iadl'],
        'derivaciones' => ['cod_derivacion', 'residentes_derivaciones'],
        'documentos' => ['cod_documento', 'residentes_documentos'],
        'registros_clinicos' => ['cod_registro_clinico', 'residentes_registros_clinicos'],
        'registros_textuales_clinicos' => ['cod_registro_textual', 'residentes_registros_textuales_clinicos'],
        'valoraciones_psicopedagogicas' => ['cod_valoracion_profesional', 'residentes_valoraciones_psicopedagogicas'],
        'valoraciones_petersen_originales' => ['cod_valoracion_petersen', 'residentes_valoraciones_petersen'],
    ];

    /** Inspección de las dos rutas físicas, sin consultar residentes ni atribuir disponibilidad clínica. */
    public static function estructuraDisponible(Connection $db, string $tabla): bool
    {
        if (! isset(self::CONTRATOS[$tabla])) {
            return false;
        }
        [$pk,$directa] = self::CONTRATOS[$tabla];
        $schema = $db->getSchemaBuilder();
        if (! $schema->hasTable($tabla) || ! $schema->hasColumns($tabla, [$pk, 'cod_atencion', 'cod_contexto_directo'])
            || ! $schema->hasTable($directa) || ! $schema->hasColumns($directa, [$pk, 'cod_residente'])
            || ! $schema->hasTable('atenciones') || ! $schema->hasColumns('atenciones', ['cod_atencion', 'cod_residente'])) {
            return false;
        }
        $enlace = static fn (string $origen, string $campo, string $destino, string $clave): bool => collect($schema->getForeignKeys($origen))
            ->contains(fn ($f) => $f['columns'] === [$campo] && $f['foreign_table'] === $destino && $f['foreign_columns'] === [$clave]
                && in_array($f['foreign_schema'] ?? null, [null, 'main', 'public'], true));
        $generada = collect($schema->getColumns($tabla))->firstWhere('name', 'cod_contexto_directo');
        $identidad = collect($schema->getIndexes($directa))->contains(fn ($i) => $i['primary'] && $i['columns'] === [$pk]);
        $unica = collect($schema->getIndexes($tabla))->contains(fn ($i) => $i['unique'] && $i['columns'] === ['cod_contexto_directo']);

        return $identidad && $unica && ContratosContextoResidente20261120::aliasCanonico($generada['generation'] ?? null, $pk)
            && ContratosContextoResidente20261120::guardasInstaladas($db, $tabla)
            && $enlace($tabla, 'cod_atencion', 'atenciones', 'cod_atencion')
            && $enlace('atenciones', 'cod_residente', 'residentes', 'cod_residente')
            && $enlace($directa, $pk, $tabla, 'cod_contexto_directo')
            && $enlace($directa, 'cod_residente', 'residentes', 'cod_residente');
    }

    public static function origen(Connection $db, string $tabla): Builder
    {
        [$pk, $directa] = self::CONTRATOS[$tabla];

        return $db->table($tabla.' as contexto')
            ->leftJoin('atenciones as episodio', 'episodio.cod_atencion', '=', 'contexto.cod_atencion')
            ->leftJoin($directa.' as sujeto', 'sujeto.'.$pk, '=', 'contexto.'.$pk)
            ->select('contexto.'.$pk.' as clave_relacion')
            ->selectRaw('COALESCE(episodio.cod_residente,sujeto.cod_residente) AS cod_residente');
    }

    public static function valor(Connection $db, string $tabla, array $datos): ?string
    {
        [$pk, $directa] = self::CONTRATOS[$tabla];
        if (isset($datos['cod_atencion'])) {
            return $db->table('atenciones')->where('cod_atencion', $datos['cod_atencion'])->value('cod_residente');
        }
        if (! array_key_exists('cod_atencion', $datos) && isset($datos[$pk])) {
            $valor = $db->query()->fromSub(self::origen($db, $tabla), 'contexto_original')
                ->where('contexto_original.clave_relacion', $datos[$pk])->value('contexto_original.cod_residente');
            if ($valor !== null) {
                return $valor;
            }
        }

        return $db->table($directa)->where($pk, $datos[$pk] ?? null)->value('cod_residente');
    }

    /** La asociación directa antecede al registro dentro de la misma transacción diferida. */
    public static function guardar(Model $registro, callable $persistir): bool
    {
        $tabla = $registro->getTable();
        if (! isset(self::CONTRATOS[$tabla])) {
            return $persistir();
        }

        return $registro->getConnection()->transaction(function () use ($registro, $persistir, $tabla): bool {
            $db = $registro->getConnection();
            // Alias técnico calculado por SQL: nunca se copia como dato al rectificar o replicar.
            unset($registro['cod_contexto_directo']);
            if (! $registro->exists && ! $registro->getKey()) {
                $registro->setAttribute($registro->getKeyName(), app(GeneradorCodigo::class)
                    ->siguiente($registro->entidadCodigo(), $registro->getConnectionName()));
            }
            if (! $registro->exists && ! array_key_exists('cod_atencion', $registro->getAttributes())) {
                $registro->setAttribute('cod_atencion', null);
            }
            if ($registro->exists && ($registro->getRawOriginal('cod_atencion') !== ($registro->getAttributes()['cod_atencion'] ?? null))) {
                throw ValidationException::withMessages(['cod_atencion' => 'El episodio original se conserva; registra una corrección enlazada.']);
            }
            $datos = $registro->getAttributes();
            if ($registro->exists) {
                self::bloquearAtencion($db, $datos);
                self::contrastar($db, $tabla, $datos);
            } else {
                self::prepararRelacion($db, $tabla, $datos);
            }

            $guardado = $persistir();
            if (! $guardado) {
                throw new \RuntimeException('No se pudo conservar el registro con su contexto original.');
            }

            return $guardado;
        });
    }

    /** Entrada técnica de los seeders; nunca asigna un residente por defecto. */
    public static function insertar(Connection $db, string $tabla, array $filas): void
    {
        $db->transaction(function () use ($db, $tabla, $filas): void {
            $preparadas = [];
            foreach ($filas as $fila) {
                self::prepararRelacion($db, $tabla, $fila);
                unset($fila['cod_residente'], $fila['cod_contexto_directo']);
                $preparadas[] = $fila;
            }
            if ($preparadas !== []) {
                $db->table($tabla)->insert($preparadas);
            }
        });
    }

    private static function prepararRelacion(Connection $db, string $tabla, array $datos): void
    {
        [$pk, $directa] = self::CONTRATOS[$tabla];
        if (! isset($datos[$pk])) {
            throw ValidationException::withMessages([$pk => 'El registro requiere su identidad original antes de asociar el residente.']);
        }
        self::bloquearAtencion($db, $datos);
        if (isset($datos['cod_atencion'])) {
            self::contrastar($db, $tabla, $datos);
        } else {
            if (self::documentoSinSujeto($tabla, $datos)) {
                return;
            }
            if (! isset($datos['cod_residente']) || ! is_string($datos['cod_residente']) || $datos['cod_residente'] === '') {
                throw ValidationException::withMessages(['cod_residente' => 'Selecciona el residente del registro.']);
            }
            $db->table($directa)->insert([$pk => $datos[$pk], 'cod_residente' => $datos['cod_residente']]);
        }
    }

    private static function contrastar(Connection $db, string $tabla, array $datos): void
    {
        $canonica = self::valor($db, $tabla, $datos);
        if (($canonica === null && ! self::documentoSinSujeto($tabla, $datos)) || (isset($datos['cod_residente']) && $canonica !== $datos['cod_residente'])) {
            throw ValidationException::withMessages(['cod_residente' => 'El residente no corresponde al contexto original del registro.']);
        }
    }

    private static function documentoSinSujeto(string $tabla, array $datos): bool
    {
        return $tabla === 'documentos' && ($datos['tipo_evento'] ?? null) === 'ADMINISTRATIVO'
            && ($datos['cod_atencion'] ?? null) === null && ($datos['cod_residente'] ?? null) === null;
    }

    private static function bloquearAtencion(Connection $db, array $datos): void
    {
        if (isset($datos['cod_atencion'])) {
            $db->table('atenciones')->where('cod_atencion', $datos['cod_atencion'])->sharedLock()->first(['cod_atencion']);
        }
    }
}
