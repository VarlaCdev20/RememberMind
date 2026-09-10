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
        // Consultar áreas institucionales con sus responsables y conteo de usuarios
        $query = AreaInstitucional::with(['responsable', 'usuarios'])
            ->withCount('usuarios');

        // Búsqueda reactiva por nombre, código o tipo
        if ($this->search) {
            $query->where(function($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('cod_area', 'like', '%' . $this->search . '%')
                  ->orWhere('tipo_area', 'like', '%' . $this->search . '%');
            });
        }

        // Filtro por Estado
        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }

        // Filtro por Tipo de área
        if ($this->filtroTipo) {
            $query->where('tipo_area', $this->filtroTipo);
        }

        // Filtro por Responsable
        if ($this->filtroResponsable) {
            $query->where('responsable_id', $this->filtroResponsable);
        }

        $areas = $query->orderBy('orden')->orderBy('nombre')->get();

        // Si estamos en edición, cargamos solo los usuarios de esta área
        $responsablesDisponibles = [];
        if ($this->isEdit && $this->areaId) {
            $responsablesDisponibles = User::where('cod_area', $this->areaId)
                ->where(function($q) {
                    $q->where('estado', 'ACTIVO')->orWhere('estado', '1')->orWhere('estado', 1);
                })
                ->get();
        }

        // Todos los usuarios activos para filtro en cabecera
        $todosResponsables = User::where(function($q) {
                $q->where('estado', 'ACTIVO')->orWhere('estado', '1')->orWhere('estado', 1);
            })
            ->whereHas('areaInstitucional')
            ->get();

        // Calcular métricas organizacionales reales
        $totalAreas = AreaInstitucional::count();
        $areasActivas = AreaInstitucional::activas()->count();
        $areasInactivas = AreaInstitucional::inactivas()->count();
        $usuariosVinculados = User::whereNotNull('cod_area')->count();
        $areasSinResponsable = AreaInstitucional::whereNull('responsable_id')->count();
        $areasSinUsuarios = AreaInstitucional::doesntHave('usuarios')->count();

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
        $area = AreaInstitucional::findOrFail($codArea);

        // Validar si el responsable actual ya no pertenece a la misma o está inactivo
        $responsableActualValido = false;
        if ($area->responsable_id) {
            $responsableActualValido = User::where('cod_usu', $area->responsable_id)
                ->where('cod_area', $area->cod_area)
                ->where('estado', 'ACTIVO')
                ->exists();
        }

        if ($area->responsable_id && !$responsableActualValido) {
            // Limpiar responsable_id
            $area->responsable_id = null;
            $area->save();

            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy(Auth::user())
                ->log("Se retiró al responsable del área '{$area->nombre}' porque ya no pertenece a la misma o está inactivo.");

            $this->dispatch('swal:modal', [
                'type' => 'warning',
                'title' => 'Responsable desvinculado',
                'text' => 'El responsable anterior ya no pertenece a esta área o está inactivo y fue retirado.',
            ]);
        }

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
        // Validar permisos
        $permisoRequerido = $this->isEdit ? 'areas.editar' : 'areas.crear';
        if (!Auth::user()->can($permisoRequerido)) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permisos para guardar estos cambios.',
            ]);
            return;
        }

        // Reglas de validación
        $rules = [
            'nombre' => 'required|string|max:100|unique:areas_institucionales,nombre,' . ($this->areaId ?? 'NULL') . ',cod_area',
            'tipo_area' => 'required|string|max:50',
            'descripcion' => 'required|string',
            'responsable_id' => 'nullable|exists:users,cod_usu',
            'color' => 'required|string|max:20',
            'estado' => 'required|in:ACTIVA,INACTIVA',
            'orden' => 'required|integer|min:0',
            'observaciones' => 'nullable|string',
            'nuevaImagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ];

        $validated = $this->validate($rules);
        unset($validated['nuevaImagen']);

        $validated['slug'] = Str::slug($this->nombre);
        $validated['icono'] = $this->obtenerIconoTipo($this->tipo_area);

        // Si se seleccionó una nueva imagen
        if ($this->nuevaImagen) {
            // Eliminar imagen anterior si existe
            if ($this->isEdit && $this->areaId) {
                $areaAnt = AreaInstitucional::find($this->areaId);
                if ($areaAnt && $areaAnt->imagen_area) {
                    Storage::disk('public')->delete($areaAnt->imagen_area);
                }
            }

            // Guardar en storage/app/public/areas
            $path = $this->nuevaImagen->store('areas', 'public');
            $validated['imagen_area'] = $path;
        }

        // Validar backend de responsabilidad de área
        if ($this->isEdit && $this->responsable_id) {
            $usuarioValido = User::where('cod_usu', $this->responsable_id)
                ->where('cod_area', $this->areaId)
                ->where(function($q) {
                    $q->where('estado', 'ACTIVO')->orWhere('estado', '1')->orWhere('estado', 1);
                })
                ->exists();

            if (!$usuarioValido) {
                $this->dispatch('swal:modal', [
                    'type' => 'error',
                    'title' => 'Responsable inválido',
                    'text' => 'El usuario seleccionado no pertenece a esta área institucional o no está activo.',
                ]);
                return;
            }
        } elseif (!$this->isEdit) {
            // En creación, el responsable se fuerza a NULL
            $validated['responsable_id'] = null;
        }

        if ($this->isEdit) {
            $area = AreaInstitucional::findOrFail($this->areaId);
            $validated['actualizado_por'] = Auth::id();
            
            // Conservar campos ocultos de BD
            $validated['roles_sugeridos'] = $this->roles_sugeridos;
            $validated['modulos_relacionados'] = $this->modulos_relacionados;

            $area->update($validated);

            // Log de actividad
            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy(Auth::user())
                ->log("El usuario editó el área institucional {$area->nombre}.");

            $mensaje = 'Área institucional actualizada correctamente.';
        } else {
            $validated['creado_por'] = Auth::id();
            
            // Conservar campos ocultos con defaults
            $validated['roles_sugeridos'] = [];
            $validated['modulos_relacionados'] = [];

            $area = AreaInstitucional::create($validated);

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
        $nuevoEstado = $area->estado === 'ACTIVA' ? 'INACTIVA' : 'ACTIVA';
        $area->estado = $nuevoEstado;
        $area->actualizado_por = Auth::id();
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
            $this->areaSeleccionada = AreaInstitucional::with('usuarios')->find($codArea);
        }
    }

    // ── VISTA DE DETALLE E HISTÓRICOS (FICHA) ──

    public function verArea($codArea)
    {
        $this->areaSeleccionada = AreaInstitucional::with(['responsable', 'usuarios' => function($q) {
            $q->orderBy('nombres');
        }])->findOrFail($codArea);

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
            $area = AreaInstitucional::with(['responsable', 'usuarios'])->findOrFail($codArea);
            $data = $this->obtenerDatosReporteArea($codArea);
            
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


