<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Turno extends ModeloOperativo
{
    protected $table = 'turnos';
    protected $primaryKey = 'cod_turno';

    public function jornadas(): HasMany
    {
        return $this->hasMany(Jornada::class, 'cod_turno', 'cod_turno');
    }

    public function programaciones(): HasMany
    {
        return $this->hasMany(ProgramacionCuidado::class, 'cod_turno', 'cod_turno');
    }

    public function setHoraFinAttribute($value): void
    {
        $this->attributes['hora_cierre'] = $value;
    }

    public function getHoraFinAttribute(): mixed
    {
        return $this->attributes['hora_cierre'] ?? null;
    }

    public function setTipoAttribute($value): void
    {
        // No existe columna tipo en V2
    }

    public function setFechaAttribute($value): void
    {
        // No existe columna fecha en V2
    }

    public function setActivoAttribute($value): void
    {
        $this->attributes['estado'] = $value ? 'ACTIVO' : 'INACTIVO';
    }

    protected static function boot(): void
    {
        parent::boot();
        static::saving(function (self $turno) {
            if (empty($turno->attributes['cod_turno'])) {
                $turno->attributes['cod_turno'] = 'TUR_' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(10));
            }
            if (!isset($turno->attributes['orden'])) {
                $turno->attributes['orden'] = 1;
            }
            if (!isset($turno->attributes['estado'])) {
                $turno->attributes['estado'] = 'ACTIVO';
            }
            unset($turno->attributes['tipo'], $turno->attributes['fecha'], $turno->attributes['activo'], $turno->attributes['hora_fin']);
        });
    }
}