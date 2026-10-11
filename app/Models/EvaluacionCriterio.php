<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

// O.R.I.O.N. 4.16.13; restricciones físicas D-137.
class EvaluacionCriterio extends ModeloExperto
{
    protected $table = 'evaluacion_criterios';

    protected $primaryKey = 'cod_evaluacion_criterio';

    protected function casts(): array
    {
        return [
            'fecha_hora_determinacion' => 'immutable_datetime',
        ];
    }

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(EvaluacionExperta::class, 'cod_evaluacion_experta', 'cod_evaluacion_experta');
    }

    public function criterio(): BelongsTo
    {
        return $this->belongsTo(NodoSemantico::class, 'cod_nodo_criterio', 'cod_nodo_semantico');
    }

    public function evidenciasCriterioEvaluacion(): HasMany
    {
        return $this->hasMany(EvidenciaCriterioEvaluacion::class, 'cod_evaluacion_criterio', 'cod_evaluacion_criterio');
    }

    public function resultado(): HasOne
    {
        return $this->hasOne(ResultadoCriterio::class, 'cod_evaluacion_criterio', 'cod_evaluacion_criterio');
    }

    public function traza(): HasOne
    {
        return $this->hasOne(TrazaInferencia::class, 'cod_evaluacion_criterio', 'cod_evaluacion_criterio');
    }
}
