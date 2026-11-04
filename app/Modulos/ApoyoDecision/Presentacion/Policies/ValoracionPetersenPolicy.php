<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Policies;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use Illuminate\Support\Facades\Gate;

/** Competencias aprobadas: Medicina/Psicología escriben sus propias cadenas; Superadministración consulta. */
final class ValoracionPetersenPolicy
{
    public function consultarOriginales(User $user, Residente $residente): bool
    {
        return Gate::forUser($user)->allows('consultarFuentesParaResultado', [EvaluacionExperta::class, $residente])
            || ($user->checkPermissionTo('valoraciones_petersen.ver', 'web')
            && $user->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'SUPERADMINISTRADOR'])
            && Gate::forUser($user)->allows('consultarResultados', [EvaluacionExperta::class, $residente]));
    }

    public function crearOriginal(User $user, Residente $residente): bool
    {
        return $this->competente($user, $residente, 'valoraciones_petersen.crear');
    }

    public function rectificarOriginal(User $user, ValoracionPetersenOriginal $valoracion): bool
    {
        return $this->competente($user, $valoracion->residente, 'valoraciones_petersen.rectificar')
            && $user->personal()->whereKey($valoracion->cod_personal)->where('estado', 'ACTIVO')->exists();
    }

    private function competente(User $user, Residente $residente, string $permiso): bool
    {
        $profesiones = [];
        if ($user->hasRole('MEDICO GENERAL/GERIATRA')) {
            $profesiones[] = 'MEDICINA';
        }
        if ($user->hasRole('PSICOLOGO/A')) {
            $profesiones[] = 'PSICOLOGIA';
        }

        return $residente->estado === 'ADMITIDO' && $profesiones !== []
            && $user->checkPermissionTo($permiso, 'web') && $this->consultarOriginales($user, $residente)
            && $user->personal()->where('estado', 'ACTIVO')->whereIn('profesion', $profesiones)->exists();
    }
}
