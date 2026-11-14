<?php

namespace App\Models;

class InformeEstudio extends RegistroConsumo
{
    protected const TIPO = 'INFORME';
    protected const ENTIDAD = 'informes_estudio';
    protected $table = 'registros_estudio';
    protected $primaryKey = 'cod_registro_estudio';
    protected $appends = ['cod_informe_estudio'];
    protected $hidden = ['cod_registro_estudio', 'tipo_evento', 'cod_componente', 'valor_numerico', 'valor_texto',
        'unidad', 'rango_referencia', 'clasificacion', 'observacion'];
    protected $fillable = ['cod_informe_estudio', 'cod_estudio', 'cod_personal', 'fecha_hora', 'hallazgos',
        'conclusion', 'recomendacion', 'origen', 'profesional_externo', 'estado'];

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }

    public function getCodInformeEstudioAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodInformeEstudioAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }
}
