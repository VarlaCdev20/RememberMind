<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Services\Reportes\ReporteDataService;
use App\Exports\Reportes\ReporteFamiliaresExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReporteFamiliaresController extends Controller
{
    public function __construct(protected ReporteDataService $service) {}

    private function compilarDatos(): array
    {
        return [
            'resumen'            => $this->service->familiaresResumen(),
            'vinculos'           => $this->service->vinculosLista(),
            'adultos_sin_fam'    => $this->service->adultosSinFamiliar(),
            'parentescos'        => $this->service->parentescosDistribucion(),
        ];
    }

    public function preview()
    {
        return view('pages.reportes.familiares.index', [
            'esPdf'    => false,
            'datos'    => $this->compilarDatos(),
            'graficas' => [
                'parentescos' => $this->service->parentescosDistribucion(),
            ],
            'metadata' => [
                'titulo'       => 'Reporte de Familiares y Red de Apoyo',
                'generado_en'  => now()->format('d/m/Y H:i'),
                'generado_por' => Auth::user()?->nombres . ' ' . Auth::user()?->ap_paterno,
                'seccion'      => 'Familiares',
            ],
        ]);
    }

    public function pdf()
    {
        $datos = $this->compilarDatos();

        $pdf = Pdf::loadView('pages.reportes.familiares.index', [
            'esPdf'    => true,
            'datos'    => $datos,
            'graficas' => [
                'parentescos' => $this->service->parentescosDistribucion(),
            ],
            'metadata' => [
                'titulo'       => 'Reporte de Familiares y Red de Apoyo',
                'generado_en'  => now()->format('d/m/Y H:i'),
                'generado_por' => Auth::user()?->nombres . ' ' . Auth::user()?->ap_paterno,
                'seccion'      => 'Familiares',
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

        return $pdf->download('reporte-familiares-' . now()->format('Y-m-d') . '.pdf');
    }

    public function excel()
    {
        $nombre = 'reporte-familiares-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new ReporteFamiliaresExport($this->service), $nombre);
    }
}
