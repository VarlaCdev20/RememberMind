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
        'consentimientos' => ['cod_residente', 'cod_admision'],
        'ejecuciones_cuidado' => ['cod_residente', 'cod_intervencion'],
        'administraciones_medicacion' => ['cod_residente', 'cod_prescripcion'],
        'relaciones_semanticas' => ['cod_version_modelo', 'cod_nodo_origen'],
        'mapeos_variables_fuente' => ['cod_version_modelo', 'cod_variable_experta'],
        'mediciones_clinicas' => ['cod_residente', 'cod_registro_medicion'],
        'registros_observaciones_cuidado' => ['cod_residente', 'cod_observacion_cuidado'],
    ];

    public static function origen(Connection $db, string $tabla): Builder
    {
        if (isset(ContextosResidente::CONTRATOS[$tabla])) {
            return ContextosResidente::origen($db, $tabla);
        }

        return match ($tabla) {
            'consentimientos' => $db->table('admisiones')->select(['cod_admision as clave_relacion', 'cod_residente']),
            'ejecuciones_cuidado' => $db->table('intervenciones_cuidado as i')->join('planes_cuidado as p', 'p.cod_plan', '=', 'i.cod_plan')
                ->select(['i.cod_intervencion as clave_relacion', 'p.cod_residente']),
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
        if (isset(ContextosResidente::CONTRATOS[$tabla])) {
            return ContextosResidente::valor($db, $tabla, $datos);
        }
        [$campo, $clave] = self::CONTRATOS[$tabla];

        return self::origen($db, $tabla)->select($campo)->where($clave === 'cod_prescripcion' ? 'p.cod_prescripcion'
            : match ($tabla) {
                'prescripciones', 'estudios_clinicos', 'valoraciones_profesionales' => 'cod_atencion',
                'consentimientos' => 'cod_admision',
                'ejecuciones_cuidado' => 'i.cod_intervencion',
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

    /** Fija el vínculo antes de contrastar el residente explícito de una escritura. */
    public static function bloquearEntrada(Connection $db, string $tabla, array $filas): void
    {
        if ($tabla === 'consentimientos') {
            $db->table('admisiones')->whereIn('cod_admision', array_values(array_unique(array_filter(array_column($filas, 'cod_admision')))))
                ->orderBy('cod_admision')->sharedLock()->get(['cod_admision']);
        } elseif ($tabla === 'ejecuciones_cuidado') {
            $intervenciones = $db->table('intervenciones_cuidado')
                ->whereIn('cod_intervencion', array_values(array_unique(array_filter(array_column($filas, 'cod_intervencion')))))
                ->orderBy('cod_intervencion')->sharedLock()->get(['cod_plan']);
            $db->table('planes_cuidado')->whereIn('cod_plan', $intervenciones->pluck('cod_plan')->unique()->all())
                ->orderBy('cod_plan')->sharedLock()->get(['cod_plan']);
        }
    }
}
