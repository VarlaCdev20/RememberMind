<?php

namespace App\Modulos\Reportes\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Residente;
use App\Modulos\Reportes\Aplicacion\Consultas\ConsultarReporteEnfermeria;
use Illuminate\Http\Request;

final class FichaPacienteReporteController extends Controller
{
    public function pdf(Request $request, Residente $adulto)
    {
        // La ruta individual conserva su URL y adopta el mismo filtro/alcance que el resumen.
        $request->query->set('codResidente', $adulto->getKey());

        return app(ReporteEnfermeriaController::class)->pdf($request, app(ConsultarReporteEnfermeria::class));
    }
}
