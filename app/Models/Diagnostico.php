<?php
namespace App\Models;
class Diagnostico extends ModeloOperativo { protected $table='diagnosticos'; protected $primaryKey='cod_diagnostico'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
