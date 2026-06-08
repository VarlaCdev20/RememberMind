<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use App\Services\Reportes\ReporteDataService;
use App\Exports\Reportes\ReporteActividadesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReporteActividadesController extends Controller
{
    public function __construct(protected ReporteDataService $service) {}

    private function compilarDatos(): array
    {
        return [
            'resumen'       => $this->service->actividadesResumen(),
            'lista'         => $this->service->actividadesLista(),
            'por_mes'       => $this->service->actividadesPorMes(),
            'estado'        => $this->service->actividadesEstado(),
            'por_adulto'    => $this->service->actividadesPorAdulto(),
        ];
    }

    public function preview()
    {
        return view('reportes.actividades.index', [
            'esPdf'    => false,
            'datos'    => $this->compilarDatos(),
            'graficas' => [
                'por_mes' => $this->service->actividadesPorMes(),
                'estado'  => $this->service->actividadesEstado(),
            ],
            'metadata' => [
                'titulo'       => 'Reporte de Actividades',
                'generado_en'  => now()->format('d/m/Y H:i'),
                'generado_por' => Auth::user()?->nombres . ' ' . Auth::user()?->ap_paterno,
                'seccion'      => 'Actividades',
            ],
        ]);
    }

    public function pdf()
    {
        $datos = $this->compilarDatos();

        $pdf = Pdf::loadView('reportes.actividades.index', [
            'esPdf'    => true,
            'datos'    => $datos,
            'graficas' => [
                'por_mes' => $this->service->actividadesPorMes(),
                'estado'  => $this->service->actividadesEstado(),
            ],
            'metadata' => [
                'titulo'       => 'Reporte de Actividades',
                'generado_en'  => now()->format('d/m/Y H:i'),
                'generado_por' => Auth::user()?->nombres . ' ' . Auth::user()?->ap_paterno,
                'seccion'      => 'Actividades',
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

        return $pdf->download('reporte-actividades-' . now()->format('Y-m-d') . '.pdf');
    }

    public function excel()
    {
        $nombre = 'reporte-actividades-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new ReporteActividadesExport($this->service), $nombre);
    }
}
