<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class ComponenteEstudio extends ModeloOperativo { protected $table='componentes_estudio'; protected $primaryKey='cod_componente'; public function tipo(): BelongsTo{return $this->belongsTo(TipoEstudioClinico::class,'cod_tipo_estudio','cod_tipo_estudio');} public function resultados(): HasMany{return $this->hasMany(ResultadoEstudio::class,'cod_componente','cod_componente');} }
