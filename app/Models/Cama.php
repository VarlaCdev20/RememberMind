<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Cama extends Model
{
    use GeneraCodigo;
    use SoftDeletes, LogsActivity;

    protected $table      = 'camas';
    protected $primaryKey = 'cod_cama';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'CAM';
    protected $digitsCode = 3;
    public $timestamps   = true;

    protected $fillable = [
        'cod_cama',
        'cod_habitacion',
        'codigo',
        'numero',
        'estado',
        'observaciones',
        'observacion',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Habitaciones')
            ->setDescriptionForEvent(fn(string $e) => "Cama {$this->codigo} {$e}.");
    }

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function habitacion(): BelongsTo
    {
        return $this->belongsTo(Habitacion::class, 'cod_habitacion', 'cod_habitacion');
    }

    public function asignacionesActivas(): HasMany
    {
        return $this->hasMany(AsignacionAdultoMayor::class, 'cod_cama', 'cod_cama')
            ->whereIn('estado', ['ACTIVO', 'ACTIVA']);
    }

    protected static function booted(): void
    {
        static::saving(function (self $cama) {
            $cama->observaciones ??= $cama->observacion;
            $cama->observacion ??= $cama->observaciones;
        });
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'DISPONIBLE');
    }

    public function scopeDeHabitacion($query, string $codHabitacion)
    {
        return $query->where('cod_habitacion', $codHabitacion);
    }

    public function getObservacionAttribute(): ?string
    {
        return $this->attributes['observacion'] ?? $this->attributes['observaciones'] ?? null;
    }

    public function setObservacionAttribute(?string $value): void
    {
        $this->attributes['observacion'] = $value;
        $this->attributes['observaciones'] = $value;
    }
}
