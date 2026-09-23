<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Alerta extends ModeloOperativo
{
    protected $table = 'alertas';

    protected $primaryKey = 'cod_alerta';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'cod_alerta',
        'cod_residente',
        'cod_personal_responsable',
        'tipo',
        'prioridad',
        'modulo',
        'cod_registro',
        'titulo',
        'descripcion',
        'fecha_hora',
        'fecha_hora_limite',
        'generacion',
        'estado',
        'cod_am',
        'nivel',
        'motivo',
        'responsable_id',
        'tipo_alerta',
        'cod_turno',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'fecha_hora_limite' => 'datetime',
        ];
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(EventoAlerta::class, 'cod_alerta', 'cod_alerta');
    }

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal_responsable', 'cod_personal');
    }

    // Scopes
    public function scopeAbiertas($query)
    {
        return $query->whereIn('estado', ['ABIERTA', 'EN_ATENCION']);
    }

    public function scopeCriticas($query)
    {
        return $query->where('prioridad', 'CRITICO');
    }

    public function scopePorAdulto($query, string $codAm)
    {
        return $query->where('cod_residente', $codAm);
    }

    public function puedeCerrarse(): bool
    {
        return in_array($this->estado, ['ABIERTA', 'EN_ATENCION'], true);
    }

    public static function coloresPorNivel(): array
    {
        return [
            'BAJO'   => 'text-estado-exito border-estado-exitoBorde bg-estado-exitoBg',
            'MEDIO'  => 'text-estado-advertencia border-estado-advertenciaBorde bg-estado-advertenciaBg',
            'ALTO'   => 'text-boton-acento border-borde-focus bg-estado-peligroBg',
            'CRITICO'=> 'text-white border-red-700 bg-red-600',
        ];
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $model) {
            if (empty($model->cod_alerta)) {
                $model->cod_alerta = 'ALA_' . strtoupper(Str::random(10));
            }

            if (isset($model->attributes['cod_am']) && empty($model->cod_residente)) {
                $model->cod_residente = $model->attributes['cod_am'];
            }

            if (isset($model->attributes['nivel']) && empty($model->attributes['prioridad'])) {
                $model->prioridad = $model->attributes['nivel'];
            }

            if (isset($model->attributes['motivo']) && empty($model->attributes['descripcion'])) {
                $model->descripcion = $model->attributes['motivo'];
            }

            if (isset($model->attributes['tipo_alerta']) && empty($model->attributes['tipo'])) {
                $model->tipo = $model->attributes['tipo_alerta'];
            }

            if (isset($model->attributes['responsable_id']) && empty($model->attributes['cod_personal_responsable'])) {
                $model->cod_personal_responsable = $model->attributes['responsable_id'];
            }

            if (empty($model->fecha_hora)) {
                $model->fecha_hora = now();
            }

            if (empty($model->tipo)) {
                $model->tipo = 'CLINICA';
            }

            if (empty($model->prioridad)) {
                $model->prioridad = 'MEDIA';
            }

            if (empty($model->generacion)) {
                $model->generacion = 'MANUAL';
            }

            if (empty($model->estado)) {
                $model->estado = 'ABIERTA';
            }

            if (empty($model->titulo)) {
                $model->titulo = !empty($model->descripcion) ? mb_substr((string)$model->descripcion, 0, 100) : 'Alerta clínica';
            }

            if (empty($model->descripcion)) {
                $model->descripcion = $model->titulo ?: 'Alerta sin descripción detallada';
            }

            if (!empty($model->cod_personal_responsable) && !Personal::where('cod_personal', $model->cod_personal_responsable)->exists()) {
                $model->cod_personal_responsable = null;
            }

            if (empty($model->cod_residente)) {
                $model->cod_residente = $model->attributes['cod_am'] ?? Residente::value('cod_residente');
                if (empty($model->cod_residente)) {
                    $res = Residente::first() ?? Residente::factory()->create();
                    $model->cod_residente = $res->cod_residente;
                }
            }

            if (isset($model->attributes['nivel']) && empty($model->prioridad)) {
                $model->prioridad = $model->attributes['nivel'];
            }

            $validCols = [
                'cod_alerta', 'cod_residente', 'cod_personal_responsable',
                'tipo', 'prioridad', 'generacion', 'titulo', 'descripcion',
                'fecha_hora', 'fecha_hora_limite', 'modulo', 'cod_registro', 'estado'
            ];
            foreach (array_keys($model->attributes) as $key) {
                if (!in_array($key, $validCols, true)) {
                    unset($model->attributes[$key]);
                }
            }
        });
    }
}
