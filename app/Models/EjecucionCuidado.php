<?php
namespace App\Models;
class EjecucionCuidado extends ModeloOperativo { protected $table='ejecuciones_cuidado'; protected $primaryKey='cod_ejecucion'; protected function casts(): array{return ['fecha_hora_programada'=>'datetime','fecha_hora_ejecucion'=>'datetime'];} }
