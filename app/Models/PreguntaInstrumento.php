<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class PreguntaInstrumento extends ModeloOperativo { protected $table='preguntas_instrumento'; protected $primaryKey='cod_pregunta'; public function instrumento(): BelongsTo{return $this->belongsTo(Instrumento::class,'cod_instrumento','cod_instrumento');} public function opciones(): HasMany{return $this->hasMany(OpcionPregunta::class,'cod_pregunta','cod_pregunta');} }
