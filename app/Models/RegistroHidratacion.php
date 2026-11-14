<?php

namespace App\Models;

class RegistroHidratacion extends RegistroConsumo
{
    protected const TIPO = 'HIDRATACION';

    protected const ENTIDAD = 'registros_hidratacion';

    protected $hidden = ['cod_registro_consumo', 'tipo_evento', 'tipo_comida', 'porcentaje_consumido', 'apetito', 'dificultad_deglucion', 'tipo_eliminacion', 'cantidad', 'caracteristica', 'continencia', 'cantidad_cualitativa', 'volumen_ml', 'color_orina', 'aspecto_orina', 'olor_orina', 'tipo_miccion', 'tipo_bristol', 'color_heces', 'esfuerzo_defecacion', 'presencia_sangre', 'presencia_moco', 'molestia_eliminacion', 'descripcion_molestia'];

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
