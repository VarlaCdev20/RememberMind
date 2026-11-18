<?php

namespace App\Models;

class ValoracionNutricional extends RegistroConsumo
{
    protected const TIPO = 'NUTRICIONAL';

    protected const ENTIDAD = 'valoraciones_nutricionales';

    protected $table = 'valoraciones_profesionales';

    protected $primaryKey = 'cod_valoracion_profesional';

    protected $appends = ['cod_valoracion_nutricional'];

    protected $hidden = ['cod_valoracion_profesional', 'tipo_evento', 'marcha', 'equilibrio', 'traslado', 'fuerza_funcional', 'resistencia', 'alimentacion_autonoma', 'bano_autonomo', 'vestido_autonomo', 'higiene_autonoma', 'continencia', 'movilidad_autonoma', 'necesita_supervision', 'nivel_dependencia'];

    protected $fillable = ['cod_valoracion_nutricional', 'cod_residente', 'cod_personal', 'cod_atencion', 'cod_medicion', 'fecha_hora', 'estado_nutricional', 'apetito', 'deglucion', 'riesgo_desnutricion', 'necesidad_asistencia', 'requerimiento_hidrico', 'restricciones_alimentarias', 'conclusion', 'recomendacion', 'estado'];

    public function getCodValoracionNutricionalAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodValoracionNutricionalAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }
}
