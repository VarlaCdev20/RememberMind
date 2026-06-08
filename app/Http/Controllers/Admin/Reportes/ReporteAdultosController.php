<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use App\Services\Reportes\ReporteDataService;
use App\Exports\Reportes\ReporteAdultosExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReporteAdultosController extends Controller
{
    public function __construct(protected ReporteDataService $service) {}

    private function prepararPayload(bool $esPdf): array
    {
        $limite = $esPdf ? 50 : 300;

        return [
            'esPdf'    => $esPdf,
            'datos'    => [
                'resumen' => $this->service->adultosResumen(),
                'lista'   => $this->service->adultosListaCompleta($limite),
                'edad'    => $this->service->adultosEdad(),
            ],
            'graficas' => [
                'estado' => $this->service->adultosEstado(),
                'genero' => $this->service->adultosGenero(),
                'edad'   => $this->service->adultosEdad(),
            ],
            'metadata' => [
                'titulo'       => 'Reporte de Adultos Mayores',
                'generado_en'  => now()->format('d/m/Y H:i'),
                'generado_por' => trim((Auth::user()?->nombres ?? '') . ' ' . (Auth::user()?->ap_paterno ?? '')),
                'seccion'      => 'Adultos Mayores',
                'limite_pdf'   => 50,
            ],
        ];
    }

    public function preview()
    {
        return view('reportes.adultos.index', $this->prepararPayload(false));
    }

    public function pdf()
    {
        $payload = $this->prepararPayload(true);

        $pdf = Pdf::loadView('reportes.adultos.index', $payload)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isRemoteEnabled'      => false,
                'isHtml5ParserEnabled' => true,
                'chroot'               => public_path(),
                'dpi'                  => 96,
            ]);

        return $pdf->download('reporte-adultos-' . now()->format('Y-m-d') . '.pdf');
    }

    public function excel()
    {
        $nombre = 'reporte-adultos-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new ReporteAdultosExport($this->service), $nombre);
    }
}
