<?php
namespace App\Models;
class Incidente extends ModeloOperativo { protected $table='incidentes'; protected $primaryKey='cod_incidente'; protected function casts(): array{return ['fecha_hora'=>'datetime','requiere_medico'=>'boolean','requiere_derivacion'=>'boolean'];} }
