<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipanteActividad extends ModeloOperativo
{
    protected $table = 'participantes_actividad';
    protected $primaryKey = 'cod_participante';

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(Actividad::class, 'cod_actividad', 'cod_actividad');
    }

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }
}