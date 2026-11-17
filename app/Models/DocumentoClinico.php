<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class DocumentoClinico extends RegistroConsumo
{
    protected const TIPO = 'CLINICO';

    protected const ENTIDAD = 'documentos_clinicos';

    protected $appends = ['cod_documento_clinico'];

    protected $hidden = ['cod_documento', 'tipo_evento', 'cod_preadmision', 'cod_usuario', 'cod_contacto', 'cod_documento_anterior', 'cod_usuario_validacion', 'nombre', 'tipo_archivo', 'fecha_vencimiento', 'fecha_validacion', 'cod_usuario_carga', 'fecha_hora_carga', 'fecha_emision', 'cod_usuario_anulacion', 'fecha_hora_anulacion', 'motivo_anulacion'];

    public function getKey()
    {
        return parent::getKey() ?? ($this->attributes['cod_documento_clinico'] ?? null);
    }

    public function getCodDocumentoClinicoAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodDocumentoClinicoAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected $table = 'documentos';

    protected $primaryKey = 'cod_documento';

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'tamano_bytes' => 'integer'];
    }

    public function herida(): BelongsTo
    {
        return $this->belongsTo(Herida::class, 'cod_herida', 'cod_herida');
    }

    public function curacionHerida(): BelongsTo
    {
        return $this->belongsTo(CuracionHerida::class, 'cod_curacion', 'cod_curacion');
    }

    protected static function booted(): void
    {
        parent::booted();
        static::saving(function (self $documento): void {
            if ($documento->exists && $documento->getOriginal('tipo_documento') === 'FOTO_HERIDA'
                && $documento->isDirty(['cod_residente', 'cod_herida', 'cod_curacion', 'cod_personal', 'ruta_archivo', 'hash_archivo', 'formato', 'tamano_bytes', 'fecha_hora', 'origen', 'tipo_documento'])) {
                throw ValidationException::withMessages(['fotografias' => 'La evidencia original no puede reemplazarse. Registre una nueva fotografía.']);
            }
            if ($documento->cod_herida && $documento->cod_curacion) {
                throw ValidationException::withMessages(['fotografias' => 'Vincule el documento a una herida o a una curación, nunca a ambas.']);
            }
            $herida = $documento->cod_herida ? Herida::find($documento->cod_herida)
                : ($documento->cod_curacion ? CuracionHerida::find($documento->cod_curacion)?->herida : null);
            if (($documento->cod_herida || $documento->cod_curacion)
                && (! $herida || $herida->cod_residente !== $documento->cod_residente)) {
                throw ValidationException::withMessages(['fotografias' => 'El documento y la herida deben pertenecer al mismo residente.']);
            }
            if ($documento->tipo_documento === 'FOTO_HERIDA' && ! $herida) {
                throw ValidationException::withMessages(['fotografias' => 'La fotografía requiere una herida o curación válida.']);
            }
        });
    }
}
