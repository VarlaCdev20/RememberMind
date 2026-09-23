<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Preadmision extends ModeloOperativo {
    protected $table='preadmisiones'; protected $primaryKey='cod_preadmision';
    protected function casts(): array { return ['fecha_nacimiento'=>'date','fecha_solicitud'=>'datetime','fecha_revision'=>'datetime']; }
    public function contacto(): BelongsTo { return $this->belongsTo(Contacto::class,'cod_contacto','cod_contacto'); }
    public function admision(): HasOne { return $this->hasOne(Admision::class,'cod_preadmision','cod_preadmision'); }
}
