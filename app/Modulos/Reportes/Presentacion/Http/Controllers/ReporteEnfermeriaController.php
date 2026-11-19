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
        $consulta->autorizar($request->user(), $pdf || $request->boolean('imprimir'));
        $reporte = $consulta->consultar($request->user(), [
            'desde' => $request->query('desde', now()->subDays(6)->toDateString()), 'hasta' => $request->query('hasta', today()->toDateString()),
            'codResidente' => $request->query('codResidente', ''), 'tipo' => $request->query('tipo', 'TODOS'),
        ]);
        if (! $pdf) {
            return response()->view('reportes::reportes.enfermeria-resumen', compact('reporte', 'pdf'))->header('Cache-Control', 'no-store, private');
        }
        $contenido = Pdf::loadView('reportes::reportes.enfermeria-resumen', compact('reporte', 'pdf'))
            ->setOption('defaultMediaType', 'print')->setPaper('a4', 'landscape')->output();
        activity('Reportes')->causedBy($request->user())->withProperties(['desde' => $reporte['filtros']['desde'], 'hasta' => $reporte['filtros']['hasta'], 'tipo' => $reporte['filtros']['tipo']])->log('Resumen de Enfermería PDF generado.');

        return response($contenido, 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'attachment; filename="resumen-enfermeria.pdf"', 'Cache-Control' => 'no-store, private']);
    }

    public function pdf(Request $request, ConsultarReporteEnfermeria $consulta)
    {
        return $this->mostrar($request, $consulta, true);
    }

    public function csv(Request $request, ConsultarReporteEnfermeria $consulta)
    {
        $consulta->autorizar($request->user(), true);
        $reporte = $consulta->consultar($request->user(), [
            'desde' => $request->query('desde', now()->subDays(6)->toDateString()), 'hasta' => $request->query('hasta', today()->toDateString()),
            'codResidente' => $request->query('codResidente', ''), 'tipo' => $request->query('tipo', 'TODOS'),
        ]);
        $archivo = fopen('php://temp', 'w+');
        fwrite($archivo, "\xEF\xBB\xBF");
        fputcsv($archivo, ['Desde', 'Hasta', 'Fecha', 'Hora', 'Momento registrado', 'Residente', 'Sección', 'Estado / resultado', 'Detalle', 'Profesional / responsable'], ';', '"', '');
        foreach ($reporte['filas'] as $fila) {
            $valores = [$reporte['filtros']['desde'], $reporte['filtros']['hasta'], $fila['fecha']->format('d/m/Y'), $fila['categoria'] === 'SUENO' ? '' : $fila['fecha']->format('H:i'),
                $fila['fecha_tipo'], $fila['residente'], $fila['tipo'], $fila['estado'], $fila['detalle'], $fila['profesional']];
            // Evitar que una observación/nombre se ejecute como fórmula al abrir Excel.
            $valores = array_map(fn ($v) => preg_match('/^[\s\x{FEFF}]*[=+@\-]/u', (string) $v) ? "'".$v : $v, $valores);
            fputcsv($archivo, $valores, ';', '"', '');
        }
        rewind($archivo);
        $contenido = stream_get_contents($archivo);
        fclose($archivo);
        activity('Reportes')->causedBy($request->user())->withProperties(collect($reporte['filtros'])->except('codResidente')->all())->log('Resumen de Enfermería CSV generado.');

        return response($contenido, 200, ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="reporte-enfermeria.csv"', 'Cache-Control' => 'no-store, private', 'X-Content-Type-Options' => 'nosniff']);
    }
}
