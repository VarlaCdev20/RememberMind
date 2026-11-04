<?php

namespace App\Modulos\ApoyoDecision\Aplicacion\Consultas;

use App\Modulos\ApoyoDecision\Dominio\Reglas\BaseTecnicaDesechable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/** Separa la consulta operativa del laboratorio y respeta el contrato de cada versión. */
final class AlcanceLecturaExperta
{
    /** Habilita lectura técnica solo dentro del entorno y la base desechables aprobados. */
    public static function habilitada(): bool
    {
        if (! app()->environment('testing') || config('sistema_experto.lectura_tecnica_pruebas.habilitada') !== true) {
            return false;
        }
        $c = DB::connection();

        return BaseTecnicaDesechable::permite($c->getDriverName(), $c->getDatabaseName(), $c->getConfig('username'), $c->getConfig('host'));
    }

    /** Excluye ejecuciones experimentales de la consulta asistencial sin cambiar su estado original. */
    public static function limitar(Builder $query): Builder
    {
        // Los hallazgos observacionales tienen su propio lector; no sustituyen el perfil cognitivo histórico.
        $query->where('origen_activacion', '<>', 'OBSERVACIONAL');
        foreach (['estado_ejecucion', 'origen_activacion'] as $campo) {
            foreach (['PRUEBA%', '%TEST%', '%EXPERIMENTAL%', '%SINTETIC%', '%ARTIFICIAL%', 'SIM-%', 'SIMUL%'] as $patron) {
                $query->whereRaw('UPPER('.$campo.') NOT LIKE ?', [$patron]);
            }
        }
        $query->where(fn ($q) => $q->whereNull('id_evaluacion_dominio')->orWhereRaw('UPPER(id_evaluacion_dominio) NOT LIKE ?', ['SIM-%']));

        return $query->whereHas('version', static function ($q): void {
            foreach (['SIM-%', 'SIMUL%', '%TEST%', '%ARTIFICIAL%', '%SINTETIC%', '%EXPERIMENTAL%', 'G09-EXP%'] as $patron) {
                $q->whereRaw('UPPER(codigo_version) NOT LIKE ?', [$patron]);
            }
        });
    }

    /** Recupera el contrato de la versión histórica, sin adoptar el de otra versión. */
    public static function contrato(string $version): array
    {
        return config('sistema_experto.lectura_clinica', [])[$version] ?? [];
    }

    /** Limita el artefacto técnico a su residente, evaluación y versión artificiales. */
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
