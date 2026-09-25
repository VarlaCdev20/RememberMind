<?php
namespace App\Models;
class ValoracionNutricional extends ModeloOperativo { protected $table='valoraciones_nutricionales'; protected $primaryKey='cod_valoracion_nutricional'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
