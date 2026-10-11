<?php

namespace App\Modulos\Institucion\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Habitacion;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarInfraestructuraResidencialHttp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InfraestructuraController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Habitacion::query()->with(['camas.ocupacionActiva.residente'])->orderBy('codigo')->get());
    }

    public function habitacion(Request $request): JsonResponse
    {
        return response()->json(app(GestionarInfraestructuraResidencialHttp::class)->habitacion($request->user(), $request->all()), 201);
    }

    public function cama(Request $request, Habitacion $habitacion): JsonResponse
    {
        return response()->json(app(GestionarInfraestructuraResidencialHttp::class)->cama($request->user(), $request->all(), $habitacion), 201);
    }
}
