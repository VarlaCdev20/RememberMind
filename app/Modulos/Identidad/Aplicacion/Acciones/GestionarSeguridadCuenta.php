<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class GestionarSeguridadCuenta
{
    public function restablecerPassword(User $actor, User $usuario): string
    {
        $actor = $this->autorizar($actor);
        $usuario = User::findOrFail($usuario->getKey());
        abort_if($usuario->getKey() === 'USU_0001' && $actor->getKey() !== 'USU_0001', 403);
        $tempPassword = Str::password(16, letters: true, numbers: true, symbols: true, spaces: false);

        DB::transaction(function () use ($actor, $usuario, $tempPassword) {
            $usuario = User::query()->lockForUpdate()->findOrFail($usuario->getKey());
            $usuario->update(['password' => $tempPassword]);
            activity('Usuarios')
                ->causedBy($actor)
                ->performedOn($usuario)
                ->event('seguridad')
                ->log("Restableció la contraseña del usuario {$usuario->name} a una clave temporal.");
        });

        return $tempPassword;
    }

    public function toggleAcceso(User $actor, User $usuario): string
    {
        $actor = $this->autorizar($actor);

        return DB::transaction(function () use ($actor, $usuario) {
            $superadmins = User::whereHas('roles', fn ($query) => $query->where('name', 'SUPERADMINISTRADOR')->where('guard_name', 'web'))->where('estado', 'ACTIVO')->orderBy('cod_usuario')->lockForUpdate()->get();
            $usuario = User::query()->lockForUpdate()->findOrFail($usuario->getKey());
            abort_if($usuario->cod_usuario === $actor->getKey(), 422, 'No puedes bloquear tu propia cuenta.');
            abort_if($usuario->cod_usuario === 'USU_0001' || $usuario->correo === 'admincasaamandita@gmail.com', 422, 'No puedes bloquear al Administrador Principal.');
            abort_if($usuario->estado === 'ACTIVO' && $usuario->hasRole('SUPERADMINISTRADOR') && $superadmins->count() <= 1, 422, 'No puedes bloquear al único superadministrador activo.');
            $nuevoAcceso = $usuario->estado === 'ACTIVO' ? 'BLOQUEADO' : 'HABILITADO';
            $usuario->update(['estado' => $nuevoAcceso === 'HABILITADO' ? 'ACTIVO' : 'INACTIVO']);
            activity('Usuarios')
                ->causedBy($actor)
                ->performedOn($usuario)
                ->event('seguridad')
                ->log(($nuevoAcceso === 'HABILITADO' ? 'Habilitó' : 'Bloqueó')." el acceso de {$usuario->name} al sistema.");

            return $nuevoAcceso;
        });
    }

    private function autorizar(User $actor): User
    {
        $persistido = User::find($actor->getKey());
        abort_unless($persistido?->estado === 'ACTIVO' && $persistido->can('usuarios.gestionar'), 403);
        abort_if(app(RolePreviewService::class)->isActive($persistido), 403);

        return $persistido;
    }
}
