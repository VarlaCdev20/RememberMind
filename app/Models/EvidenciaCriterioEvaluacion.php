<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// O.R.I.O.N. 4.16.14; restricciones físicas D-137.
class EvidenciaCriterioEvaluacion extends ModeloExperto
{
    protected $table = 'evidencias_criterio_evaluacion';

    protected $primaryKey = 'cod_evidencia_criterio';

    protected function casts(): array
    {
        return [
            'fecha_hora_vinculacion' => 'immutable_datetime',
        ];
    }

    public function evaluacionCriterio(): BelongsTo
    {
        return $this->belongsTo(EvaluacionCriterio::class, 'cod_evaluacion_criterio', 'cod_evaluacion_criterio');
    }

    public function evidencia(): BelongsTo
    {
        return $this->belongsTo(EvidenciaEvaluacion::class, 'cod_evidencia_evaluacion', 'cod_evidencia_evaluacion');
    }
}
