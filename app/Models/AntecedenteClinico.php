<?php

namespace App\Models;

class AntecedenteClinico extends RegistroConsumo
{
    protected const TIPO = 'ANTECEDENTE';

    protected const ENTIDAD = 'antecedentes_clinicos';

    protected $table = 'registros_clinicos';

    protected $primaryKey = 'cod_registro_clinico';

    protected $appends = ['cod_antecedente'];

    protected $hidden = ['cod_registro_clinico', 'tipo_evento', 'cod_antecedente_ref', 'cod_diagnostico_ref',
        'cod_atencion', 'codigo_clinico', 'nombre', 'tipo', 'certeza', 'fecha_hora', 'sustancia', 'reaccion', 'gravedad', 'ubicacion', 'fecha_colocacion', 'fecha_retiro'];

    public function getCodAntecedenteAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodAntecedenteAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected function casts(): array
    {
        return ['fecha_referencia' => 'date'];
    }
}
