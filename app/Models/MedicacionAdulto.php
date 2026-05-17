<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class MedicacionAdulto extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'medicacion_adulto';
    protected $primaryKey = 'cod_med_adulto';

    public $incrementing = true;
    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'cod_am',
        'nombre_medicamento',
        'dosis',
        'frecuencia',
        'via_administracion',
        'hora_programada',
        'fecha_inicio',
        'fecha_fin',
        'medico_indica',
        'documento_receta',
        'estado',
        'observacion',
        'registrado_por',
    ];

    protected $casts = [
        'fecha_inicio'    => 'date',
        'fecha_fin'       => 'date',
        'hora_programada' => 'datetime:H:i',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Medicación')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created'  => "Se registró medicación '{$this->nombre_medicamento}' para el adulto mayor {$this->cod_am}.",
                    'updated'  => "Se actualizó medicación '{$this->nombre_medicamento}' del adulto mayor {$this->cod_am}.",
                    'deleted'  => "Se eliminó medicación '{$this->nombre_medicamento}' del adulto mayor {$this->cod_am}.",
                    'restored' => "Se restauró medicación '{$this->nombre_medicamento}' del adulto mayor {$this->cod_am}.",
                    default    => "Evento '{$eventName}' en medicación del adulto mayor {$this->cod_am}.",
                };
            });
    }

    // ── Relaciones ──────────────────────────

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    public function receta()
    {
        return $this->belongsTo(DocumentoAdultoMayor::class, 'documento_receta', 'cod_doc_am');
    }

    public function administraciones()
    {
        return $this->hasMany(AdministracionMedicacion::class, 'cod_med_adulto', 'cod_med_adulto');
    }

    public function registrador()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'cod_usu');
    }
}
