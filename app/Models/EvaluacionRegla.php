<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// O.R.I.O.N. 4.16.21; restricciones físicas D-137.
class EvaluacionRegla extends ModeloExperto
{
    protected $table = 'evaluaciones_reglas';

    protected $primaryKey = 'cod_evaluacion_regla';

    protected function casts(): array
    {
        return [
            'fecha_hora_evaluacion' => 'immutable_datetime',
        ];
    }

    public function traza(): BelongsTo
    {
        return $this->belongsTo(TrazaInferencia::class, 'cod_traza_inferencia', 'cod_traza_inferencia');
    }

    public function regla(): BelongsTo
    {
        return $this->belongsTo(ReglaExperta::class, 'cod_regla_experta', 'cod_regla_experta');
    }

    public function evaluacionesCondicionesRegla(): HasMany
    {
        return $this->hasMany(EvaluacionCondicionRegla::class, 'cod_evaluacion_regla', 'cod_evaluacion_regla');
    }
}
