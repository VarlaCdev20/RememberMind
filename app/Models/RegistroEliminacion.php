<?php
namespace App\Models;
class RegistroEliminacion extends ModeloOperativo { protected $table='registros_eliminacion'; protected $primaryKey='cod_eliminacion'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
