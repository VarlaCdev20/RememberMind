<?php
require __DIR__ . '/vendor/autoload.php';
 = require __DIR__ . '/bootstrap/app.php';
->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

 = [
    'adulto_mayor',
    'ficha_medica_adulto',
    'valoracion_funcional_adulto',
    'signos_vitales_adulto',
    'medicacion_adulto',
    'administracion_medicacion',
    'planes_cuidado',
    'tareas_plan_cuidado',
    'seguimientos_diarios',
    'alertas_adulto',
    'acciones_alerta',
    'notas_evolucion_medica',
    'pases_turno',
    'habitaciones',
    'camas'
];

foreach ( as ) {
    echo str_pad(, 30) . ': ' . DB::table()->count() . PHP_EOL;
}
