<?php

return [
    // Presentación EVA aprobada: no genera diagnósticos ni alertas automáticas.
    'dolor_intensidad_presentacion' => [
        ['min' => 0, 'max' => 0, 'state' => 'none', 'label' => 'Sin dolor'],
        ['min' => 1, 'max' => 3, 'state' => 'low', 'label' => 'Intensidad leve'],
        ['min' => 4, 'max' => 6, 'state' => 'medium', 'label' => 'Intensidad intermedia'],
        ['min' => 7, 'max' => 10, 'state' => 'high', 'label' => 'Intensidad alta'],
    ],
    'minutos_proximo_medicacion' => (int) env('ENFERMERIA_MINUTOS_PROXIMO_MEDICACION', 60),
    'minutos_proxima_tarea' => (int) env('ENFERMERIA_MINUTOS_PROXIMA_TAREA', 60),
    'minutos_reevaluacion_prn' => (int) env('ENFERMERIA_MINUTOS_REEVALUACION_PRN', 60),
    'porcentaje_baja_ingesta' => (int) env('ENFERMERIA_PORCENTAJE_BAJA_INGESTA', 50),
];
