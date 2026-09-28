<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use LogicException;

class AsignacionResidenteJornada extends ModeloOperativo
{
    protected $table = 'asignaciones_residente_jornada';
    protected $primaryKey = 'cod_asignacion';

    /** Columnas exactas de asignaciones_residente_jornada en PostgreSQL V2. */
    protected $fillable = [
        'cod_asignacion', 'cod_residente', 'cod_jornada', 'cod_personal',
        'nivel_supervision', 'fecha_hora', 'estado', 'observacion',
    ];

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $asig): void {
            if (empty($asig->cod_asignacion)) {
                $asig->cod_asignacion = 'ARJ_'.Str::upper(Str::random(10));
            }
            if (empty($asig->fecha_hora)) {
                $asig->fecha_hora = now();
            }
            if (empty($asig->cod_jornada)) {
                throw new LogicException('La asignación de residente requiere una jornada explícita.');
            }
            if (empty($asig->cod_personal)) {
                throw new LogicException('La asignación de residente requiere un profesional explícito.');
            }
        });
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    /** Nombre conservado para las vistas existentes; la entidad real es Residente. */
    public function adultoMayor(): BelongsTo
    {
        return $this->residente();
    }

    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'cod_jornada', 'cod_jornada');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    // Propiedades calculadas para UI; las consultas deben usar columnas V2.
    public function getTurnoAttribute(): mixed
    {
        return $this->jornada?->turno;
    }

    public function getEnfermeroAttribute(): mixed
    {
        return $this->personal?->usuario;
    }

    public function getCodTurnoAttribute(): ?string
    {
        return $this->jornada?->cod_turno;
    }
}
