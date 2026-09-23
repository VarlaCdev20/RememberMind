<?php
namespace App\Models;
class PaseTurno extends ModeloOperativo { protected $table='pases_turno'; protected $primaryKey='cod_pase'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
