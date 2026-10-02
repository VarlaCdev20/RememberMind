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
                ->whereIn('cod_contacto', $usuario->contactos()->select('cod_contacto'))
                ->where('autoriza_informacion', true)->where('estado', 'ACTIVO')
                ->pluck('cod_residente');
            $residentesVinculados = Residente::query()->whereIn('cod_residente', $codigos)
                ->where('estado', 'ADMITIDO')->orderBy('apellido_paterno')->get();
            $resumen = ['residentes_admitidos' => $residentesVinculados->count()];
            return request()->expectsJson() ? response()->json($resumen) : view('pages.dashboard', compact('resumen', 'residentesVinculados'));
        }
        $resumen = [];
        if ($usuario->can('viewAny', Residente::class)) {
            $resumen['residentes_admitidos'] = Residente::query()->where('estado', 'ADMITIDO')->count();
        }
        if ($usuario->can('preadmisiones.ver')) {
            $resumen['preadmisiones_pendientes'] = Preadmision::query()->where('estado', 'PENDIENTE')->count();
        }
        if ($usuario->can('admisiones.ver')) {
            $resumen['admisiones_activas'] = Admision::query()->where('estado', 'ACTIVA')->count();
        }
        if ($usuario->can('ocupaciones_cama.ver')) {
            $resumen['camas_ocupadas'] = OcupacionCama::query()->where('estado', 'ACTIVA')->count();
        }
        if ($usuario->can('alertas.ver')) {
            $resumen['alertas_abiertas'] = Alerta::query()->whereNotIn('estado', ['CERRADA', 'ANULADA'])->count();
        }

        return request()->expectsJson() ? response()->json($resumen) : view('pages.dashboard', compact('resumen'));
    }
}
