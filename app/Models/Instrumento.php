<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Instrumento extends ModeloOperativo {
    protected $table='instrumentos'; protected $primaryKey='cod_instrumento';
    protected function casts(): array { return ['puntaje_maximo'=>'decimal:2']; }
    public function preguntas(): HasMany { return $this->hasMany(PreguntaInstrumento::class,'cod_instrumento','cod_instrumento'); }
    public function aplicaciones(): HasMany { return $this->hasMany(AplicacionInstrumento::class,'cod_instrumento','cod_instrumento'); }
}
