<?php

namespace App\Models;

use App\Compartido\Infraestructura\Persistencia\GeneradorCodigo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

class Alerta extends ModeloOperativo
{
    /** Estados operativos observados. No constituyen un nuevo catálogo físico. */
    public const ESTADOS_ACTIVOS = ['ABIERTA', 'RECONOCIDA', 'ASIGNADA', 'EN_ATENCION', 'PENDIENTE'];

    public const ESTADOS_TERMINALES = ['CERRADA', 'ANULADA'];

    public const PRIORIDADES_ALTAS = ['ALTO', 'ALTA', 'CRITICO', 'CRITICA'];

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
        return $query->whereIn('estado', self::ESTADOS_ACTIVOS);
    }

    public function scopeCriticas($query)
    {
        return $query->whereIn('prioridad', ['CRITICO', 'CRITICA']);
    }

    public function scopePorResidente($query, string $codResidente)
    {
        return $query->where('cod_residente', $codResidente);
    }

    public function puedeCerrarse(): bool
    {
        return in_array($this->estado, [...self::ESTADOS_ACTIVOS, 'ATENDIDA'], true);
    }

    public static function coloresPorNivel(): array
    {
        return [
            'BAJO' => 'text-estado-exito border-estado-exitoBorde bg-estado-exitoBg',
            'MEDIO' => 'text-estado-advertencia border-estado-advertenciaBorde bg-estado-advertenciaBg',
            'ALTO' => 'text-boton-acento border-borde-focus bg-estado-peligroBg',
            'CRITICO' => 'text-white border-red-700 bg-red-600',
        ];
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $model) {
            if (empty($model->cod_alerta)) {
                $model->cod_alerta = app(GeneradorCodigo::class)->siguiente('alertas', $model->getConnectionName());
            }

            if (empty($model->fecha_hora)) {
                $model->fecha_hora = now();
            }

            if (empty($model->tipo)) {
                $model->tipo = 'CLINICA';
            }

            if (empty($model->prioridad)) {
                $model->prioridad = 'MEDIO';
            }

            if (empty($model->generacion)) {
                $model->generacion = 'MANUAL';
            }

            if (empty($model->estado)) {
                $model->estado = 'ABIERTA';
            }

            if (empty($model->titulo)) {
                $model->titulo = ! empty($model->descripcion) ? mb_substr((string) $model->descripcion, 0, 100) : 'Alerta clínica';
            }

            if (empty($model->descripcion)) {
                $model->descripcion = $model->titulo ?: 'Alerta sin descripción detallada';
            }

            if (empty($model->cod_residente)) {
                throw new LogicException('La alerta requiere un residente explícito.');
            }

            $validCols = [
                'cod_alerta', 'cod_residente', 'cod_personal_responsable',
                'tipo', 'prioridad', 'generacion', 'titulo', 'descripcion',
                'fecha_hora', 'fecha_hora_limite', 'modulo', 'cod_registro', 'estado',
            ];
            foreach (array_keys($model->attributes) as $key) {
                if (! in_array($key, $validCols, true)) {
                    unset($model->attributes[$key]);
                }
            }
        });
    }
}
