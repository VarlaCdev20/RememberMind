<?php
namespace App\Models;
class MedicionAntropometrica extends ModeloOperativo { protected $table='mediciones_antropometricas'; protected $primaryKey='cod_medicion'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
