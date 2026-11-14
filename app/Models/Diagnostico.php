<?php

namespace App\Models;

class Diagnostico extends RegistroConsumo
{
    protected const TIPO = 'DIAGNOSTICO';

    protected const ENTIDAD = 'diagnosticos';

    protected $table = 'registros_clinicos';

    protected $primaryKey = 'cod_registro_clinico';

    protected $appends = ['cod_diagnostico'];

    protected $hidden = ['cod_registro_clinico', 'tipo_evento', 'cod_antecedente_ref', 'cod_diagnostico_ref',
        'tipo_antecedente', 'descripcion', 'fecha_referencia', 'fuente_informacion', 'sustancia', 'reaccion', 'gravedad', 'ubicacion', 'fecha_colocacion', 'fecha_retiro'];

    public function getCodDiagnosticoAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodDiagnosticoAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }
}
