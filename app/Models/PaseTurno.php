<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PaseTurno extends ModeloOperativo
{
    protected $table = 'pases_turno';
    protected $primaryKey = 'cod_pase';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    public const ESTADO_BORRADOR = 'BORRADOR';
    public const ESTADO_ENTREGADO = 'ENTREGADO';
    public const ESTADO_RECIBIDO = 'RECIBIDO';
    public const ESTADO_ANULADO = 'ANULADO';

    protected $fillable = [
        'cod_pase',
        'cod_residente',
        'cod_jornada_saliente',
        'cod_jornada_entrante',
        'cod_personal_saliente',
        'cod_personal_entrante',
        'fecha_hora',
        'estado_general',
        'resumen',
        'pendientes',
        'vigilancia',
        'recomendacion',
        'estado',
        'fecha_hora_recepcion',
        'observacion_recepcion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'fecha_hora_recepcion' => 'datetime',
        ];
    }

    public function newEloquentBuilder($query)
    {
        return new class($query) extends \Illuminate\Database\Eloquent\Builder {
            public function where($column, $operator = null, $value = null, $boolean = 'and')
            {
                if ($column === 'cod_am') {
                    $column = 'cod_residente';
                }
                return parent::where($column, $operator, $value, $boolean);
            }
        };
    }

    public function setCodAmAttribute($value): void
    {
        $this->attributes['cod_residente'] = $value;
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function jornadaSaliente(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'cod_jornada_saliente', 'cod_jornada');
    }

    public function jornadaEntrante(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'cod_jornada_entrante', 'cod_jornada');
    }

    public function personalSaliente(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal_saliente', 'cod_personal');
    }

    public function personalEntrante(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal_entrante', 'cod_personal');
    }

    // Estados del ciclo de vida
    public function esBorrador(): bool
    {
        return $this->estado === 'BORRADOR';
    }

    public function esEntregado(): bool
    {
        return in_array($this->estado, ['ENTREGADO', 'GENERADO'], true);
    }

    public function esRecibido(): bool
    {
        return $this->estado === 'RECIBIDO';
    }

    public function esAnulado(): bool
    {
        return $this->estado === 'ANULADO';
    }

    public function puedeRecibirse(): bool
    {
        return in_array($this->estado, ['ENTREGADO', 'GENERADO', 'PENDIENTE', 'EMITIDO'], true);
    }

    public function puedeEditarse(): bool
    {
        return $this->estado === 'BORRADOR';
    }

    public function getTurnoSalienteAttribute()
    {
        return $this->jornadaSaliente?->turno;
    }

    public function getTurnoEntranteAttribute()
    {
        return $this->jornadaEntrante?->turno;
    }

    public function getEnfermeroSalienteAttribute()
    {
        return $this->personalSaliente?->usuario;
    }

    public function getEnfermeroEntranteAttribute()
    {
        return $this->personalEntrante?->usuario;
    }

    public function getCodAmAttribute(): string
    {
        return (string) ($this->attributes['cod_residente'] ?? '');
    }

    public function getFechaRecibidoAttribute(): ?\Carbon\Carbon
    {
        return $this->fecha_hora_recepcion ?? ($this->estado === 'RECIBIDO' ? now() : null);
    }

    public function getFechaAttribute(): mixed
    {
        return $this->fecha_hora;
    }

    public function getHoraInicioAttribute(): mixed
    {
        return $this->fecha_hora?->format('H:i:s');
    }

    public function getObservacionesAttribute(): ?string
    {
        return $this->resumen;
    }

    public function getAlertasActivasJsonAttribute(): array
    {
        if (!empty($this->pendientes)) {
            $decoded = json_decode($this->pendientes, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [];
    }

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $model) {
            if (empty($model->cod_pase)) {
                $model->cod_pase = 'PAS_' . strtoupper(Str::random(10));
            }
            if (empty($model->fecha_hora)) {
                $model->fecha_hora = now();
            }
            if (empty($model->estado)) {
                $model->estado = 'BORRADOR';
            }
        });
    }
}
