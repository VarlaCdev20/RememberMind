<?php

namespace App\Http\Controllers\Administracion;

use App\Backend\Modulos\Administracion\Servicios\CentroCoordinacionService;
use App\Backend\Modulos\Reportes\Servicios\DashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CentroCoordinacionController extends Controller
{
    public function __invoke(Request $request, CentroCoordinacionService $centro, DashboardService $dashboard)
    {
        $periodo = $request->validate([
            'periodo' => ['sometimes', 'in:7d,4w,3m,year'],
        ])['periodo'] ?? '7d';

        return view('pages.admin.administracion.dashboard', [
            'datos' => $centro->resumen($periodo),
            'saludo' => $dashboard->obtenerSaludoUsuario($request->user(), 'ADMINISTRADOR'),
        ]);
    }
}
