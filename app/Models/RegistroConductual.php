<?php

namespace App\Models;

class RegistroConductual extends RegistroConsumo
{
    protected const TIPO = 'CONDUCTA';
    protected const ENTIDAD = 'registros_conductuales';
    protected $table = 'registros_observaciones_cuidado';
    protected $primaryKey = 'cod_observacion_cuidado';
    protected $appends = ['cod_registro_conductual'];
    protected $hidden = ['cod_observacion_cuidado', 'tipo_evento', 'marcha', 'equilibrio', 'traslado', 'tipo_apoyo', 'dispositivo', 'fatiga', 'riesgo_caida', 'observacion', 'motivo_registro', 'actividad_realizada', 'distancia_metros', 'dolor_movilidad', 'mareo', 'disnea', 'debilidad', 'cambio_habitual', 'tolerancia_movilidad', 'fecha', 'horas_sueno', 'despertares', 'insomnio', 'somnolencia_diurna', 'agitacion_nocturna', 'calidad', 'cod_control_cognitivo', 'orientacion_persona', 'orientacion_lugar', 'orientacion_tiempo', 'memoria_reciente', 'memoria_remota', 'atencion', 'comprension', 'lenguaje', 'sigue_instrucciones', 'repite_preguntas', 'olvida_indicaciones', 'reconoce_personas', 'reconoce_entorno', 'confusion', 'cambio_cognitivo', 'cod_control_iadl', 'cod_usuario_registro', 'cod_control_anterior', 'actividad', 'oportunidad', 'desempeno', 'tipo_ayuda', 'causa_ayuda', 'nivel_previo', 'fuente', 'registrado_en'];

    public function getCodRegistroConductualAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodRegistroConductualAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'apatia' => 'boolean', 'agitacion' => 'boolean', 'agresividad' => 'boolean',
            'ansiedad' => 'boolean', 'aislamiento' => 'boolean', 'deambulacion' => 'boolean', 'cambio_conducta' => 'boolean'];
    }
}
