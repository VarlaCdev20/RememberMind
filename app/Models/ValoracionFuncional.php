<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValoracionFuncional extends ModeloOperativo
{
    protected $table = 'valoraciones_funcionales';
    protected $primaryKey = 'cod_valoracion_funcional';

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'necesita_supervision' => 'boolean'];
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function scopeVigente(Builder $query): Builder
    {
        return $query->whereIn('estado', ['ACTIVA', 'VIGENTE']);
    }

    public function scopeHistorica(Builder $query): Builder
    {
        return $query->whereNotIn('estado', ['ACTIVA', 'VIGENTE']);
    }

    public function getAdultoMayorAttribute()
    {
        return $this->residente;
    }

    public function getPreadmisionAttribute()
    {
        return null;
    }

    public function getFechaValoracionAttribute(): mixed
    {
        return $this->fecha_hora;
    }

    public function getHoraValoracionAttribute(): ?string
    {
        return $this->fecha_hora?->format('H:i:s');
    }

    public function getCodValEnfAttribute(): string
    {
        return (string) $this->cod_valoracion_funcional;
    }

    public function getCodValoracionAttribute(): string
    {
        return (string) $this->cod_valoracion_funcional;
    }

    public function getObservacionAttribute(): ?string
    {
        return $this->conclusion;
    }

    public function getEstadoGeneralAttribute(): string
    {
        return $this->nivel_dependencia ?? 'REGULAR';
    }

    public function getNivelConcienciaAttribute(): string
    {
        return $this->necesita_supervision ? 'SUPERVISIÓN REQUERIDA' : 'CONSCIENTE / AUTÓNOMO';
    }

    public function getOrientacionAttribute(): ?string
    {
        return $this->conclusion ?? 'ORIENTADO EN TIEMPO Y ESPACIO';
    }

    public function getComunicacionAttribute(): ?string
    {
        return 'ADECUADA';
    }

    public function getSignosVitalesInicialesAttribute(): ?string
    {
        return null;
    }
}