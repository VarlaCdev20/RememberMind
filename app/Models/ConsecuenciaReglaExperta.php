<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// O.R.I.O.N. 4.16.19; restricciones físicas D-137.
class ConsecuenciaReglaExperta extends ModeloExperto
{
    protected $table = 'consecuencias_regla_experta';

    protected $primaryKey = 'cod_consecuencia_regla';

    public function regla(): BelongsTo
    {
        return $this->belongsTo(ReglaExperta::class, 'cod_regla_experta', 'cod_regla_experta');
    }

    public function criterioDominioResultado(): BelongsTo
    {
        return $this->belongsTo(CriterioDominioResultado::class, 'cod_criterio_dominio_resultado', 'cod_criterio_dominio_resultado');
    }

    public function valor(): BelongsTo
    {
        return $this->belongsTo(ValorSemantico::class, 'cod_valor_semantico', 'cod_valor_semantico');
    }
}
