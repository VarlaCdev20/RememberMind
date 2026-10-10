<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// O.R.I.O.N. 4.16.18; restricciones físicas D-137.
class CondicionReglaExperta extends ModeloExperto
{
    protected $table = 'condiciones_regla_experta';

    protected $primaryKey = 'cod_condicion_regla';

    public function regla(): BelongsTo
    {
        return $this->belongsTo(ReglaExperta::class, 'cod_regla_experta', 'cod_regla_experta');
    }

    public function variable(): BelongsTo
    {
        return $this->belongsTo(VariableExperta::class, 'cod_variable_experta', 'cod_variable_experta');
    }

    public function valor(): BelongsTo
    {
        return $this->belongsTo(ValorSemantico::class, 'cod_valor_semantico', 'cod_valor_semantico');
    }

    public function evaluacionesCondicionesRegla(): HasMany
    {
        return $this->hasMany(EvaluacionCondicionRegla::class, 'cod_condicion_regla', 'cod_condicion_regla');
    }
}
