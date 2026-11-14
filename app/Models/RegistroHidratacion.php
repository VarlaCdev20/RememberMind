<?php

namespace App\Models;

class RegistroHidratacion extends RegistroConsumo
{
    protected const TIPO = 'HIDRATACION';

    protected const ENTIDAD = 'registros_hidratacion';

    protected $hidden = ['cod_registro_consumo', 'tipo_evento', 'tipo_comida', 'porcentaje_consumido', 'apetito', 'dificultad_deglucion'];

    protected $appends = ['cod_hidratacion'];

    public function getCodHidratacionAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodHidratacionAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'cantidad_ml' => 'decimal:2'];
    }
}
