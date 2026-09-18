<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class HorarioPrescripcion extends ModeloOperativo { protected $table='horarios_prescripcion'; protected $primaryKey='cod_horario_prescripcion'; public function prescripcion(): BelongsTo{return $this->belongsTo(Prescripcion::class,'cod_prescripcion','cod_prescripcion');} }
