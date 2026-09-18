<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Contacto extends ModeloOperativo {
    protected $table='contactos'; protected $primaryKey='cod_contacto';
    public function usuario(): BelongsTo { return $this->belongsTo(User::class,'cod_usuario','cod_usuario'); }
    public function residentes(): BelongsToMany { return $this->belongsToMany(Residente::class,'residentes_contactos','cod_contacto','cod_residente')->using(ResidenteContacto::class); }
    public function vinculos(): HasMany { return $this->hasMany(ResidenteContacto::class,'cod_contacto','cod_contacto'); }
}
