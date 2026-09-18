<?php
namespace App\Models;
class ValoracionFuncional extends ModeloOperativo { protected $table='valoraciones_funcionales'; protected $primaryKey='cod_valoracion_funcional'; protected function casts(): array{return ['fecha_hora'=>'datetime','necesita_supervision'=>'boolean'];} }
