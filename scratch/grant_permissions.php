<?php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$role = Role::where('name', 'SUPERADMINISTRADOR')->first();
if(!$role) {
    $role = Role::create(['name' => 'SUPERADMINISTRADOR']);
}
$permissions = Permission::all();
$role->syncPermissions($permissions);
echo 'Asignados ' . $permissions->count() . ' permisos al SUPERADMINISTRADOR' . PHP_EOL;
