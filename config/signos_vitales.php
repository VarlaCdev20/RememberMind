<?php

/* Umbrales institucionales aprobados para el formulario de Enfermería.
 * Las categorías no definidas aquí permanecen sin evaluación automática.
 */
return [
    'presion' => ['sistolica_critica_alta' => 180, 'diastolica_critica_alta' => 120, 'sistolica_advertencia_baja' => 90, 'sistolica_referencia_alta' => 120, 'diastolica_referencia_alta' => 80],
    'pulso' => ['critico_bajo' => 40, 'advertencia_baja' => 50, 'normal_alta' => 90, 'advertencia_alta' => 110, 'alto_alta' => 130],
    'respiracion' => ['critico_bajo' => 8, 'advertencia_baja' => 11, 'normal_alta' => 20, 'alto_alta' => 24],
    'temperatura' => ['critico_bajo' => 35.0, 'referencia_baja' => 36.1, 'referencia_alta' => 37.7, 'advertencia_alta' => 37.8, 'critico_alto' => 39.1],
    // ADA PALTC 2026: elevación sostenida >250 mg/dL en 24 h requiere revisión.
    // Una lectura aislada solo sugiere revisar contexto; no crea alerta automática.
    'glucemia' => ['critico_bajo' => 54, 'advertencia_baja' => 70, 'revision_alta' => 250],
];
