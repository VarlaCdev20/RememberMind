<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * SignosVitalesAdulto — Registros de control físico y signos vitales.
 *
 * Sin SoftDeletes: datos clínicos de trazabilidad que NO deben eliminarse.
 */
class SignosVitalesAdulto extends Model
{
    use LogsActivity;

    protected $table = 'signos_vitales_adulto';
    protected $primaryKey = 'cod_signo';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'cod_am',
        'fecha',
        'hora',
        'presion_arterial',
        'frecuencia_cardiaca',
        'temperatura',
        'saturacion',
        'glucosa',
        'peso',
        'talla',
        'imc',
        'dolor',
        'observacion',
        'registrado_por',
    ];

    protected $casts = [
        'fecha'               => 'date',
        'hora'                => 'datetime:H:i',
        'frecuencia_cardiaca' => 'integer',
        'temperatura'         => 'decimal:1',
        'saturacion'          => 'integer',
        'glucosa'             => 'decimal:2',
        'peso'                => 'decimal:2',
        'talla'               => 'decimal:2',
        'imc'                 => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Signos vitales')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Se registraron nuevos signos vitales para el adulto mayor {$this->cod_am}.",
                    'updated' => "Se actualizaron los signos vitales #{$this->cod_signo}.",
                    default   => "Evento '{$eventName}' en signos vitales.",
                };
            });
    }

    // ── Relaciones ──────────────────────────

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'cod_usu');
    }
}
