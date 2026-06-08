<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class EvaluacionGeriatrica extends Model
{
    use GeneraCodigo;
    use SoftDeletes, LogsActivity;

    protected $table = 'evaluaciones_geriatricas';
    protected $primaryKey = 'cod_eval_ger';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'EGE';
    protected $digitsCode = 5;

    protected $fillable = [
        'cod_eval_ger', 'cod_am', 'cod_instrumento',
        'registrado_por', 'evaluador_id', 'fecha_eval', 'puntaje', 'puntaje_total',
        'resultado_cualitativo', 'categoria_resultado', 'nivel_alerta', 'nivel_riesgo',
        'observaciones', 'estado', 'estado_eval', 'datos_formulario', 'motivo_anulacion',
        'anulado_por', 'anulado_en',
    ];

    protected $casts = [
        'fecha_eval' => 'date',
        'puntaje' => 'decimal:2',
        'puntaje_total' => 'decimal:2',
        'datos_formulario' => 'array',
        'anulado_en' => 'datetime',
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('EvaluacionGeriatrica')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Se registró una nueva evaluación geriátrica (#{$this->cod_eval_ger}) para el adulto mayor {$this->cod_am}.",
                    'updated' => "Se actualizó la evaluación geriátrica #{$this->cod_eval_ger}.",
                    'deleted' => "Se eliminó la evaluación geriátrica #{$this->cod_eval_ger}.",
                    default   => "Evaluación geriátrica {$this->cod_eval_ger} modificada ({$eventName}).",
                };
            });
    }

    protected static function booted(): void
    {
        static::creating(function ($eval) {
            if (!$eval->cod_eval_ger) {
                $ultimo = self::where('cod_eval_ger', 'like', 'EVG_%')
                    ->orderByDesc('cod_eval_ger')
                    ->value('cod_eval_ger');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 4)) + 1
                    : 1;

                $eval->cod_eval_ger = 'EVG_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function ($eval) {
            $eval->registrado_por ??= $eval->evaluador_id;
            $eval->evaluador_id ??= $eval->registrado_por;
            $eval->puntaje_total ??= $eval->puntaje;
            $eval->puntaje ??= $eval->puntaje_total;
            $eval->categoria_resultado ??= $eval->resultado_cualitativo;
            $eval->resultado_cualitativo ??= $eval->categoria_resultado;
            $eval->nivel_riesgo ??= $eval->resultado_cualitativo;
            $eval->estado_eval ??= $eval->estado;
            $eval->estado ??= $eval->estado_eval ?? 'COMPLETADA';
            $eval->nivel_alerta ??= $eval->estado === 'ALERTA' ? 'CRITICO' : 'NORMAL';
        });
    }

    public function adulto()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function instrumento()
    {
        return $this->belongsTo(InstrumentoGeriatrico::class, 'cod_instrumento', 'cod_instrumento');
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'evaluador_id', 'cod_usu');
    }

    public function anulador()
    {
        return $this->belongsTo(User::class, 'evaluador_id', 'cod_usu');
    }

    public function evaluador()
    {
        return $this->belongsTo(User::class, 'evaluador_id', 'cod_usu');
    }

    // Virtual accessors for backwards compatibility with EvaluacionCognitiva
    public function getTipoEvaluacionAttribute()
    {
        return $this->instrumento;
    }

    public function getPersonalSaludAttribute()
    {
        return $this->registrador;
    }

    public function getResultadoInterpretacionAttribute()
    {
        return $this->categoria_resultado;
    }

    public function setRegistradoPorAttribute($value): void
    {
        $this->attributes['registrado_por'] = $value;
        $this->attributes['evaluador_id'] = $value;
    }

    public function getPuntajeTotalAttribute()
    {
        return $this->puntaje;
    }

    public function setPuntajeTotalAttribute($value): void
    {
        $this->attributes['puntaje_total'] = $value;
        $this->attributes['puntaje'] = $value;
    }

    public function getCategoriaResultadoAttribute()
    {
        return $this->resultado_cualitativo;
    }

    public function setCategoriaResultadoAttribute($value): void
    {
        $this->attributes['categoria_resultado'] = $value;
        $this->attributes['resultado_cualitativo'] = $value;
    }

    public function getNivelAlertaAttribute()
    {
        return $this->attributes['nivel_alerta'] ?? $this->estado;
    }

    public function setEstadoEvalAttribute($value): void
    {
        $this->attributes['estado_eval'] = $value;
        $this->attributes['estado'] = $value;
    }

    public function getEstadoEvalAttribute()
    {
        return $this->estado;
    }

    public function setNivelAlertaAttribute($value): void
    {
        $this->attributes['nivel_alerta'] = $value;
        $this->attributes['estado'] = $value === 'CRITICO' ? 'ALERTA' : ($this->attributes['estado'] ?? 'COMPLETADA');
    }

    public function getNivelRiesgoAttribute()
    {
        return $this->attributes['nivel_riesgo'] ?? $this->resultado_cualitativo;
    }

    public function setNivelRiesgoAttribute($value): void
    {
        $this->attributes['nivel_riesgo'] = $value;
        $this->attributes['resultado_cualitativo'] = $value;
    }

    public function setDatosFormularioAttribute($value): void
    {
        $this->attributes['datos_formulario'] = is_array($value) ? json_encode($value) : $value;

        if (is_array($value) && empty($this->attributes['observaciones'])) {
            $this->attributes['observaciones'] = json_encode($value);
        }
    }

    public function setMotivoAnulacionAttribute($value): void
    {
        $this->attributes['motivo_anulacion'] = $value;
        if ($value) {
            $this->attributes['observaciones'] = trim(($this->attributes['observaciones'] ?? '') . "\nMotivo de anulación: " . $value);
        }
    }

    public function setAnuladoPorAttribute($value): void
    {
        $this->attributes['anulado_por'] = $value;
    }

    public function setAnuladoEnAttribute($value): void
    {
        $this->attributes['anulado_en'] = $value;
        $this->attributes['deleted_at'] = $value;
    }
}
