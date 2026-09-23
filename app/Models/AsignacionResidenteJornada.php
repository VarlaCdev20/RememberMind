<?php
namespace App\Models;
class AsignacionResidenteJornada extends ModeloOperativo { protected $table='asignaciones_residente_jornada'; protected $primaryKey='cod_asignacion'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
