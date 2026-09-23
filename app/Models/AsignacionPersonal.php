<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AsignacionPersonal extends ModeloOperativo {
    protected $table='asignaciones_personal'; protected $primaryKey='cod_asignacion_personal';
    protected function casts(): array { return ['fecha_asignacion'=>'datetime']; }
    public function jornada(): BelongsTo { return $this->belongsTo(Jornada::class,'cod_jornada','cod_jornada'); }
    public function personal(): BelongsTo { return $this->belongsTo(Personal::class,'cod_personal','cod_personal'); }
    public function area(): BelongsTo { return $this->belongsTo(Area::class,'cod_area','cod_area'); }
}
