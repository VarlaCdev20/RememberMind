<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Cama extends ModeloOperativo
{
    protected $table = 'camas';
    protected $primaryKey = 'cod_cama';

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class, 'cod_habitacion', 'cod_habitacion');
    }

    public function ocupaciones(): HasMany
    {
        return $this->hasMany(OcupacionCama::class, 'cod_cama', 'cod_cama');
    }

    public function ocupacionActiva(): HasOne
    {
        return $this->hasOne(OcupacionCama::class, 'cod_cama', 'cod_cama')->where('estado', 'ACTIVA');
    }

    public function asignacionesActivas(): HasMany
    {
        return $this->ocupaciones()->where('estado', 'ACTIVA');
    }

    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'DISPONIBLE')->whereDoesntHave('ocupacionActiva');
    }

    public function setNumeroAttribute($value): void
    {
        if (empty($this->attributes['codigo'])) {
            $this->attributes['codigo'] = (string) $value;
        }
    }

    public function getNumeroAttribute(): ?string
    {
        return $this->attributes['codigo'] ?? null;
    }

    protected static function boot(): void
    {
        parent::boot();
        static::saving(function (self $cama) {
            if (empty($cama->attributes['cod_cama'])) {
                $cama->attributes['cod_cama'] = 'CAM_' . Str::upper(Str::random(10));
            }
            if (empty($cama->attributes['codigo'])) {
                $cama->attributes['codigo'] = (string) ($cama->attributes['numero'] ?? ('CAM-' . rand(100, 999)));
            }
            unset($cama->attributes['numero']);
        });
    }
}