<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Consentimiento extends ModeloOperativo {
    protected $table='consentimientos'; protected $primaryKey='cod_consentimiento';
    protected function casts(): array { return ['firma_residente'=>'boolean','fecha_consentimiento'=>'datetime']; }
    protected static function booted(): void { static::saving(function (self $consentimiento): void { if ((bool)$consentimiento->firma_residente === (bool)$consentimiento->cod_residente_contacto) { throw \Illuminate\Validation\ValidationException::withMessages(['firma_residente'=>'Debe firmar el residente o un contacto vinculado, no ambos.']); } if ($consentimiento->cod_residente_contacto && ! ResidenteContacto::query()->whereKey($consentimiento->cod_residente_contacto)->where('cod_residente',$consentimiento->cod_residente)->exists()) { throw \Illuminate\Validation\ValidationException::withMessages(['cod_residente_contacto'=>'El contacto no pertenece al residente.']); } }); }
    public function residente(): BelongsTo { return $this->belongsTo(Residente::class,'cod_residente','cod_residente'); }
    public function admision(): BelongsTo { return $this->belongsTo(Admision::class,'cod_admision','cod_admision'); }
    public function firmanteContacto(): BelongsTo { return $this->belongsTo(ResidenteContacto::class,'cod_residente_contacto','cod_residente_contacto'); }
}
