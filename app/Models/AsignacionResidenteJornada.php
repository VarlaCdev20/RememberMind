<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class AsignacionResidenteJornada extends RegistroConsumo
{
    protected const TIPO = 'RESIDENTE';

    protected const ENTIDAD = 'asignaciones_residente_jornada';

    protected $table = 'asignaciones_jornada';

    protected $primaryKey = 'cod_asignacion_jornada';

    protected $appends = ['cod_asignacion'];

    protected $hidden = ['cod_asignacion_jornada', 'tipo_evento', 'cod_asignacion_personal', 'cod_area', 'funcion', 'tipo_asignacion', 'fecha_asignacion'];

    public function getCodAsignacionAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodAsignacionAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

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
        parent::booted();

        static::creating(function (self $asig): void {
            if (empty($asig->cod_asignacion)) {
                $asig->cod_asignacion = app(\App\Compartido\Infraestructura\Persistencia\GeneradorCodigo::class)->siguiente('asignaciones_residente_jornada', $asig->getConnectionName());
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
