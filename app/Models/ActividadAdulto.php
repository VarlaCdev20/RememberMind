<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class ActividadAdulto extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Actividades')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Se registró participación en actividad para el adulto mayor {$this->cod_am}.",
                    'updated' => "Se actualizó el registro de actividad #{$this->cod_act_adul}.",
                    'deleted' => "Se eliminó el registro de actividad #{$this->cod_act_adul}.",
                    default   => "Actividad {$this->cod_act_adul} modificada ({$eventName}).",
                };
            });
    }
    protected $table = 'actividades_adulto';
    protected $primaryKey = 'cod_act_adul';

    public $incrementing = true;
    protected $keyType = 'int';

    // Timestamps habilitados — columnas existen desde strengthen_administrative_tables migration
    public $timestamps = true;

    protected $fillable = [
        'fecha',
        'hora',
        'obs',
        'estado',
        'cod_tipo_act',
        'cod_am'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    /**
     * Relaciones
     */

    public function tipoActividad()
    {
        return $this->belongsTo(TipoActividadAdulto::class, 'cod_tipo_act', 'cod_tipo_act');
    }

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }
}