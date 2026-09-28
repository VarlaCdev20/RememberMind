<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ResultadoEstudio extends ModeloOperativo { protected $table='resultados_estudio'; protected $primaryKey='cod_resultado_estudio'; public function estudio(): BelongsTo{return $this->belongsTo(EstudioClinico::class,'cod_estudio','cod_estudio');} public function componente(): BelongsTo{return $this->belongsTo(ComponenteEstudio::class,'cod_componente','cod_componente');} }
