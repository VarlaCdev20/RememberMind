<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
class ControlFuncionalIadl extends ModeloOperativo
{
    /** Tabla aprobada, sin alias de compatibilidad. */
    protected $table = 'controles_funcionales_iadl';

    /** Identidad string canónica; generación correlativa técnica heredada. */
    protected $primaryKey = 'cod_control_iadl';

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

    /** Impide sobrescrituras de la evidencia; el padre ya impide borrados ordinarios. */
    protected static function booted(): void
    {
        static::updating(static function (): never {
            throw new LogicException('IADL es inmutable: registrar una rectificación sin sobrescribir el original.');
        });
    }
}
