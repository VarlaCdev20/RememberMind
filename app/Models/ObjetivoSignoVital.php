<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ObjetivoSignoVital extends ModeloOperativo
{
    protected $table = 'objetivos_signos_vitales';
    protected $primaryKey = 'cod_objetivo_signo';

    public const PARAMETROS = [
        'presion_sistolica' => 'Presión sistólica',
        'presion_diastolica' => 'Presión diastólica',
        'frecuencia_cardiaca' => 'Pulso',
        'frecuencia_respiratoria' => 'Respiración',
        'temperatura' => 'Temperatura',
        'saturacion_oxigeno' => 'Saturación de oxígeno',
        'glucemia' => 'Glucemia',
    ];

    protected $fillable = [
        'cod_objetivo_signo', 'cod_residente', 'cod_personal', 'parametro',
        'min_objetivo', 'max_objetivo', 'min_critico', 'max_critico',
        'vigente_desde', 'vigente_hasta', 'estado', 'motivo',
    ];

    protected function casts(): array
    {
        return [
            'vigente_desde' => 'datetime', 'vigente_hasta' => 'datetime',
            'min_objetivo' => 'decimal:2', 'max_objetivo' => 'decimal:2',
            'min_critico' => 'decimal:2', 'max_critico' => 'decimal:2',
        ];
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    protected static function booted(): void
    {
        static::creating(function (self $objetivo): void {
            if (! $objetivo->cod_objetivo_signo) {
                $objetivo->cod_objetivo_signo = 'OSV_'.strtoupper(Str::random(12));
            }
        });
    }
}
