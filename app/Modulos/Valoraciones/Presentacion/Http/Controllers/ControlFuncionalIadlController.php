<?php

namespace App\Modulos\Valoraciones\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ControlFuncionalIadl;
use App\Models\Residente;
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarControlIadl;
use App\Modulos\Valoraciones\Dominio\Servicios\AnalizadorIadlC03;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ControlFuncionalIadlController extends Controller
{
    public function index(Request $request, Residente $adulto_mayor, AnalizadorIadlC03 $analizador): View
    {
        $historial = ControlFuncionalIadl::query()
            ->where('cod_residente', $adulto_mayor->cod_residente)
            ->orderByDesc('fecha_hora')->orderByDesc('registrado_en')
            ->get();

        $filas = $historial->map(static fn (ControlFuncionalIadl $fila): array => [
            ...$fila->only([
                'cod_control_iadl', 'cod_control_anterior', 'actividad', 'oportunidad',
                'desempeno', 'tipo_ayuda', 'causa_ayuda', 'nivel_previo', 'fuente',
                'estado', 'observacion'
            ]),
            'fecha_hora' => $fila->fecha_hora->format('Y-m-d H:i:s'),
            'registrado_en' => $fila->registrado_en->format('Y-m-d H:i:s'),
        ])->all();
        $anterior = null;
        if ($request->filled('rectificar')) {
            abort_unless($request->user()->can('controles_funcionales_iadl.rectificar'), 403);
            $anterior = $historial->firstWhere('cod_control_iadl', $request->string('rectificar')->toString());
            abort_unless($anterior, 404);
            abort_if($historial->contains('cod_control_anterior', $anterior->cod_control_iadl), 409,
                'Este control ya tiene una rectificación.');
        }

        return view('valoraciones.iadl.index', [
            'adulto' => $adulto_mayor,
            'registros' => $filas,
            'sintesis' => $analizador->analizar($filas),
            'anterior' => $anterior,
            'actividades' => ControlFuncionalIadl::ACTIVIDADES,
            'opciones' => [
                'oportunidad' => ControlFuncionalIadl::OPORTUNIDADES,
                'desempeno' => ControlFuncionalIadl::DESEMPENOS,
                'tipo_ayuda' => ControlFuncionalIadl::AYUDAS,
                'causa_ayuda' => ControlFuncionalIadl::CAUSAS,
                'nivel_previo' => ControlFuncionalIadl::NIVELES_PREVIOS,
                'fuente' => ControlFuncionalIadl::FUENTES,
            ],
        ]);
    }

    public function store(Request $request, Residente $adulto_mayor, RegistrarControlIadl $registrar): RedirectResponse
    {
        $registrar->ejecutar($request->user(), $adulto_mayor, $request->all());

        return redirect()
            ->route('admin.adultos-mayores.iadl.index', ['adulto_mayor' => $adulto_mayor->getRouteKey()])
            ->with('status', 'Observación IADL registrada con trazabilidad. C03 sigue pendiente de valoración profesional.');
    }
}
