<?php

namespace App\Models;

use App\Traits\GeneraCodigo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ValoracionEnfermeriaAdmision extends Model
{
    use GeneraCodigo;
    use LogsActivity;

    protected $table = 'valoracion_enfermeria_admision';
    protected $primaryKey = 'cod_val_enf';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'VEA';
    protected $digitsCode = 5;

    protected $fillable = [
        'cod_val_enf',
        'cod_am',
        'cod_pre',
        'fecha_valoracion',
        'hora_valoracion',
        'estado_general',
        'nivel_conciencia',
        'orientacion',
        'comunicacion',
        'hay_dolor',
        'intensidad_dolor',
        'ubicacion_dolor',
        'movilidad',
        'apoyo_movilidad',
        'riesgo_caida',
        'piel_estado',
        'hay_heridas',
        'ubicacion_heridas',
        'higiene_ingreso',
        'continencia_basica',
        'alimentacion_aparente',
        'signos_vitales_iniciales',
        'observacion',
        'recomendacion_enfermeria',
        'estado',
        'registrado_por',
    ];

    protected $casts = [
        'fecha_valoracion' => 'date',
        'hora_valoracion' => 'string',
        'hay_dolor' => 'boolean',
        'hay_heridas' => 'boolean',
    ];

    public function adultoMayor(): BelongsTo
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function preadmision(): BelongsTo
    {
        return $this->belongsTo(Preadmision::class, 'cod_pre', 'cod_pre');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por', 'cod_usu');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Valoracion Enfermeria Admision')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Registró valoración inicial de enfermería para el adulto mayor {$this->cod_am}.",
                    'updated' => "Actualizó valoración inicial de enfermería para el adulto mayor {$this->cod_am}.",
                    default => "Evento '{$eventName}' en valoración inicial de enfermería del adulto mayor {$this->cod_am}.",
                };
            });
    }
}
