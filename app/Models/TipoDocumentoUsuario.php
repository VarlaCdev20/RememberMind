<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class TipoDocumentoUsuario extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'tipos_documentos_usuario';
    protected $primaryKey = 'cod_tipo_doc';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cod_tipo_doc',
        'nombre',
        'descripcion',
        'aplica_roles',
        'obligatorio',
        'requiere_vencimiento',
        'requiere_validacion',
        'estado',
        'orden',
    ];

    protected $casts = [
        'aplica_roles' => 'array',
        'obligatorio' => 'boolean',
        'requiere_vencimiento' => 'boolean',
        'requiere_validacion' => 'boolean',
        'orden' => 'integer',
    ];

    /**
     * Spatie Activitylog options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('TiposDocumentosUsuario')
            ->setDescriptionForEvent(function (string $eventName) {
                return match ($eventName) {
                    'created' => "Se creó el tipo de documento de usuario '{$this->nombre}' ({$this->cod_tipo_doc}).",
                    'updated' => "Se actualizó el tipo de documento de usuario '{$this->nombre}' ({$this->cod_tipo_doc}).",
                    'deleted' => "Se archivó/eliminó el tipo de documento de usuario '{$this->nombre}' ({$this->cod_tipo_doc}).",
                    default   => "Tipo de documento de usuario {$this->nombre} modificado ({$eventName}).",
                };
            });
    }

    protected static function booted(): void
    {
        static::creating(function ($tipo) {
            if (!$tipo->cod_tipo_doc) {
                $ultimo = self::withTrashed()
                    ->where('cod_tipo_doc', 'like', 'TDU_%')
                    ->orderByDesc('cod_tipo_doc')
                    ->value('cod_tipo_doc');

                $numero = $ultimo
                    ? ((int) substr($ultimo, 4)) + 1
                    : 1;

                $tipo->cod_tipo_doc = 'TDU_' . str_pad($numero, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Relación con documentos cargados
     */
    public function documentos()
    {
        return $this->hasMany(DocumentoUsuario::class, 'cod_tipo_doc', 'cod_tipo_doc');
    }

    /**
     * Scopes
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'ACTIVO');
    }
}
