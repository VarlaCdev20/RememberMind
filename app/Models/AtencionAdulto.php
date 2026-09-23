<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class AtencionAdulto extends Model
{
    use GeneraCodigo;
    use LogsActivity;
    protected $table = 'atenciones_adulto';
    protected $primaryKey = 'cod_aten_adul';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'ATE';
    protected $digitsCode = 5;

    // Timestamps habilitados — columnas existen desde strengthen_administrative_tables migration
    public $timestamps = true;

    protected $fillable = [
        'fecha',
        'hora',
        'obs',
        'observacion',
        'estado',
        'cod_tipo_aten',
        'cod_am',
        'registrado_por',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function getObsAttribute(): ?string
    {
        return $this->observacion;
    }

    public function setObsAttribute(?string $value): void
    {
        $this->attributes['observacion'] = $value;
    }

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Atenciones')
            ->setDescriptionForEvent(function (string $eventName) {
                if ($eventName === 'created') return "Se registró una nueva atención para el adulto mayor {$this->cod_am}.";
                if ($eventName === 'updated') return "Se actualizó la información de la atención #{$this->cod_aten_adul}.";
                if ($eventName === 'deleted') return "Se eliminó el registro de atención del adulto mayor {$this->cod_am}.";
                return "Atención {$this->cod_aten_adul} modificada ({$eventName}).";
            });
    }

    /**
     * Relaciones
     */

    public function tipoAtencion()
    {
        return $this->belongsTo(TipoAtencionAdulto::class, 'cod_tipo_aten', 'cod_tipo_aten');
    }

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }
}
