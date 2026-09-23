<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EjecucionCuidado extends ModeloOperativo
{
    protected $table = 'ejecuciones_cuidado';
    protected $primaryKey = 'cod_ejecucion';

    protected function casts(): array
    {
        return [
            'fecha_hora_programada' => 'datetime',
            'fecha_hora_ejecucion' => 'datetime',
        ];
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function intervencion(): BelongsTo
    {
        return $this->belongsTo(IntervencionCuidado::class, 'cod_intervencion', 'cod_intervencion');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class, 'cod_jornada', 'cod_jornada');
    }

    // [TEMPORAL LEGACY V1] Alias temporal para compatibilidad hacia atras en vistas heredadas
    public function getCodRegistroCuidadoAttribute(): string
    {
        return (string) $this->cod_ejecucion;
    }
}
