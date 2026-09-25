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
            ->whereHas('area', fn ($query) => $query->whereIn('estado', ['ACTIVA', 'ACTIVO']))
            ->with('area')
            ->latest('fecha_asignacion')
            ->first()
            ?->area;

        if ($areaAsignada) {
            return $areaAsignada;
        }

        $areaClinica = Area::query()
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->where(function ($query) {
                $query->whereRaw('LOWER(nombre) LIKE ?', ['%medic%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%médic%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%clinic%'])
                    ->orWhereRaw('LOWER(nombre) LIKE ?', ['%clínic%']);
            })
            ->orderBy('cod_area')
            ->first();

        if (! $areaClinica) {
            throw new LogicException('No existe un área clínica activa para registrar la atención.');
        }

        return $areaClinica;
    }
}
