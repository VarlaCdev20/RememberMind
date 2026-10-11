<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Instrumento extends ModeloOperativo {
    protected $table='instrumentos'; protected $primaryKey='cod_instrumento';
    protected function casts(): array { return ['puntaje_maximo'=>'decimal:2']; }
    public function preguntas(): HasMany { return $this->hasMany(PreguntaInstrumento::class,'cod_instrumento','cod_instrumento'); }
    public function aplicaciones(): HasMany { return $this->hasMany(AplicacionInstrumento::class,'cod_instrumento','cod_instrumento'); }

    /** Alias de presentación requeridos por el formulario histórico. */
    public function getSiglasAttribute(): string { return (string) $this->codigo; }
    public function getTipoResultadoAttribute(): string { return (string) $this->tipo; }
    public function getPuntoCorteNormalAttribute(): mixed { return null; }
    public function getPuntoCorteRiesgoAttribute(): mixed { return null; }
}
