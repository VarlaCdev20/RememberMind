<?php

declare(strict_types=1);

namespace App\Modulos\Valoraciones\Presentacion\Policies;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Clinica\Aplicacion\Consultas\ContextoClinicoService;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Illuminate\Support\Facades\Gate;
use LogicException;

/** Competencia de captura aprobada por la propietaria el 28/10/2026. */
final class ControlFuncionalIadlPolicy
{
    public function consultar(User $user, Residente $residente): bool
    {
        return $user->estado === 'ACTIVO' && $residente->estado === 'ADMITIDO'
            && $user->checkPermissionTo('controles_funcionales_iadl.ver', 'web')
            && Gate::forUser($user)->allows('consultarResultados', [EvaluacionExperta::class, $residente])
            && Gate::forUser($user)->allows('consultarFuncionalidad', [EvaluacionExperta::class, $residente]);
    }

    public function registrar(User $user, Residente $residente): bool
    {
        return $this->escribir($user, $residente, 'controles_funcionales_iadl.crear');
    }

    public function rectificar(User $user, Residente $residente): bool
    {
        return $this->escribir($user, $residente, 'controles_funcionales_iadl.rectificar');
    }

    private function escribir(User $user, Residente $residente, string $permiso): bool
    {
        if (session(RolePreviewService::SESSION_KEY) !== null || ! $this->consultar($user, $residente)
            || ! $user->checkPermissionTo($permiso, 'web')) {
            return false;
        }
        try {
            $contexto = app(ContextoClinicoService::class);
            $personal = $contexto->personalActivo($user);
            $contexto->areaAtencion($personal);
        } catch (LogicException) {
            return false;
        }

        return ($user->hasRole('MEDICO GENERAL/GERIATRA') && $personal->profesion === 'MEDICINA')
            || ($user->hasRole('PSICOLOGO/A') && $personal->profesion === 'PSICOLOGIA');
    }
}
