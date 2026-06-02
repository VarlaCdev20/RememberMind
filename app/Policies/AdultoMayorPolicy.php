<?php

namespace App\Policies;

use App\Models\AdultoMayor;
use App\Models\User;
use App\Support\ClinicalAccess;

class AdultoMayorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('adultos.ver') || $user->can('salud.ver');
    }

    public function view(User $user, AdultoMayor $adultoMayor): bool
    {
        return $this->viewClinicalData($user, $adultoMayor);
    }

    public function viewClinicalData(User $user, AdultoMayor $adultoMayor): bool
    {
        $allowed = ClinicalAccess::userHasClinicalAccess($user, $adultoMayor);

        if (! $allowed && ClinicalAccess::isHealthStaff($user)) {
            ClinicalAccess::logDeniedAccess($user, $adultoMayor);
        }

        return $allowed;
    }

    public function manageClinicalData(User $user, AdultoMayor $adultoMayor): bool
    {
        return $this->viewClinicalData($user, $adultoMayor);
    }
}
