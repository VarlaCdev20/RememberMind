<?php

namespace App\Modulos\Identidad\Aplicacion\Consultas;

use App\Models\MovimientoLaboralPersonal;
use App\Models\User;

/** Lectura del cambio y asignaciones existentes; no fabrica un acuse de revisión. */
final class ConsultarCambiosLaboralesPendientes
{
    public function consultar(User $actor): array
    {
        $actor = $actor->fresh();
        if (! app(CapacidadDocumentalPersonal::class)->disponible() || $actor?->estado !== 'ACTIVO' || $actor->hasRole('FAMILIAR')
            || ! $actor->hasAnyRole(['ADMINISTRADOR', 'SUPERADMINISTRADOR']) || ! $actor->can('asignaciones_personal.ver')) {
            return [];
        }

        return MovimientoLaboralPersonal::where('estado_nuevo', 'INACTIVO')
            ->whereHas('personal', fn ($p) => $p->where('estado', 'INACTIVO')->whereHas('asignaciones', fn ($a) => $a->whereIn('estado', ['ACTIVO', 'ACTIVA'])))
            ->with('personal')->orderByDesc('fecha_hora_efecto')->orderByDesc('cod_movimiento_laboral')->get()->unique('cod_personal')
            ->map(fn ($movimiento) => ['clave' => $movimiento->getKey(), 'persona' => trim($movimiento->personal->nombres.' '.$movimiento->personal->apellido_paterno),
                'mensaje' => 'Situación laboral modificada; existen asignaciones con estado activo para revisar.',
                'fecha' => $movimiento->fecha_hora_efecto->format('d/m/Y H:i'), 'url' => route('admin.administracion.asignaciones')])->values()->all();
    }
}
