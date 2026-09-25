<?php

namespace App\Backend\Modulos\Identidad\Servicios;

use App\Backend\Modulos\Documentos\Servicios\DocumentacionUsuarioService;

use App\Models\User;
use App\Models\AsignacionPersonal;
use Spatie\Activitylog\Models\Activity;

class UsuarioFichaService
{
    /**
     * Obtiene el expediente consolidado del usuario.
     */
    public function obtenerExpedienteCompleto(User $usuario): array
    {
        $usuario->load([
            'roles',
        ]);

        return [
            'usuario' => $usuario,
            'rol' => $usuario->roles->first()?->name ?? 'Sin rol',
            'nombre_rol' => $this->formatearRol($usuario->roles->first()?->name),
            'area' => $usuario->areaInstitucional?->nombre ?? 'Sin asignar',
            'horarios' => $this->obtenerHorariosActivos($usuario),
            'historial_horarios' => $this->obtenerHistorialHorarios($usuario),
            'avance_documental' => app(DocumentacionUsuarioService::class)->calcularAvanceDocumental($usuario),
        ];
    }

    /**
     * Obtiene las asignaciones de horarios activas del usuario.
     */
    public function obtenerHorariosActivos(User $usuario)
    {
        // Usuario y personal tienen claves distintas. El horario se localiza por
        // personal.cod_usuario y luego por la jornada/turno relacionados.
        return AsignacionPersonal::query()
            ->with(['jornada.turno', 'area', 'personal'])
            ->whereHas('personal', fn ($query) => $query->where('cod_usuario', $usuario->cod_usuario))
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->latest('fecha_asignacion')
            ->first();
    }

    /**
     * Obtiene el historial de asignaciones de horarios pasadas.
     */
    public function obtenerHistorialHorarios(User $usuario)
    {
        // El historial V2 proviene de asignaciones_personal; no se consultan las
        // La agenda se obtiene de jornadas y asignaciones_personal V2.
        return AsignacionPersonal::query()
            ->with(['jornada.turno', 'area', 'personal'])
            ->whereHas('personal', fn ($query) => $query->where('cod_usuario', $usuario->cod_usuario))
            ->latest('fecha_asignacion')
            ->get();
    }

    /**
     * Obtiene el historial de auditoría de actividad del usuario desde Spatie Activitylog.
     */
    public function obtenerHistorialActividad(User $usuario, array $filtros = [])
    {
        $query = Activity::query()
            ->where(function ($q) use ($usuario) {
                // Eventos causados por el usuario o sobre el usuario
                $q->where('causer_id', $usuario->cod_usuario)
                  ->orWhere(function ($sub) use ($usuario) {
                      $sub->where('subject_type', User::class)
                          ->where('subject_id', $usuario->cod_usuario);
                  })
                  ->orWhere(function ($sub) use ($usuario) {
                      $sub->where('subject_type', \App\Models\Documento::class)
                          ->whereIn('subject_id', function ($db) use ($usuario) {
                              $db->select('cod_documento')
                                 ->from('documentos')
                                 ->where('cod_usuario', $usuario->cod_usuario);
                          });
                  });
            });

        // Aplicar filtros de fecha y acción
        if (!empty($filtros['accion'])) {
            $query->where('event', $filtros['accion']);
        }

        if (!empty($filtros['fecha_desde'])) {
            $query->where('created_at', '>=', $filtros['fecha_desde'] . ' 00:00:00');
        }

        if (!empty($filtros['fecha_hasta'])) {
            $query->where('created_at', '<=', $filtros['fecha_hasta'] . ' 23:59:59');
        }

        return $query->orderByDesc('created_at')->paginate(15);
    }

    /**
     * Helper para formatear nombres de roles de forma amigable.
     */
    private function formatearRol(?string $roleName): string
    {
        if (!$roleName) return 'SIN ROL';
        
        return match($roleName) {
            'super_admin' => 'SUPER ADMINISTRADOR',
            'admin' => 'ADMINISTRADOR',
            'personal_admin' => 'PERSONAL ADMINISTRATIVO',
            'personal_salud' => 'PERSONAL DE SALUD',
            'familiar' => 'FAMILIAR',
            default => strtoupper(str_replace('_', ' ', $roleName))
        };
    }
}
