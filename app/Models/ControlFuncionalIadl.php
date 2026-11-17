<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use LogicException;

/**
 * TÍTULO: Observación longitudinal de funcionalidad instrumental.
 * CAPA: Infrastructure - App Models.
 * RESPONSABILIDAD: Mapear la extensión IADL aprobada y conservar su historia.
 * ENTRADAS: Una observación con autor clínico, cuenta técnica y actividad.
 * SALIDAS: Relaciones y datos originales; ninguna acreditación C03.
 * RESTRICCIONES: Rectificar requiere otra fila; no se sobrescribe ni borra historia.
 * TRAZABILIDAD: Diccionario PR #16, aprobación explícita de esta tarea.
 */
class ControlFuncionalIadl extends RegistroConsumo
{
    /** Tabla aprobada, sin alias de compatibilidad. */
    protected const TIPO = 'IADL';

    protected const ENTIDAD = 'controles_funcionales_iadl';

    protected $table = 'registros_observaciones_cuidado';

    protected $appends = ['cod_control_iadl'];

    protected $attributes = ['estado' => 'REGISTRADO'];

    protected $hidden = ['cod_observacion_cuidado', 'tipo_evento', 'cod_jornada', 'marcha', 'equilibrio', 'traslado', 'tipo_apoyo', 'dispositivo', 'fatiga', 'riesgo_caida', 'motivo_registro', 'actividad_realizada', 'distancia_metros', 'dolor_movilidad', 'mareo', 'disnea', 'debilidad', 'cambio_habitual', 'tolerancia_movilidad', 'estado_animo', 'apatia', 'agitacion', 'agresividad', 'ansiedad', 'aislamiento', 'deambulacion', 'participacion', 'cambio_conducta', 'descripcion', 'intervencion', 'respuesta', 'fecha', 'horas_sueno', 'despertares', 'insomnio', 'somnolencia_diurna', 'agitacion_nocturna', 'calidad', 'cod_control_cognitivo', 'orientacion_persona', 'orientacion_lugar', 'orientacion_tiempo', 'memoria_reciente', 'memoria_remota', 'atencion', 'comprension', 'lenguaje', 'sigue_instrucciones', 'repite_preguntas', 'olvida_indicaciones', 'reconoce_personas', 'reconoce_entorno', 'confusion', 'cambio_cognitivo'];

    /** Identidad string canónica; generación correlativa técnica heredada. */
    protected $primaryKey = 'cod_observacion_cuidado';

    public function getKey()
    {
        return parent::getKey() ?? ($this->attributes['cod_control_iadl'] ?? null);
    }

    public function getCodControlIadlAttribute(): ?string
    {
        return $this->getKey();
    }

    public function setCodControlIadlAttribute(?string $codigo): void
    {
        $this->attributes[$this->getKeyName()] = $codigo;
    }

    /** Actividades del contrato aprobado; no constituyen baremos. */
    public const ACTIVIDADES = ['MEDICACION', 'PERTENENCIAS', 'RUTINAS', 'CITAS', 'COMUNICACION', 'FINANZAS', 'COMPRAS', 'TRANSPORTE'];

    /** Contexto de oportunidad; OBSERVADA no acredita por sí sola interpretabilidad. */
    public const OPORTUNIDADES = ['OBSERVADA', 'REFERIDA', 'SIN_OPORTUNIDAD', 'NO_HABITUAL'];

    /** Desempeño descriptivo, separado de independencia relativa. */
    public const DESEMPENOS = ['INDEPENDIENTE', 'CON_APOYO', 'NO_EVALUABLE'];

    /** Ayuda registrada, nunca ponderación clínica. */
    public const AYUDAS = ['NINGUNA', 'RECORDATORIO', 'SUPERVISION', 'SUSTITUCION', 'NO_APLICA'];

    /** Causas declaradas; el motor no acredita causalidad. */
    public const CAUSAS = ['NINGUNA', 'COGNITIVA', 'FISICA', 'SENSORIAL', 'INSTITUCIONAL', 'MIXTA', 'NO_DETERMINADA', 'NO_APLICA'];

    /** Nivel previo; desconocido permanece explícito. */
    public const NIVELES_PREVIOS = ['INDEPENDIENTE', 'CON_APOYO', 'NO_HABITUAL', 'DESCONOCIDO'];

    /** Fuente descriptiva, sin duplicar identidad de informantes en otra columna. */
    public const FUENTES = ['OBSERVACION_DIRECTA', 'RESIDENTE', 'FAMILIAR', 'EQUIPO_CUIDADOS'];

    /** @return array<string, string> Fechas separadas, sin valores clínicos derivados. */
    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'registrado_en' => 'datetime'];
    }

    /** @return BelongsTo Cuenta técnica original; nunca se atribuye a un primer usuario disponible. */
    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_registro', 'cod_usuario');
    }

    /** @return BelongsTo Observación rectificada; no se elimina ni modifica la original. */
    public function anterior(): BelongsTo
    {
        return $this->belongsTo(self::class, 'cod_control_anterior', 'cod_control_iadl');
    }

    public function sucesora(): HasOne
    {
        return $this->hasOne(self::class, 'cod_control_anterior', 'cod_control_iadl');
    }

    /** Impide sobrescrituras de la evidencia; el padre ya impide borrados ordinarios. */
    protected static function booted(): void
    {
        parent::booted();
        // Evita que campos exclusivos de otra clase oculten relaciones originales como atencion.
        static::addGlobalScope('campos_iadl', static function ($query): void {
            if ($query->getQuery()->columns === null) {
                $query->select(array_map(fn ($campo) => $query->getModel()->qualifyColumn($campo), ['cod_observacion_cuidado', 'tipo_evento', 'cod_control_iadl', 'cod_residente', 'cod_personal', 'cod_usuario_registro', 'cod_atencion', 'cod_control_anterior', 'fecha_hora', 'actividad', 'oportunidad', 'desempeno', 'tipo_ayuda', 'causa_ayuda', 'nivel_previo', 'fuente', 'estado', 'observacion', 'registrado_en']));
            }
        });

        static::updating(static function (): never {
            throw new LogicException('IADL es inmutable: registrar una rectificación sin sobrescribir el original.');
        });
    }
}
