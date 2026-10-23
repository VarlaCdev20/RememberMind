<?php

namespace App\Modulos\ApoyoDecision\Presentacion\Policies;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Dominio\Revision\DisciplinaRevision;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class EvaluacionExpertaPolicy
{
    /** Guardado observacional autorizado solo a Medicina/Psicología, nunca por auditoría o rol de lectura. */
    public function ejecutarObservaciones(User $user, Residente $residente): Response
    {
        $competente = false;
        foreach (DisciplinaRevision::cases() as $disciplina) {
            $competente = $competente || ($user->hasRole($disciplina->rol())
                && $user->personal()->where('estado', 'ACTIVO')->where('profesion', $disciplina->value)->exists());
        }

        return $competente && $residente->estado === 'ADMITIDO'
            && $user->checkPermissionTo('evaluaciones_expertas.ejecutar_observaciones', 'web')
            && $this->consultarResultados($user, $residente)->allowed()
            ? Response::allow() : Response::denyAsNotFound();
    }

    /** Reutiliza el alcance técnico vigente; la lectura clínica no concede auditoría. */
    public function consultarTrazabilidad(User $user, Residente $residente): Response
    {
        return $this->consultarResultados($user, $residente)->allowed()
            && (new VersionModeloExpertoPolicy)->consultar($user)
            ? Response::allow() : Response::denyAsNotFound();
    }

    /** Conserva el permiso específico del contrato IADL de PR #16, sin concederlo por otro recurso. */
    public function consultarFuncionalidad(User $user, Residente $residente): Response
    {
        $competente = $user->hasRole('SUPERADMINISTRADOR')
            || ($user->hasRole('MEDICO GENERAL/GERIATRA')
                && $user->personal()->where('estado', 'ACTIVO')->where('profesion', 'MEDICINA')->exists())
            || ($user->hasRole('PSICOLOGO/A')
                && $user->personal()->where('estado', 'ACTIVO')->where('profesion', 'PSICOLOGIA')->exists());

        return ($competente && $this->consultarResultados($user, $residente)->allowed()
            && $user->checkPermissionTo('controles_funcionales_iadl.ver', 'web'))
            || $this->consultarFuentesParaResultado($user, $residente)->allowed()
            ? Response::allow() : Response::denyAsNotFound();
    }

    /** Lectura original de Enfermería limitada al residente asignado y al resultado experto. */
    public function consultarFuentesParaResultado(User $user, Residente $residente): Response
    {
        return $user->hasRole('ENFERMEROS')
            && $this->consultarResultados($user, $residente)->allowed()
            ? Response::allow() : Response::denyAsNotFound();
    }

    /** El rol de auditoría o lectura nunca autoriza una firma clínica. */
    public function revisarValoracion(User $user, EvaluacionExperta $evaluacion, DisciplinaRevision $disciplina): Response
    {
        $permitido = $user->estado === 'ACTIVO' && session(RolePreviewService::SESSION_KEY) === null
            && ! $user->hasRole('FAMILIAR')
            && $user->hasRole($disciplina->rol()) && $user->checkPermissionTo($disciplina->permiso(), 'web')
            && $user->personal()->where('estado', 'ACTIVO')->where('profesion', $disciplina->value)->exists()
            && $evaluacion->residente?->estado === 'ADMITIDO'
            && $this->consultarResultados($user, $evaluacion->residente)->allowed();

        return $permitido ? Response::allow() : Response::denyAsNotFound();
    }

    public function consultarResultados(User $user, Residente $residente): Response
    {
        $accesoMedico = $user->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'SUPERADMINISTRADOR'])
            && $user->checkPermissionTo('valoracion_medica.ver', 'web');
        $accesoEnfermeria = $user->hasRole('ENFERMEROS')
            && $user->checkPermissionTo('enfermeria.ver_ficha_paciente', 'web')
            && app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosQuery($user, null, $user->getKey())
                ->whereKey($residente->getKey())->exists();
        $accesoPsicologia = $user->hasRole('PSICOLOGO/A')
            && $user->checkPermissionTo('valoraciones_psicologicas.ver', 'web')
            && $user->checkPermissionTo('evaluaciones_expertas.revisar_psicologia', 'web')
            && $user->personal()->where('estado', 'ACTIVO')->where('profesion', 'PSICOLOGIA')->exists();

        $permitido = $user->estado === 'ACTIVO'
            && session(RolePreviewService::SESSION_KEY) === null
            && ! $user->hasRole('FAMILIAR')
            && ($accesoMedico || $accesoEnfermeria || $accesoPsicologia)
            && $user->checkPermissionTo('residentes.ver', 'web')
            && $user->checkPermissionTo('controles_cognitivos.ver', 'web')
            && Gate::forUser($user)->allows('view', $residente)
            && ($user->hasRole('SUPERADMINISTRADOR') || $user->personal()->where('estado', 'ACTIVO')->exists());

        return $permitido ? Response::allow() : Response::denyAsNotFound();
    }
}
