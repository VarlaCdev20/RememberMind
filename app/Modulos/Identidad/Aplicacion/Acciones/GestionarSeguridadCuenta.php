<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class GestionarSeguridadCuenta
{
    public function restablecerPassword(User $actor, User $usuario): string
    {
        abort_unless($actor->can('usuarios.gestionar'), 403);
        $tempPassword = Str::password(16, letters: true, numbers: true, symbols: true, spaces: false);

        $usuario->update([
            'password' => Hash::make($tempPassword),
        ]);

        activity('Usuarios')
            ->causedBy($actor)
            ->performedOn($usuario)
            ->event('seguridad')
            ->log("Restableció la contraseña del usuario {$usuario->name} a una clave temporal.");

        return $tempPassword;
    }

    public function toggleAcceso(User $actor, User $usuario): string
    {
        abort_unless($actor->can('usuarios.gestionar'), 403);
        abort_if($usuario->cod_usuario === $actor->getKey(), 422, 'No puedes bloquear tu propia cuenta.');
        $nuevoAcceso = $usuario->acceso_sistema === 'HABILITADO' ? 'BLOQUEADO' : 'HABILITADO';
        $usuario->update(['acceso_sistema' => $nuevoAcceso]);

        activity('Usuarios')
            ->causedBy($actor)
            ->performedOn($usuario)
            ->event('seguridad')
            ->log(($nuevoAcceso === 'HABILITADO' ? 'Habilitó' : 'Bloqueó')." el acceso de {$usuario->name} al sistema.");

        return $nuevoAcceso;
    }
}
