<?php

return [
    // Bindings explícitos a instrumentos/preguntas originales. Su ausencia no identifica las tareas nominales como HVLT-R.
    'perfiles_instrumentales' => [],
    // Registro de contratos de PUBLICACIÓN, nunca activación de inferencia.
    // Vacío hasta decisión institucional: ACTIVO por sí solo no acredita el paquete.
    // Cada versión histórica requerirá criterios y catálogos de ejecución/traza
    // aprobados explícitamente. No hay fallback a la versión vigente.
    'lectura_clinica' => [],
    // Solo se configura explícitamente en el arranque de la BDD de QA.
    // APP_ENV=testing y nombre desechable siguen siendo obligatorios.
    'lectura_tecnica_pruebas' => ['habilitada' => false],
    // Capacidad local para la cohorte ficticia autorizada; jamás habilita publicación clínica.
    'simulacion_local' => [
        'habilitada' => filter_var(env('SISTEMA_EXPERTO_SIMULACION_LOCAL', false), FILTER_VALIDATE_BOOL),
        'base_permitida' => env('SISTEMA_EXPERTO_SIMULACION_BASE', ''),
    ],
];
