<?php

namespace App\Modulos\Clinica\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Alergia;
use App\Models\AntecedenteClinico;
use App\Models\Atencion;
use App\Models\Diagnostico;
use App\Models\Incidente;
use App\Models\IndicacionClinica;
use App\Models\MedicionAntropometrica;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\ValoracionDolor;
use App\Modulos\Clinica\Aplicacion\Acciones\AutorizacionClinicaService;
use App\Modulos\Clinica\Aplicacion\Acciones\RegistrarExpedienteClinico;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpedienteClinicoController extends Controller
{
    private const ROLES_CLINICOS = ['MEDICO GENERAL/GERIATRA', 'ENFERMEROS', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'];

    public function __construct(private readonly AutorizacionClinicaService $autorizacion) {}

    public function index(Request $request, Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);

        return response()->json(['residente' => $residente, 'atenciones' => Atencion::query()->whereBelongsTo($residente, 'residente')->with(['area', 'personal', 'notas'])->latest('fecha_hora')->get(), 'antecedentes' => AntecedenteClinico::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_referencia')->get(), 'diagnosticos' => Diagnostico::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(), 'alergias' => Alergia::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(), 'signos_vitales' => SignoVital::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->limit(50)->get(), 'dolor' => ValoracionDolor::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->limit(50)->get(), 'antropometria' => MedicionAntropometrica::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->limit(50)->get(), 'incidentes' => Incidente::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get(), 'indicaciones' => IndicacionClinica::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora')->get()]);
    }

    public function crearAtencion(Request $request, Residente $residente): JsonResponse
    {
        return response()->json(app(RegistrarExpedienteClinico::class)->crearAtencion($request->user(), $request->all(), $residente), 201);
    }

    public function registrar(Request $request, Residente $residente, string $tipo): JsonResponse
    {
        return response()->json(app(RegistrarExpedienteClinico::class)->registrar($request->user(), $request->all(), $residente, $tipo), 201);
    }
}
