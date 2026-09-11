<?php

return [
    'minutos_proximo_medicacion' => (int) env('ENFERMERIA_MINUTOS_PROXIMO_MEDICACION', 60),
    'minutos_proxima_tarea' => (int) env('ENFERMERIA_MINUTOS_PROXIMA_TAREA', 60),
    'minutos_reevaluacion_prn' => (int) env('ENFERMERIA_MINUTOS_REEVALUACION_PRN', 60),
    'porcentaje_baja_ingesta' => (int) env('ENFERMERIA_PORCENTAJE_BAJA_INGESTA', 50),
];
