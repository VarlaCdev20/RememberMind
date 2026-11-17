<?php

namespace App\Models;

class IndicacionClinica extends RegistroConsumo
{
    protected const TIPO = 'INDICACION';

    protected const ENTIDAD = 'indicaciones_clinicas';

    protected $appends = ['cod_indicacion'];

    protected $hidden = ['cod_registro_textual', 'tipo_evento', 'cod_pase', 'cod_jornada_saliente', 'cod_jornada_entrante', 'cod_personal_saliente', 'cod_personal_entrante', 'estado_general', 'resumen', 'pendientes', 'vigilancia', 'recomendacion', 'fecha_hora_recepcion', 'observacion_recepcion'];

    public function getKey()
    {
        return parent::getKey() ?? ($this->attributes['cod_indicacion'] ?? null);
    }

    public function getCodIndicacionAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodIndicacionAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected $table = 'registros_textuales_clinicos';

    protected $primaryKey = 'cod_registro_textual';

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }
}
