<?php

namespace App\Models;

class DispositivoClinico extends RegistroConsumo
{
    protected const TIPO = 'DISPOSITIVO';

    protected const ENTIDAD = 'dispositivos_clinicos';

    protected $table = 'registros_clinicos';

    protected $primaryKey = 'cod_registro_clinico';

    protected $appends = ['cod_dispositivo'];

    protected $hidden = ['cod_registro_clinico', 'tipo_evento', 'cod_antecedente_ref', 'cod_diagnostico_ref',
        'tipo_antecedente', 'fecha_referencia', 'fuente_informacion', 'cod_atencion', 'codigo_clinico', 'nombre', 'certeza', 'fecha_hora', 'sustancia', 'reaccion', 'gravedad'];

    public function getCodDispositivoAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodDispositivoAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected function casts(): array
    {
        return ['fecha_colocacion' => 'datetime', 'fecha_retiro' => 'datetime'];
    }
}
