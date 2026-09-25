<?php
namespace App\Models;
class IndicacionClinica extends ModeloOperativo { protected $table='indicaciones_clinicas'; protected $primaryKey='cod_indicacion'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
