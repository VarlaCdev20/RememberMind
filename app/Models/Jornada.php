<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Jornada extends ModeloOperativo {
    protected $table='jornadas'; protected $primaryKey='cod_jornada';
    protected function casts(): array { return ['fecha_jornada'=>'date']; }
    public function turno(): BelongsTo { return $this->belongsTo(Turno::class,'cod_turno','cod_turno'); }
    public function asignacionesPersonal(): HasMany { return $this->hasMany(AsignacionPersonal::class,'cod_jornada','cod_jornada'); }
}
