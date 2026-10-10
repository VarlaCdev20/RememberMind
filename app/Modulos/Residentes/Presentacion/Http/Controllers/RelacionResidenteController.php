<?php

namespace App\Modulos\Residentes\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Consentimiento;
use App\Models\Residente;
use App\Models\Visita;
use App\Modulos\Residentes\Aplicacion\Acciones\RegistrarVisitaConsentimientoHttp;
use App\Modulos\Social\Aplicacion\Acciones\VincularContactoResidenteHttp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelacionResidenteController extends Controller
{
    public function index(Residente $residente): JsonResponse
    {
        $this->authorize('view', $residente);
        if (auth()->user()->hasRole('FAMILIAR')) {
            $visitas = auth()->user()->can('visitas.ver') ? Visita::query()->where('cod_residente', $residente->cod_residente)->whereIn('cod_contacto', auth()->user()->contactos()->where('estado', 'ACTIVO')->select('cod_contacto'))->latest('fecha_hora_programada')->get(['fecha_hora_programada', 'fecha_hora_ingreso']) : collect();

            return response()->json(['visitas' => $visitas]);
        }

        return response()->json(['contactos' => $residente->vinculosContacto()->with('contacto')->get(), 'consentimientos' => Consentimiento::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_consentimiento')->get(), 'visitas' => Visita::query()->where('cod_residente', $residente->cod_residente)->latest('fecha_hora_programada')->get(), 'ocupacion' => $residente->ocupacionActiva()->with('cama.habitacion')->first()]);
    }

    public function vincularContacto(Request $request, Residente $residente): JsonResponse
    {
        return response()->json(app(VincularContactoResidenteHttp::class)->vincularContacto($request->user(), $request->all(), $residente), 201);
    }

    public function registrarVisita(Request $request, Residente $residente): JsonResponse
    {
        return response()->json(app(RegistrarVisitaConsentimientoHttp::class)->registrarVisita($request->user(), $request->all(), $residente), 201);
    }

    public function registrarConsentimiento(Request $request, Residente $residente): JsonResponse
    {
        return response()->json(app(RegistrarVisitaConsentimientoHttp::class)->registrarConsentimiento($request->user(), $request->all(), $residente), 201);
    }
}
