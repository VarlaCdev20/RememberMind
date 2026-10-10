<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// O.R.I.O.N. 4.16.4; restricciones físicas D-137.
class VariableExperta extends ModeloExperto
{
    protected $table = 'variables_expertas';

    protected $primaryKey = 'cod_variable_experta';

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

    public function nodo(): BelongsTo
    {
        return $this->belongsTo(NodoSemantico::class, 'cod_nodo_semantico', 'cod_nodo_semantico');
    }

    public function propietarioPrimario(): BelongsTo
    {
        return $this->belongsTo(NodoSemantico::class, 'cod_nodo_propietario_primario', 'cod_nodo_semantico');
    }

    public function dominio(): BelongsTo
    {
        return $this->belongsTo(DominioValoresExperto::class, 'cod_dominio_valores', 'cod_dominio_valores');
    }

    public function usuarioCreacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_creacion', 'cod_usuario');
    }

    public function mapeosVariablesFuente(): HasMany
    {
        return $this->hasMany(MapeoVariableFuente::class, 'cod_variable_experta', 'cod_variable_experta');
    }

    public function condicionesReglaExperta(): HasMany
    {
        return $this->hasMany(CondicionReglaExperta::class, 'cod_variable_experta', 'cod_variable_experta');
    }
}
