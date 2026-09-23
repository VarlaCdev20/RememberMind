<?php

namespace App\Livewire\Identidad;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\AreaInstitucional;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\Reportes\ReportExportService;
use App\Services\Reportes\ReportFileNameService;
use App\Exports\AreasInstitucionalesExport;

class AreasInstitucionalesPanel extends Component
{
    use WithFileUploads;

    // Filtros de búsqueda
    public $search = '';
    public $filtroEstado = '';
    public $filtroTipo = '';
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
    public $tipo_area = 'Administrativa';
    public $descripcion;
    public $responsable_id;
    public $color = '#2F3E5C';
    public $estado = 'ACTIVA';
    public $orden = 0;
    public $observaciones;
    public $nuevaImagen; // Para la carga de portadas

    // Conservar en BD pero ocultar en UI
    public $roles_sugeridos = [];
    public $modulos_relacionados = [];
    public $icono = 'ph-buildings';

    // Ficha de detalle de área
    public $areaSeleccionada = null;

    public function mount()
    {
        // No se requiere precargar roles ya que no se editan aquí
    }

    public function render()
    {
        $reporte = app(\App\Services\Reportes\AreasReportDataService::class)->getGeneralReportData();
        $areas = $reporte['areas']
            ->when($this->search, fn ($items) => $items->filter(fn ($area) =>
                str_contains(mb_strtolower($area->nombre.' '.$area->cod_area), mb_strtolower($this->search))))
            ->when($this->filtroEstado, fn ($items) => $items->where('estado', $this->filtroEstado))
            ->when($this->filtroResponsable, fn ($items) => $items->filter(fn ($area) =>
                $area->responsable?->cod_usuario === $this->filtroResponsable))
            ->values();

        // Si estamos en edición, cargamos solo los usuarios de esta área
        $responsablesDisponibles = collect();
        if ($this->isEdit && $this->areaId) {
            $responsablesDisponibles = app(\App\Services\Reportes\AreasReportDataService::class)
                ->getAreaReportData($this->areaId)['area']->usuarios;
        }

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
            'responsablesDisponibles' => $responsablesDisponibles,
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
        $this->filtroTipo = '';
        $this->filtroResponsable = '';
    }

    // ── GESTIÓN DE ACCESO A FORMULARIO (CRUD) ──

    public function crearArea()
    {
        // Validar Spatie Permission
        if (!Auth::user()->can('areas.crear')) {
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
        if (!Auth::user()->can('areas.editar')) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para editar áreas institucionales.',
            ]);
            return;
        }

        $this->resetErrorBag();
        $area = app(\App\Services\Reportes\AreasReportDataService::class)->getAreaReportData($codArea)['area'];

        $this->areaId = $area->cod_area;
        $this->nombre = $area->nombre;
        $this->tipo_area = $area->tipo_area;
        $this->descripcion = $area->descripcion;
        $this->responsable_id = $area->responsable_id;
        $this->color = $area->color ?? '#2F3E5C';
        $this->estado = $area->estado;
        $this->orden = $area->orden;
        $this->observaciones = $area->observaciones;

        // Conservar campos ocultos de BD
        $this->roles_sugeridos = $area->roles_sugeridos ?? [];
        $this->modulos_relacionados = $area->modulos_relacionados ?? [];
        $this->icono = $area->icono ?? 'ph-buildings';

        $this->isEdit = true;
        $this->mostrarFormulario = true;
        $this->mostrarFicha = false;
        $this->nuevaImagen = null;
    }

    public function guardarArea()
    {
        $permisoRequerido = $this->isEdit ? 'areas.editar' : 'areas.crear';
        if (!Auth::user()->can($permisoRequerido)) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permisos para guardar estos cambios.',
            ]);
            return;
        }

        $rules = [
            'nombre' => 'required|string|max:80|unique:areas,nombre,' . ($this->areaId ?? 'NULL') . ',cod_area',
            'tipo_area' => 'required|string|max:50',
            'descripcion' => 'nullable|string',
            'responsable_id' => 'nullable|exists:usuarios,cod_usuario',
            'color' => 'required|string|max:20',
            'estado' => 'required|in:ACTIVA,INACTIVA',
            'orden' => 'required|integer|min:0',
            'observaciones' => 'nullable|string',
            'nuevaImagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ];

        $this->validate($rules);

        if ($this->nuevaImagen) {
            $this->addError('nuevaImagen', 'La BDD V2 congelada no define una columna de imagen para áreas.');
            return;
        }

        $datos = [
            'nombre' => mb_strtoupper(trim($this->nombre), 'UTF-8'),
            'descripcion' => $this->descripcion ?: null,
            'estado' => $this->estado === 'ACTIVA' ? 'ACTIVO' : 'INACTIVO',
        ];

        if ($this->isEdit) {
            $area = AreaInstitucional::findOrFail($this->areaId);
            $area->update($datos);

            // Log de actividad
            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy(Auth::user())
                ->log("El usuario editó el área institucional {$area->nombre}.");

            $mensaje = 'Área institucional actualizada correctamente.';
        } else {
            $area = AreaInstitucional::create($datos + [
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
        if (!Auth::user()->can('areas.cambiar_estado')) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para cambiar el estado de las áreas institucionales.',
            ]);
            return;
        }

        $area = AreaInstitucional::findOrFail($codArea);
        $nuevoEstado = $area->estado === 'ACTIVO' ? 'INACTIVO' : 'ACTIVO';
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
            $this->areaSeleccionada = app(\App\Services\Reportes\AreasReportDataService::class)
                ->getAreaReportData($codArea)['area'];
        }
    }

    // ── VISTA DE DETALLE E HISTÓRICOS (FICHA) ──

    public function verArea($codArea)
    {
        $this->areaSeleccionada = app(\App\Services\Reportes\AreasReportDataService::class)
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
        if (!Auth::user()->can('areas.reportes')) {
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
        if (!Auth::user()->can('areas.reportes')) {
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
        if (!Auth::user()->can('areas.reportes')) {
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
        if (!Auth::user()->can('areas.reportes')) {
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
                    'tipo_area' => $area->tipo_area,
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
        if (!Auth::user()->can('areas.reportes')) {
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
        if (!Auth::user()->can('areas.reportes')) {
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

            activity('AreasInstitucionales')
                ->causedBy(Auth::user())
                ->log('Se exportó el reporte general de áreas en formato Excel.');

            return $exportService->exportExcel(new AreasInstitucionalesExport, $filename);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error exportar Excel general: " . $e->getMessage());
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Error de Exportación',
                'text' => 'No se pudo generar el reporte en Excel: ' . $e->getMessage(),
            ]);
        }
    }

    public function exportarAreasExcel()
    {
        return $this->exportarReporteGeneralExcel();
    }

    public function exportarUsuariosAreaExcel($codArea)
    {
        if (!Auth::user()->can('areas.reportes')) {
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
            $area = AreaInstitucional::findOrFail($codArea);
            $fileNameService = app(ReportFileNameService::class);
            $filename = $fileNameService->generate("usuarios_area_{$area->nombre}", 'xlsx');

            $exportService = app(ReportExportService::class);

            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy(Auth::user())
                ->log("Se exportó el reporte de usuarios del área '{$area->nombre}' en formato Excel.");

            return $exportService->exportExcel(new \App\Exports\UsuariosPorAreaExport($codArea), $filename);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error exportar Excel de usuarios del área: " . $e->getMessage());
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Error de Exportación',
                'text' => 'No se pudo generar el reporte en Excel de los usuarios: ' . $e->getMessage(),
            ]);
        }
    }

    public function exportarReporteGeneralCsv()
    {
        if (!Auth::user()->can('areas.reportes')) {
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

            activity('AreasInstitucionales')
                ->causedBy(Auth::user())
                ->log('Se exportó el reporte general de áreas en formato CSV.');

            return $exportService->exportCsv(new AreasInstitucionalesExport, $filename);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error exportar CSV general: " . $e->getMessage());
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Error de Exportación',
                'text' => 'No se pudo generar el reporte en CSV: ' . $e->getMessage(),
            ]);
        }
    }

    public function obtenerDatosReporteGeneral()
    {
        return app(\App\Services\Reportes\AreasReportDataService::class)->getGeneralReportData();
    }

    public function obtenerDatosReporteArea($codArea)
    {
        return app(\App\Services\Reportes\AreasReportDataService::class)->getAreaReportData($codArea);
    }

    public function obtenerDatosGraficoUsuariosPorArea()
    {
        return app(\App\Services\Reportes\AreasReportDataService::class)->getUsuariosPorArea();
    }

    public function obtenerDatosGraficoAreasPorTipo()
    {
        return app(\App\Services\Reportes\AreasReportDataService::class)->getAreasPorTipo();
    }

    public function obtenerDatosGraficoActivosInactivosPorArea()
    {
        return app(\App\Services\Reportes\AreasReportDataService::class)->getActivosInactivosPorArea();
    }

    public function obtenerDatosGraficoEvolucionMensual()
    {
        return app(\App\Services\Reportes\AreasReportDataService::class)->getEvolucionMensualGlobal();
    }

    public function obtenerDatosGraficoActivosInactivosArea($codArea)
    {
        return app(\App\Services\Reportes\AreasReportDataService::class)->getActivosInactivosArea($codArea);
    }

    public function obtenerDatosGraficoUsuariosPorRolArea($codArea)
    {
        return app(\App\Services\Reportes\AreasReportDataService::class)->getUsuariosPorRolArea($codArea);
    }

    public function obtenerDatosGraficoEvolucionArea($codArea)
    {
        return app(\App\Services\Reportes\AreasReportDataService::class)->getEvolucionArea($codArea);
    }

    public function obtenerDatosGraficoRankingAreas()
    {
        return app(\App\Services\Reportes\AreasReportDataService::class)->getRankingAreasUsuarios();
    }

    // ── AUXILIARES Y FORMATO DE UI ──

    public function obtenerColorTipo($tipo)
    {
        return match ($tipo) {
            'Administrativa' => 'bg-[#2F3E5C]/10 text-[#2F3E5C] border-[#2F3E5C]/20',
            'Salud' => 'bg-[#63775B]/10 text-[#63775B] border-[#63775B]/20',
            'Social' => 'bg-[#967B66]/10 text-[#967B66] border-[#967B66]/20',
            'Soporte' => 'bg-[#5E6599]/10 text-[#5E6599] border-[#5E6599]/20',
            default => 'bg-[#9B8B7E]/10 text-[#9B8B7E] border-[#9B8B7E]/20',
        };
    }

    public function obtenerIconoTipo($tipo)
    {
        return match ($tipo) {
            'Administrativa' => 'ph-buildings',
            'Salud' => 'ph-stethoscope',
            'Social' => 'ph-hand-heart',
            'Soporte' => 'ph-shield-check',
            default => 'ph-folder',
        };
    }

    protected function resetForm()
    {
        $this->areaId = null;
        $this->nombre = '';
        $this->tipo_area = 'Administrativa';
        $this->descripcion = '';
        $this->responsable_id = null;
        $this->color = '#2F3E5C';
        $this->estado = 'ACTIVA';
        $this->orden = 0;
        $this->observaciones = '';
        $this->nuevaImagen = null;
        
        $this->roles_sugeridos = [];
        $this->modulos_relacionados = [];
        $this->icono = 'ph-buildings';
    }
}
