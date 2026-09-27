<?php

namespace App\Policies;

use App\Models\Preadmision;
use App\Models\User;
use App\Models\ValoracionEnfermeriaPreadmision;

class ValoracionEnfermeriaPreadmisionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('valoracion_enfermeria.ver');
    }

    public function view(User $user, ValoracionEnfermeriaPreadmision $valoracion): bool
    {
        return $user->can('valoracion_enfermeria.ver');
    }

    public function create(User $user, ?Preadmision $preadmision = null): bool
    {
        if ($user->estado !== 'ACTIVO') {
            return false;
        }

        if (! $user->hasRole('ENFERMEROS')) {
            return false;
        }

        if (! $user->can('valoracion_enfermeria.registrar')) {
            return false;
        }

        $personal = $user->personal;
        if (! $personal || $personal->estado !== 'ACTIVO') {
            return false;
        }

        if ($preadmision !== null && $preadmision->estado !== 'PENDIENTE') {
            return false;
        }

        return true;
    }

    public function update(User $user, ValoracionEnfermeriaPreadmision $valoracion): bool
    {
        if ($user->estado !== 'ACTIVO') {
            return false;
        }

        if (! $user->hasRole('ENFERMEROS')) {
            return false;
        }

        if (! $user->can('valoracion_enfermeria.editar')) {
            return false;
        }

        $personal = $user->personal;
        if (! $personal || $personal->estado !== 'ACTIVO') {
            return false;
        }

        $preadmision = $valoracion->preadmision;
        if ($preadmision && $preadmision->estado !== 'PENDIENTE') {
            return false;
        }

        return true;
    }

    public function delete(User $user, ValoracionEnfermeriaPreadmision $valoracion): bool
    {
        return false;
    }

    public function forceDelete(User $user, ValoracionEnfermeriaPreadmision $valoracion): bool
    {
        return false;
    }
}
