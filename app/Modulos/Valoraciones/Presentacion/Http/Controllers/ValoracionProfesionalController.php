<?php

namespace App\Modulos\Valoraciones\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarValoracionProfesional;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ValoracionProfesionalController extends Controller
{
    public function __construct(private readonly AutorizacionClinicaService $autorizacion) {}

    public function registrar(Request $request, Residente $residente, string $tipo): JsonResponse
    {
        return response()->json(app(RegistrarValoracionProfesional::class)->registrar($request->user(), $request->all(), $residente, $tipo), 201);
    }
}
