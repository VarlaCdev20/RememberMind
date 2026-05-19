<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class EvaluacionGeriatrica extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'evaluaciones_geriatricas';
    protected $primaryKey = 'cod_eval_ger';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_eval_ger', 'cod_am', 'cod_instrumento',
        'registrado_por', 'evaluador_id', 'evaluador_tipo',
        'fecha_eval', 'hora_eval', 'puntaje_total',
        'categoria_resultado', 'nivel_alerta', 'nivel_riesgo',
        'observaciones', 'datos_formulario',
        'estado_eval', 'motivo_anulacion', 'anulado_por', 'anulado_en'
    ];

    protected $casts = [
        'fecha_eval' => 'date',
        'anulado_en' => 'datetime',
        'datos_formulario' => 'array',
        'puntaje_total' => 'decimal:2'
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
        return $this->belongsTo(User::class, 'registrado_por', 'cod_usu');
    }

    public function anulador()
    {
        return $this->belongsTo(User::class, 'anulado_por', 'cod_usu');
    }

    public function evaluador()
    {
        return $this->morphTo(__FUNCTION__, 'evaluador_tipo', 'evaluador_id');
    }
}
