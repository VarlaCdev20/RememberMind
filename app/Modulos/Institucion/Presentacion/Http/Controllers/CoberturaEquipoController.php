<?php

namespace App\Modulos\Institucion\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modulos\Institucion\Aplicacion\Consultas\ConsultarCoberturaEquipo;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class CoberturaEquipoController extends Controller
{
    public function __invoke(Request $request, ConsultarCoberturaEquipo $consulta)
    {
        $entrada = $request->only('fecha', 'area', 'turno', 'modo', 'estado');
        try {
            return view('institucion::paginas.cobertura-equipo', $consulta->consultar($request->user(), $entrada));
        } catch (ValidationException $error) {
            if ($request->expectsJson()) {
                throw $error;
            }

            return redirect()->route('admin.cobertura-equipo.index', array_diff_key($entrada, $error->errors()))->withErrors($error->errors());
        }
    }
}
