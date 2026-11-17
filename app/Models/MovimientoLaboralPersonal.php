<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoLaboralPersonal extends RegistroHistoriaLaboral
{
    protected const CLASE_ACTO = 'LABORAL';

    protected $appends = ['cod_movimiento_laboral'];

    protected $hidden = ['cod_historial_laboral', 'clase_acto', 'cod_evento_asignacion', 'cod_asignacion_personal',
        'numero_evento', 'cod_evento_anterior', 'tipo_evento', 'cod_usuario_afectado_aviso', 'estado_aviso_planificacion',
        'numero_intentos_aviso', 'fecha_hora_ultimo_intento_aviso', 'fecha_hora_disponible_aviso', 'categoria_fallo_aviso'];

    protected $primaryKey = 'cod_movimiento_laboral';

    public function getCodMovimientoLaboralAttribute($valor): ?string
    {
        return $this->attributes['cod_historial_laboral'] ?? $valor;
    }

    public function setCodMovimientoLaboralAttribute(string $codigo): void
    {
        $this->attributes['cod_historial_laboral'] = $codigo;
    }

    protected function casts(): array
    {
        return ['fecha_hora_registro' => 'datetime', 'fecha_hora_efecto' => 'datetime'];
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'cod_personal', 'cod_personal');
    }

    public function registrador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cod_usuario_registro', 'cod_usuario');
    }

    protected static function booted(): void
    {
        parent::booted();
        static::updating(fn () => throw new \LogicException('Un movimiento laboral conserva la transición original.'));
    }
}
