<?php
namespace App\Models;
class ProgramacionCuidado extends ModeloOperativo { protected $table='programaciones_cuidado'; protected $primaryKey='cod_programacion'; protected function casts(): array{return ['fecha_activacion'=>'date','fecha_desactivacion'=>'date'];} }
