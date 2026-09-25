<?php

namespace App\Policies;

use App\Models\AdministracionMedicacion;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\User;

class AdministracionMedicacionPolicy
{
    public function create(User $user): bool
    {
        // Si es enfermero y tiene personal registrado, y existen jornadas activas en el sistema,
        // verificar que esté en turno activo (con asignación a una jornada activa/abierta hoy)
        if (method_exists($user, 'hasRole') && $user->hasRole('ENFERMEROS') && $user->personal) {
            $hayJornadasActivas = Jornada::whereIn('estado', ['ABIERTA', 'ACTIVA'])
                ->whereDate('fecha_jornada', today())
                ->exists();

            if ($hayJornadasActivas) {
                $enTurno = AsignacionPersonal::where('cod_personal', $user->personal->cod_personal)
                    ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                    ->whereHas('jornada', function ($q) {
                        $q->whereIn('estado', ['ABIERTA', 'ACTIVA', 'PLANIFICADA'])
                          ->whereDate('fecha_jornada', today());
                    })
                    ->exists();

                if (!$enTurno) {
                    return false;
                }
            }
        }

        $tieneRol = method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['ENFERMEROS', 'MEDICO GENERAL/GERIATRA']);
        $tienePermiso = $user->can('administraciones_medicacion.crear');

        return $tieneRol && $tienePermiso;
    }

    public function view(User $user, AdministracionMedicacion $registro): bool
    {
        return $user->can('administraciones_medicacion.ver');
    }
}
