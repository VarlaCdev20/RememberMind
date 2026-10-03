<?php

return [
    // Excepción reversible para construir los módulos clínicos en local.
    'superadmin_clinical_write' => env(
        'SUPERADMIN_CLINICAL_WRITE',
        in_array(env('APP_ENV'), ['local', 'testing'], true)
    ),
];
