<?php
namespace App\Models;
class ValoracionDolor extends ModeloOperativo { protected $table='valoraciones_dolor'; protected $primaryKey='cod_valoracion_dolor'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
