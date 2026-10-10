<?php

namespace App\Policies;

use App\Backend\Modulos\Identidad\Servicios\RolePreviewService;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class EvaluacionExpertaPolicy
{
    public function consultarResultados(User $user, Residente $residente): Response
    {
        $accesoMedico = $user->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'SUPERADMINISTRADOR'])
            && $user->checkPermissionTo('valoracion_medica.ver', 'web');
        $accesoEnfermeria = $user->hasRole('ENFERMEROS')
            && $user->checkPermissionTo('enfermeria.ver_ficha_paciente', 'web')
            && app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosQuery($user, null, $user->getKey())
                ->whereKey($residente->getKey())->exists();

        $permitido = $user->estado === 'ACTIVO'
            && session(RolePreviewService::SESSION_KEY) === null
            && ! $user->hasRole('FAMILIAR')
            && ($accesoMedico || $accesoEnfermeria)
            && $user->checkPermissionTo('residentes.ver', 'web')
            && $user->checkPermissionTo('controles_cognitivos.ver', 'web')
            && Gate::forUser($user)->allows('view', $residente)
            && ($user->hasRole('SUPERADMINISTRADOR') || $user->personal()->where('estado', 'ACTIVO')->exists());

        return $permitido ? Response::allow() : Response::denyAsNotFound();
    }
}
