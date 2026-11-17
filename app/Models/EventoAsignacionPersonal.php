<?php

namespace App\Models;

/** El acto es inmutable; solo evoluciona el seguimiento técnico aprobado del aviso. */
class EventoAsignacionPersonal extends RegistroHistoriaLaboral
{
    protected const CLASE_ACTO = 'ASIGNACION';

    protected $appends = ['cod_evento_asignacion'];

    protected $hidden = ['cod_historial_laboral', 'clase_acto', 'cod_movimiento_laboral', 'cod_personal'];
    protected $primaryKey = 'cod_evento_asignacion';

    public function getCodEventoAsignacionAttribute($valor): ?string
    {
        return $this->attributes['cod_historial_laboral'] ?? $valor;
    }

    public function setCodEventoAsignacionAttribute(string $codigo): void
    {
        $this->attributes['cod_historial_laboral'] = $codigo;
    }

    protected function casts(): array
    {
        return ['fecha_hora_efecto' => 'datetime', 'fecha_hora_registro' => 'datetime',
            'numero_intentos_aviso' => 'integer', 'fecha_hora_ultimo_intento_aviso' => 'datetime', 'fecha_hora_disponible_aviso' => 'datetime'];
    }
}
