<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear roles base
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'personal_salud']);
        Role::firstOrCreate(['name' => 'personal_admin']);
        Role::firstOrCreate(['name' => 'voluntario']);
        Role::firstOrCreate(['name' => 'familiar']);
    }
}
