<?php

namespace App\Http\Controllers\Admin\AreasInstitucionales;

use App\Http\Controllers\Controller;
use App\Models\AreaInstitucional;
use App\Models\User;
use App\Services\Reportes\ReportExportService;
use App\Services\Reportes\ReportFileNameService;
use App\Services\Reportes\AreasReportDataService;
use App\Exports\AreasInstitucionalesExport;
use App\Exports\UsuariosPorAreaExport;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AreaReporteController extends Controller
{
    protected $reportDataService;
    protected $fileNameService;
    protected $exportService;

    public function __construct(
        AreasReportDataService $reportDataService,
        ReportFileNameService $fileNameService,
        ReportExportService $exportService
    ) {
        $this->reportDataService = $reportDataService;
        $this->fileNameService = $fileNameService;
        $this->exportService = $exportService;
    }

    /**
     * Sanitiza cadenas de texto para asegurar codificación UTF-8 limpia.
     */
    private function cleanUtf8($value)
    {
        if (is_array($value)) {
            return array_map(fn($item) => $this->cleanUtf8($item), $value);
        }

        if (is_string($value)) {
            // Limpia y convierte caracteres mal formados
            return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        return $value;
    }

    /**
     * Exporta el reporte general en PDF.
     */
    public function generalPdf()
    {
        try {
            $data = $this->reportDataService->getGeneralReportData();
            
            $mappedAreas = [];
            foreach ($data['areas'] as $area) {
                $mappedAreas[] = [
                    'nombre' => $this->cleanUtf8($area->nombre),
                    'tipo_area' => $this->cleanUtf8($area->tipo_area),
                    'responsable_nombre' => $area->responsable ? $this->cleanUtf8($area->responsable->name) : 'Sin asignar',
                    'usuarios_activos_count' => $area->usuarios->filter(fn($u) => in_array($u->estado, ['ACTIVO', 1, '1']))->count(),
                    'usuarios_inactivos_count' => $area->usuarios->filter(fn($u) => !in_array($u->estado, ['ACTIVO', 1, '1']))->count(),
                    'estado' => $this->cleanUtf8($area->estado),
                ];
            }

            $viewData = [
                'areas' => $mappedAreas,
                'totales' => [
                    'total_areas' => $data['totalAreas'],
                    'areas_activas' => $data['areasActivas'],
                    'areas_inactivas' => $data['areasInactivas'],
                    'personal_activo' => $data['usuariosVinculados'],
                    'sin_responsable' => $data['areasSinResponsable'],
                    'areas_sin_usuarios' => $data['areasSinUsuarios'],
                    'usuarios_activos_vinculados' => $data['usuariosActivosVinculados'],
                    'usuarios_inactivos_vinculados' => $data['usuariosInactivosVinculados'],
                    'area_mas_usuarios_nombre' => $this->cleanUtf8($data['areaMasUsuariosNombre']),
                    'area_mas_usuarios_count' => $data['areaMasUsuariosCount'],
                    'porcentaje_areas_activas' => $data['porcentajeAreasActivas'],
                    'porcentaje_areas_responsable' => $data['porcentajeAreasResponsable'],
                    'total_usuarios' => User::count(),
                ],
                'fecha' => $data['fecha'],
                'usuario' => $this->cleanUtf8($data['usuario']),
            ];

            // Sanitizar todo el arreglo para mayor seguridad
            $viewData = $this->cleanUtf8($viewData);

            $filename = $this->fileNameService->generate('areas_institucionales', 'pdf');

            // Log seguro en UTF-8
            activity('AreasInstitucionales')
                ->causedBy(Auth::user())
                ->withProperties([
                    'formato' => 'PDF',
                    'tipo_reporte' => 'general'
                ])
                ->log("Se exportó el reporte general de áreas institucionales en formato PDF.");

            return $this->exportService->exportPdf('pdf.exports.areas.general', $viewData, $filename);

        } catch (\Throwable $e) {
            Log::error("Error exportar PDF general: " . $e->getMessage());
            return back()->with('error', 'No se pudo generar el reporte general en PDF. Verifique los registros del sistema.');
        }
    }

    /**
     * Exporta el reporte de una área específica en PDF.
     */
    public function areaPdf($codArea)
    {
        try {
            $area = AreaInstitucional::with(['responsable', 'usuarios'])->findOrFail($codArea);
            $data = $this->reportDataService->getAreaReportData($codArea);
            
            // Sanitizar datos para UTF-8 limpio
            $data = $this->cleanUtf8($data);

            $nombreSeguro = Str::slug($area->nombre, '_');
            $filename = $this->fileNameService->generate("area_{$nombreSeguro}", 'pdf');

            // Log seguro en UTF-8
            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy(Auth::user())
                ->withProperties([
                    'area_nombre' => $this->cleanUtf8($area->nombre),
                    'formato' => 'PDF',
                    'tipo_reporte' => 'especifico'
                ])
                ->log("Se exportó el reporte del área {$area->nombre} en formato PDF.");

            return $this->exportService->exportPdf('pdf.exports.areas.area', $data, $filename);

        } catch (\Throwable $e) {
            Log::error("Error exportar PDF área ($codArea): " . $e->getMessage());
            return back()->with('error', 'No se pudo generar el reporte del área en PDF. Verifique los registros del sistema.');
        }
    }

    /**
     * Exporta el reporte general en Excel.
     */
    public function generalExcel()
    {
        try {
            $filename = $this->fileNameService->generate('areas_institucionales', 'xlsx');

            activity('AreasInstitucionales')
                ->causedBy(Auth::user())
                ->withProperties([
                    'formato' => 'Excel',
                    'tipo_reporte' => 'general'
                ])
                ->log("Se exportó el reporte general de áreas institucionales en formato Excel.");

            return $this->exportService->exportExcel(new AreasInstitucionalesExport, $filename);

        } catch (\Throwable $e) {
            Log::error("Error exportar Excel general: " . $e->getMessage());
            return back()->with('error', 'No se pudo generar el reporte general en Excel.');
        }
    }

    /**
     * Exporta el reporte general en CSV.
     */
    public function generalCsv()
    {
        try {
            $filename = $this->fileNameService->generate('areas_institucionales', 'csv');

            activity('AreasInstitucionales')
                ->causedBy(Auth::user())
                ->withProperties([
                    'formato' => 'CSV',
                    'tipo_reporte' => 'general'
                ])
                ->log("Se exportó el reporte general de áreas institucionales en formato CSV.");

            return $this->exportService->exportCsv(new AreasInstitucionalesExport, $filename);

        } catch (\Throwable $e) {
            Log::error("Error exportar CSV general: " . $e->getMessage());
            return back()->with('error', 'No se pudo generar el reporte general en CSV.');
        }
    }

    /**
     * Exporta los usuarios de una área específica en Excel.
     */
    public function areaExcel($codArea)
    {
        try {
            $area = AreaInstitucional::findOrFail($codArea);
            $nombreSeguro = Str::slug($area->nombre, '_');
            $filename = $this->fileNameService->generate("usuarios_area_{$nombreSeguro}", 'xlsx');

            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy(Auth::user())
                ->withProperties([
                    'area_nombre' => $this->cleanUtf8($area->nombre),
                    'formato' => 'Excel',
                    'tipo_reporte' => 'especifico'
                ])
                ->log("Se exportó el reporte de usuarios del área {$area->nombre} en formato Excel.");

            return $this->exportService->exportExcel(new UsuariosPorAreaExport($codArea), $filename);

        } catch (\Throwable $e) {
            Log::error("Error exportar Excel de área ($codArea): " . $e->getMessage());
            return back()->with('error', 'No se pudo generar el reporte de personal en Excel.');
        }
    }
}


