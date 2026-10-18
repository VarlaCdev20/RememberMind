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
        foreach ($permisosSeleccionados as $permiso) {
            abort_unless(is_string($permiso), 422);
        }
        $seleccion = Permission::query()->where('guard_name', $rol->guard_name)->whereIn('name', $permisosSeleccionados)->get();
        abort_unless($seleccion->count() === count(array_unique($permisosSeleccionados)), 422);
        $permisosExpertosReservados = ['evaluaciones_expertas.ejecutar_observaciones', 'reglas_expertas.gestionar', 'reglas_expertas.revisar'];
        abort_if($seleccion->whereIn('name', $permisosExpertosReservados)->isNotEmpty()
            && ! in_array($rol->name, ['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A'], true), 403);
        $rol->syncPermissions($rol->name === 'SUPERADMINISTRADOR'
            ? Permission::query()->where('guard_name', $rol->guard_name)->whereNotIn('name', $permisosExpertosReservados)->get()
            : $seleccion);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (function_exists('activity')) {
            activity('Roles y Permisos')->causedBy($actor)
                ->performedOn($rol)
                ->log('El usuario '.$actor->nombres.' actualizó los permisos del rol '.strtoupper($rol->name));
        }

    }
}
