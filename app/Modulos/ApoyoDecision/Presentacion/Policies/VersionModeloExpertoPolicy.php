<?php

namespace App\Modulos\ApoyoDecision\Presentacion\Policies;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;

final class VersionModeloExpertoPolicy
{
    /** Lectura técnica del conocimiento; esta habilidad no coincide con el bypass global view/*.ver. */
    public function consultar(User $user): bool
    {
        return $user->estado === 'ACTIVO' && $user->hasRole('SUPERADMINISTRADOR')
            && $user->checkPermissionTo('auditoria.ver', 'web')
            && session(RolePreviewService::SESSION_KEY) === null;
    }

    public function consultarGobierno(User $user): bool
    {
        return $user->estado === 'ACTIVO' && session(RolePreviewService::SESSION_KEY) === null
            && $user->checkPermissionTo('reglas_expertas.ver', 'web')
            && ($user->hasRole('SUPERADMINISTRADOR') || $this->profesional($user));
    }

    public function gestionarReglas(User $user): bool
    {
        return $this->consultarGobierno($user) && $this->profesional($user)
            && $user->checkPermissionTo('reglas_expertas.gestionar', 'web');
    }

    public function revisarReglas(User $user): bool
    {
        return $this->consultarGobierno($user) && $this->profesional($user)
            && $user->checkPermissionTo('reglas_expertas.revisar', 'web');
    }

    private function profesional(User $user): bool
    {
        foreach (['MEDICO GENERAL/GERIATRA' => 'MEDICINA', 'PSICOLOGO/A' => 'PSICOLOGIA'] as $role => $profession) {
            if ($user->hasRole($role) && $user->personal()->where('estado', 'ACTIVO')->where('profesion', $profession)->exists()) {
                return true;
            }
        }

        return false;
    }
}
