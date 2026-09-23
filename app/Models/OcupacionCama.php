<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OcupacionCama extends ModeloOperativo {
    protected $table='ocupaciones_cama'; protected $primaryKey='cod_ocupacion';
    protected function casts(): array { return ['fecha_hora_asignacion'=>'datetime','fecha_hora_liberacion'=>'datetime']; }
    protected static function booted(): void { static::creating(function (self $ocupacion): void { if ($ocupacion->estado === 'ACTIVA' && (self::query()->where('estado','ACTIVA')->where('cod_cama',$ocupacion->cod_cama)->exists() || self::query()->where('estado','ACTIVA')->where('cod_residente',$ocupacion->cod_residente)->exists())) { throw \Illuminate\Validation\ValidationException::withMessages(['ocupacion'=>'La cama o el residente ya tiene una ocupación activa.']); } }); }
    public function residente(): BelongsTo { return $this->belongsTo(Residente::class,'cod_residente','cod_residente'); }
    public function cama(): BelongsTo { return $this->belongsTo(Cama::class,'cod_cama','cod_cama'); }
    public function admision(): BelongsTo { return $this->belongsTo(Admision::class,'cod_admision','cod_admision'); }
}
