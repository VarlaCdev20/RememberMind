<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$roles = Role::with('permissions')->get();
$permissions = Permission::all();

$data = [
    'roles' => [],
    'permissions' => []
];

foreach ($roles as $role) {
    $data['roles'][$role->name] = $role->permissions->pluck('name')->toArray();
}

foreach ($permissions as $permission) {
    $data['permissions'][] = $permission->name;
}

file_put_contents('roles_audit.json', json_encode($data, JSON_PRETTY_PRINT));
echo "Roles and permissions dumped.\n";
