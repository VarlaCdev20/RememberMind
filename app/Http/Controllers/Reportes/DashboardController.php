<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Admision;
use App\Models\Alerta;
use App\Models\OcupacionCama;
use App\Models\Preadmision;
use App\Models\Residente;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|JsonResponse
    {
        $usuario = request()->user();
        if ($usuario->hasRole('FAMILIAR')) {
            $codigos = \App\Models\ResidenteContacto::query()
                ->whereIn('cod_contacto', $usuario->contactos()->pluck('cod_contacto'))
                ->where('autoriza_informacion', true)->where('estado', 'ACTIVO')
                ->pluck('cod_residente');
            $resumen = [
                'residentes_admitidos' => Residente::query()->whereIn('cod_residente', $codigos)->where('estado', 'ADMITIDO')->count(),
                'preadmisiones_pendientes' => 0, 'admisiones_activas' => 0,
                'camas_ocupadas' => 0, 'alertas_abiertas' => 0,
            ];
            return request()->expectsJson() ? response()->json($resumen) : view('pages.dashboard', compact('resumen'));
        }
        $resumen = [
            'residentes_admitidos' => Residente::query()->where('estado', 'ADMITIDO')->count(),
            'preadmisiones_pendientes' => Preadmision::query()->where('estado', 'PENDIENTE')->count(),
            'admisiones_activas' => Admision::query()->where('estado', 'ACTIVA')->count(),
            'camas_ocupadas' => OcupacionCama::query()->where('estado', 'ACTIVA')->count(),
            'alertas_abiertas' => Alerta::query()->whereNotIn('estado', ['CERRADA', 'ANULADA'])->count(),
        ];

        return request()->expectsJson() ? response()->json($resumen) : view('pages.dashboard', compact('resumen'));
    }
}
