<?php

return [
    // Registro de contratos de PUBLICACIÓN, nunca activación de inferencia.
    // Vacío hasta decisión institucional: ACTIVO por sí solo no acredita el paquete.
    // Cada versión histórica requerirá criterios y catálogos de ejecución/traza
    // aprobados explícitamente. No hay fallback a la versión vigente.
    'lectura_clinica' => [],
    // Solo se configura explícitamente en el arranque de la BDD de QA.
    // APP_ENV=testing y nombre desechable siguen siendo obligatorios.
    'lectura_tecnica_pruebas' => ['habilitada' => false],
];
