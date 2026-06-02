<?php

namespace App\Support;

use App\Models\AdultoMayor;
use App\Models\AsignacionSaludAdulto;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ClinicalAccess
{
    public static function userCanManageAll(?User $user): bool
    {
        return $user !== null
            && ($user->hasAnyRole(['admin', 'super_admin']) || $user->can('asignaciones_clinicas.gestionar'));
    }

    public static function isHealthStaff(?User $user): bool
    {
        return $user !== null
            && ($user->hasRole('personal_salud') || $user->personalSalud()->exists());
    }

    public static function personalSaludId(?User $user): ?int
    {
        return $user?->personalSalud?->cod_per_sal;
    }

    public static function userHasClinicalAccess(?User $user, AdultoMayor $adultoMayor): bool
    {
        if (! $user) {
            return false;
        }

        if (self::userCanManageAll($user)) {
            return true;
        }

        if (self::isHealthStaff($user)) {
            return self::hasActiveAssignment($user, $adultoMayor);
        }

        return $user->can('adultos.ver_expediente') || $user->can('salud.ver');
    }

    public static function hasActiveAssignment(?User $user, AdultoMayor|string $adultoMayor): bool
    {
        if (! $user || ! Schema::hasTable('asignaciones_salud_adulto')) {
            return false;
        }

        $codPerSal = self::personalSaludId($user);

        if (! $codPerSal) {
            return false;
        }

        $codAm = $adultoMayor instanceof AdultoMayor ? $adultoMayor->cod_am : $adultoMayor;

        return AsignacionSaludAdulto::query()
            ->activa()
            ->where('cod_am', $codAm)
            ->where('cod_per_sal', $codPerSal)
            ->exists();
    }

    public static function scopeAdultos(Builder $query, ?User $user): Builder
    {
        if (! self::isHealthStaff($user) || self::userCanManageAll($user)) {
            return $query;
        }

        $codPerSal = self::personalSaludId($user);

        if (! $codPerSal || ! Schema::hasTable('asignaciones_salud_adulto')) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('asignacionesSalud', function (Builder $asignaciones) use ($codPerSal) {
            $asignaciones->activa()->where('cod_per_sal', $codPerSal);
        });
    }

    public static function visibleAdultCodes(?User $user): ?array
    {
        if (! self::isHealthStaff($user) || self::userCanManageAll($user)) {
            return null;
        }

        $codPerSal = self::personalSaludId($user);

        if (! $codPerSal || ! Schema::hasTable('asignaciones_salud_adulto')) {
            return [];
        }

        return AsignacionSaludAdulto::query()
            ->activa()
            ->where('cod_per_sal', $codPerSal)
            ->pluck('cod_am')
            ->unique()
            ->values()
            ->all();
    }

    public static function logDeniedAccess(?User $user, AdultoMayor $adultoMayor, string $context = 'datos clinicos'): void
    {
        if (! $user || app()->runningInConsole()) {
            return;
        }

        $request = request();
        $key = "clinical_denied_{$user->cod_usu}_{$adultoMayor->cod_am}_{$context}";

        if ($request->attributes->get($key)) {
            return;
        }

        $request->attributes->set($key, true);

        try {
            activity('Seguridad Clinica')
                ->causedBy($user)
                ->performedOn($adultoMayor)
                ->event('acceso_denegado')
                ->withProperties([
                    'cod_am' => $adultoMayor->cod_am,
                    'contexto' => $context,
                    'ruta' => $request->fullUrl(),
                ])
                ->log("Intento de acceso clinico sin asignacion activa al adulto mayor {$adultoMayor->cod_am}.");
        } catch (Throwable) {
            // La bitacora no debe interrumpir la denegacion de acceso.
        }
    }
}
