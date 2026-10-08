<?php

namespace App\Policies;

use App\Models\Habitacion;
use App\Models\User;

class HabitacionPolicy
{
    public function create(User $usuario): bool
    {
        return $this->permitido($usuario, 'habitaciones.crear');
    }

    public function update(User $usuario, Habitacion $habitacion): bool
    {
        return $this->permitido($usuario, 'habitaciones.editar');
    }

    private function permitido(User $usuario, string $permiso): bool
    {
        return $usuario->estado === 'ACTIVO' && ! $usuario->hasRole('FAMILIAR') && $usuario->can('habitaciones.ver') && $usuario->can($permiso);
    }
}
