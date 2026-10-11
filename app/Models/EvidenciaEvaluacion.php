<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// O.R.I.O.N. 4.16.11; restricciones físicas D-137.
class EvidenciaEvaluacion extends ModeloExperto
{
    protected $table = 'evidencias_evaluacion';

    protected $primaryKey = 'cod_evidencia_evaluacion';

    protected function casts(): array
    {
        return [
            'fecha_hora_incorporacion' => 'immutable_datetime',
        ];
    }

    public function evaluacion(): BelongsTo
    {
        return $this->belongsTo(EvaluacionExperta::class, 'cod_evaluacion_experta', 'cod_evaluacion_experta');
    }

    public function mapeoVariableFuente(): BelongsTo
    {
        return $this->belongsTo(MapeoVariableFuente::class, 'cod_mapeo_variable_fuente', 'cod_mapeo_variable_fuente');
    }

    public function valor(): BelongsTo
    {
        return $this->belongsTo(ValorSemantico::class, 'cod_valor_semantico', 'cod_valor_semantico');
    }

    public function relacionesEvidenciasEvaluacionPorEvidenciaOrigen(): HasMany
    {
        return $this->hasMany(RelacionEvidenciaEvaluacion::class, 'cod_evidencia_origen', 'cod_evidencia_evaluacion');
    }

    public function relacionesEvidenciasEvaluacionPorEvidenciaDestino(): HasMany
    {
        return $this->hasMany(RelacionEvidenciaEvaluacion::class, 'cod_evidencia_destino', 'cod_evidencia_evaluacion');
    }

    public function evidenciasCriterioEvaluacion(): HasMany
    {
        return $this->hasMany(EvidenciaCriterioEvaluacion::class, 'cod_evidencia_evaluacion', 'cod_evidencia_evaluacion');
    }

    public function evidenciasSoporteCondicion(): HasMany
    {
        return $this->hasMany(EvidenciaSoporteCondicion::class, 'cod_evidencia_evaluacion', 'cod_evidencia_evaluacion');
    }
}
