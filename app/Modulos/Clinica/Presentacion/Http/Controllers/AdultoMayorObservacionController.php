<?php

namespace App\Modulos\Clinica\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use App\Modulos\Clinica\Aplicacion\Acciones\GestionarNotasClinicas;
use Illuminate\Http\Request;

class AdultoMayorObservacionController extends Controller
{
    public function index(Residente $adulto_mayor)
    {
        $query = $adulto_mayor->observaciones();
        if (request('buscar')) {
            $buscar = request('buscar');
            $query->where('contenido', 'ilike', "%{$buscar}%");
        }
        $registros = $query->orderByDesc('fecha_hora')->paginate(15)->withQueryString();

        return view('pages.adultos-mayores.observaciones.index', ['adulto_mayor' => $adulto_mayor, 'observaciones' => $registros]);
    }

    public function store(Request $request, Residente $adulto_mayor)
    {
        app(GestionarNotasClinicas::class)->store(auth()->user(), $request->all(), $adulto_mayor);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'observaciones'])->with('success', 'Observación registrada correctamente.');
    }

    public function update(Request $request, Residente $adulto_mayor, $observacionId)
    {
        app(GestionarNotasClinicas::class)->update(auth()->user(), $request->all(), $adulto_mayor, $observacionId);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'observaciones'])->with('success', 'Observación actualizada correctamente.');
    }

    public function destroy(Residente $adulto_mayor, $observacionId)
    {
        app(GestionarNotasClinicas::class)->destroy(auth()->user(), [], $adulto_mayor, $observacionId);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'observaciones'])->with('success', 'Observación anulada correctamente.');
    }

    public function restore(Residente $adulto_mayor, $observacionId)
    {
        app(GestionarNotasClinicas::class)->restore(auth()->user(), [], $adulto_mayor, $observacionId);

        return redirect()->route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto_mayor->cod_residente, 'tab' => 'observaciones'])->with('success', 'Observación restaurada correctamente.');
    }
}
