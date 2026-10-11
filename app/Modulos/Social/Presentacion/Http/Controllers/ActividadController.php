<?php

namespace App\Modulos\Social\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Modulos\Social\Aplicacion\Acciones\GestionarActividadesHttp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActividadController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Actividad::query()->with('participantes.residente')->latest('fecha_hora')->paginate(25));
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(app(GestionarActividadesHttp::class)->store($request->user(), $request->all()), 201);
    }

    public function participante(Request $request, Actividad $actividad): JsonResponse
    {
        return response()->json(app(GestionarActividadesHttp::class)->participante($request->user(), $request->all(), $actividad), 201);
    }
}
