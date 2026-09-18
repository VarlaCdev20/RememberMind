<?php
namespace App\Models;
class RegistroHidratacion extends ModeloOperativo { protected $table='registros_hidratacion'; protected $primaryKey='cod_hidratacion'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
