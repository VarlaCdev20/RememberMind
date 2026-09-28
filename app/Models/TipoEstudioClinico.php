<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
class TipoEstudioClinico extends ModeloOperativo {
    protected $table='tipos_estudio_clinico'; protected $primaryKey='cod_tipo_estudio';
    protected function casts(): array { return ['requiere_componentes'=>'boolean','requiere_informe'=>'boolean']; }
    public function componentes(): HasMany { return $this->hasMany(ComponenteEstudio::class,'cod_tipo_estudio','cod_tipo_estudio'); }
}
