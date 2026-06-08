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
        $this->attributes['evaluador_id'] = $value;
    }

    public function getPuntajeTotalAttribute()
    {
        return $this->puntaje;
    }

    public function setPuntajeTotalAttribute($value): void
    {
        $this->attributes['puntaje'] = $value;
    }

    public function getCategoriaResultadoAttribute()
    {
        return $this->resultado_cualitativo;
    }

    public function setCategoriaResultadoAttribute($value): void
    {
        $this->attributes['resultado_cualitativo'] = $value;
    }

    public function getNivelAlertaAttribute()
    {
        return $this->estado;
    }

    public function setEstadoEvalAttribute($value): void
    {
        $this->attributes['estado'] = $value;
    }

    public function getEstadoEvalAttribute()
    {
        return $this->estado;
    }

    public function setNivelAlertaAttribute($value): void
    {
        $this->attributes['estado'] = $value === 'CRITICO' ? 'ALERTA' : ($this->attributes['estado'] ?? 'COMPLETADA');
    }

    public function getNivelRiesgoAttribute()
    {
        return $this->resultado_cualitativo;
    }

    public function setNivelRiesgoAttribute($value): void
    {
        $this->attributes['resultado_cualitativo'] = $value;
    }

    public function setDatosFormularioAttribute($value): void
    {
        if (is_array($value) && empty($this->attributes['observaciones'])) {
            $this->attributes['observaciones'] = json_encode($value);
        }
    }

    public function setMotivoAnulacionAttribute($value): void
    {
        if ($value) {
            $this->attributes['observaciones'] = trim(($this->attributes['observaciones'] ?? '') . "\nMotivo de anulación: " . $value);
        }
    }

    public function setAnuladoPorAttribute($value): void
    {
        // La BD limpia no persiste anulador separado; se conserva estado/deleted_at.
    }

    public function setAnuladoEnAttribute($value): void
    {
        $this->attributes['deleted_at'] = $value;
    }
}
