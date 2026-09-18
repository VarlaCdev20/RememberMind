<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Actividad extends ModeloOperativo { protected $table='actividades'; protected $primaryKey='cod_actividad'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} public function participantes(): HasMany{return $this->hasMany(ParticipanteActividad::class,'cod_actividad','cod_actividad');} public function residentes(): BelongsToMany{return $this->belongsToMany(Residente::class,'participantes_actividad','cod_actividad','cod_residente');} }
