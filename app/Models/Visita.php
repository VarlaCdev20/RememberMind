<?php
namespace App\Models;
class Visita extends ModeloOperativo { protected $table='visitas'; protected $primaryKey='cod_visita'; protected function casts(): array{return ['fecha_hora_programada'=>'datetime','fecha_hora_ingreso'=>'datetime','fecha_hora_salida'=>'datetime'];} }
