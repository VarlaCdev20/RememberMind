<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DocumentoUsuario extends Model
{
    use LogsActivity;

    protected $table = 'documentos_usuarios';
    protected $primaryKey = 'cod_doc_usu';

    public $incrementing = true;
    protected $keyType = 'int';

    // Estados válidos del ciclo de vida documental
    const ESTADO_PENDIENTE  = 'PENDIENTE';
    const ESTADO_CARGADO    = 'CARGADO';
    const ESTADO_OBSERVADO  = 'OBSERVADO';
    const ESTADO_APROBADO   = 'APROBADO';
    const ESTADO_VENCIDO    = 'VENCIDO';

    // Columnas verificadas contra la BD real (2026-06-04).
    protected $fillable = [
        // Originales
        'cod_usu',
        'nom_doc',
        'tipo_doc',
        'ruta_archivo',
        'extension',
        'fecha_doc',
        'observaciones',
        // Control documental agregado en 2026_06_04_210000
        'mime_type',
        'tamanio',
        'estado',
        'subido_por',
        'validado_por',
        'fecha_validacion',
        'observacion_validacion',
        'fecha_vencimiento_plazo',
    ];

    protected $casts = [
        'fecha_doc'              => 'date',
        'fecha_validacion'       => 'datetime',
        'fecha_vencimiento_plazo' => 'date',
        'tamanio'                => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('DocumentosUsuarios')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Se registró el documento '{$this->nom_doc}' para el usuario {$this->cod_usu}.",
                    'updated' => "Se actualizó '{$this->nom_doc}' (cod: {$this->cod_doc_usu}) — Estado: {$this->estado}.",
                    'deleted' => "Se eliminó el documento '{$this->nom_doc}' (cod: {$this->cod_doc_usu}).",
                    default   => "Documento '{$this->nom_doc}' modificado ({$eventName}).",
                };
            });
    }

    // ── Scopes de estado ─────────────────────────────────────────────────────

    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopeCargados($query)
    {
        return $query->where('estado', self::ESTADO_CARGADO);
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado', self::ESTADO_APROBADO);
    }

    public function scopeObservados($query)
    {
        return $query->where('estado', self::ESTADO_OBSERVADO);
    }

    public function scopeVencidos($query)
    {
        return $query->where('estado', self::ESTADO_VENCIDO);
    }

    public function scopeActivos($query)
    {
        return $query->whereIn('estado', [self::ESTADO_CARGADO, self::ESTADO_APROBADO]);
    }

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function usuario()
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    public function subidor()
    {
        return $this->belongsTo(User::class, 'subido_por', 'cod_usu');
    }

    public function validador()
    {
        return $this->belongsTo(User::class, 'validado_por', 'cod_usu');
    }
}
