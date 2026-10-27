<?php

namespace App\Modulos\Institucion\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modulos\Institucion\Aplicacion\Consultas\ConsultarCoberturaEquipo;
use Illuminate\Http\Request;

final class CoberturaEquipoController extends Controller
{
    public function __invoke(Request $request, ConsultarCoberturaEquipo $consulta)
    {
        return view('institucion::paginas.cobertura-equipo', $consulta->consultar($request->user(), $request->only('fecha', 'area', 'turno')));
    }
}
