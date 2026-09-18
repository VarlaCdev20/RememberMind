<?php

namespace App\Http\Controllers\Residentes;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use Illuminate\Http\JsonResponse;

class ResidenteController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Residente::query()->with('ocupacionActiva.cama.habitacion')->orderBy('apellido_paterno')->paginate());
    }

    public function show(Residente $residente): JsonResponse
    {
        return response()->json($residente->load(['admisiones', 'vinculosContacto.contacto', 'ocupacionActiva.cama.habitacion']));
    }
}
