<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
class ResidenteContacto extends Pivot {
    protected $table='residentes_contactos'; protected $primaryKey='cod_residente_contacto';
    public $incrementing=false; public $timestamps=false; protected $keyType='string'; protected $guarded=[];
    protected function casts(): array { return ['responsable_principal'=>'boolean','contacto_emergencia'=>'boolean','autoriza_informacion'=>'boolean','autoriza_salida'=>'boolean']; }
    protected static function booted(): void { static::saving(function (self $vinculo): void { if ($vinculo->estado === 'ACTIVO' && self::query()->where('cod_residente',$vinculo->cod_residente)->where('cod_contacto',$vinculo->cod_contacto)->where('estado','ACTIVO')->when($vinculo->exists,fn($q)=>$q->where($vinculo->getKeyName(),'<>',$vinculo->getKey()))->exists()) { throw \Illuminate\Validation\ValidationException::withMessages(['cod_contacto'=>'El contacto ya tiene un vínculo activo con el residente.']); } }); }
    public function residente(): BelongsTo { return $this->belongsTo(Residente::class,'cod_residente','cod_residente'); }
    public function contacto(): BelongsTo { return $this->belongsTo(Contacto::class,'cod_contacto','cod_contacto'); }
    public function getParentescoVinculoAttribute(): string { return (string) $this->parentesco; }
    public function getEsResponsableAttribute(): bool { return (bool) $this->responsable_principal; }
    public function getObservacionesAttribute(): ?string { return $this->observacion; }
}
