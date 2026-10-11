<?php
namespace App\Models;
class Alergia extends ModeloOperativo { protected $table='alergias'; protected $primaryKey='cod_alergia'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
