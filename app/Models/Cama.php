<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Cama extends ModeloOperativo {
    protected $table='camas'; protected $primaryKey='cod_cama';
    public function habitacion(): BelongsTo { return $this->belongsTo(Habitacion::class,'cod_habitacion','cod_habitacion'); }
    public function ocupaciones(): HasMany { return $this->hasMany(OcupacionCama::class,'cod_cama','cod_cama'); }
    public function ocupacionActiva(): HasOne { return $this->hasOne(OcupacionCama::class,'cod_cama','cod_cama')->where('estado','ACTIVA'); }
}
