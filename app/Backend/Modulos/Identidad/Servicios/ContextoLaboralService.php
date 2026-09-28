<?php

namespace App\Backend\Modulos\Identidad\Servicios;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Personal;
use App\Models\User;
use LogicException;

class ContextoLaboralService
{
    /** @return array{0: Personal, 1: Area} */
    public function resolver(?User $usuario): array
    {
        if (! $usuario) {
            throw new LogicException('La operación requiere un usuario autenticado.');
        }

        $personal = Personal::query()
            ->where('cod_usuario', $usuario->cod_usuario)
            ->where('estado', 'ACTIVO')
            ->first();

        if (! $personal) {
            throw new LogicException('El usuario no tiene un perfil de personal activo.');
        }

        $asignacion = AsignacionPersonal::query()
            ->with('area')
            ->where('cod_personal', $personal->cod_personal)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->whereHas('area', fn ($query) => $query->whereIn('estado', ['ACTIVA', 'ACTIVO']))
            ->latest('fecha_asignacion')
            ->first();

        if (! $asignacion?->area) {
            throw new LogicException('El usuario no tiene un área institucional activa asignada.');
        }

        return [$personal, $asignacion->area];
    }
}
