<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AplicacionInstrumento extends ModeloOperativo { protected $table='aplicaciones_instrumento'; protected $primaryKey='cod_aplicacion'; protected function casts(): array{return ['fecha_hora'=>'datetime'];} public function instrumento(): BelongsTo{return $this->belongsTo(Instrumento::class,'cod_instrumento','cod_instrumento');} public function respuestas(): HasMany{return $this->hasMany(RespuestaInstrumento::class,'cod_aplicacion','cod_aplicacion');} }
