<?php

namespace App\Models;

use App\Traits\GeneraCodigo;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class DocumentoAdultoMayor extends Model
{
    use GeneraCodigo;
    use LogsActivity;

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Documentos')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Se cargó el documento '{$this->nombre}' para el adulto mayor {$this->cod_am}.",
                    'updated' => "Se actualizó la información del documento #{$this->cod_doc_am}.",
                    'deleted' => "Se eliminó el documento #{$this->cod_doc_am}.",
                    default   => "Documento {$this->cod_doc_am} modificado ({$eventName}).",
                };
            });
    }
    protected $table = 'documentos_adulto_mayor';
    protected $primaryKey = 'cod_doc_am';

    public $incrementing = false;
    protected $keyType = 'string';
    protected $prefixCode = 'DAM';
    protected $digitsCode = 5;

    // Timestamps habilitados — columnas existen desde strengthen_administrative_tables migration
    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'tipo_documento',
        'ruta_archivo',
        'fecha_subida',
        'estado',
        'observaciones',
        'modulo_ref',
        'cod_am'
    ];

    public function getNomDocAttribute(): ?string
    {
        return $this->nombre;
    }

    public function setNomDocAttribute(?string $value): void
    {
        $this->attributes['nombre'] = $value;
    }

    public function getTipoDocAttribute(): ?string
    {
        return $this->tipo_documento;
    }

    public function setTipoDocAttribute(?string $value): void
    {
        $this->attributes['tipo_documento'] = $value;
    }

    public function getFechaDocAttribute(): mixed
    {
        return $this->fecha_subida;
    }

    public function setFechaDocAttribute(mixed $value): void
    {
        $this->attributes['fecha_subida'] = $value;
    }

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
