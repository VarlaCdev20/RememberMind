<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DocumentoUsuario extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'documentos_usuarios';
    protected $primaryKey = 'cod_doc_usu';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_doc_usu',
        'cod_usu',
        'cod_tipo_doc',
        'tipo_documento',
        'nombre_documento',
        'archivo',
        'mime_type',
        'extension',
        'tamanio',
        'fecha_emision',
        'fecha_vencimiento',
        'estado',
        'observaciones',
        'motivo_observacion',
        'subido_por',
        'validado_por',
        'fecha_validacion',
        'reemplaza_a',
        'creado_por',
        'actualizado_por',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'fecha_validacion' => 'datetime',
        'tamanio' => 'integer',
    ];

    /**
     * Spatie Activitylog options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('DocumentosUsuarios')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Se subió/creó el documento '{$this->nombre_documento}' para el usuario {$this->usuario?->name} ({$this->cod_doc_usu}).",
                    'updated' => "Se actualizó el documento '{$this->nombre_documento}' ({$this->cod_doc_usu}) — Estado: {$this->estado}.",
                    'deleted' => "Se archivó/eliminó el documento '{$this->nombre_documento}' ({$this->cod_doc_usu}).",
                    default   => "Documento {$this->nombre_documento} modificado ({$eventName}).",
                };
            });
    }

    protected static function booted(): void
    {
        static::creating(function ($doc) {
            if (!$doc->cod_doc_usu) {
                $ultimo = self::withTrashed()
                    ->where('cod_doc_usu', 'like', 'DUS_%')
                    ->orderByDesc('cod_doc_usu')
                    ->value('cod_doc_usu');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 4)) + 1
                    : 1;

                $doc->cod_doc_usu = 'DUS_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Relación con el usuario dueño
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'cod_usu', 'cod_usu');
    }

    /**
     * Relación con el catálogo de tipos
     */
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumentoUsuario::class, 'cod_tipo_doc', 'cod_tipo_doc');
    }

    /**
     * Relación con quién subió el archivo
     */
    public function subidor()
    {
        return $this->belongsTo(User::class, 'subido_por', 'cod_usu');
    }

    /**
     * Relación con quién validó el archivo
     */
    public function validador()
    {
        return $this->belongsTo(User::class, 'validado_por', 'cod_usu');
    }

    /**
     * Relación con el documento al que reemplaza
     */
    public function reemplazado()
    {
        return $this->belongsTo(self::class, 'reemplaza_a', 'cod_doc_usu');
    }

    /**
     * Relación con los documentos que lo han reemplazado
     */
    public function reemplazos()
    {
        return $this->hasMany(self::class, 'reemplaza_a', 'cod_doc_usu');
    }
}