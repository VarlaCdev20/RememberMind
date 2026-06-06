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
     * Normaliza un estado de actividad a su representación visual estandarizada.
     * Cubre variantes en mayúsculas/minúsculas sin alterar la base de datos.
     */
    public static function normalizarEstado(string $estado): array
    {
        return match (strtoupper(trim($estado))) {
            'COMPLETADA', 'REALIZADA', 'FINALIZADA' => [
                'etiqueta' => 'Realizada',
                'color'    => '#2A9D8F',
                'clase'    => 'border-[#8DA280]/30 bg-[#8DA280]/14 text-[#63775B]',
            ],
            'PROGRAMADA', 'PENDIENTE' => [
                'etiqueta' => 'Programada',
                'color'    => '#D9A05B',
                'clase'    => 'border-[#D9A05B]/30 bg-[#D9A05B]/12 text-[#9A6B2E]',
            ],
            'CANCELADA', 'ANULADA' => [
                'etiqueta' => 'Cancelada',
                'color'    => '#E97A5F',
                'clase'    => 'border-[#E27D60]/25 bg-[#E27D60]/10 text-[#E27D60]',
            ],
            'REPROGRAMADA' => [
                'etiqueta' => 'Reprogramada',
                'color'    => '#7A68B0',
                'clase'    => 'border-[#7A68B0]/30 bg-[#7A68B0]/10 text-[#5A4E8A]',
            ],
            default => [
                'etiqueta' => ucfirst(strtolower($estado)),
                'color'    => '#C7B5A3',
                'clase'    => 'border-[#C7B5A3]/40 bg-[#D5C7B9]/40 text-[#7C7168]',
            ],
        };
    }

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