<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Turno extends ModeloOperativo {
    protected $table='turnos'; protected $primaryKey='cod_turno';
    public function jornadas(): HasMany { return $this->hasMany(Jornada::class,'cod_turno','cod_turno'); }
    public function programaciones(): HasMany { return $this->hasMany(ProgramacionCuidado::class,'cod_turno','cod_turno'); }
}
