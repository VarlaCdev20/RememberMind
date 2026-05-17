<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class ObsAdulto extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'obs_adulto';
    protected $primaryKey = 'cod_obs_adul';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'fecha',
        'tipo_obs',
        'descripcion',
        'cod_am',
        'cod_est_adul',
        'registrado_por',
        'nivel_importancia'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Observaciones')
            ->setDescriptionForEvent(function (string $eventName) {
                if ($eventName === 'created') return "Se registró una observación de tipo {$this->tipo_obs} para el adulto mayor {$this->cod_am}.";
                if ($eventName === 'updated') return "Se actualizó la observación #{$this->cod_obs_adul} del adulto mayor {$this->cod_am}.";
                if ($eventName === 'deleted') return "Se eliminó la observación #{$this->cod_obs_adul}.";
                return "Observación {$this->cod_obs_adul} modificada ({$eventName}).";
            });
    }

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function estadoAdulto()
    {
        return $this->belongsTo(EstadoAdulto::class, 'cod_est_adul', 'cod_est_adul');
    }
}