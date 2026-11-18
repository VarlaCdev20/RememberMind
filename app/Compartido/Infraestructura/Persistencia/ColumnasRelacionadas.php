<?php

namespace App\Compartido\Infraestructura\Persistencia;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Validation\ValidationException;

/** Lecturas calculadas a partir de las relaciones canónicas, sin duplicar su almacenamiento. */
final class ColumnasRelacionadas
{
    public const CONTRATOS = [
        'prescripciones' => ['cod_residente', 'cod_atencion'],
        'estudios_clinicos' => ['cod_residente', 'cod_atencion'],
        'valoraciones_profesionales' => ['cod_residente', 'cod_atencion'],
        'administraciones_medicacion' => ['cod_residente', 'cod_prescripcion'],
        'relaciones_semanticas' => ['cod_version_modelo', 'cod_nodo_origen'],
        'mapeos_variables_fuente' => ['cod_version_modelo', 'cod_variable_experta'],
    ];

    public static function origen(Connection $db, string $tabla): Builder
    {
        return match ($tabla) {
            'prescripciones', 'estudios_clinicos', 'valoraciones_profesionales' => $db->table('atenciones')
                ->select(['cod_atencion as clave_relacion', 'cod_residente']),
            'administraciones_medicacion' => $db->table('prescripciones as p')
                ->join('atenciones as a', 'a.cod_atencion', '=', 'p.cod_atencion')
                ->select(['p.cod_prescripcion as clave_relacion', 'a.cod_residente']),
            'relaciones_semanticas' => $db->table('nodos_semanticos')
                ->select(['cod_nodo_semantico as clave_relacion', 'cod_version_modelo']),
            'mapeos_variables_fuente' => $db->table('nodos_semanticos')->whereNotNull('cod_variable_experta')
                ->select(['cod_variable_experta as clave_relacion', 'cod_version_modelo']),
        };
    }

    public static function unir(Builder $consulta, string $tabla): void
    {
        [$campo, $clave] = self::CONTRATOS[$tabla];
        $alias = 'relacion_'.$tabla;
        $consulta->joinSub(self::origen($consulta->getConnection(), $tabla), $alias,
            $alias.'.clave_relacion', '=', $tabla.'.'.$clave);
        if ($consulta->columns === null) {
            $consulta->select([$tabla.'.*', $alias.'.'.$campo]);
        }
    }

    public static function consulta(Connection $db, string $tabla, ?string $alias = null): Builder
    {
        $origen = $db->table($tabla);
        self::unir($origen, $tabla);

        return $db->query()->fromSub($origen, $alias ?? $tabla);
    }

    public static function valor(Connection $db, string $tabla, array $datos): ?string
    {
        [$campo, $clave] = self::CONTRATOS[$tabla];

        return self::origen($db, $tabla)->select($campo)->where($clave === 'cod_prescripcion' ? 'p.cod_prescripcion'
            : match ($tabla) {
                'prescripciones', 'estudios_clinicos', 'valoraciones_profesionales' => 'cod_atencion',
                'relaciones_semanticas' => 'cod_nodo_semantico',
                'mapeos_variables_fuente' => 'cod_variable_experta',
            }, $datos[$clave] ?? null)->value($campo);
    }

    public static function preparar(Connection $db, string $tabla, array $datos): array
    {
        if (! isset(self::CONTRATOS[$tabla])) {
            return $datos;
        }
        [$campo, $clave] = self::CONTRATOS[$tabla];
        if (isset($datos[$campo], $datos[$clave]) && self::valor($db, $tabla, $datos) !== $datos[$campo]) {
            throw ValidationException::withMessages([$clave => 'El vínculo indicado no corresponde al contexto del registro.']);
        }
        unset($datos[$campo]);

        return $datos;
    }
}
