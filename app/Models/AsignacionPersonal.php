<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionPersonal extends ModeloOperativo
{
    protected $table = 'asignaciones_personal';
    protected $primaryKey = 'cod_asignacion_personal';

    /**
     * Solo se permiten columnas físicas de la BDD V2. La aplicación debe resolver
     * usuario, personal, jornada y área antes de crear la asignación.
     */
    protected $fillable = [
        'cod_asignacion_personal', 'cod_jornada', 'cod_personal', 'cod_area',
        'funcion', 'tipo_asignacion', 'fecha_asignacion', 'estado', 'observacion',
    ];

    protected function casts(): array
    {
        return ['fecha_asignacion' => 'datetime'];
    }

    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'cod_jornada', 'cod_jornada');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'cod_area', 'cod_area');
    }

    // Accessors de presentación: nunca cambian columnas ni reescriben SQL.
    public function getDiaSemanaAttribute(): ?string
    {
        return $this->jornada?->fecha_jornada?->locale('es')->isoFormat('dddd');
    }

    public function getHoraInicioAttribute(): ?string
    {
        return $this->jornada?->turno?->hora_inicio;
    }

    public function getHoraFinAttribute(): ?string
    {
        return $this->jornada?->turno?->hora_cierre;
    }

    public function getCodTurnoAttribute(): ?string
    {
        return $this->jornada?->cod_turno;
    }

    public function getCodUsuarioAttribute(): ?string
    {
        return $this->personal?->cod_usuario;
    }
}
