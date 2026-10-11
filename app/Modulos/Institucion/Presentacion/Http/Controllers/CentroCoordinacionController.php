<?php

namespace App\Modulos\Institucion\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modulos\Institucion\Aplicacion\Consultas\CentroCoordinacionService;
use App\Modulos\Reportes\Aplicacion\Consultas\DashboardService;
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
