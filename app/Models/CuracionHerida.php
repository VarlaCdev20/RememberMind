<?php
namespace App\Models;
class CuracionHerida extends ModeloOperativo { protected $table='curaciones_herida'; protected $primaryKey='cod_curacion'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
