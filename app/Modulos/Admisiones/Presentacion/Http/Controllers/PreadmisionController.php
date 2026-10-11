<?php

namespace App\Modulos\Admisiones\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Preadmision;
use App\Modulos\Admisiones\Aplicacion\Acciones\GestionarPreadmisionesHttp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PreadmisionController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        if ($request->expectsJson()) {
            return response()->json(Preadmision::query()->latest('fecha_solicitud')->paginate(20));
        }

        return view('pages.preadmisiones.index');
    }

    public function store(Request $request): JsonResponse
    {
        return response()->json(app(GestionarPreadmisionesHttp::class)->store($request->user(), $request->all()), 201);
    }

    public function revisar(Request $request, Preadmision $preadmision): JsonResponse
    {
        return response()->json(app(GestionarPreadmisionesHttp::class)->revisar($request->user(), $request->all(), $preadmision), 200);
    }
}
