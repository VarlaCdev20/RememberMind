<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// O.R.I.O.N. 4.16.6; restricciones físicas D-137.
class MapeoVariableFuente extends ModeloExperto
{
    protected $table = 'mapeos_variables_fuente';

    protected $primaryKey = 'cod_mapeo_variable_fuente';

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

    public function variable(): BelongsTo
    {
        return $this->belongsTo(VariableExperta::class, 'cod_variable_experta', 'cod_variable_experta');
    }

    public function fuente(): BelongsTo
    {
        return $this->belongsTo(FuenteDatoExperta::class, 'cod_fuente_dato_experta', 'cod_fuente_dato_experta');
    }

    public function usuarioCreacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_creacion', 'cod_usuario');
    }

    public function mapeosValoresFuente(): HasMany
    {
        return $this->hasMany(MapeoValorFuente::class, 'cod_mapeo_variable_fuente', 'cod_mapeo_variable_fuente');
    }

    public function evidenciasEvaluacion(): HasMany
    {
        return $this->hasMany(EvidenciaEvaluacion::class, 'cod_mapeo_variable_fuente', 'cod_mapeo_variable_fuente');
    }
}
