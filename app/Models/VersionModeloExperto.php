<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// O.R.I.O.N. 4.16.1; restricciones físicas D-137.
class VersionModeloExperto extends ModeloExperto
{
    protected $table = 'versiones_modelo_experto';

    protected $primaryKey = 'cod_version_modelo';

    protected function casts(): array
    {
        return [
            'fecha_hora_creacion' => 'immutable_datetime',
            'fecha_hora_vigencia' => 'immutable_datetime',
            'fecha_hora_retiro' => 'immutable_datetime',
        ];
    }

    public function versionAnterior(): BelongsTo
    {
        return $this->belongsTo(VersionModeloExperto::class, 'cod_version_anterior', 'cod_version_modelo');
    }

    public function usuarioCreacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_creacion', 'cod_usuario');
    }

    public function versionesModeloExperto(): HasMany
    {
        return $this->hasMany(VersionModeloExperto::class, 'cod_version_anterior', 'cod_version_modelo');
    }

    public function nodosSemanticos(): HasMany
    {
        return $this->hasMany(NodoSemantico::class, 'cod_version_modelo', 'cod_version_modelo');
    }

    public function relacionesSemanticas(): HasMany
    {
        return $this->hasMany(RelacionSemantica::class, 'cod_version_modelo', 'cod_version_modelo');
    }

    public function dominiosValoresExpertos(): HasMany
    {
        return $this->hasMany(DominioValoresExperto::class, 'cod_version_modelo', 'cod_version_modelo');
    }

    public function variablesExpertas(): HasMany
    {
        return $this->hasMany(VariableExperta::class, 'cod_version_modelo', 'cod_version_modelo');
    }

    public function fuentesDatosExpertas(): HasMany
    {
        return $this->hasMany(FuenteDatoExperta::class, 'cod_version_modelo', 'cod_version_modelo');
    }

    public function mapeosVariablesFuente(): HasMany
    {
        return $this->hasMany(MapeoVariableFuente::class, 'cod_version_modelo', 'cod_version_modelo');
    }

    public function reglasExpertas(): HasMany
    {
        return $this->hasMany(ReglaExperta::class, 'cod_version_modelo', 'cod_version_modelo');
    }

    public function evaluacionesExpertas(): HasMany
    {
        return $this->hasMany(EvaluacionExperta::class, 'cod_version_modelo', 'cod_version_modelo');
    }
}
