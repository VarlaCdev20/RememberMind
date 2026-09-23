<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Services\Reportes\ReporteDataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReporteBitacoraController extends Controller
{
    public function __construct(protected ReporteDataService $service) {}

    private function compilarDatos(): array
    {
        return [
            'resumen'   => $this->service->bitacoraResumen(),
            'lista'     => $this->service->bitacoraLista(),
            'modulos'   => $this->service->bitacoraModulos(),
            'eventos'   => $this->service->bitacoraEventos(),
            'tendencia' => $this->service->bitacoraTendencia(),
        ];
    }

    public function preview()
    {
        return view('pages.reportes.bitacora.index', [
            'esPdf'    => false,
            'datos'    => $this->compilarDatos(),
            'graficas' => [
                'modulos'   => $this->service->bitacoraModulos(),
                'eventos'   => $this->service->bitacoraEventos(),
                'tendencia' => $this->service->bitacoraTendencia(),
            ],
            'metadata' => [
                'titulo'       => 'Reporte de Bitácora de Auditoría',
                'generado_en'  => now()->format('d/m/Y H:i'),
                'generado_por' => Auth::user()?->nombres . ' ' . Auth::user()?->ap_paterno,
                'seccion'      => 'Bitácora',
                'nota'         => 'La exportación a Excel de la bitácora no está disponible por política de seguridad.',
            ],
        ]);
    }

    public function pdf()
    {
        $datos = $this->compilarDatos();

        $pdf = Pdf::loadView('pages.reportes.bitacora.index', [
            'esPdf'    => true,
            'datos'    => $datos,
            'graficas' => [
                'modulos'   => $this->service->bitacoraModulos(),
                'eventos'   => $this->service->bitacoraEventos(),
                'tendencia' => $this->service->bitacoraTendencia(),
            ],
            'metadata' => [
                'titulo'       => 'Reporte de Bitácora de Auditoría',
                'generado_en'  => now()->format('d/m/Y H:i'),
                'generado_por' => Auth::user()?->nombres . ' ' . Auth::user()?->ap_paterno,
                'seccion'      => 'Bitácora',
                'nota'         => 'La exportación a Excel de la bitácora no está disponible por política de seguridad.',
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

        return $pdf->download('reporte-bitacora-' . now()->format('Y-m-d') . '.pdf');
    }
}
