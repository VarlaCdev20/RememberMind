<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$a = App\Models\AdultoMayor::first();
if ($a) {
    echo json_encode([
        'id' => $a->cod_am,
        'obs' => $a->observaciones()->count(),
        'aten' => $a->atenciones()->count(),
        'eval' => $a->evaluacionesCognitivas()->count(),
        'fam' => $a->familiares()->count(),
        'act' => $a->actividades()->count(),
        'doc' => $a->documentos()->count(),
        'nombres' => $a->nombres . ' ' . $a->ap_paterno
    ]);
} else {
    echo "No AdultoMayor found.";
}
