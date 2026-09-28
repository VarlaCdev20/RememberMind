<?php
namespace App\Models;
class DispositivoClinico extends ModeloOperativo { protected $table='dispositivos_clinicos'; protected $primaryKey='cod_dispositivo'; protected function casts(): array{return ['fecha_colocacion'=>'datetime','fecha_retiro'=>'datetime'];} }
