<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use App\Services\Reportes\ReporteDataService;
use App\Exports\Reportes\ReporteSaludExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReporteSaludController extends Controller
{
    public function __construct(protected ReporteDataService $service) {}

    private function compilarDatos(): array
    {
        return [
            'resumen'      => $this->service->saludResumen(),
            'fichas'       => $this->service->fichasLista(),
            'medicaciones' => $this->service->medicacionLista(),
            'valoraciones' => $this->service->valoracionesLista(),
            'atenciones'   => $this->service->atencionesPorMes(),
            'dependencia'  => $this->service->dependenciaDistribucion(),
            'riesgos'      => $this->service->riesgosCaida(),
        ];
    }

    public function preview()
    {
        return view('reportes.salud.index', [
            'esPdf'    => false,
            'datos'    => $this->compilarDatos(),
            'graficas' => [
                'dependencia' => $this->service->dependenciaDistribucion(),
                'riesgos'     => $this->service->riesgosCaida(),
                'atenciones'  => $this->service->atencionesPorMes(),
            ],
            'metadata' => [
                'titulo'       => 'Reporte de Salud y Seguimiento',
                'generado_en'  => now()->format('d/m/Y H:i'),
                'generado_por' => Auth::user()?->nombres . ' ' . Auth::user()?->ap_paterno,
                'seccion'      => 'Salud y Seguimiento',
            ],
        ]);
    }

    public function pdf()
    {
        $datos = $this->compilarDatos();

        $pdf = Pdf::loadView('reportes.salud.index', [
            'esPdf'    => true,
            'datos'    => $datos,
            'graficas' => [
                'dependencia' => $this->service->dependenciaDistribucion(),
                'riesgos'     => $this->service->riesgosCaida(),
                'atenciones'  => $this->service->atencionesPorMes(),
            ],
            'metadata' => [
                'titulo'       => 'Reporte de Salud y Seguimiento',
                'generado_en'  => now()->format('d/m/Y H:i'),
                'generado_por' => Auth::user()?->nombres . ' ' . Auth::user()?->ap_paterno,
                'seccion'      => 'Salud y Seguimiento',
            ],
        ])
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'defaultFont'          => 'DejaVu Sans',
            'isRemoteEnabled'      => false,
            'isHtml5ParserEnabled' => true,
            'chroot'               => public_path(),
            'dpi'                  => 96,
        ]);

        return $pdf->download('reporte-salud-' . now()->format('Y-m-d') . '.pdf');
    }

    public function excel()
    {
        $nombre = 'reporte-salud-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new ReporteSaludExport($this->service), $nombre);
    }
}
