<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    /** Dictámenes de Psicología y Medicina; no alteran el resultado automático. */
    public function revisionesProfesionales(): HasMany
    {
        return $this->hasMany(RevisionEvaluacionExperta::class, 'cod_evaluacion_experta', 'cod_evaluacion_experta');
    }

    public function evidenciasEvaluacion(): HasMany
    {
        return $this->hasMany(EvidenciaEvaluacion::class, 'cod_evaluacion_experta', 'cod_evaluacion_experta');
    }

    public function evaluacionCriterios(): HasMany
    {
        return $this->hasMany(EvaluacionCriterio::class, 'cod_evaluacion_experta', 'cod_evaluacion_experta');
    }

    /** Mantiene visible el historial D-137 al anexar una instantánea, sin publicar evaluaciones exclusivamente V2. */
    public function scopeParaLecturaD137(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q->whereNull('version_conservacion')
            ->orWhereHas('evaluacionCriterios', fn (Builder $c) => $c->where('tipo_registro', 'D137')));
    }

    /** Relación original del contrato de conservación aprobado. */
    public function hechosInstantanea(): HasMany
    {
        return $this->hasMany(HechoInstantaneaExperta::class, 'cod_evaluacion_experta', 'cod_evaluacion_experta');
    }

    /** Relación original del contrato de conservación aprobado. */
    public function clasificacionDclInstantanea(): HasOne
    {
        return $this->hasOne(ClasificacionDclInstantanea::class, 'cod_evaluacion_experta', 'cod_evaluacion_experta');
    }
}
