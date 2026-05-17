<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class DocumentoAdultoMayor extends Model
{
    use SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Documentos')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Se cargó el documento '{$this->nom_doc}' para el adulto mayor {$this->cod_am}.",
                    'updated' => "Se actualizó la información del documento #{$this->cod_doc_am}.",
                    'deleted' => "Se eliminó el documento #{$this->cod_doc_am}.",
                    default   => "Documento {$this->cod_doc_am} modificado ({$eventName}).",
                };
            });
    }
    protected $table = 'documentos_adulto_mayor';
    protected $primaryKey = 'cod_doc_am';

    public $incrementing = true;
    protected $keyType = 'int';

    // Timestamps habilitados — columnas existen desde strengthen_administrative_tables migration
    public $timestamps = true;

    protected $fillable = [
        'nom_doc',
        'tipo_doc',
        'ruta_archivo',
        'extension',
        'fecha_doc',
        'observaciones',
        'cod_am'
    ];

    /**
     * Relaciones
     */

    public function adultoMayor()
    {
        return $this->belongsTo(AdultoMayor::class, 'cod_am', 'cod_am');
    }

    // ── Relaciones FASE 2: Documentos como respaldo ──

    /**
     * Medicaciones que usan este documento como receta médica.
     */
    public function medicacionesComoReceta()
    {
        return $this->hasMany(MedicacionAdulto::class, 'documento_receta', 'cod_doc_am');
    }

    /**
     * Cambios de estado que usan este documento como respaldo.
     */
    public function respaldosCambioEstado()
    {
        return $this->hasMany(HistorialEstadoAdulto::class, 'documento_respaldo', 'cod_doc_am');
    }
}