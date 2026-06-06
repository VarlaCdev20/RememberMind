<?php

namespace App\Http\Controllers\Admin\Reportes;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use App\Exports\Reportes\ReporteInstitucionalExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReporteInstitucionalController extends Controller
{
    public function __construct(protected DashboardService $service) {}

    private function obtenerDatos(): array
    {
        $usuario = Auth::user();

        return [
            'generadoEn'  => now()->format('d/m/Y H:i'),
            'usuario'     => $this->service->obtenerSaludoUsuario($usuario),
            'kpis'        => $this->service->obtenerKpisInstitucionales(),
            'salud'       => $this->service->obtenerResumenSalud(),
            'redFamiliar' => $this->service->obtenerRedFamiliar(),
            'equipo'      => $this->service->obtenerEquipoInstitucional(),
            'alertas'     => $this->service->obtenerAlertasEstructuradas(),
            'bitacora'    => array_slice($this->service->obtenerBitacoraAuditoria(), 0, 5),
        ];
    }

    public function preview()
    {
        $datos = $this->obtenerDatos();
        $datos['esPdf'] = false;

        return view('reportes.institucional.general', $datos);
    }

    public function pdf()
    {
        $datos = $this->obtenerDatos();
        $datos['esPdf'] = true;

        $pdf = Pdf::loadView('reportes.institucional.general', $datos)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'         => 'DejaVu Sans',
                'isRemoteEnabled'     => false,
                'isHtml5ParserEnabled' => true,
                'chroot'              => public_path(),
                'dpi'                 => 96,
            ]);

        $nombre = 'reporte-institucional-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($nombre);
    }

    public function excel()
    {
        $datos  = $this->obtenerDatos();
        $nombre = 'reporte-institucional-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new ReporteInstitucionalExport($datos), $nombre);
    }
}
