<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// O.R.I.O.N. 4.16.15; restricciones físicas D-137.
class CriterioDominioResultado extends ModeloExperto
{
    protected $table = 'criterios_dominios_resultado';

    protected $primaryKey = 'cod_criterio_dominio_resultado';

    protected function casts(): array
    {
        return [
            'fecha_hora_creacion' => 'immutable_datetime',
        ];
    }

    public function criterio(): BelongsTo
    {
        return $this->belongsTo(NodoSemantico::class, 'cod_nodo_criterio', 'cod_nodo_semantico');
    }

    public function dominio(): BelongsTo
    {
        return $this->belongsTo(DominioValoresExperto::class, 'cod_dominio_valores', 'cod_dominio_valores');
    }

    public function usuarioCreacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_creacion', 'cod_usuario');
    }

    public function consecuenciasReglaExperta(): HasMany
    {
        return $this->hasMany(ConsecuenciaReglaExperta::class, 'cod_criterio_dominio_resultado', 'cod_criterio_dominio_resultado');
    }
}
