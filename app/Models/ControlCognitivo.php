<?php

namespace App\Models;

class ControlCognitivo extends RegistroConsumo
{
    protected const TIPO = 'COGNITIVO';

    protected const ENTIDAD = 'controles_cognitivos';

    protected $table = 'registros_observaciones_cuidado';

    protected $primaryKey = 'cod_observacion_cuidado';

    protected $appends = ['cod_control_cognitivo'];

    protected $hidden = ['cod_observacion_cuidado', 'tipo_evento', 'marcha', 'equilibrio', 'traslado', 'tipo_apoyo', 'dispositivo', 'fatiga', 'riesgo_caida', 'motivo_registro', 'actividad_realizada', 'distancia_metros', 'dolor_movilidad', 'mareo', 'disnea', 'debilidad', 'cambio_habitual', 'tolerancia_movilidad', 'estado_animo', 'apatia', 'agitacion', 'agresividad', 'ansiedad', 'aislamiento', 'deambulacion', 'participacion', 'cambio_conducta', 'descripcion', 'intervencion', 'respuesta', 'fecha', 'horas_sueno', 'despertares', 'insomnio', 'somnolencia_diurna', 'agitacion_nocturna', 'calidad', 'cod_control_iadl', 'cod_usuario_registro', 'cod_control_anterior', 'actividad', 'oportunidad', 'desempeno', 'tipo_ayuda', 'causa_ayuda', 'nivel_previo', 'fuente', 'registrado_en'];

    public function getKey()
    {
        return parent::getKey() ?? ($this->attributes['cod_control_cognitivo'] ?? null);
    }

    public function getCodControlCognitivoAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodControlCognitivoAttribute(?string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'sigue_instrucciones' => 'boolean', 'repite_preguntas' => 'boolean', 'olvida_indicaciones' => 'boolean', 'reconoce_personas' => 'boolean', 'reconoce_entorno' => 'boolean', 'confusion' => 'boolean', 'cambio_cognitivo' => 'boolean'];
    }
}
