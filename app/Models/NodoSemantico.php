<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

// O.R.I.O.N. 4.16.2; restricciones físicas D-137.
class NodoSemantico extends ModeloExperto
{
    protected $table = 'nodos_semanticos';

    protected $primaryKey = 'cod_nodo_semantico';

    protected function casts(): array
    {
        return [
            'fecha_hora_creacion' => 'immutable_datetime',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(VersionModeloExperto::class, 'cod_version_modelo', 'cod_version_modelo');
    }

    public function usuarioCreacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_creacion', 'cod_usuario');
    }

    public function relacionesSemanticasPorNodoOrigen(): HasMany
    {
        return $this->hasMany(RelacionSemantica::class, 'cod_nodo_origen', 'cod_nodo_semantico');
    }

    public function relacionesSemanticasPorNodoDestino(): HasMany
    {
        return $this->hasMany(RelacionSemantica::class, 'cod_nodo_destino', 'cod_nodo_semantico');
    }

    public function variableExperta(): HasOne
    {
        return $this->hasOne(VariableExperta::class, 'cod_nodo_semantico', 'cod_nodo_semantico');
    }

    public function variablesExpertasPorPropietarioPrimario(): HasMany
    {
        return $this->hasMany(VariableExperta::class, 'cod_nodo_propietario_primario', 'cod_nodo_semantico');
    }

    public function criterioDominioResultado(): HasOne
    {
        return $this->hasOne(CriterioDominioResultado::class, 'cod_nodo_criterio', 'cod_nodo_semantico');
    }

    public function evaluacionCriterios(): HasMany
    {
        return $this->hasMany(EvaluacionCriterio::class, 'cod_nodo_criterio', 'cod_nodo_semantico');
    }
}
