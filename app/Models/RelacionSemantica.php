<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

// O.R.I.O.N. 4.16.3; restricciones físicas D-137.
class RelacionSemantica extends ModeloExperto
{
    protected $table = 'relaciones_semanticas';

    protected $primaryKey = 'cod_relacion_semantica';

    protected function casts(): array
    {
        return [
            'fecha_hora_creacion' => 'immutable_datetime',
        ];
    }

    public function version(): BelongsTo
    {
        return $this->belongsTo(VersionModeloExperto::class, 'cod_version_modelo', 'cod_version_modelo');
    }

    public function nodoOrigen(): BelongsTo
    {
        return $this->belongsTo(NodoSemantico::class, 'cod_nodo_origen', 'cod_nodo_semantico');
    }

    public function nodoDestino(): BelongsTo
    {
        return $this->belongsTo(NodoSemantico::class, 'cod_nodo_destino', 'cod_nodo_semantico');
    }

    public function usuarioCreacion(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_creacion', 'cod_usuario');
    }
}
