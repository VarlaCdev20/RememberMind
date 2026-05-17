<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

/**
 * HistorialEstadoAdulto — Trazabilidad de cambios de estado institucional.
 *
 * Sin SoftDeletes: el historial de cambios de estado es un registro
 * de auditoría que NO debe eliminarse bajo ninguna circunstancia.
 */
class HistorialEstadoAdulto extends Model
{
    use LogsActivity;

    protected $table = 'historial_estado_adulto';
    protected $primaryKey = 'cod_hist_estado';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'cod_am',
        'estado_anterior',
        'estado_nuevo',
        'fecha_cambio',
        'motivo',
        'documento_respaldo',
        'cambiado_por',
        'observacion',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Historial Estado')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Se registró un cambio de estado del adulto mayor {$this->cod_am}.",
                    'updated' => "Se actualizó registro de cambio de estado del adulto mayor {$this->cod_am}.",
                    default   => "Evento '{$eventName}' en historial de estado del adulto mayor {$this->cod_am}.",
                };
            });
    }

    // ── Relaciones ──────────────────────────

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function estadoAnteriorRelacion()
    {
        return $this->belongsTo(EstadoAdulto::class, 'estado_anterior', 'cod_est_adul');
    }

    public function estadoNuevoRelacion()
    {
        return $this->belongsTo(EstadoAdulto::class, 'estado_nuevo', 'cod_est_adul');
    }

    public function documentoRespaldo()
    {
        return $this->belongsTo(DocumentoAdultoMayor::class, 'documento_respaldo', 'cod_doc_am');
    }

    public function cambiadoPor()
    {
        return $this->belongsTo(User::class, 'cambiado_por', 'cod_usu');
    }
}
