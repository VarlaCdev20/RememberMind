<?php

namespace App\Services\Usuarios;

use App\Models\User;
use App\Models\AsignacionTurno;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;

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
        $rol = $usuario->roles->first()?->name;
        if (in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'])) {
            return \App\Models\HorarioPersonalSalud::where('cod_usu', $usuario->cod_usu)
                ->where('estado', 'ACTIVO')
                ->first();
        }
        if (in_array($rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR'])) {
            return \App\Models\HorarioPersonalAdmin::where('cod_usu', $usuario->cod_usu)
                ->where('estado', 'ACTIVO')
                ->first();
        }
        return null;
    }

    /**
     * Obtiene el historial de asignaciones de horarios pasadas.
     */
    public function obtenerHistorialHorarios(User $usuario)
    {
        $rol = $usuario->roles->first()?->name;
        if (in_array($rol, ['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA'])) {
            return \App\Models\HorarioPersonalSalud::where('cod_usu', $usuario->cod_usu)
                ->orderBy('dia_semana')
                ->get();
        }
        if (in_array($rol, ['SUPERADMINISTRADOR', 'ADMINISTRADOR'])) {
            return \App\Models\HorarioPersonalAdmin::where('cod_usu', $usuario->cod_usu)
                ->orderBy('dia_semana')
                ->get();
        }
        return collect();
    }

    /**
     * Obtiene el historial de auditoría de actividad del usuario desde Spatie Activitylog.
     */
    public function obtenerHistorialActividad(User $usuario, array $filtros = [])
    {
        $query = Activity::query()
            ->where(function ($q) use ($usuario) {
                // Eventos causados por el usuario o sobre el usuario
                $q->where('causer_id', $usuario->cod_usu)
                  ->orWhere(function ($sub) use ($usuario) {
                      $sub->where('subject_type', User::class)
                          ->where('subject_id', $usuario->cod_usu);
                  })
                  ->orWhere(function ($sub) use ($usuario) {
                      $sub->where('subject_type', \App\Models\DocumentoUsuario::class)
                          ->whereIn('subject_id', function ($db) use ($usuario) {
                              $db->select('cod_doc_usu')
                                 ->from('documentos_usuarios')
                                 ->where('cod_usu', $usuario->cod_usu);
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
            'voluntario' => 'VOLUNTARIO',
            'familiar' => 'FAMILIAR',
            default => strtoupper(str_replace('_', ' ', $roleName))
        };
    }
}
