<?php
namespace App\Models;
class Derivacion extends ModeloOperativo { protected $table='derivaciones'; protected $primaryKey='cod_derivacion'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
