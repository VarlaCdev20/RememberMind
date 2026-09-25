<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use LogicException;

class PlanCuidado extends ModeloOperativo
{
    protected $table = 'planes_cuidado';

    protected $primaryKey = 'cod_plan';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'cod_plan', 'cod_residente', 'cod_area', 'cod_personal', 'tipo_plan',
        'nombre', 'objetivo_general', 'prioridad', 'fecha_hora_apertura',
        'fecha_hora_cierre', 'estado', 'observacion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora_apertura' => 'datetime',
            'fecha_hora_cierre' => 'datetime',
        ];
    }

    public function intervenciones(): HasMany
    {
        return $this->hasMany(IntervencionCuidado::class, 'cod_plan', 'cod_plan');
    }

    public function tareasActivas(): HasMany
    {
        return $this->intervenciones()->whereIn('estado', ['ACTIVA', 'ACTIVO']);
    }

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_residente', 'cod_residente');
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'cod_area', 'cod_area');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }


    public function getCodAmAttribute(): string
    {
        return (string) ($this->attributes['cod_residente'] ?? '');
    }

    public function getNivelCuidadoAttribute(): string
    {
        return (string) ($this->attributes['prioridad'] ?? 'MODERADO');
    }

    public function scopeActivos($query)
    {
        return $query->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE']);
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $model) {
            if (empty($model->cod_plan)) {
                $model->cod_plan = 'PLC_' . strtoupper(Str::random(10));
            }

            if (empty($model->cod_area)) {
                throw new LogicException('El plan de cuidado requiere un área responsable explícita.');
            }
            if (empty($model->cod_personal)) {
                throw new LogicException('El plan de cuidado requiere un profesional responsable explícito.');
            }
            if (empty($model->tipo_plan)) {
                $model->tipo_plan = 'ENFERMERIA';
            }
            if (empty($model->nombre)) {
                $model->nombre = 'Plan de Cuidados de Enfermería';
            }
            if (empty($model->objetivo_general)) {
                $model->objetivo_general = 'Mantenimiento y cuidado integral del residente';
            }
            if (empty($model->prioridad)) {
                $model->prioridad = 'MEDIA';
            }
            if (empty($model->fecha_hora_apertura)) {
                $model->fecha_hora_apertura = now();
            }
            if (empty($model->estado)) {
                $model->estado = 'ACTIVO';
            }
        });
    }
}
