<?php

namespace App\Modulos\Institucion\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Jornada;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\RegistrarCuentaInstitucionalHttp;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarInstitucionHttp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstitucionalController extends Controller
{
    public function usuarios(): JsonResponse
    {
        return response()->json(User::query()->with(['personal', 'roles'])->orderBy('correo')->paginate(25));
    }

    public function guardarUsuario(Request $request): JsonResponse
    {
        return response()->json(app(RegistrarCuentaInstitucionalHttp::class)->guardarUsuario($request->user(), $request->all()), 201);
    }

    public function guardarPersonal(Request $request): JsonResponse
    {
        return response()->json(app(GestionarInstitucionHttp::class)->guardarPersonal($request->user(), $request->all()), 201);
    }

    public function guardarArea(Request $request): JsonResponse
    {
        return response()->json(app(GestionarInstitucionHttp::class)->guardarArea($request->user(), $request->all()), 201);
    }

    public function guardarTurno(Request $request): JsonResponse
    {
        return response()->json(app(GestionarInstitucionHttp::class)->guardarTurno($request->user(), $request->all()), 201);
    }

    public function abrirJornada(Request $request): JsonResponse
    {
        return response()->json(app(GestionarInstitucionHttp::class)->abrirJornada($request->user(), $request->all()), 201);
    }

    public function asignarPersonal(Request $request, Jornada $jornada): JsonResponse
    {
        return response()->json(app(GestionarInstitucionHttp::class)->asignarPersonal($request->user(), $request->all(), $jornada), 201);
    }
}
