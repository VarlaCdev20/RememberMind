<?php
namespace App\Models;
class InformeEstudio extends ModeloOperativo { protected $table='informes_estudio'; protected $primaryKey='cod_informe_estudio'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} }
