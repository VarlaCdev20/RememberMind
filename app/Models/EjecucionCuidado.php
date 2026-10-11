<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use LogicException;

class EjecucionCuidado extends ModeloOperativo
{
    protected $table = 'ejecuciones_cuidado';
    protected $primaryKey = 'cod_ejecucion';

    protected $fillable = [
        'cod_ejecucion', 'cod_intervencion', 'cod_residente', 'cod_jornada',
        'cod_personal', 'fecha_hora_programada', 'fecha_hora_ejecucion',
        'resultado', 'motivo_omision', 'estado', 'observacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora_programada' => 'datetime',
            'fecha_hora_ejecucion' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (EjecucionCuidado $model) {
            if (empty($model->cod_ejecucion)) {
                $model->cod_ejecucion = 'EJE_'.Str::upper(Str::random(10));
            }
            if (empty($model->cod_residente)) {
                throw new LogicException('La ejecución de cuidado requiere un residente explícito.');
            }
            if (empty($model->cod_intervencion)) {
                throw new LogicException('La ejecución de cuidado requiere una intervención explícita.');
            }
            if (empty($model->cod_jornada)) {
                throw new LogicException('La ejecución de cuidado requiere una jornada explícita.');
            }
            if (empty($model->cod_personal)) {
                throw new LogicException('La ejecución de cuidado requiere un profesional explícito.');
            }
            if (empty($model->attributes['estado'])) {
                $model->estado = 'PENDIENTE';
            }
        });
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function intervencion(): BelongsTo
    {
        return $this->belongsTo(IntervencionCuidado::class, 'cod_intervencion', 'cod_intervencion');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'cod_jornada', 'cod_jornada');
    }

    public function puedeCompletarse(): bool
    {
        return in_array($this->estado, ['PENDIENTE', 'EN_PROCESO'], true);
    }

    // Accessors de compatibilidad con V1
    public function getCodRegistroCuidadoAttribute(): string
    {
        return (string) $this->cod_ejecucion;
    }

    public function getCodTareaAttribute(): string
    {
        return (string) $this->cod_ejecucion;
    }

    public function getTituloAttribute(): string
    {
        return $this->intervencion?->nombre ?? 'Cuidado Asistencial';
    }

    public function getAreaAttribute(): string
    {
        return 'CUIDADOS';
    }

    public function getHoraProgramadaAttribute(): string
    {
        return $this->fecha_hora_programada ? $this->fecha_hora_programada->format('H:i') : '--:--';
    }

    public function getPrioridadAttribute(): string
    {
        return 'MEDIA';
    }
}
