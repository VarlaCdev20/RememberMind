<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// O.R.I.O.N. 4.16.12; restricciones físicas D-137.
class RelacionEvidenciaEvaluacion extends ModeloExperto
{
    protected $table = 'relaciones_evidencias_evaluacion';

    protected $primaryKey = 'cod_relacion_evidencia';

    protected function casts(): array
    {
        return [
            'fecha_hora_creacion' => 'immutable_datetime',
        ];
    }

    public function evidenciaOrigen(): BelongsTo
    {
        return $this->belongsTo(EvidenciaEvaluacion::class, 'cod_evidencia_origen', 'cod_evidencia_evaluacion');
    }

    public function evidenciaDestino(): BelongsTo
    {
        return $this->belongsTo(EvidenciaEvaluacion::class, 'cod_evidencia_destino', 'cod_evidencia_evaluacion');
    }
}
