<?php
namespace App\Models;
class SeguimientoPedagogico extends ModeloOperativo { protected $table='seguimientos_pedagogicos'; protected $primaryKey='cod_seguimiento_pedagogico'; protected function casts(): array{return ['fecha_hora'=>'datetime','cambio_desempeno'=>'boolean'];} }
