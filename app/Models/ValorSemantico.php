<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// O.R.I.O.N. 4.16.8; restricciones físicas D-137.
class ValorSemantico extends ModeloExperto
{
    protected $table = 'valores_semanticos';

    protected $primaryKey = 'cod_valor_semantico';

    protected function casts(): array
    {
        return [
            'fecha_hora_creacion' => 'immutable_datetime',
        ];
    }

    public function dominio(): BelongsTo
    {
        return $this->belongsTo(DominioValoresExperto::class, 'cod_dominio_valores', 'cod_dominio_valores');
    }

    public function usuarioCreacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_creacion', 'cod_usuario');
    }

    public function mapeosValoresFuente(): HasMany
    {
        return $this->hasMany(MapeoValorFuente::class, 'cod_valor_semantico', 'cod_valor_semantico');
    }

    public function condicionesReglaExperta(): HasMany
    {
        return $this->hasMany(CondicionReglaExperta::class, 'cod_valor_semantico', 'cod_valor_semantico');
    }

    public function consecuenciasReglaExperta(): HasMany
    {
        return $this->hasMany(ConsecuenciaReglaExperta::class, 'cod_valor_semantico', 'cod_valor_semantico');
    }

    public function evidenciasEvaluacion(): HasMany
    {
        return $this->hasMany(EvidenciaEvaluacion::class, 'cod_valor_semantico', 'cod_valor_semantico');
    }

    public function resultadosCriterio(): HasMany
    {
        return $this->hasMany(ResultadoCriterio::class, 'cod_valor_semantico', 'cod_valor_semantico');
    }
}
