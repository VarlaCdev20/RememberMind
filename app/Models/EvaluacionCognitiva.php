<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class EvaluacionCognitiva extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Cognitivo')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Se registró una nueva evaluación cognitiva para el adulto mayor {$this->cod_am}.",
                    'updated' => "Se actualizó la evaluación cognitiva #{$this->cod_eval_cog}.",
                    'deleted' => "Se eliminó la evaluación cognitiva #{$this->cod_eval_cog}.",
                    default   => "Evaluación cognitiva {$this->cod_eval_cog} modificada ({$eventName}).",
                };
            });
    }
    protected $table = 'evaluaciones_cognitivas';
    protected $primaryKey = 'cod_eval_cog';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_eval_cog',
        'cod_am',
        'cod_tipo_eval',
        'cod_per_sal',
        'fecha_eval',
        'hora_eval',
        'puntaje_total',
        'puntaje_maximo',
        'resultado_interpretacion',
        'nivel_riesgo',
        'observaciones',
        'estado_eval',
    ];

    protected $casts = [
        'fecha_eval' => 'date',
        'puntaje_total' => 'decimal:2',
        'puntaje_maximo' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($evaluacion) {
            if (!$evaluacion->cod_eval_cog) {
                $ultimo = self::where('cod_eval_cog', 'like', 'EVC_%')
                    ->orderByDesc('cod_eval_cog')
                    ->value('cod_eval_cog');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 4)) + 1
                    : 1;

                $evaluacion->cod_eval_cog = 'EVC_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function tipoEvaluacion()
    {
        return $this->belongsTo(TipoEvaluacionCognitiva::class, 'cod_tipo_eval', 'cod_tipo_eval');
    }

    public function personalSalud()
    {
        return $this->belongsTo(PersonalSalud::class, 'cod_per_sal', 'cod_per_sal');
    }
}