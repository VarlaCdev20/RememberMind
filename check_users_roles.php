<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Spatie\Permission\Models\Role;
use App\Models\User;

$roles = Role::withCount('users')->get();
foreach($roles as $role) {
    echo $role->name . ": " . $role->users_count . " users\n";
}
