<?php

namespace App\Backend\Modulos\Clinica\Servicios;

use App\Models\Area;
use App\Models\Personal;
use App\Models\User;
use LogicException;

class ContextoClinicoService
{
    public function personalActivo(?User $usuario): Personal
    {
        if (! $usuario) {
            throw new LogicException('La operación clínica requiere un usuario autenticado.');
        }

        if (strtoupper(trim((string) $usuario->estado)) !== 'ACTIVO') {
            throw new LogicException('La operación clínica requiere una cuenta de usuario activa.');
        }

        $personal = Personal::query()
            ->where('cod_usuario', $usuario->cod_usuario)
            ->where('estado', 'ACTIVO')
            ->first();

        if (! $personal) {
            throw new LogicException('El usuario autenticado no tiene un perfil de personal activo.');
        }

        return $personal;
    }

    public function areaAtencion(Personal $personal): Area
    {
        $areaAsignada = $personal->asignaciones()
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->whereHas('jornada', fn ($query) => $query
                ->whereDate('fecha_jornada', today())
                ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO']))
            ->whereHas('area', fn ($query) => $query->whereIn('estado', ['ACTIVA', 'ACTIVO']))
            ->with('area')
            ->latest('fecha_asignacion')
            ->first()
            ?->area;

        if ($areaAsignada) {
            return $areaAsignada;
        }

        throw new LogicException('El personal no tiene un área clínica activa asignada para la jornada vigente.');
    }
}
