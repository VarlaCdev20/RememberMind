<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/** Observaciones IADL inmutables; NO equivalen a DCL-C03 acreditado. */
class ControlFuncionalIadl extends ModeloOperativo
{
    protected $table = 'controles_funcionales_iadl';
    protected $primaryKey = 'cod_control_iadl';

    public const ACTIVIDADES = [
        'MEDICACION' => 'Organización y seguimiento de medicación',
        'PERTENENCIAS' => 'Organización de pertenencias personales',
        'RUTINAS' => 'Cumplimiento de rutinas y actividades',
        'CITAS' => 'Organización de citas y compromisos',
        'COMUNICACION' => 'Uso de teléfono y comunicación',
        'FINANZAS' => 'Administración de dinero',
        'COMPRAS' => 'Gestión de compras',
        'TRANSPORTE' => 'Planificación de desplazamientos',
    ];
    public const OPORTUNIDADES = ['OBSERVADA', 'REFERIDA', 'SIN_OPORTUNIDAD', 'NO_HABITUAL'];
    public const DESEMPENOS = ['INDEPENDIENTE', 'CON_APOYO', 'NO_EVALUABLE'];
    public const AYUDAS = ['NINGUNA', 'RECORDATORIO', 'SUPERVISION', 'SUSTITUCION', 'NO_APLICA'];
    public const CAUSAS = ['NINGUNA', 'COGNITIVA', 'FISICA', 'SENSORIAL', 'INSTITUCIONAL', 'MIXTA', 'NO_DETERMINADA', 'NO_APLICA'];
    public const NIVELES_PREVIOS = ['INDEPENDIENTE', 'CON_APOYO', 'NO_HABITUAL', 'DESCONOCIDO'];
    public const FUENTES = ['OBSERVACION_DIRECTA', 'RESIDENTE', 'FAMILIAR', 'EQUIPO_CUIDADOS'];

    protected function casts(): array
    {
        return ['fecha_hora' => 'datetime', 'registrado_en' => 'datetime'];
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_registro', 'cod_usuario');
    }

    public function anterior(): BelongsTo
    {
        return $this->belongsTo(self::class, 'cod_control_anterior', 'cod_control_iadl');
    }

    protected static function booted(): void
    {
        static::creating(function (self $modelo): void {
            if (!$modelo->cod_control_iadl) {
                $modelo->cod_control_iadl = 'IAD_'.strtoupper(Str::random(12));
            }
        });
        static::updating(function (): void {
            throw new \LogicException('IADL es inmutable: registrar una rectificación, no sobrescribir.');
        });
    }
}
