<?php

namespace App\Policies;

use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\User;

class ResidentePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->estado !== 'ACTIVO') {
            return false;
        }
        return $user->hasRole('SUPERADMINISTRADOR') && in_array($ability, ['viewAny', 'view'], true) ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return ! $user->hasRole('FAMILIAR') && $user->can('residentes.ver');
    }

    public function view(User $user, Residente $residente): bool
    {
        if (! $user->hasRole('FAMILIAR')) {
            return $user->can('residentes.ver');
        }

        if (! $user->can('residentes.ver')) {
            return false;
        }

        return ResidenteContacto::query()
            ->where('cod_residente', $residente->cod_residente)
            ->whereIn('cod_contacto', $user->contactos()->select('cod_contacto'))
            ->where('autoriza_informacion', true)
            ->where('estado', 'ACTIVO')
            ->whereHas('contacto', fn ($query) => $query->where('estado', 'ACTIVO'))
            ->exists();
    }

    public function create(): bool
    {
        return false;
    }
}
