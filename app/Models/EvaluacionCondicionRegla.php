<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// O.R.I.O.N. 4.16.22; restricciones físicas D-137.
class EvaluacionCondicionRegla extends ModeloExperto
{
    protected $table = 'evaluaciones_condiciones_regla';

    protected $primaryKey = 'cod_evaluacion_condicion';

    protected function casts(): array
    {
        return [
            'fecha_hora_evaluacion' => 'immutable_datetime',
        ];
    }

    public function evaluacionRegla(): BelongsTo
    {
        return $this->belongsTo(EvaluacionRegla::class, 'cod_evaluacion_regla', 'cod_evaluacion_regla');
    }

    public function condicion(): BelongsTo
    {
        return $this->belongsTo(CondicionReglaExperta::class, 'cod_condicion_regla', 'cod_condicion_regla');
    }

    public function evidenciasSoporteCondicion(): HasMany
    {
        return $this->hasMany(EvidenciaSoporteCondicion::class, 'cod_evaluacion_condicion', 'cod_evaluacion_condicion');
    }
}
