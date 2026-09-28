<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Habitacion extends ModeloOperativo
{
    protected $table = 'habitaciones';
    protected $primaryKey = 'cod_habitacion';

    public function camas(): HasMany
    {
        return $this->hasMany(Cama::class, 'cod_habitacion', 'cod_habitacion');
    }

    public function camasDisponibles(): HasMany
    {
        return $this->camas()->whereIn('estado', ['ACTIVA', 'DISPONIBLE'])->whereDoesntHave('ocupacionActiva');
    }

    public function asignacionesActivas(): HasManyThrough
    {
        return $this->hasManyThrough(OcupacionCama::class, Cama::class, 'cod_habitacion', 'cod_cama', 'cod_habitacion', 'cod_cama')->where('ocupaciones_cama.estado', 'ACTIVA');
    }

    public function getTipoHabitacionAttribute(): ?string
    {
        return $this->tipo;
    }

    public function setTipoHabitacionAttribute($value): void
    {
        $this->attributes['tipo'] = (string) $value;
    }

    public function getUbicacionAttribute(): ?string
    {
        return $this->piso;
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
        static::saving(function (self $model) { if (empty($model->capacidad)) $model->capacidad = 1; });
        static::saving(function (self $hab) {
            if (empty($hab->attributes['cod_habitacion'])) {
                $hab->attributes['cod_habitacion'] = 'HAB_' . Str::upper(Str::random(10));
            }
            if (empty($hab->attributes['codigo'])) {
                $hab->attributes['codigo'] = (string) ($hab->attributes['numero'] ?? ('HAB-' . rand(100, 999)));
            }
            unset($hab->attributes['numero'], $hab->attributes['tipo_habitacion']);
        });
    }
}