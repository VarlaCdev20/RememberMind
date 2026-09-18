<?php
namespace App\Models;
class SignoVital extends ModeloOperativo { protected $table='signos_vitales'; protected $primaryKey='cod_signo'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
