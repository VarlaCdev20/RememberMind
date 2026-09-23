<?php

namespace App\Models;

/** Adaptador de la interfaz de Enfermería sobre la tabla V2 `turnos`. */
class TurnoEnfermeria extends Turno
{
    public function scopeActivos($query)
    {
        return $query->whereIn('estado', ['ACTIVO', 'ACTIVA'])->orderBy('orden');
    }

    public function getHoraFinAttribute(): mixed
    {
        return $this->hora_cierre;
    }

    public function getHorarioAttribute(): string
    {
        return substr((string) $this->hora_inicio, 0, 5).' – '.substr((string) $this->hora_cierre, 0, 5);
    }
}
