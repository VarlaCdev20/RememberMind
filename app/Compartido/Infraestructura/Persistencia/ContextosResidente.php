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
    ];

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
                unset($fila['cod_residente']);
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
            if (! isset($datos['cod_residente']) || ! is_string($datos['cod_residente']) || $datos['cod_residente'] === '') {
                throw ValidationException::withMessages(['cod_residente' => 'Selecciona el residente del registro.']);
            }
            $db->table($directa)->insert([$pk => $datos[$pk], 'cod_residente' => $datos['cod_residente']]);
        }
    }

    private static function contrastar(Connection $db, string $tabla, array $datos): void
    {
        $canonica = self::valor($db, $tabla, $datos);
        if ($canonica === null || (isset($datos['cod_residente']) && $canonica !== $datos['cod_residente'])) {
            throw ValidationException::withMessages(['cod_residente' => 'El residente no corresponde al contexto original del registro.']);
        }
    }

    private static function bloquearAtencion(Connection $db, array $datos): void
    {
        if (isset($datos['cod_atencion'])) {
            $db->table('atenciones')->where('cod_atencion', $datos['cod_atencion'])->sharedLock()->first(['cod_atencion']);
        }
    }
}
