<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Models\User;

final class CambiarEstadoPersonal
{
    public function ejecutar(User $actor, User $usuario): void
    {
        abort_unless($actor->can('usuarios.cambiar_estado'), 403);
        $usuario->estado = ($usuario->estado === 'ACTIVO' || $usuario->estado === 1 || $usuario->estado === true || $usuario->estado === '1')
            ? 'SUSPENDIDO'
            : 'ACTIVO';

        $usuario->save();

    }
}
