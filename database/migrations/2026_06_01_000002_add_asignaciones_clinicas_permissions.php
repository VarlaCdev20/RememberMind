<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'asignaciones_clinicas.ver',
            'asignaciones_clinicas.crear',
            'asignaciones_clinicas.finalizar',
            'asignaciones_clinicas.suspender',
            'asignaciones_clinicas.gestionar',
            'asignaciones_clinicas.mis_pacientes',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        Role::query()
            ->whereIn('name', ['admin', 'super_admin'])
            ->where('guard_name', 'web')
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permissions));

        Role::query()
            ->where('name', 'personal_salud')
            ->where('guard_name', 'web')
            ->first()
            ?->givePermissionTo('asignaciones_clinicas.mis_pacientes');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'asignaciones_clinicas.ver',
            'asignaciones_clinicas.crear',
            'asignaciones_clinicas.finalizar',
            'asignaciones_clinicas.suspender',
            'asignaciones_clinicas.gestionar',
            'asignaciones_clinicas.mis_pacientes',
        ];

        Role::query()
            ->whereIn('name', ['admin', 'super_admin', 'personal_salud'])
            ->where('guard_name', 'web')
            ->get()
            ->each(fn (Role $role) => $role->revokePermissionTo($permissions));

        Permission::query()
            ->whereIn('name', $permissions)
            ->where('guard_name', 'web')
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
