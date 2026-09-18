<?php
namespace App\Models;
class EventoAlerta extends ModeloOperativo { protected $table='eventos_alerta'; protected $primaryKey='cod_evento_alerta'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
