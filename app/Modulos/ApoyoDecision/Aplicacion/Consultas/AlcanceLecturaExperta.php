<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/** Publicación técnica aislada; nunca habilita conocimiento clínico. */
final class AlcanceLecturaExperta
{
    public static function habilitada(): bool
    {
        if (! app()->environment('testing') || config('sistema_experto.lectura_tecnica_pruebas.habilitada') !== true) {
            return false;
        }
        $c = DB::connection();

        return BaseTecnicaDesechable::permite($c->getDriverName(), $c->getDatabaseName(), $c->getConfig('username'), $c->getConfig('host'));
    }

    public static function limitar(Builder $query): Builder
    {
        if (self::habilitada()) {
            return $query->where('cod_version_modelo', 'VER_TEST')
                ->where('origen_activacion', 'PRUEBA_TECNICA')->where('estado_ejecucion', 'PRUEBA_FINALIZADA');
        }

        return $query->whereNotLike('estado_ejecucion', 'PRUEBA%')->whereNotLike('origen_activacion', 'PRUEBA%');
    }

    public static function contrato(string $version): array
    {
        if (self::habilitada() && $version === 'VER_TEST') {
            return ['criterios' => ['COG-MEM'], 'estados_ejecucion' => ['PRUEBA_FINALIZADA'],
                'componentes_memoria' => ['MAP_INS'], 'estados_inferencia' => ['PRUEBA_FINALIZADA'],
                'roles_soporte' => ['INSTRUMENTAL'], 'estados_participacion_soporte' => ['PRUEBA_TECNICA']];
        }

        return config('sistema_experto.lectura_clinica', [])[$version] ?? [];
    }

    public static function informe(?string $evaluacion, string $residente): ?array
    {
        if (! self::habilitada()) {
            return null;
        }
        $informe = config('sistema_experto.lectura_tecnica_pruebas.informe');
        if (! is_array($informe) || ($informe['evaluacion'] ?? null) !== $evaluacion
            || ($informe['residente'] ?? null) !== $residente || ($informe['version'] ?? null) !== 'VER_TEST') {
            return null;
        }

        return $informe;
    }
}
