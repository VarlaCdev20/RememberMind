<?php
use App\Models\User;
use App\Models\PersonalSalud;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = User::first();
if ($u) {
    if (!PersonalSalud::where('cod_usu', $u->cod_usu)->exists()) {
        PersonalSalud::create([
            'cod_usu' => $u->cod_usu,
            'fecha_ing' => date('Y-m-d'),
            'anios_exp' => 10,
            'matricula_prof' => 'MS-100200',
            'estado_laboral' => 'ACTIVO',
            'observaciones' => 'Personal médico administrativo inicial'
        ]);
        echo "PersonalSalud creado para: " . $u->cod_usu . "\n";
    } else {
        echo "PersonalSalud ya existe para este usuario.\n";
    }
} else {
    echo "No se encontró ningún usuario.\n";
}
