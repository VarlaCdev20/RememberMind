<?php

/** Ficticios: 15 casos de las plantillas documentales G09. */
$referencias = [
 'ATE-S01' => ['COG-ATE','ATE-R-SOSTENIMIENTO'],
 'ATE-S02' => ['COG-ATE','ATE-R-SELECCION'],
 'ATE-S03' => ['COG-ATE','ATE-R-DISTRIBUCION'],
 'EJE-S01' => ['COG-EJE','EJE-R-INHIBICION'],
 'EJE-S02' => ['COG-EJE','EJE-R-FLEXIBILIDAD'],
 'EJE-S03' => ['COG-EJE','EJE-R-ACTUALIZACION'],
 'EJE-S04' => ['COG-EJE','EJE-R-PLANIFICACION'],
 'EJE-S05' => ['COG-EJE','EJE-R-RAZONAMIENTO'],
 'LEN-S01' => ['COG-LEN','LEN-R-DENOMINACION'],
 'LEN-S02' => ['COG-LEN','LEN-R-FLUIDEZ'],
 'LEN-S03' => ['COG-LEN','LEN-R-COMPRENSION'],
 'LEN-S04' => ['COG-LEN','LEN-R-REPETICION'],
 'LEN-S05' => ['COG-LEN','LEN-R-DISCURSO'],
 'VIS-S01' => ['COG-VIS','VIS-R-PERCEPCION'],
 'VIS-S02' => ['COG-VIS','VIS-R-CONSTRUCCION'],
];

$casos = [];
foreach ($referencias as $codigo => [$criterio,$etiqueta]) {
 $casos[$codigo] = [
  'criterio_esperado' => $criterio,
  'referencia_esperada' => $etiqueta,
  'entrada' => [
   'id_caso' => 'LAB-CASO-'.$codigo,
   'actor_sintetico' => 'SYN-ACTOR-001',
   'origen' => 'SINTETICO',
   'version_laboratorio' => 'LAB-G09-20261015',
   'subcomponente' => $codigo,
   'compuerta_simulada' => 'LAB-EV-2',
   'hechos' => [
    ['id'=>'SYN-EV-'.$codigo.'-CTX', 'codigo'=>'LAB-CTX-VALIDO','fuente'=>'SYN-SRC-'.$codigo.'-CTX','episodio'=>'SYN-EP-'.$codigo],
    ['id'=>'SYN-EV-'.$codigo.'-COMP','codigo'=>'LAB-COMP-'.$codigo,'fuente'=>'SYN-SRC-'.$codigo.'-COMP','episodio'=>'SYN-EP-'.$codigo],
   ],
  ],
 ];
}
return $casos;
