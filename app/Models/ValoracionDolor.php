<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ValoracionDolor extends RegistroConsumo
{
    protected const TIPO = 'DOLOR';

    protected const ENTIDAD = 'valoraciones_dolor';

    protected $appends = ['cod_valoracion_dolor'];

    protected $hidden = ['cod_registro_medicion', 'tipo_evento', 'cod_jornada', 'presion_sistolica', 'presion_diastolica', 'frecuencia_cardiaca', 'frecuencia_respiratoria', 'temperatura', 'saturacion_oxigeno', 'glucemia', 'observacion', 'peso', 'talla', 'imc', 'perimetro_braquial', 'perimetro_pantorrilla', 'cod_medicion_antropometrica'];

    public function getKey()
    {
        return parent::getKey() ?? ($this->attributes['cod_valoracion_dolor'] ?? null);
    }

    public function getCodValoracionDolorAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodValoracionDolorAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected $table = 'mediciones_clinicas';

    protected $primaryKey = 'cod_registro_medicion';

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'intensidad' => 'integer'];
    }

    public function origen(): BelongsTo
    {
        return $this->belongsTo(self::class, 'cod_valoracion_origen', 'cod_registro_medicion');
    }

    public function reevaluaciones(): HasMany
    {
        return $this->hasMany(self::class, 'cod_valoracion_origen', 'cod_registro_medicion');
    }
}
