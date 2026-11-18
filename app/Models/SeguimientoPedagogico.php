<?php

namespace App\Models;

class SeguimientoPedagogico extends RegistroConsumo
{
    protected const TIPO = 'PEDAGOGICA';

    protected const ENTIDAD = 'seguimientos_pedagogicos';

    protected $appends = ['cod_seguimiento_pedagogico'];

    protected $hidden = ['cod_valoracion_profesional', 'tipo_evento', 'cod_atencion', 'estado_animo', 'afecto', 'ansiedad', 'apatia', 'percepcion', 'conducta', 'comunicacion', 'interaccion_social', 'impresion_cognitiva', 'conclusion', 'recomendacion'];

    public function getKey()
    {
        return parent::getKey() ?? ($this->attributes['cod_seguimiento_pedagogico'] ?? null);
    }

    public function getCodSeguimientoPedagogicoAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodSeguimientoPedagogicoAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected $table = 'valoraciones_psicopedagogicas';

    protected $primaryKey = 'cod_valoracion_profesional';

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'cambio_desempeno' => 'boolean'];
    }
}
