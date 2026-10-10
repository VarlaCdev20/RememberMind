<?php

namespace App\Modulos\Valoraciones\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use App\Modulos\Valoraciones\Aplicacion\Acciones\GestionarEvaluaciones;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdultoMayorEvaluacionController extends Controller
{
    public function index(Residente $adulto_mayor)
    {
        return new RedirectResponse(route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'evaluaciones']));
    }

    public function store(Request $request, Residente $adulto_mayor)
    {
        app(GestionarEvaluaciones::class)->store(auth()->user(), $request->all(), $adulto_mayor);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'evaluaciones'])->with('success', 'Evaluación registrada correctamente.');
    }

    public function show(Residente $adulto_mayor, $evaluacionId)
    {
        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'evaluaciones']);
    }

    public function destroy(Residente $adulto_mayor, $id)
    {
        app(GestionarEvaluaciones::class)->destroy(auth()->user(), [], $adulto_mayor, $id);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'evaluaciones'])->with('success', 'Evaluación anulada correctamente.');
    }

    public function restore(Residente $adulto_mayor, $id)
    {
        app(GestionarEvaluaciones::class)->restore(auth()->user(), [], $adulto_mayor, $id);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'evaluaciones'])->with('success', 'Evaluación restaurada correctamente.');
    }
}
