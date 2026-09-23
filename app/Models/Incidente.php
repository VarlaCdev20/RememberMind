<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Incidente extends ModeloOperativo
{
    protected $table = 'incidentes';
    protected $primaryKey = 'cod_incidente';

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
            'requiere_medico' => 'boolean',
            'requiere_derivacion' => 'boolean',
        ];
    }

    protected static array $columnasValidas = [
        'cod_incidente',
        'cod_residente',
        'cod_personal',
        'cod_jornada',
        'tipo_incidente',
        'gravedad',
        'lugar',
        'fecha_hora',
        'descripcion',
        'medida_inmediata',
        'requiere_medico',
        'requiere_derivacion',
        'estado',
        'observacion',
    ];

    public function setTipoIncidenteAttribute($value): void
    {
        $this->attributes['tipo_incidente'] = $value !== null ? Str::upper(trim(preg_replace('/\s+/', ' ', (string) $value))) : null;
    }

    public function setGravedadAttribute($value): void
    {
        $this->attributes['gravedad'] = $value !== null ? Str::upper(trim((string) $value)) : null;
    }

    public function setEstadoAttribute($value): void
    {
        $this->attributes['estado'] = $value !== null ? Str::upper(trim((string) $value)) : 'ABIERTO';
    }

    public function setDescripcionAttribute($value): void
    {
        $this->attributes['descripcion'] = $value !== null ? trim((string) $value) : '';
    }

    public function setMedidaInmediataAttribute($value): void
    {
        $this->attributes['medida_inmediata'] = $value !== null ? trim((string) $value) : null;
    }

    public function setObservacionAttribute($value): void
    {
        $this->attributes['observacion'] = $value !== null ? trim((string) $value) : null;
    }

    public function getTipoAttribute(): string
    {
        return (string) ($this->attributes['tipo_incidente'] ?? 'INCIDENTE');
    }

    public function getFechaHoraEventoAttribute()
    {
        return $this->fecha_hora;
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function adultoMayor(): BelongsTo
    {
        return $this->residente();
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function registrador(): BelongsTo
    {
        return $this->personal();
    }

    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'cod_jornada', 'cod_jornada');
    }

    public function alertaAsociada(): HasOne
    {
        return $this->hasOne(Alerta::class, 'cod_registro', 'cod_incidente')
            ->where('modulo', 'INCIDENTES');
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class, 'cod_registro', 'cod_incidente')
            ->where('modulo', 'INCIDENTES');
    }

    public function esGrave(): bool
    {
        return in_array(Str::upper(trim((string)$this->gravedad)), ['ALTA', 'GRAVE', 'CRITICA', 'CRÃTICA', 'URGENTE'], true);
    }

    protected static function booted(): void
    {
        static::creating(function (self $registro): void {
            if (empty($registro->cod_incidente)) {
                $registro->cod_incidente = 'INC_' . strtoupper(Str::random(10));
            }
            if (empty($registro->estado)) {
                $registro->estado = 'ABIERTO';
            }
            if (empty($registro->cod_personal)) {
                $pers = auth()->user()?->personal ?? Personal::first();
                if ($pers) {
                    $registro->cod_personal = $pers->cod_personal;
                }
            }
            $registro->attributes = array_intersect_key($registro->attributes, array_flip(static::$columnasValidas));
        });
    }
}
