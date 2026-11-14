<?php

namespace App\Models;

class Alergia extends RegistroConsumo
{
    protected const TIPO = 'ALERGIA';

    protected const ENTIDAD = 'alergias';

    protected $table = 'registros_clinicos';

    protected $primaryKey = 'cod_registro_clinico';

    protected $appends = ['cod_alergia'];

    protected $hidden = ['cod_registro_clinico', 'tipo_evento', 'cod_antecedente_ref', 'cod_diagnostico_ref',
        'tipo_antecedente', 'descripcion', 'fecha_referencia', 'fuente_informacion', 'cod_atencion', 'codigo_clinico', 'nombre', 'certeza', 'ubicacion', 'fecha_colocacion', 'fecha_retiro'];

    public function getCodAlergiaAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodAlergiaAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }
}
