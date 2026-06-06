<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;
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
    public function index()
    {
        $usuario = Auth::user();

        // 1. Auditoría institucional (Evento en ESPAÑOL con contexto extendido)
        $this->registrarAcceso($usuario);

        // 2. Obtención de datos (Optimizado mediante Caché en el Servicio)
        $datos = $this->dashboardService->obtenerDatosDashboard($usuario);

        return view('dashboard', $datos);
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
            $yaExiste = \Spatie\Activitylog\Models\Activity::where('causer_id', $usuario->cod_usu)
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
     * - Asignación de Voluntarios
     */
}
