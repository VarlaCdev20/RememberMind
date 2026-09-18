<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Medicamento extends ModeloOperativo {
    protected $table='medicamentos'; protected $primaryKey='cod_medicamento';
    protected function casts(): array { return ['control_especial'=>'boolean']; }
    public function prescripciones(): HasMany { return $this->hasMany(Prescripcion::class,'cod_medicamento','cod_medicamento'); }
}
