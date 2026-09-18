<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Models\Admision;
use App\Models\Alerta;
use App\Models\OcupacionCama;
use App\Models\Preadmision;
use App\Models\Residente;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'residentes_admitidos' => Residente::query()->where('estado', 'ADMITIDO')->count(),
            'preadmisiones_pendientes' => Preadmision::query()->where('estado', 'PENDIENTE')->count(),
            'admisiones_activas' => Admision::query()->where('estado', 'ACTIVA')->count(),
            'camas_ocupadas' => OcupacionCama::query()->where('estado', 'ACTIVA')->count(),
            'alertas_abiertas' => Alerta::query()->whereNotIn('estado', ['CERRADA', 'ANULADA'])->count(),
        ]);
    }
}
