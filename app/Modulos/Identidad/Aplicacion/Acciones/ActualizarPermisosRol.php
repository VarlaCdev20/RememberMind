<?php

namespace App\Modulos\Identidad\Aplicacion\Acciones;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class ActualizarPermisosRol
{
    public function guardar(User $actor, Role $rol, array $permisosSeleccionados): void
    {
        abort_unless($actor->can('roles.editar_permisos'), 403);
        $rol->syncPermissions($rol->name === 'SUPERADMINISTRADOR'
            ? Permission::query()->where('guard_name', $rol->guard_name)->get()
            : $permisosSeleccionados);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (function_exists('activity')) {
            activity('Roles y Permisos')->causedBy($actor)
                ->performedOn($rol)
                ->log('El usuario '.$actor->nombres.' actualizó los permisos del rol '.strtoupper($rol->name));
        }

    }
}
