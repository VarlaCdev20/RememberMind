<?php

return [
    'avisos_planificacion_habilitados' => env('AVISOS_PLANIFICACION_HABILITADOS', false),
    // Excepción reversible para construir los módulos clínicos en local.
    'superadmin_clinical_write' => env(
        'SUPERADMIN_CLINICAL_WRITE',
        in_array(env('APP_ENV'), ['local', 'testing'], true)
    ),
];
