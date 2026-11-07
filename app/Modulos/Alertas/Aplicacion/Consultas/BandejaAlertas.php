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

        // Las alertas persistidas pertenecen a los flujos clínicos y de cuidado.
        // Los avisos administrativos se consultan desde sus fuentes institucionales.
        $perfil = app(ExperienciaAlertas::class)->paraUsuario($usuario);
        if ($perfil['foco'] === 'supervision') {
            return $query->whereRaw('1 = 0');
        }

        if (in_array($perfil['foco'], ['psicologia', 'nutricion', 'funcional', 'pedagogia'], true)) {
            $this->sinSolicitudesMedicas($query);
        }

        return $usuario->hasAnyRole(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR'])
            ? $query : $this->turnos->acotarAlertasQuery($query, $usuario);
    }

    /** La campana avisa al destinatario; la bandeja conserva el seguimiento autorizado. */
    public function notificacionesPendientes(User $usuario): Builder
    {
        $consulta = $this->pendientes($usuario);
        $rol = app(ExperienciaAlertas::class)->paraUsuario($usuario)['rol'];

        if ($rol !== VinculosProfesionalesAlertas::ROL_MEDICO) {
            $this->sinSolicitudesMedicas($consulta);
        }

        if (isset(VinculosProfesionalesAlertas::ESPECIALIDADES[$rol]) && $rol !== 'ENFERMEROS') {
            app(VinculosProfesionalesAlertas::class)->aplicar($consulta, $usuario, $rol);
        }

        return $consulta;
    }

    private function sinSolicitudesMedicas(Builder $consulta): void
    {
        $consulta->whereRaw("COALESCE(UPPER(TRIM(modulo)), '') <> ?", ['SOLICITUD_MEDICA']);
    }

    public function pendientes(?User $usuario): Builder
    {
        return $this->consulta($usuario)->abiertas();
    }
}
