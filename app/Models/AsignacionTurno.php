<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AsignacionTurno extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'asignaciones_turno';
    protected $primaryKey = 'cod_asignacion';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_asignacion',
        'cod_usu',
        'cod_area',
        'cod_turno',
        'dias_semana',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'tipo_asignacion',
        'observaciones',
        'creado_por',
        'actualizado_por'
    ];

    protected $casts = [
        'dias_semana' => 'array',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date'
    ];

    /**
     * Spatie Activitylog options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('TurnosAsignaciones')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Se creó la asignación de turno '{$this->cod_asignacion}' para el usuario ID '{$this->cod_usu}' en el turno '{$this->cod_turno}'.",
                    'updated' => "Se actualizó la asignación de turno '{$this->cod_asignacion}' para el usuario ID '{$this->cod_usu}'.",
                    'deleted' => "Se eliminó/archivó la asignación de turno '{$this->cod_asignacion}'.",
                    default   => "Asignación de turno {$this->cod_asignacion} modificada ({$eventName}).",
                };
            });
    }

    protected static function booted(): void
    {
        static::creating(function ($asig) {
            if (!$asig->cod_asignacion) {
                $ultimo = self::withTrashed()
                    ->where('cod_asignacion', 'like', 'AST_%')
                    ->orderByDesc('cod_asignacion')
                    ->value('cod_asignacion');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 4)) + 1
                    : 1;

                $asig->cod_asignacion = 'AST_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Relationship with user
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    /**
     * Relationship with area
     */
    public function area()
    {
        return $this->belongsTo(AreaInstitucional::class, 'cod_area', 'cod_area');
    }

    /**
     * Relationship with turn
     */
    public function turno()
    {
        return $this->belongsTo(TurnoInstitucional::class, 'cod_turno', 'cod_turno');
    }

    /**
     * Relationship with creator/editor
     */
    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por', 'cod_usu');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'actualizado_por', 'cod_usu');
    }

    /**
     * Scopes
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 'ACTIVA');
    }

    public function scopeInactivas($query)
    {
        return $query->where('estado', 'INACTIVA');
    }

    public function scopeFinalizadas($query)
    {
        return $query->where('estado', 'FINALIZADA');
    }
}
