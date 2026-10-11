<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class IntervencionCuidado extends ModeloOperativo { protected $table='intervenciones_cuidado'; protected $primaryKey='cod_intervencion'; public function plan(): BelongsTo{return $this->belongsTo(PlanCuidado::class,'cod_plan','cod_plan');} public function programaciones(): HasMany{return $this->hasMany(ProgramacionCuidado::class,'cod_intervencion','cod_intervencion');} public function ejecuciones(): HasMany{return $this->hasMany(EjecucionCuidado::class,'cod_intervencion','cod_intervencion');} }
