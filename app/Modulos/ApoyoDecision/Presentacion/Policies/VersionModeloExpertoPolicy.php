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
}
