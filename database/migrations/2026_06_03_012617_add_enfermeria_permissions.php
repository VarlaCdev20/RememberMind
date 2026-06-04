<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permissions = [
            'enfermeria.ver_dashboard',
            'enfermeria.ver_pacientes_asignados',
            'enfermeria.ver_ficha_paciente',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Asignar al rol "Enfermería" si existe
        $role = Role::where('name', 'Enfermería')->first();
        if ($role) {
            $role->givePermissionTo($permissions);
        }
        
        // También asignarlos a Super Admin
        $superadmin = Role::where('name', 'Super Admin')->first();
        if ($superadmin) {
            $superadmin->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = [
            'enfermeria.ver_dashboard',
            'enfermeria.ver_pacientes_asignados',
            'enfermeria.ver_ficha_paciente',
        ];

        foreach ($permissions as $permission) {
            Permission::where('name', $permission)->delete();
        }
    }
};
