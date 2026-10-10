<?php

namespace App\Modulos\Valoraciones\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Instrumento;
use App\Models\Residente;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Valoraciones\Aplicacion\Acciones\AplicarInstrumento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstrumentoController extends Controller
{
    private const ROLES_APLICADORES = ['MEDICO GENERAL/GERIATRA', 'ENFERMEROS', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'];

    public function __construct(private readonly AutorizacionClinicaService $autorizacion) {}

    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('instrumentos.ver'), 403);

        return response()->json(Instrumento::query()->with('preguntas.opciones')->where('estado', 'ACTIVO')->get());
    }

    public function aplicar(Request $request, Instrumento $instrumento, Residente $residente): JsonResponse
    {
        return response()->json(app(AplicarInstrumento::class)->aplicar($request->user(), $request->all(), $instrumento, $residente), 201);
    }
}
