<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// O.R.I.O.N. 4.16.20; restricciones físicas D-137.
class TrazaInferencia extends ModeloExperto
{
    protected $table = 'trazas_inferencia';

    protected $primaryKey = 'cod_traza_inferencia';

    protected function casts(): array
    {
        return [
            'fecha_hora_inicio' => 'immutable_datetime',
            'fecha_hora_fin' => 'immutable_datetime',
        ];
    }

    public function evaluacionCriterio(): BelongsTo
    {
        return $this->belongsTo(EvaluacionCriterio::class, 'cod_evaluacion_criterio', 'cod_evaluacion_criterio');
    }

    public function evaluacionesReglas(): HasMany
    {
        return $this->hasMany(EvaluacionRegla::class, 'cod_traza_inferencia', 'cod_traza_inferencia');
    }
}
