<?php

namespace App\Models;

use App\Compartido\Infraestructura\Persistencia\LeeColumnasRelacionadas;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EstudioClinico extends ModeloOperativo
{
    use LeeColumnasRelacionadas;

    protected $table = 'estudios_clinicos';

    protected $primaryKey = 'cod_estudio';

    protected function casts(): array
    {
        return ['fecha_solicitud' => 'datetime', 'fecha_realizacion' => 'datetime'];
    }

    public function getCodResidenteAttribute(?string $value): ?string
    {
        return $this->valorRelacionado($value);
    }

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(TipoEstudioClinico::class, 'cod_tipo_estudio', 'cod_tipo_estudio');
    }

    public function resultados(): HasMany
    {
        return $this->hasMany(ResultadoEstudio::class, 'cod_estudio', 'cod_estudio');
    }

    public function informes(): HasMany
    {
        return $this->hasMany(InformeEstudio::class, 'cod_estudio', 'cod_estudio');
    }
}
