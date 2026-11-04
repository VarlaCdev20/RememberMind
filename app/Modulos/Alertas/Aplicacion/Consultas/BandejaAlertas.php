<?php

namespace App\Modulos\Alertas\Aplicacion\Consultas;

use App\Models\Alerta;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Illuminate\Database\Eloquent\Builder;

/** Mismo permiso y ámbito para bandeja, campana y métricas. Nunca amplía el rol. */
final class BandejaAlertas
{
    public function __construct(private readonly TurnoEnfermeriaService $turnos) {}

    public function consulta(?User $usuario): Builder
    {
        $query = Alerta::query();
        if (! $usuario || $usuario->fresh()?->estado !== 'ACTIVO'
            || $usuario->hasRole('FAMILIAR') || ! $usuario->can('alertas.ver')) {
            return $query->whereRaw('1 = 0');
        }

        return $usuario->hasAnyRole(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR'])
            ? $query : $this->turnos->acotarAlertasQuery($query, $usuario);
    }

    public function pendientes(?User $usuario): Builder
    {
        return $this->consulta($usuario)->abiertas();
    }
}
