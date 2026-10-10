<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// O.R.I.O.N. 4.16.9; restricciones físicas D-137.
class MapeoValorFuente extends ModeloExperto
{
    protected $table = 'mapeos_valores_fuente';

    protected $primaryKey = 'cod_mapeo_valor_fuente';

    protected function casts(): array
    {
        return [
            'fecha_hora_vigencia_desde' => 'immutable_datetime',
            'fecha_hora_vigencia_hasta' => 'immutable_datetime',
            'fecha_hora_creacion' => 'immutable_datetime',
        ];
    }

    public function mapeoVariableFuente(): BelongsTo
    {
        return $this->belongsTo(MapeoVariableFuente::class, 'cod_mapeo_variable_fuente', 'cod_mapeo_variable_fuente');
    }

    public function valor(): BelongsTo
    {
        return $this->belongsTo(ValorSemantico::class, 'cod_valor_semantico', 'cod_valor_semantico');
    }

    public function usuarioCreacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_creacion', 'cod_usuario');
    }
}
