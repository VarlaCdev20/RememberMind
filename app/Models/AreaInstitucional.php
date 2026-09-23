<?php

namespace App\Models;

/** Adaptador de la interfaz histórica sobre la tabla V2 `areas`. */
class AreaInstitucional extends Area
{
    public function scopeActivas($query)
    {
        return $query->whereIn('estado', ['ACTIVA', 'ACTIVO']);
    }

    public function scopeInactivas($query)
    {
        return $query->whereIn('estado', ['INACTIVA', 'INACTIVO']);
    }

    public function getTipoAreaAttribute(): string { return 'INSTITUCIONAL'; }
    public function getResponsableIdAttribute(): ?string { return null; }
    public function getOrdenAttribute(): int { return 0; }
    public function getObservacionesAttribute(): ?string { return $this->observacion; }
    public function getRolesSugeridosAttribute(): array { return []; }
    public function getModulosRelacionadosAttribute(): array { return []; }
    public function getColorAttribute(): string { return '#2F3E5C'; }
    public function getIconoAttribute(): string { return 'ph-buildings'; }
    public function getImagenAreaAttribute(): ?string { return null; }
}
