<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Alerta extends ModeloOperativo { protected $table='alertas'; protected $primaryKey='cod_alerta'; protected function casts(): array{return ['fecha_hora'=>'datetime','fecha_hora_limite'=>'datetime'];} public function eventos(): HasMany{return $this->hasMany(EventoAlerta::class,'cod_alerta','cod_alerta');} public function residente(): BelongsTo{return $this->belongsTo(Residente::class,'cod_residente','cod_residente');} }
