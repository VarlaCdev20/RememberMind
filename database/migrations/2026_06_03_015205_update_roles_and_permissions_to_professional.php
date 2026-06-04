<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Renombrar 'admin' a 'SUPERADMINISTRADOR' para no perder acceso
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->update(['name' => 'SUPERADMINISTRADOR']);
        } else {
            Role::firstOrCreate(['name' => 'SUPERADMINISTRADOR']);
        }

        // 2. Crear los nuevos roles solicitados
        $newRoles = [
            'ADMINISTRADOR',
            'ENFERMEROS',
            'MEDICO GENERAL/GERIATRA',
            'PSICOLOGO/A',
            'PEDAGOGO',
            'NUTRICIONISTA',
            'FISIOTERAPEUTA'
        ];

        foreach ($newRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 3. Obtener todos los permisos existentes para asignarlos al SUPERADMINISTRADOR
        $superAdmin = Role::where('name', 'SUPERADMINISTRADOR')->first();
        if ($superAdmin) {
            $allPermissions = Permission::all();
            $superAdmin->syncPermissions($allPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $superAdmin = Role::where('name', 'SUPERADMINISTRADOR')->first();
        if ($superAdmin) {
            $superAdmin->update(['name' => 'admin']);
        }
    }
};
