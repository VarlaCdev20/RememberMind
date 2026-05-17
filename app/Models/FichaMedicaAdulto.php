<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class FichaMedicaAdulto extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'ficha_medica_adulto';
    protected $primaryKey = 'cod_ficha_medica';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'cod_am',
        'hipertension',
        'diabetes',
        'problemas_cardiacos',
        'acv',
        'parkinson',
        'epilepsia',
        'alzheimer_diagnosticado',
        'depresion',
        'ansiedad',
        'problemas_sueno',
        'problemas_visuales',
        'problemas_auditivos',
        'dolor_cronico',
        'alergias',
        'restricciones_alimentarias',
        'hospitalizaciones',
        'cirugias',
        'observacion_medica',
        'registrado_por',
        'estado',
    ];

    protected $casts = [
        'hipertension'             => 'boolean',
        'diabetes'                 => 'boolean',
        'problemas_cardiacos'      => 'boolean',
        'acv'                      => 'boolean',
        'parkinson'                => 'boolean',
        'epilepsia'                => 'boolean',
        'alzheimer_diagnosticado'  => 'boolean',
        'depresion'                => 'boolean',
        'ansiedad'                 => 'boolean',
        'problemas_sueno'          => 'boolean',
        'problemas_visuales'       => 'boolean',
        'problemas_auditivos'      => 'boolean',
        'dolor_cronico'            => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Ficha Médica')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created'  => "Se registró una ficha médica del adulto mayor {$this->cod_am}.",
                    'updated'  => "Se actualizó la ficha médica del adulto mayor {$this->cod_am}.",
                    'deleted'  => "Se eliminó la ficha médica del adulto mayor {$this->cod_am}.",
                    'restored' => "Se restauró la ficha médica del adulto mayor {$this->cod_am}.",
                    default    => "Evento '{$eventName}' en ficha médica del adulto mayor {$this->cod_am}.",
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
