<?php

namespace App\Models;

/** Adaptador de la interfaz histórica sobre la tabla V2 `turnos`. */
class TurnoInstitucional extends Turno
{
    public function scopeActivos($query)
    {
        return $query->whereIn('estado', ['ACTIVO', 'ACTIVA']);
    }

    public function scopeInactivos($query)
    {
        return $query->whereIn('estado', ['INACTIVO', 'INACTIVA']);
    }

    public function getHoraFinAttribute(): mixed { return $this->hora_cierre; }
    public function getDescripcionAttribute(): ?string { return $this->observacion; }
    public function getObservacionesAttribute(): ?string { return $this->observacion; }
    public function getColorAttribute(): string { return '#2F3E5C'; }
}
