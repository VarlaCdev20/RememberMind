<?php
namespace App\Models;
class RegistroMovilidad extends ModeloOperativo { protected $table='registros_movilidad'; protected $primaryKey='cod_movilidad'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
