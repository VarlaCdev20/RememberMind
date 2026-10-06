<?php

namespace App\Policies;

use App\Models\Cama;
use App\Models\User;

class CamaPolicy
{
    public function create(User $usuario): bool
    {
        return $this->permitido($usuario, 'camas.crear');
    }

    public function update(User $usuario, Cama $cama): bool
    {
        return $this->permitido($usuario, 'camas.editar');
    }

    private function permitido(User $usuario, string $permiso): bool
    {
        return $usuario->estado === 'ACTIVO' && ! $usuario->hasRole('FAMILIAR') && $usuario->can('habitaciones.ver') && $usuario->can($permiso);
    }
}
