<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role;

class SolicitudDocumentalPersonal extends ModeloOperativo
{
    protected $table = 'solicitudes_documentales_personal';

    protected $primaryKey = 'cod_solicitud_documental';

    protected function casts(): array
    {
        return ['fecha_hora_emision' => 'datetime'];
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_solicitante', 'cod_usuario');
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'rol_id');
    }

    public function destinatarios(): HasMany
    {
        return $this->hasMany(DestinatarioSolicitudDocumental::class, 'cod_solicitud_documental', 'cod_solicitud_documental');
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Una solicitud emitida conserva su contenido original.'));
    }
}
