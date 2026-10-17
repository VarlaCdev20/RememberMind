<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuracionHerida extends ModeloOperativo
{
    protected $table = 'curaciones_herida';

    protected $primaryKey = 'cod_curacion';

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'longitud' => 'decimal:2', 'ancho' => 'decimal:2', 'profundidad' => 'decimal:2'];
    }

    public function herida(): BelongsTo
    {
        return $this->belongsTo(Herida::class, 'cod_herida', 'cod_herida');
    }

    public function documentosClinicos(): HasMany
    {
        return $this->hasMany(DocumentoClinico::class, 'cod_curacion', 'cod_curacion');
    }
}
