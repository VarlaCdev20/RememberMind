<?php

namespace App\Modulos\Reportes\Presentacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modulos\Reportes\Aplicacion\Consultas\ConsultarReporteEnfermeria;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

final class ReporteEnfermeriaController extends Controller
{
    public function mostrar(Request $request, ConsultarReporteEnfermeria $consulta, bool $pdf = false)
    {
        $consulta->autorizar($request->user(), $pdf);
        $reporte = $consulta->consultar($request->user(), [
            'desde' => $request->query('desde', now()->subDays(6)->toDateString()), 'hasta' => $request->query('hasta', today()->toDateString()),
            'codResidente' => $request->query('codResidente', ''), 'tipo' => $request->query('tipo', 'TODOS'),
        ]);
        if (! $pdf) {
            return response()->view('reportes::reportes.enfermeria-resumen', compact('reporte'))->header('Cache-Control', 'no-store, private');
        }
        $contenido = Pdf::loadView('reportes::reportes.enfermeria-resumen', compact('reporte'))
            ->setOption('defaultMediaType', 'print')->setPaper('a4', 'landscape')->output();
        activity('Reportes')->causedBy($request->user())->withProperties(['desde' => $reporte['filtros']['desde'], 'hasta' => $reporte['filtros']['hasta'], 'tipo' => $reporte['filtros']['tipo']])->log('Resumen de Enfermería PDF generado.');

        return response($contenido, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="resumen-enfermeria.pdf"', 'Cache-Control' => 'no-store, private']);
    }

    public function pdf(Request $request, ConsultarReporteEnfermeria $consulta)
    {
        return $this->mostrar($request, $consulta, true);
    }
}
