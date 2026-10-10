<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// O.R.I.O.N. 4.16.16; restricciones físicas D-137.
class ResultadoCriterio extends ModeloExperto
{
    protected $table = 'resultados_criterio';

    protected $primaryKey = 'cod_resultado_criterio';

    protected function casts(): array
    {
        return [
            'fecha_hora_determinacion' => 'immutable_datetime',
        ];
    }

    public function evaluacionCriterio(): BelongsTo
    {
        return $this->belongsTo(EvaluacionCriterio::class, 'cod_evaluacion_criterio', 'cod_evaluacion_criterio');
    }

    public function valor(): BelongsTo
    {
        return $this->belongsTo(ValorSemantico::class, 'cod_valor_semantico', 'cod_valor_semantico');
    }
}
