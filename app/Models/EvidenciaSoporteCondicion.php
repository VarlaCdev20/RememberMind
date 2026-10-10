<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// O.R.I.O.N. 4.16.23; restricciones físicas D-137.
class EvidenciaSoporteCondicion extends ModeloExperto
{
    protected $table = 'evidencias_soporte_condicion';

    protected $primaryKey = 'cod_evidencia_soporte_condicion';

    public function evaluacionCondicion(): BelongsTo
    {
        return $this->belongsTo(EvaluacionCondicionRegla::class, 'cod_evaluacion_condicion', 'cod_evaluacion_condicion');
    }

    public function evidencia(): BelongsTo
    {
        return $this->belongsTo(EvidenciaEvaluacion::class, 'cod_evidencia_evaluacion', 'cod_evidencia_evaluacion');
    }
}
