<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Habitacion extends Model
{
    use GeneraCodigo;
    use SoftDeletes, LogsActivity;

    protected $table      = 'habitaciones';
    protected $primaryKey = 'cod_habitacion';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'HAB';
    protected $digitsCode = 3;
    public $timestamps   = true;

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo_habitacion',
        'ubicacion',
        'capacidad',
        'estado',
        'observacion',
    ];

    protected $casts = [
        'capacidad' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Habitaciones')
            ->setDescriptionForEvent(fn(string $e) => match($e) {
                'created' => "Habitación {$this->codigo} registrada.",
                'updated' => "Habitación {$this->codigo} actualizada.",
                'deleted' => "Habitación {$this->codigo} eliminada.",
                default   => "Habitación {$this->codigo} modificada ({$e}).",
            });
    }

    // ── Relaciones ─────────────────────────────────────────────────────────────

    public function camas(): HasMany
    {
        return $this->hasMany(Cama::class, 'cod_habitacion', 'cod_habitacion');
    }

    public function camasDisponibles(): HasMany
    {
        return $this->camas()->where('estado', 'DISPONIBLE');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeDisponibles($query)
    {
        return $query->where('estado', 'DISPONIBLE');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->where('tipo_habitacion', $tipo);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function getCapacidadDisponibleAttribute(): int
    {
        return $this->camasDisponibles()->count();
    }
}
