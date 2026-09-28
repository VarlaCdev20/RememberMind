<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Actividad extends ModeloOperativo
{
    protected $table = 'actividades';
    protected $primaryKey = 'cod_actividad';

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'datetime',
        ];
    }

    public function participantes(): HasMany
    {
        return $this->hasMany(ParticipanteActividad::class, 'cod_actividad', 'cod_actividad');
    }

    public function residentes(): BelongsToMany
    {
        return $this->belongsToMany(Residente::class, 'participantes_actividad', 'cod_actividad', 'cod_residente');
    }

    public function adultoMayor(): HasOneThrough
    {
        return $this->hasOneThrough(
            Residente::class,
            ParticipanteActividad::class,
            'cod_actividad',
            'cod_residente',
            'cod_actividad',
            'cod_residente'
        );
    }

    public function getCodActAdulAttribute(): string
    {
        return $this->cod_actividad;
    }

    public function getCodTipoActAttribute(): string
    {
        return $this->tipo;
    }

    public function getTipoActividadAttribute(): object
    {
        return (object) [
            'cod_tipo_act' => $this->tipo,
            'tipo' => $this->tipo,
            'nombre' => $this->tipo,
        ];
    }

    public function getFechaAttribute()
    {
        return $this->fecha_hora;
    }

    public function getHoraAttribute(): ?string
    {
        return $this->fecha_hora?->format('H:i:s');
    }

    public function getObsAttribute(): ?string
    {
        return $this->observacion ?: $this->descripcion;
    }

    public function getUpdatedAtAttribute()
    {
        return $this->fecha_hora;
    }

    public static function normalizarEstado(string $estado): array
    {
        $estado = strtoupper(trim($estado));
        return match ($estado) {
            'REALIZADA', 'COMPLETADA', 'FINALIZADA' => [
                'etiqueta' => 'Realizada',
                'clase' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            ],
            'CANCELADA', 'ANULADA' => [
                'etiqueta' => 'Cancelada',
                'clase' => 'bg-rose-50 text-rose-700 border-rose-200',
            ],
            'REPROGRAMADA' => [
                'etiqueta' => 'Reprogramada',
                'clase' => 'bg-amber-50 text-amber-700 border-amber-200',
            ],
            default => [
                'etiqueta' => 'Programada',
                'clase' => 'bg-sky-50 text-sky-700 border-sky-200',
            ],
        };
    }
}