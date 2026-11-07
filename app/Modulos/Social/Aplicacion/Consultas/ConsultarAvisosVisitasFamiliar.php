<?php

namespace App\Modulos\Social\Aplicacion\Consultas;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class ConsultarAvisosVisitasFamiliar
{
    public function consultar(User $actor): Collection
    {
        $actor = $actor->fresh();
        if ($actor?->estado !== 'ACTIVO' || ! $actor->hasRole('FAMILIAR')
            || ! $actor->can('visitas.ver') || ! $actor->can('residentes.ver')) {
            return collect();
        }

        return DB::table('visitas as v')
            ->join('contactos as c', 'c.cod_contacto', '=', 'v.cod_contacto')
            ->join('residentes as r', 'r.cod_residente', '=', 'v.cod_residente')
            ->where('c.cod_usuario', $actor->getKey())
            ->where('c.estado', 'ACTIVO')
            ->whereIn('v.estado', ['PROGRAMADA', 'AUTORIZADA'])
            ->where('v.fecha_hora_programada', '>=', now())
            ->whereExists(fn ($vinculo) => $vinculo->selectRaw('1')->from('residentes_contactos as rc')
                ->whereColumn('rc.cod_contacto', 'v.cod_contacto')->whereColumn('rc.cod_residente', 'v.cod_residente')
                ->where('rc.estado', 'ACTIVO')->where('rc.autoriza_informacion', true))
            ->orderBy('v.fecha_hora_programada')->orderBy('v.cod_visita')
            ->get(['r.nombres', 'r.apellido_paterno', 'v.cod_visita', 'v.fecha_hora_programada', 'v.estado']);
    }
}
