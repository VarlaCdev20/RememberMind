<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;

use App\Backend\Modulos\Identidad\Servicios\RolePreviewService;
use App\Backend\Modulos\Identidad\Servicios\VisibilidadNavegacion;
use App\Backend\Modulos\Reportes\Servicios\DashboardService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

/**
 * DashboardController
 *
 * Orquestador principal del panel administrativo institucional.
 * Gestiona la auditoría de acceso y la integración con la capa de servicios.
 *
 * @author Arquitecto Senior Laravel
 * @version 3.0 (Profesional)
 */
class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Punto de entrada principal del dashboard.
     */
    public function index(RolePreviewService $rolePreview, VisibilidadNavegacion $visibilidadNavegacion)
    {
        $usuario = Auth::user();
        $previewRole = $rolePreview->activeRole($usuario);

        // 1. Auditoría institucional (Evento en ESPAÑOL con contexto extendido)
        $this->registrarAcceso($usuario);
        $previewDashboard = match ($previewRole) {
            'ADMINISTRADOR' => 'admin.administracion.dashboard',
            'MEDICO GENERAL/GERIATRA' => 'admin.medico.dashboard',
            'ENFERMEROS' => 'admin.enfermeria.dashboard',
            'PSICOLOGO/A' => 'admin.psicologia.dashboard',
            default => null,
        };

        if ($previewDashboard && $visibilidadNavegacion->puedeVerRuta($previewDashboard) && ! request()->routeIs($previewDashboard)) {
            return redirect()->route($previewDashboard);
        }

        // 2. Redirección basada en rol
        if (! $rolePreview->isActive($usuario) && $visibilidadNavegacion->puedeVerRuta('admin.medico.dashboard') && ($usuario->hasRole('MEDICO GENERAL/GERIATRA') || (!$usuario->hasAnyRole(['SUPERADMINISTRADOR', 'ADMINISTRADOR', 'superadmin', 'admin']) && $usuario->can('valoracion_medica.ver')))) {
            return redirect()->route('admin.medico.dashboard');
        }

        if (! $rolePreview->isActive($usuario) && $visibilidadNavegacion->puedeVerRuta('admin.enfermeria.dashboard') && ($usuario->hasRole('ENFERMEROS') || (!$usuario->hasAnyRole(['SUPERADMINISTRADOR', 'ADMINISTRADOR', 'superadmin', 'admin']) && $usuario->can('enfermeria.ver_dashboard')))) {
            return redirect()->route('admin.enfermeria.dashboard');
        }

        if (! $rolePreview->isActive($usuario) && $visibilidadNavegacion->puedeVerRuta('admin.psicologia.dashboard') && $usuario->hasRole('PSICOLOGO/A')) {
            return redirect()->route('admin.psicologia.dashboard');
        }

        if ($visibilidadNavegacion->puedeVerRuta('admin.administracion.dashboard')
            && ($previewRole === 'ADMINISTRADOR' || (! $rolePreview->isActive($usuario) && $usuario->hasRole('ADMINISTRADOR')))
            && ! request()->routeIs('admin.administracion.*')) {
            return redirect()->route('admin.administracion.dashboard');
        }

        // 3. Obtención de datos (Optimizado mediante Caché en el Servicio)
        $datos = $this->dashboardService->obtenerDatosDashboard($usuario);

        return view('pages.dashboard', $datos);
    }

    /**
     * Registra la trazabilidad del acceso con estándares de auditoría.
     * Solo registra el primer acceso del día para evitar saturar la bitácora.
     */
    private function registrarAcceso($usuario)
    {
        if (Schema::hasTable('activity_log')) {
            $hoy = now()->toDateString();

            // Verificar si ya se registró el acceso hoy
            $yaExiste = \Spatie\Activitylog\Models\Activity::where('causer_id', $usuario->getKey())
                ->where('event', 'inicio_sesion')
                ->whereDate('created_at', $hoy)
                ->exists();

            if (!$yaExiste) {
                $rol = $usuario->getRoleNames()->first() ?? 'Sin rol';

                activity('Seguridad')
                    ->causedBy($usuario)
                    ->event('inicio_sesion')
                    ->withProperties([
                        'rol'       => $rol,
                        'correo'    => $usuario->correo,
                        'ip'        => request()->ip(),
                        'navegador' => request()->userAgent(),
                        'modulo'    => 'Seguridad'
                    ])
                    ->log('Inició sesión en el sistema');
            }
        }
    }

    /**
     * NOTA PARA DESARROLLADORES:
     * Para invalidar la caché del dashboard tras cambios críticos,
     * llamar a: $this->dashboardService->limpiarCache(auth()->id());
     *
     * Puntos sugeridos:
     * - Store/Update de Usuarios
     * - Registro de Actividades
     */
}
