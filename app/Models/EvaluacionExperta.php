<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// O.R.I.O.N. 4.16.10; restricciones físicas D-137.
class EvaluacionExperta extends ModeloExperto
{
    protected $table = 'evaluaciones_expertas';

    protected $primaryKey = 'cod_evaluacion_experta';

    protected function casts(): array
    {
        return [
            'fecha_hora_inicio' => 'immutable_datetime',
            'fecha_hora_fin' => 'immutable_datetime',
        ];
    }

    public function residente(): BelongsTo
    {
        return $this->belongsTo(Residente::class, 'cod_residente', 'cod_residente');
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(VersionModeloExperto::class, 'cod_version_modelo', 'cod_version_modelo');
    }

    public function personalSolicitante(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal_solicitante', 'cod_personal');
    }

    public function evidenciasEvaluacion(): HasMany
    {
        return $this->hasMany(EvidenciaEvaluacion::class, 'cod_evaluacion_experta', 'cod_evaluacion_experta');
    }

    public function evaluacionCriterios(): HasMany
    {
        return $this->hasMany(EvaluacionCriterio::class, 'cod_evaluacion_experta', 'cod_evaluacion_experta');
    }
}
