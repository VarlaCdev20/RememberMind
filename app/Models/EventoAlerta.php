<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoAlerta extends ModeloOperativo
{
    protected $table = 'eventos_alerta';
    protected $primaryKey = 'cod_evento_alerta';

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }

    public function alerta(): BelongsTo
    {
        return $this->belongsTo(Alerta::class, 'cod_alerta', 'cod_alerta');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario', 'cod_usuario');
    }
}
