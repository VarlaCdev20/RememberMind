<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\DB;

$rolesObsoletos = ['admin', 'personal_admin', 'personal_salud', 'voluntario', 'familiar'];

$reporte = [
    'roles_eliminados' => [],
    'roles_conservados' => [],
    'usuarios_afectados' => 0
];

// Validar que realmente tengan 0 usuarios
$puedenBorrarse = [];
foreach ($rolesObsoletos as $roleName) {
    $role = Role::where('name', $roleName)->first();
    if ($role) {
        $userCount = DB::table('model_has_roles')->where('role_id', $role->id)->count();
        if ($userCount === 0) {
            $puedenBorrarse[] = $roleName;
        } else {
            echo "ATENCIÓN: El rol {$roleName} aún tiene {$userCount} usuarios. No se borrará.\n";
            $reporte['usuarios_afectados'] += $userCount;
        }
    }
}

// Eliminar roles
if (!empty($puedenBorrarse)) {
    Role::whereIn('name', $puedenBorrarse)->delete();
    $reporte['roles_eliminados'] = $puedenBorrarse;
}

// Roles conservados
$reporte['roles_conservados'] = Role::pluck('name')->toArray();

// Limpiar cache
app()[PermissionRegistrar::class]->forgetCachedPermissions();

echo json_encode($reporte, JSON_PRETTY_PRINT);
