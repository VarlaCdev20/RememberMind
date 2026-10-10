<?php

namespace App\Modulos\Cuidados\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AsignacionResidenteJornada;
use App\Models\ControlCognitivo;
use App\Models\EjecucionCuidado;
use App\Models\Herida;
use App\Models\IntervencionCuidado;
use App\Models\PaseTurno;
use App\Models\PlanCuidado;
use App\Models\RegistroConductual;
use App\Models\RegistroEliminacion;
use App\Models\RegistroHidratacion;
use App\Models\RegistroIngesta;
use App\Models\RegistroMovilidad;
use App\Models\RegistroSueno;
use App\Models\Residente;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Cuidados\Aplicacion\Acciones\GestionarCuidadosHttp;
use App\Modulos\Institucion\Aplicacion\Acciones\AsignarResidenteJornadaHttp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CuidadoController extends Controller
{
    private const ROLES_PLAN = ['MEDICO GENERAL/GERIATRA', 'ENFERMEROS', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'];

    public function __construct(private readonly AutorizacionClinicaService $autorizacion) {}

    public function index(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);

        return response()->json(['asignaciones' => AsignacionResidenteJornada::query()->where('cod_residente', $residente->cod_residente)->get(), 'controles_cognitivos' => ControlCognitivo::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(), 'conducta' => RegistroConductual::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(), 'sueno' => RegistroSueno::query()->where('cod_residente', $residente->cod_residente)->latest('fecha')->get(), 'ingesta' => RegistroIngesta::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(), 'hidratacion' => RegistroHidratacion::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(), 'eliminacion' => RegistroEliminacion::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(), 'movilidad' => RegistroMovilidad::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(), 'heridas' => Herida::query()->where('cod_residente', $residente->cod_residente)->with('curaciones')->latest('fecha_hora_identificacion')->get(), 'planes' => PlanCuidado::query()->where('cod_residente', $residente->cod_residente)->with('intervenciones')->get(), 'ejecuciones' => EjecucionCuidado::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora_programada')->get(), 'pases' => PaseTurno::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get()]);
    }

    public function registrar(Request $request, Residente $residente, string $tipo): JsonResponse
    {
        return response()->json(app(GestionarCuidadosHttp::class)->registrar($request->user(), $request->all(), $residente, $tipo), 201);
    }

    public function crearPlan(Request $request, Residente $residente): JsonResponse
    {
        return response()->json(app(GestionarCuidadosHttp::class)->crearPlan($request->user(), $request->all(), $residente), 201);
    }

    public function crearIntervencion(Request $request, PlanCuidado $plan): JsonResponse
    {
        return response()->json(app(GestionarCuidadosHttp::class)->crearIntervencion($request->user(), $request->all(), $plan), 201);
    }

    public function programar(Request $request, IntervencionCuidado $intervencion): JsonResponse
    {
        return response()->json(app(GestionarCuidadosHttp::class)->programar($request->user(), $request->all(), $intervencion), 201);
    }

    public function asignarJornada(Request $request, Residente $residente): JsonResponse
    {
        return response()->json(app(AsignarResidenteJornadaHttp::class)->asignarJornada($request->user(), $request->all(), $residente), 201);
    }

    public function curarHerida(Request $request, Herida $herida): JsonResponse
    {
        return response()->json(app(GestionarCuidadosHttp::class)->curarHerida($request->user(), $request->all(), $herida), 201);
    }

    public function registrarPase(Request $request, Residente $residente): JsonResponse
    {
        return response()->json(app(GestionarCuidadosHttp::class)->registrarPase($request->user(), $request->all(), $residente), 201);
    }

    public function ejecutar(Request $request, IntervencionCuidado $intervencion): JsonResponse
    {
        return response()->json(app(GestionarCuidadosHttp::class)->ejecutar($request->user(), $request->all(), $intervencion), 201);
    }
}
