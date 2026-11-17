<?php

namespace App\Models;

class ValoracionPsicologica extends RegistroConsumo
{
    protected const TIPO = 'PSICOLOGICA';

    protected const ENTIDAD = 'valoraciones_psicologicas';

    protected $table = 'valoraciones_profesionales';

    protected $primaryKey = 'cod_valoracion_profesional';

    protected $appends = ['cod_valoracion_psicologica'];

    protected $hidden = ['cod_seguimiento_pedagogico', 'cod_actividad', 'atencion', 'comprension_instrucciones', 'ejecucion_tarea', 'reconocimiento', 'orientacion', 'participacion', 'interaccion', 'cambio_desempeno', 'observacion', 'cod_valoracion_profesional', 'tipo_evento', 'cod_medicion', 'estado_nutricional', 'apetito', 'deglucion', 'riesgo_desnutricion', 'necesidad_asistencia', 'requerimiento_hidrico', 'restricciones_alimentarias', 'marcha', 'equilibrio', 'traslado', 'fuerza_funcional', 'resistencia', 'alimentacion_autonoma', 'bano_autonomo', 'vestido_autonomo', 'higiene_autonoma', 'continencia', 'movilidad_autonoma', 'necesita_supervision', 'nivel_dependencia', 'cod_valoracion_psicologica_ref'];

    protected $fillable = ['cod_valoracion_psicologica', 'cod_residente', 'cod_personal', 'cod_atencion', 'fecha_hora', 'estado_animo', 'afecto', 'ansiedad', 'apatia', 'percepcion', 'conducta', 'comunicacion', 'interaccion_social', 'impresion_cognitiva', 'conclusion', 'recomendacion', 'estado'];

    public function getCodValoracionPsicologicaAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodValoracionPsicologicaAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime'];
    }
}
