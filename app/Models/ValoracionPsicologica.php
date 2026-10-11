<?php
namespace App\Models;
class ValoracionPsicologica extends ModeloOperativo { protected $table='valoraciones_psicologicas'; protected $primaryKey='cod_valoracion_psicologica'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
