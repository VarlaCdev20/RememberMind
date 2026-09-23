<?php

namespace App\Policies;

use App\Models\AdministracionMedicacion;
use App\Models\User;
use App\Services\Enfermeria\MiTurnoService;

class AdministracionMedicacionPolicy
{
    public function create(User $user): bool
    {
        if (! $user->hasAnyRole(['ENFERMEROS', 'MEDICO GENERAL/GERIATRA'])) {
            return false;
        }

        if (! $user->can('administraciones_medicacion.crear')) {
            return false;
        }

        if ($user->hasRole('ENFERMEROS')) {
            $personal = $user->personal;
            if (! $personal) {
                return false;
            }

            $jornada = app(MiTurnoService::class)->resolverJornadaActual($personal);
            if (! $jornada) {
                return false;
            }
        }

        return true;
    }

    public function view(User $user, AdministracionMedicacion $registro): bool
    {
        return $user->can('administraciones_medicacion.ver');
    }
}
