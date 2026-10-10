<?php

namespace App\Frontend\Livewire\Superadministrador\Identidad;

use Livewire\Component;
use App\Models\Area;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use App\Backend\Modulos\Reportes\Servicios\ReportExportService;
use App\Backend\Modulos\Reportes\Servicios\ReportFileNameService;
use App\Exports\AreasInstitucionalesExport;

class AreasInstitucionalesPanel extends Component
{
    // Filtros de búsqueda
    public $search = '';
    public $filtroEstado = '';
    public $filtroResponsable = '';

    // Control de modales y paneles
    public $mostrarFormulario = false;
    public $mostrarFicha = false;
    public $mostrarReportes = false;
    public $isEdit = false;

    // Reportes interactivos
    public $reporteTipo = null;
    public $reporteData = [];

    // Propiedades del formulario
    public $areaId; // Corresponde al cod_area
    public $nombre;
    public $descripcion;
    public $estado = 'ACTIVO';

    // Ficha de detalle de área
    public $areaSeleccionada = null;

    public function mount()
    {
        // No se requiere precargar roles ya que no se editan aquí
    }

    public function render()
    {
        $reporte = app(\App\Backend\Modulos\Reportes\Servicios\AreasReportDataService::class)->getGeneralReportData();
        $areas = $reporte['areas']
            ->when($this->search, fn ($items) => $items->filter(fn ($area) =>
                str_contains(mb_strtolower($area->nombre.' '.$area->cod_area), mb_strtolower($this->search))))
            ->when($this->filtroEstado, fn ($items) => $items->whereIn(
                'estado', $this->filtroEstado === 'ACTIVO' ? ['ACTIVO', 'ACTIVA'] : ['INACTIVO', 'INACTIVA']
            ))
            ->when($this->filtroResponsable, fn ($items) => $items->filter(fn ($area) =>
                $area->responsable?->cod_usuario === $this->filtroResponsable))
            ->values();

        // Todos los usuarios activos para filtro en cabecera
        $todosResponsables = User::where('estado', 'ACTIVO')->get();

        // Calcular métricas organizacionales reales
        $totalAreas = $reporte['totalAreas'];
        $areasActivas = $reporte['areasActivas'];
        $areasInactivas = $reporte['areasInactivas'];
        $usuariosVinculados = $reporte['usuariosVinculados'];
        $areasSinResponsable = $reporte['areasSinResponsable'];
        $areasSinUsuarios = $reporte['areasSinUsuarios'];

        return view('livewire.identidad.areas-institucionales-panel', [
            'areas' => $areas,
            'todosResponsables' => $todosResponsables,
            'totalAreas' => $totalAreas,
            'areasActivas' => $areasActivas,
            'areasInactivas' => $areasInactivas,
            'usuariosVinculados' => $usuariosVinculados,
            'areasSinResponsable' => $areasSinResponsable,
            'areasSinUsuarios' => $areasSinUsuarios,
        ]);
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->filtroEstado = '';
        $this->filtroResponsable = '';
    }

    private function usuarioAutorizado(string $permiso): bool
    {
        $usuario = Auth::user();

        return $usuario !== null
            && strtoupper((string) $usuario->estado) === 'ACTIVO'
            && $usuario->can($permiso);
    }

    // ── GESTIÓN DE ACCESO A FORMULARIO (CRUD) ──

    public function crearArea()
    {
        // Validar Spatie Permission
        if (!$this->usuarioAutorizado('areas.crear')) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para registrar nuevas áreas institucionales.',
            ]);
            return;
        }

        $this->resetErrorBag();
        $this->resetForm();
        $this->isEdit = false;
        $this->mostrarFormulario = true;
        $this->mostrarFicha = false;
    }

    public function editarArea($codArea)
    {
        // Validar Spatie Permission
        if (!$this->usuarioAutorizado('areas.editar')) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para editar áreas institucionales.',
            ]);
            return;
        }

        $this->resetErrorBag();
        $area = app(\App\Backend\Modulos\Reportes\Servicios\AreasReportDataService::class)->getAreaReportData($codArea)['area'];

        $this->areaId = $area->cod_area;
        $this->nombre = $area->nombre;
        $this->descripcion = $area->descripcion;
        $this->estado = in_array($area->estado, ['ACTIVO', 'ACTIVA'], true) ? 'ACTIVO' : 'INACTIVO';

        $this->isEdit = true;
        $this->mostrarFormulario = true;
        $this->mostrarFicha = false;
    }

    public function guardarArea()
    {
        $permisoRequerido = $this->isEdit ? 'areas.editar' : 'areas.crear';
        if (!$this->usuarioAutorizado($permisoRequerido)) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permisos para guardar estos cambios.',
            ]);
            return;
        }

        $rules = [
            'nombre' => 'required|string|max:80|unique:areas,nombre,' . ($this->areaId ?? 'NULL') . ',cod_area',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:ACTIVO,INACTIVO',
        ];

        $this->validate($rules);

        $datos = [
            'nombre' => mb_strtoupper(trim($this->nombre), 'UTF-8'),
            'descripcion' => $this->descripcion ?: null,
            'estado' => $this->estado,
        ];

        if ($this->isEdit) {
            $area = Area::findOrFail($this->areaId);
            $area->update($datos);

            // Log de actividad
            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy(Auth::user())
                ->log("El usuario editó el área institucional {$area->nombre}.");

            $mensaje = 'Área institucional actualizada correctamente.';
        } else {
            $area = Area::create($datos + [
                'cod_area' => 'ARE_'.Str::upper(Str::random(12)),
            ]);

            // Log de actividad
            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy(Auth::user())
                ->log("El usuario creó la área institucional {$area->nombre}.");

            $mensaje = 'Área institucional registrada correctamente.';
        }

        $this->mostrarFormulario = false;
        $this->resetForm();

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Operación exitosa',
            'text' => $mensaje,
        ]);
    }

    public function toggleEstado($codArea)
    {
        // Validar Spatie Permission
        if (!$this->usuarioAutorizado('areas.cambiar_estado')) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para cambiar el estado de las áreas institucionales.',
            ]);
            return;
        }

        $area = Area::findOrFail($codArea);
        $nuevoEstado = in_array($area->estado, ['ACTIVO', 'ACTIVA'], true) ? 'INACTIVO' : 'ACTIVO';
        $area->estado = $nuevoEstado;
        $area->save();

        // Registrar acción en log
        activity('AreasInstitucionales')
            ->performedOn($area)
            ->causedBy(Auth::user())
            ->log("Se cambió el estado del área {$area->nombre} a {$nuevoEstado}.");

        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Estado modificado',
            'text' => "El área '{$area->nombre}' ahora está {$nuevoEstado}.",
        ]);

        if ($this->areaSeleccionada && $this->areaSeleccionada->cod_area === $codArea) {
            $this->areaSeleccionada = app(\App\Backend\Modulos\Reportes\Servicios\AreasReportDataService::class)
                ->getAreaReportData($codArea)['area'];
        }
    }

    // ── VISTA DE DETALLE E HISTÓRICOS (FICHA) ──

    public function verArea($codArea)
    {
        $this->areaSeleccionada = app(\App\Backend\Modulos\Reportes\Servicios\AreasReportDataService::class)
            ->getAreaReportData($codArea)['area'];

        $this->mostrarFicha = true;
        $this->mostrarFormulario = false;
    }

    public function cerrarFicha()
    {
        $this->mostrarFicha = false;
        $this->areaSeleccionada = null;
    }

    public function cerrarFormulario()
    {
        $this->mostrarFormulario = false;
        $this->resetForm();
    }

    // ── MÓDULO DE REPORTES INTERACTIVOS ──

    public function abrirReportes()
    {
        if (!$this->usuarioAutorizado('areas.reportes')) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            return;
        }

        $this->mostrarReportes = true;
        $this->reporteTipo = 'general';
        $this->reporteData = $this->obtenerDatosReporteGeneral();
    }

    public function cerrarReportes()
    {
        $this->mostrarReportes = false;
        $this->reporteTipo = null;
        $this->reporteData = [];
    }

    public function abrirReporteArea($codArea)
    {
        if (!$this->usuarioAutorizado('areas.reportes')) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            return;
        }

        $this->mostrarReportes = true;
        $this->reporteTipo = 'especifico';
        $this->reporteData = $this->obtenerDatosReporteArea($codArea);
        
        activity('AreasInstitucionales')
            ->causedBy(Auth::user())
            ->log("Se abrió el reporte específico del área: {$this->reporteData['area']['nombre']}.");
    }

    public function cerrarReporteArea()
    {
        $this->reporteTipo = null;
        $this->reporteData = [];
    }

    public function generarReporteGeneral()
    {
        if (!$this->usuarioAutorizado('areas.reportes')) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            return;
        }

        $this->reporteTipo = 'general';
        $this->reporteData = $this->obtenerDatosReporteGeneral();

        activity('AreasInstitucionales')
            ->causedBy(Auth::user())
            ->log('Se generó el reporte general de áreas institucionales.');
    }

    public function generarReporteArea($codArea)
    {
        $this->abrirReporteArea($codArea);
    }

    public function imprimirReporteGeneral()
    {
        $this->dispatch('print-window');
    }

    public function exportarReporteGeneralPdf()
    {
        if (!$this->usuarioAutorizado('areas.reportes')) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            return;
        }

        try {
            $data = $this->obtenerDatosReporteGeneral();
            
            $mappedAreas = [];
            foreach ($data['areas'] as $area) {
                $mappedAreas[] = [
                    'nombre' => $area->nombre,
                    'responsable_nombre' => $area->responsable ? $area->responsable->name : 'Sin asignar',
                    'usuarios_activos_count' => $area->usuarios->filter(fn($u) => in_array($u->estado, ['ACTIVO', 1, '1']))->count(),
                    'usuarios_inactivos_count' => $area->usuarios->filter(fn($u) => !in_array($u->estado, ['ACTIVO', 1, '1']))->count(),
                    'estado' => $area->estado,
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
                    'area_mas_usuarios_nombre' => $data['areaMasUsuariosNombre'],
                    'area_mas_usuarios_count' => $data['areaMasUsuariosCount'],
                    'porcentaje_areas_activas' => $data['porcentajeAreasActivas'],
                    'porcentaje_areas_responsable' => $data['porcentajeAreasResponsable'],
                    'total_usuarios' => User::count(),
                ],
                'fecha' => $data['fecha'],
                'usuario' => $data['usuario'],
            ];
            
            $fileNameService = app(ReportFileNameService::class);
            $filename = $fileNameService->generate('areas_institucionales', 'pdf');

            $exportService = app(ReportExportService::class);

            activity('AreasInstitucionales')
                ->causedBy(Auth::user())
                ->log('Se exportó el reporte general de áreas en formato PDF con la nueva arquitectura.');

            return $exportService->exportPdf('pdf.exports.areas.general', $viewData, $filename);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error exportar PDF general: " . $e->getMessage());
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se pudo generar el PDF',
                'text' => 'Verifica que Chrome/Chromium esté instalado para Puppeteer.',
            ]);
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'No se pudo generar el PDF',
                'text' => 'Verifica que Chrome/Chromium esté instalado para Puppeteer.',
            ]);
        }
    }

    public function imprimirReporteArea($codArea)
    {
        $this->dispatch('print-window');
    }

    public function exportarReporteAreaPdf($codArea)
    {
        if (!$this->usuarioAutorizado('areas.reportes')) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            return;
        }

        try {
            $data = $this->obtenerDatosReporteArea($codArea);
            $area = $data['area'];
            
            $fileNameService = app(ReportFileNameService::class);
            $filename = $fileNameService->generate("area_{$area->nombre}", 'pdf');

            $exportService = app(ReportExportService::class);

            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy(Auth::user())
                ->log("Se exportó el reporte del área '{$area->nombre}' en formato PDF.");

            return $exportService->exportPdf('pdf.exports.areas.area', $data, $filename);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error exportar PDF área: " . $e->getMessage());
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se pudo generar el PDF',
                'text' => 'Verifica que Chrome/Chromium esté instalado para Puppeteer.',
            ]);
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'No se pudo generar el PDF',
                'text' => 'Verifica que Chrome/Chromium esté instalado para Puppeteer.',
            ]);
        }
    }

    public function exportarReporteGeneralExcel()
    {
        if (!$this->usuarioAutorizado('areas.reportes')) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            return;
        }

        try {
            $fileNameService = app(ReportFileNameService::class);
            $filename = $fileNameService->generate('areas_institucionales', 'xlsx');

            $exportService = app(ReportExportService::class);

            $respuesta = $exportService->exportExcel(new AreasInstitucionalesExport, $filename);

            activity('AreasInstitucionales')
                ->causedBy(Auth::user())
                ->log('Se exportó el reporte general de áreas en formato Excel.');

            return $respuesta;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Error de Exportación',
                'text' => 'No se pudo generar el reporte en Excel. Vuelve a intentarlo.',
            ]);
        }
    }

    public function exportarAreasExcel()
    {
        return $this->exportarReporteGeneralExcel();
    }

    public function exportarUsuariosAreaExcel($codArea)
    {
        if (!$this->usuarioAutorizado('areas.reportes')) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            return;
        }

        try {
            $area = Area::findOrFail($codArea);
            $fileNameService = app(ReportFileNameService::class);
            $filename = $fileNameService->generate("usuarios_area_{$area->nombre}", 'xlsx');

            $exportService = app(ReportExportService::class);

            $respuesta = $exportService->exportExcel(new \App\Exports\UsuariosPorAreaExport($codArea), $filename);

            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy(Auth::user())
                ->log("Se exportó el reporte de usuarios del área '{$area->nombre}' en formato Excel.");

            return $respuesta;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Área no disponible',
                'text' => 'El área seleccionada ya no está disponible. Actualiza el listado.',
            ]);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Error de Exportación',
                'text' => 'No se pudo generar el reporte en Excel de los usuarios. Vuelve a intentarlo.',
            ]);
        }
    }

    public function exportarReporteGeneralCsv()
    {
        if (!$this->usuarioAutorizado('areas.reportes')) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para generar reportes de áreas institucionales.',
            ]);
            return;
        }

        try {
            $fileNameService = app(ReportFileNameService::class);
            $filename = $fileNameService->generate('areas_institucionales', 'csv');

            $exportService = app(ReportExportService::class);

            $respuesta = $exportService->exportCsv(new AreasInstitucionalesExport, $filename);

            activity('AreasInstitucionales')
                ->causedBy(Auth::user())
                ->log('Se exportó el reporte general de áreas en formato CSV.');

            return $respuesta;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Error de Exportación',
                'text' => 'No se pudo generar el reporte en CSV. Vuelve a intentarlo.',
            ]);
        }
    }

    public function obtenerDatosReporteGeneral()
    {
        return app(\App\Backend\Modulos\Reportes\Servicios\AreasReportDataService::class)->getGeneralReportData();
    }

    public function obtenerDatosReporteArea($codArea)
    {
        return app(\App\Backend\Modulos\Reportes\Servicios\AreasReportDataService::class)->getAreaReportData($codArea);
    }

    public function obtenerDatosGraficoUsuariosPorArea()
    {
        return app(\App\Backend\Modulos\Reportes\Servicios\AreasReportDataService::class)->getUsuariosPorArea();
    }

    public function obtenerDatosGraficoAreasPorEstado()
    {
        return app(\App\Backend\Modulos\Reportes\Servicios\AreasReportDataService::class)->getAreasPorEstado();
    }

    public function obtenerDatosGraficoActivosInactivosPorArea()
    {
        return app(\App\Backend\Modulos\Reportes\Servicios\AreasReportDataService::class)->getActivosInactivosPorArea();
    }

    public function obtenerDatosGraficoEvolucionMensual()
    {
        return app(\App\Backend\Modulos\Reportes\Servicios\AreasReportDataService::class)->getEvolucionMensualGlobal();
    }

    public function obtenerDatosGraficoActivosInactivosArea($codArea)
    {
        return app(\App\Backend\Modulos\Reportes\Servicios\AreasReportDataService::class)->getActivosInactivosArea($codArea);
    }

    public function obtenerDatosGraficoUsuariosPorRolArea($codArea)
    {
        return app(\App\Backend\Modulos\Reportes\Servicios\AreasReportDataService::class)->getUsuariosPorRolArea($codArea);
    }

    public function obtenerDatosGraficoEvolucionArea($codArea)
    {
        return app(\App\Backend\Modulos\Reportes\Servicios\AreasReportDataService::class)->getEvolucionArea($codArea);
    }

    public function obtenerDatosGraficoRankingAreas()
    {
        return app(\App\Backend\Modulos\Reportes\Servicios\AreasReportDataService::class)->getRankingAreasUsuarios();
    }

    // ── AUXILIARES Y FORMATO DE UI ──

    protected function resetForm()
    {
        $this->areaId = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->estado = 'ACTIVO';
    }
}
