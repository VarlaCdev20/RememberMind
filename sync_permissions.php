<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$role = Role::firstOrCreate(['name' => 'SUPERADMINISTRADOR']);
$role->syncPermissions(Permission::all());

echo "Permisos de base de datos sincronizados.\n";
