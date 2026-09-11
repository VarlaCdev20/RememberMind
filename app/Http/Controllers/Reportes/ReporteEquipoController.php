<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Services\Reportes\ReporteDataService;
use App\Exports\Reportes\ReporteEquipoExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReporteEquipoController extends Controller
{
    public function __construct(protected ReporteDataService $service) {}

    private function compilarDatos(): array
    {
        return [
            'resumen'          => $this->service->equipoResumen(),
            'personal_salud'   => $this->service->personalSaludLista(),
            'personal_admin'   => $this->service->personalAdminLista(),
            'voluntarios'      => $this->service->voluntariosLista(),
            'especialidades'   => $this->service->especialidadesDistribucion(),
            'areas_voluntarios' => $this->service->areasVoluntariosDistribucion(),
        ];
    }

    public function preview()
    {
        return view('pages.reportes.equipo.index', [
            'esPdf'    => false,
            'datos'    => $this->compilarDatos(),
            'graficas' => [
                'especialidades'    => $this->service->especialidadesDistribucion(),
                'areas_voluntarios' => $this->service->areasVoluntariosDistribucion(),
            ],
            'metadata' => [
                'titulo'       => 'Reporte del Equipo Institucional',
                'generado_en'  => now()->format('d/m/Y H:i'),
                'generado_por' => Auth::user()?->nombres . ' ' . Auth::user()?->ap_paterno,
                'seccion'      => 'Equipo Institucional',
            ],
        ]);
    }

    public function pdf()
    {
        $datos = $this->compilarDatos();

        $pdf = Pdf::loadView('pages.reportes.equipo.index', [
            'esPdf'    => true,
            'datos'    => $datos,
            'graficas' => [
                'especialidades'    => $this->service->especialidadesDistribucion(),
                'areas_voluntarios' => $this->service->areasVoluntariosDistribucion(),
            ],
            'metadata' => [
                'titulo'       => 'Reporte del Equipo Institucional',
                'generado_en'  => now()->format('d/m/Y H:i'),
                'generado_por' => Auth::user()?->nombres . ' ' . Auth::user()?->ap_paterno,
                'seccion'      => 'Equipo Institucional',
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

        return $pdf->download('reporte-equipo-' . now()->format('Y-m-d') . '.pdf');
    }

    public function excel()
    {
        $nombre = 'reporte-equipo-' . now()->format('Y-m-d') . '.xlsx';
        return Excel::download(new ReporteEquipoExport($this->service), $nombre);
    }
}
