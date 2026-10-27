<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntregaSolicitudDocumental extends ModeloOperativo
{
    protected $table = 'entregas_solicitud_documental';

    protected $primaryKey = 'cod_entrega_documental';

    protected function casts(): array
    {
        return ['fecha_hora_registro' => 'datetime'];
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(DestinatarioSolicitudDocumental::class, 'cod_destinatario_documental', 'cod_destinatario_documental');
    }

    public function documento(): BelongsTo
    {
        return $this->belongsTo(Documento::class, 'cod_documento', 'cod_documento');
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_registro', 'cod_usuario');
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new \LogicException('Una entrega conserva el documento y destinatario originales.'));
    }
}
