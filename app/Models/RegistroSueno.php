<?php

namespace App\Models;

use Illuminate\Support\Carbon;

class RegistroSueno extends RegistroConsumo
{
    protected const TIPO = 'SUENO';

    protected const ENTIDAD = 'registros_sueno';

    protected $table = 'registros_observaciones_cuidado';

    protected $primaryKey = 'cod_observacion_cuidado';

    protected $appends = ['cod_registro_sueno'];

    protected $hidden = ['cod_observacion_cuidado', 'tipo_evento', 'fecha_hora', 'cod_atencion', 'marcha', 'equilibrio', 'traslado', 'tipo_apoyo', 'dispositivo', 'fatiga', 'riesgo_caida', 'motivo_registro', 'actividad_realizada', 'distancia_metros', 'dolor_movilidad', 'mareo', 'disnea', 'debilidad', 'cambio_habitual', 'tolerancia_movilidad', 'estado_animo', 'apatia', 'agitacion', 'agresividad', 'ansiedad', 'aislamiento', 'deambulacion', 'participacion', 'cambio_conducta', 'descripcion', 'intervencion', 'respuesta'];

    protected $fillable = ['cod_registro_sueno', 'cod_residente', 'cod_personal', 'cod_jornada', 'fecha', 'horas_sueno', 'despertares', 'insomnio', 'somnolencia_diurna', 'agitacion_nocturna', 'calidad', 'observacion', 'estado'];

    public function getCodRegistroSuenoAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodRegistroSuenoAttribute(string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    public function setFechaAttribute(mixed $fecha): void
    {
        $this->attributes['fecha'] = $fecha === null ? null : Carbon::parse($fecha)->toDateString();
    }

    protected function casts(): array
    {
        return ['fecha' => 'date', 'insomnio' => 'boolean', 'somnolencia_diurna' => 'boolean', 'agitacion_nocturna' => 'boolean'];
    }
}
