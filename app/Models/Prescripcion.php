<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Prescripcion extends ModeloOperativo { protected $table='prescripciones'; protected $primaryKey='cod_prescripcion'; protected function casts(): array{return ['segun_necesidad'=>'boolean','fecha_hora_prescripcion'=>'datetime','fecha_hora_suspension'=>'datetime'];} public function residente(): BelongsTo{return $this->belongsTo(Residente::class,'cod_residente','cod_residente');} public function medicamento(): BelongsTo{return $this->belongsTo(Medicamento::class,'cod_medicamento','cod_medicamento');} public function horarios(): HasMany{return $this->hasMany(HorarioPrescripcion::class,'cod_prescripcion','cod_prescripcion');} public function administraciones(): HasMany{return $this->hasMany(AdministracionMedicacion::class,'cod_prescripcion','cod_prescripcion');} }
