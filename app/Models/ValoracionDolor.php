<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ValoracionDolor extends ModeloOperativo
{
    protected $table = 'valoraciones_dolor';

    protected $primaryKey = 'cod_valoracion_dolor';

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'intensidad' => 'integer'];
    }

    public function origen(): BelongsTo
    {
        return $this->belongsTo(self::class, 'cod_valoracion_origen', 'cod_valoracion_dolor');
    }

    public function reevaluaciones(): HasMany
    {
        return $this->hasMany(self::class, 'cod_valoracion_origen', 'cod_valoracion_dolor');
    }
}
