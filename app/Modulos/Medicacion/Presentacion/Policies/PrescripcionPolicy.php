<?php

namespace App\Modulos\Medicacion\Presentacion\Policies;

use App\Models\Prescripcion;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Acciones\AccesoClinicoTemporalService;

class PrescripcionPolicy
{
    public function view(User $user, Prescripcion $prescripcion): bool
    {
        return $user->can('prescripciones.ver');
    }

    public function create(User $user): bool
    {
        return app(AccesoClinicoTemporalService::class)->tieneRol($user, ['MEDICO GENERAL/GERIATRA'])
            && $user->can('prescripciones.crear');
    }

    public function update(User $user, Prescripcion $prescripcion): bool
    {
        return $this->create($user);
    }
}
