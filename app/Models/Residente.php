<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Residente extends ModeloOperativo {
    private static bool $creacionDesdeAdmision = false;
    protected $table='residentes'; protected $primaryKey='cod_residente';
    protected function casts(): array { return ['fecha_nacimiento'=>'date']; }
    protected static function booted(): void { static::creating(function (): void { if (! self::$creacionDesdeAdmision) { throw new \LogicException('Los residentes solo pueden crearse mediante la admisión formal.'); } }); }
    public static function crearDesdeAdmision(array $attributes): self { self::$creacionDesdeAdmision=true; try { return self::query()->create($attributes); } finally { self::$creacionDesdeAdmision=false; } }
    public function admisiones(): HasMany { return $this->hasMany(Admision::class,'cod_residente','cod_residente'); }
    public function contactos(): BelongsToMany { return $this->belongsToMany(Contacto::class,'residentes_contactos','cod_residente','cod_contacto')->using(ResidenteContacto::class); }
    public function vinculosContacto(): HasMany { return $this->hasMany(ResidenteContacto::class,'cod_residente','cod_residente'); }
    public function ocupacionesCama(): HasMany { return $this->hasMany(OcupacionCama::class,'cod_residente','cod_residente'); }
    public function ocupacionActiva(): HasOne { return $this->hasOne(OcupacionCama::class,'cod_residente','cod_residente')->where('estado','ACTIVA'); }
    public function atenciones(): HasMany { return $this->hasMany(Atencion::class,'cod_residente','cod_residente'); }
    public function prescripciones(): HasMany { return $this->hasMany(Prescripcion::class,'cod_residente','cod_residente'); }
}
