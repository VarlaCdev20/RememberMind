<?php

namespace App\Policies;

use App\Models\AdministracionMedicacion;
use App\Models\User;

class AdministracionMedicacionPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['ENFERMEROS', 'MEDICO GENERAL/GERIATRA'])
            && $user->can('administraciones_medicacion.crear');
    }

    public function view(User $user, AdministracionMedicacion $registro): bool
    {
        return $user->can('administraciones_medicacion.ver');
    }
}
