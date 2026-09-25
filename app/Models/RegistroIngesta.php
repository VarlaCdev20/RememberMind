<?php
namespace App\Models;
class RegistroIngesta extends ModeloOperativo { protected $table='registros_ingesta'; protected $primaryKey='cod_ingesta'; protected function casts(): array{return ['fecha_hora'=>'datetime','dificultad_deglucion'=>'boolean'];} }
