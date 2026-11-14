<?php

namespace App\Models;

class RegistroIngesta extends RegistroConsumo
{
    protected const TIPO = 'INGESTA';

    protected const ENTIDAD = 'registros_ingesta';

    protected $hidden = ['cod_registro_consumo', 'tipo_evento', 'cantidad_ml', 'tipo_liquido', 'tipo_eliminacion', 'cantidad', 'caracteristica', 'continencia', 'cantidad_cualitativa', 'volumen_ml', 'color_orina', 'aspecto_orina', 'olor_orina', 'tipo_miccion', 'tipo_bristol', 'color_heces', 'esfuerzo_defecacion', 'presencia_sangre', 'presencia_moco', 'molestia_eliminacion', 'descripcion_molestia'];

    protected $appends = ['cod_ingesta'];

    public function getCodIngestaAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodIngestaAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'dificultad_deglucion' => 'boolean'];
    }
}
