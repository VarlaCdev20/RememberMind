<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
class PlanCuidado extends ModeloOperativo { protected $table='planes_cuidado'; protected $primaryKey='cod_plan'; protected function casts(): array{return ['fecha_hora_apertura'=>'datetime','fecha_hora_cierre'=>'datetime'];} public function intervenciones(): HasMany{return $this->hasMany(IntervencionCuidado::class,'cod_plan','cod_plan');} }
