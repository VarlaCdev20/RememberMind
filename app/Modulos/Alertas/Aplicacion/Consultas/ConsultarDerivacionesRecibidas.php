<?php

namespace App\Modulos\Alertas\Aplicacion\Consultas;

use App\Models\Derivacion;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ConsultarDerivacionesRecibidas
{
    public function consultar(User $actor): Collection
    {
        $actor = $actor->fresh(['personal']);
        if ($actor?->estado !== 'ACTIVO' || $actor->personal?->estado !== 'ACTIVO'
            || ! $actor->can('derivaciones.ver') || $actor->hasRole('FAMILIAR')
            || app(RolePreviewService::class)->isActive($actor)
            || ! $actor->hasAnyRole(array_keys(VinculosProfesionalesAlertas::ESPECIALIDADES))) {
            return collect();
        }

        $personal = $actor->personal;
        $areas = $personal->asignaciones()->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->whereHas('jornada', fn (Builder $jornada) => $jornada->whereDate('fecha_jornada', today())
                ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO', 'ACTIVO']))
            ->select('cod_area');

        return Derivacion::query()->where('estado', 'SOLICITADA')
            ->whereIn('cod_residente', app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosQuery($actor)->select('cod_residente'))
            ->where(fn (Builder $q) => $q->where('cod_personal_receptor', $personal->getKey())
                ->orWhere(fn (Builder $area) => $area->whereNull('cod_personal_receptor')->whereIn('cod_area_receptora', $areas)))
            ->latest('fecha_hora')->get(['cod_derivacion', 'prioridad', 'fecha_hora']);
    }
}
