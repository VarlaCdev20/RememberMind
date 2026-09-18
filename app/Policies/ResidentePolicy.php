<?php

namespace App\Policies;

use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Models\User;

class ResidentePolicy
{
    public function before(User $user, string $ability): ?bool
    {
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

        $contacto = $user->contactos()->value('cod_contacto');

        return $contacto && ResidenteContacto::query()
            ->where('cod_residente', $residente->cod_residente)
            ->where('cod_contacto', $contacto)
            ->where('autoriza_informacion', true)
            ->where('estado', 'ACTIVO')
            ->exists();
    }

    public function create(): bool
    {
        return false;
    }
}
