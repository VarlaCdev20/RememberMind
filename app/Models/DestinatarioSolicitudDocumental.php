<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DestinatarioSolicitudDocumental extends ModeloOperativo
{
    protected $table = 'destinatarios_solicitud_documental';

    protected $primaryKey = 'cod_destinatario_documental';

    protected function casts(): array
    {
        return ['fecha_limite_original' => 'date', 'fecha_limite_prorrogada' => 'date', 'fecha_hora_prorroga' => 'datetime'];
    }

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudDocumentalPersonal::class, 'cod_solicitud_documental', 'cod_solicitud_documental');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function prorrogante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_prorroga', 'cod_usuario');
    }

    public function entregas(): HasMany
    {
        return $this->hasMany(EntregaSolicitudDocumental::class, 'cod_destinatario_documental', 'cod_destinatario_documental');
    }

    protected static function booted(): void
    {
        static::updating(function (self $fila): void {
            foreach (['cod_solicitud_documental', 'cod_personal', 'origen_plazo', 'fecha_limite_original'] as $campo) {
                if ($fila->isDirty($campo)) {
                    throw new \LogicException('La membresía y el plazo originales se conservan.');
                }
            }
            if ($fila->getRawOriginal('fecha_limite_prorrogada') !== null && $fila->isDirty()) {
                throw new \LogicException('La prórroga concedida no se sobrescribe.');
            }
        });
    }
}
