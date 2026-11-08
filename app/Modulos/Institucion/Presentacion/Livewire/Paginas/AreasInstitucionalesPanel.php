<?php

namespace App\Modulos\Institucion\Presentacion\Livewire\Paginas;

use App\Models\Area;
use App\Models\FotografiaInstitucional;
use App\Models\User;
use App\Modulos\Institucion\Aplicacion\Acciones\GestionarAreasInstitucionales;
use App\Modulos\Institucion\Aplicacion\Consultas\ConsultarCoberturaEquipo;
use App\Modulos\Institucion\Presentacion\Policies\FotografiaInstitucionalPolicy;
use App\Modulos\Reportes\Aplicacion\Consultas\AreasReportDataService;
use App\Modulos\Reportes\Infraestructura\Exportaciones\AreasInstitucionalesExport;
use App\Modulos\Reportes\Infraestructura\Exportaciones\ReportExportService;
use App\Modulos\Reportes\Infraestructura\Exportaciones\ReportFileNameService;
use App\Modulos\Reportes\Infraestructura\Exportaciones\UsuariosPorAreaExport;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AreasInstitucionalesPanel extends Component
{
    use WithFileUploads, WithPagination;

    // Filtros de búsqueda
    public $search = '';

    public $filtroEstado = '';

    public $filtroResponsable = '';

    // Control de modales y paneles
    public $mostrarFormulario = false;

    public $mostrarFicha = false;

    public $mostrarReportes = false;

    #[Locked]
    public $isEdit = false;

    // Reportes interactivos
    public $reporteTipo = null;

    public $reporteData = [];

    // Propiedades del formulario
    #[Locked]
    public $areaId; // Corresponde al cod_area

    public $nombre;

    public $descripcion;

    public $estado = 'ACTIVO';

    public ?string $cod_fotografia = null;

    public $archivo_fotografia = null;

    public string $texto_alternativo = '';

    public bool $selectorFotografiaAbierto = false;

    public string $busquedaFotografia = '';

    #[Locked]
    public array $formularioAreaOriginal = [];

    // Ficha de detalle de área
    public $areaSeleccionada = null;

    public string $fechaEquipo = '';

    public function mount()
    {
        $this->fechaEquipo = today()->toDateString();
    }

    public function render()
    {
        abort_unless($this->usuarioAutorizado('areas.ver'), 403);
        $policyFotos = app(FotografiaInstitucionalPolicy::class);
        $actor = Auth::user()->fresh();
        $puedeVerFotos = $policyFotos->viewAny($actor);
        $puedeSubirFoto = $policyFotos->manage($actor);
        $fotoArea = $this->mostrarFormulario && $puedeVerFotos && $this->cod_fotografia
            ? FotografiaInstitucional::select('cod_fotografia', 'titulo', 'texto_alternativo', 'estado')->find($this->cod_fotografia) : null;
        $fotosGaleriaArea = $this->mostrarFormulario && $this->selectorFotografiaAbierto && $puedeVerFotos
            ? FotografiaInstitucional::select('cod_fotografia', 'titulo', 'texto_alternativo', 'estado')
                ->where('estado', 'ACTIVA')->when(trim($this->busquedaFotografia) !== '', fn ($q) => $q->whereLike('titulo', '%'.trim($this->busquedaFotografia).'%'))
                ->orderByDesc('fecha_registro')->orderByDesc('cod_fotografia')->paginate(8, ['*'], 'fotografiasArea') : null;
        $vistaFotos = compact('puedeVerFotos', 'puedeSubirFoto', 'fotoArea', 'fotosGaleriaArea');
        if (auth()->user()->hasRole('GERENTE') && ! auth()->user()->hasRole('SUPERADMINISTRADOR')) {
            $resumen = Area::query()->get(['estado']);
            $totalAreas = $resumen->count();
            $areasActivas = $resumen->whereIn('estado', ['ACTIVO', 'ACTIVA'])->count();
            $areasInactivas = $totalAreas - $areasActivas;
            $areas = Area::query()->with('fotografiaPrincipal:cod_fotografia,titulo,texto_alternativo,estado')->when(trim($this->search) !== '', fn ($q) => $q->where(fn ($buscar) => $buscar->whereLike('nombre', '%'.trim($this->search).'%')->orWhereLike('cod_area', '%'.trim($this->search).'%')))
                ->when($this->filtroEstado, fn ($q) => $q->whereIn('estado', $this->filtroEstado === 'ACTIVO' ? ['ACTIVO', 'ACTIVA'] : ['INACTIVO', 'INACTIVA']))->orderBy('nombre')->get();
            $equipo = null;
            $areaActual = $this->mostrarFicha && $this->areaSeleccionada
                ? Area::with('fotografiaPrincipal:cod_fotografia,titulo,texto_alternativo,estado')->findOrFail($this->areaSeleccionada->getKey()) : null;
            $puedeVerEquipo = $actor->can('personal.ver') && $actor->can('jornadas.ver')
                && $actor->can('asignaciones_personal.ver') && ! $actor->hasRole('FAMILIAR');
            if ($areaActual && $puedeVerEquipo) {
                $fechaValidada = Validator::make(['fechaEquipo' => $this->fechaEquipo], ['fechaEquipo' => 'required|date_format:Y-m-d'],
                    ['fechaEquipo.required' => 'Selecciona una fecha para consultar el equipo.', 'fechaEquipo.date_format' => 'Selecciona una fecha válida.']);
                if ($fechaValidada->fails()) {
                    $this->addError('fechaEquipo', $fechaValidada->errors()->first('fechaEquipo'));
                } else {
                    $equipo = app(ConsultarCoberturaEquipo::class)->consultar($actor,
                        ['area' => $areaActual->getKey(), 'fecha' => $this->fechaEquipo], incluirFechasArea: true);
                }
            }

            return view('institucion::livewire.gerencia.areas-equipo', [...compact('areas', 'areaActual', 'equipo', 'puedeVerEquipo', 'totalAreas', 'areasActivas', 'areasInactivas'), ...$vistaFotos]);
        }
        $reporte = app(AreasReportDataService::class)->getGeneralReportData();
        $areas = $reporte['areas']
            ->when($this->search, fn ($items) => $items->filter(fn ($area) => str_contains(mb_strtolower($area->nombre.' '.$area->cod_area), mb_strtolower($this->search))))
            ->when($this->filtroEstado, fn ($items) => $items->whereIn(
                'estado', $this->filtroEstado === 'ACTIVO' ? ['ACTIVO', 'ACTIVA'] : ['INACTIVO', 'INACTIVA']
            ))
            ->when($this->filtroResponsable, fn ($items) => $items->filter(fn ($area) => $area->responsable?->cod_usuario === $this->filtroResponsable))
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
            ...$vistaFotos,
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
        $usuario = Auth::user()?->fresh();

        return $usuario !== null
            && strtoupper((string) $usuario->estado) === 'ACTIVO'
            && $usuario->can($permiso);
    }

    // ── GESTIÓN DE ACCESO A FORMULARIO (CRUD) ──

    public function crearArea()
    {
        // Validar Spatie Permission
        if (! $this->usuarioAutorizado('areas.crear')) {
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
        $this->recordarFormularioOriginal();
        $this->mostrarFormulario = true;
        $this->mostrarFicha = false;
    }

    public function editarArea($codArea)
    {
        // Validar Spatie Permission
        if (! $this->usuarioAutorizado('areas.editar')) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para editar áreas institucionales.',
            ]);

            return;
        }

        $this->resetErrorBag();
        $this->resetForm();
        $area = app(AreasReportDataService::class)->getAreaReportData($codArea)['area'];

        $this->areaId = $area->cod_area;
        $this->nombre = $area->nombre;
        $this->descripcion = $area->descripcion;
        $this->estado = in_array($area->estado, ['ACTIVO', 'ACTIVA'], true) ? 'ACTIVO' : 'INACTIVO';
        $this->cod_fotografia = $area->cod_fotografia;
        $this->recordarFormularioOriginal();

        $this->isEdit = true;
        $this->mostrarFormulario = true;
        $this->mostrarFicha = false;
    }

    public function guardarArea()
    {
        $permisoRequerido = $this->isEdit ? 'areas.editar' : 'areas.crear';
        if (! $this->usuarioAutorizado($permisoRequerido)) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permisos para guardar estos cambios.',
            ]);

            return;
        }

        $rules = [
            'nombre' => 'required|string|max:80|unique:areas,nombre,'.($this->areaId ?? 'NULL').',cod_area',
            'descripcion' => 'nullable|string',
            'estado' => 'required|in:ACTIVO,INACTIVO',
            'cod_fotografia' => 'nullable|string|max:20',
            'archivo_fotografia' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'texto_alternativo' => 'nullable|string|max:255',
        ];

        $this->validate($rules);

        try {
            $mensaje = app(GestionarAreasInstitucionales::class)->guardarArea(Auth::user(), ['nombre' => $this->nombre, 'descripcion' => $this->descripcion, 'estado' => $this->estado, 'isEdit' => $this->isEdit, 'areaId' => $this->areaId,
                'cod_fotografia' => $this->cod_fotografia, 'archivo_fotografia' => $this->archivo_fotografia, 'texto_alternativo' => $this->texto_alternativo]);
        } catch (ValidationException|HttpException $error) {
            throw $error;
        } catch (\Throwable $error) {
            report($error);
            $this->addError('guardadoArea', 'No se pudo guardar el área. Tus datos se conservan; vuelve a intentarlo.');

            return;
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
        if (! $this->usuarioAutorizado('areas.cambiar_estado')) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para cambiar el estado de las áreas institucionales.',
            ]);

            return;
        }

        [$area, $nuevoEstado] = app(GestionarAreasInstitucionales::class)->toggleEstado(Auth::user(), $codArea);
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'title' => 'Estado modificado',
            'text' => "El área '{$area->nombre}' ahora está {$nuevoEstado}.",
        ]);

        if ($this->areaSeleccionada && $this->areaSeleccionada->cod_area === $codArea) {
            $this->areaSeleccionada = app(AreasReportDataService::class)
                ->getAreaReportData($codArea)['area'];
        }
    }

    // ── VISTA DE DETALLE E HISTÓRICOS (FICHA) ──

    public function verArea($codArea)
    {
        abort_unless($this->usuarioAutorizado('areas.ver'), 403);
        if (auth()->user()->hasRole('GERENTE') && ! auth()->user()->hasRole('SUPERADMINISTRADOR')) {
            $this->areaSeleccionada = Area::findOrFail($codArea);
            $this->mostrarFicha = true;
            $this->mostrarFormulario = false;

            return;
        }
        $this->areaSeleccionada = app(AreasReportDataService::class)
            ->getAreaReportData($codArea)['area'];

        $this->mostrarFicha = true;
        $this->mostrarFormulario = false;
    }

    public function cerrarFicha()
    {
        $this->mostrarFicha = false;
        $this->areaSeleccionada = null;
    }

    public function updatedFechaEquipo(): void
    {
        $this->resetValidation('fechaEquipo');
    }

    public function cerrarFormulario()
    {
        $this->mostrarFormulario = false;
        $this->resetForm();
    }

    public function abrirGaleriaArea(): void
    {
        $this->autorizarFormularioFoto();
        abort_unless(app(FotografiaInstitucionalPolicy::class)->viewAny(Auth::user()->fresh()), 403);
        $this->selectorFotografiaAbierto = ! $this->selectorFotografiaAbierto;
    }

    public function seleccionarFotografia(string $codigo): void
    {
        $this->autorizarFormularioFoto();
        $policy = app(FotografiaInstitucionalPolicy::class);
        $actor = Auth::user()->fresh();
        abort_unless($policy->viewAny($actor), 403);
        $foto = FotografiaInstitucional::findOrFail($codigo);
        if (! $policy->view($actor, $foto)) {
            $this->addError('cod_fotografia', 'Esta imagen está archivada. Elige otra fotografía.');

            return;
        }
        $this->cod_fotografia = $foto->getKey();
        $this->reset('archivo_fotografia', 'texto_alternativo', 'selectorFotografiaAbierto');
        $this->resetValidation(['cod_fotografia', 'archivo_fotografia']);
    }

    public function quitarFotografia(): void
    {
        $this->autorizarFormularioFoto();
        $this->reset('cod_fotografia', 'archivo_fotografia', 'texto_alternativo');
        $this->resetValidation(['cod_fotografia', 'archivo_fotografia']);
    }

    public function updatedArchivoFotografia(): void
    {
        $this->autorizarFormularioFoto();
        abort_unless(app(FotografiaInstitucionalPolicy::class)->manage(Auth::user()->fresh()), 403);
        $this->validateOnly('archivo_fotografia', ['archivo_fotografia' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048']);
        if ($this->archivo_fotografia) {
            $this->cod_fotografia = null;
            $this->selectorFotografiaAbierto = false;
        }
    }

    public function updatedBusquedaFotografia(): void
    {
        $this->resetPage('fotografiasArea');
    }

    private function autorizarFormularioFoto(): void
    {
        abort_unless($this->mostrarFormulario && $this->usuarioAutorizado($this->isEdit ? 'areas.editar' : 'areas.crear'), 403);
    }

    private function recordarFormularioOriginal(): void
    {
        $this->formularioAreaOriginal = ['nombre' => (string) $this->nombre, 'descripcion' => (string) $this->descripcion,
            'estado' => (string) $this->estado, 'cod_fotografia' => (string) $this->cod_fotografia,
            'archivo' => false, 'texto_alternativo' => (string) $this->texto_alternativo];
    }

    // ── MÓDULO DE REPORTES INTERACTIVOS ──

    public function abrirReportes()
    {
        if (! $this->usuarioAutorizado('areas.reportes')) {
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
        if (! $this->usuarioAutorizado('areas.reportes')) {
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
        if (! $this->usuarioAutorizado('areas.reportes')) {
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
        if (! $this->usuarioAutorizado('areas.reportes')) {
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
                    'usuarios_activos_count' => $area->usuarios->filter(fn ($u) => in_array($u->estado, ['ACTIVO', 1, '1']))->count(),
                    'usuarios_inactivos_count' => $area->usuarios->filter(fn ($u) => ! in_array($u->estado, ['ACTIVO', 1, '1']))->count(),
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
            Log::error('Error exportar PDF general: '.$e->getMessage());
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
        if (! $this->usuarioAutorizado('areas.reportes')) {
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
            Log::error('Error exportar PDF área: '.$e->getMessage());
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
        if (! $this->usuarioAutorizado('areas.reportes')) {
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
        if (! $this->usuarioAutorizado('areas.reportes')) {
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

            $respuesta = $exportService->exportExcel(new UsuariosPorAreaExport($codArea), $filename);

            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy(Auth::user())
                ->log("Se exportó el reporte de usuarios del área '{$area->nombre}' en formato Excel.");

            return $respuesta;
        } catch (ModelNotFoundException $e) {
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
        if (! $this->usuarioAutorizado('areas.reportes')) {
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
        return app(AreasReportDataService::class)->getGeneralReportData();
    }

    public function obtenerDatosReporteArea($codArea)
    {
        return app(AreasReportDataService::class)->getAreaReportData($codArea);
    }

    public function obtenerDatosGraficoUsuariosPorArea()
    {
        return app(AreasReportDataService::class)->getUsuariosPorArea();
    }

    public function obtenerDatosGraficoAreasPorEstado()
    {
        return app(AreasReportDataService::class)->getAreasPorEstado();
    }

    public function obtenerDatosGraficoActivosInactivosPorArea()
    {
        return app(AreasReportDataService::class)->getActivosInactivosPorArea();
    }

    public function obtenerDatosGraficoEvolucionMensual()
    {
        return app(AreasReportDataService::class)->getEvolucionMensualGlobal();
    }

    public function obtenerDatosGraficoActivosInactivosArea($codArea)
    {
        return app(AreasReportDataService::class)->getActivosInactivosArea($codArea);
    }

    public function obtenerDatosGraficoUsuariosPorRolArea($codArea)
    {
        return app(AreasReportDataService::class)->getUsuariosPorRolArea($codArea);
    }

    public function obtenerDatosGraficoEvolucionArea($codArea)
    {
        return app(AreasReportDataService::class)->getEvolucionArea($codArea);
    }

    public function obtenerDatosGraficoRankingAreas()
    {
        return app(AreasReportDataService::class)->getRankingAreasUsuarios();
    }

    // ── AUXILIARES Y FORMATO DE UI ──

    protected function resetForm()
    {
        $this->areaId = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->estado = 'ACTIVO';
        $this->reset('cod_fotografia', 'archivo_fotografia', 'texto_alternativo', 'selectorFotografiaAbierto', 'busquedaFotografia', 'formularioAreaOriginal');
        $this->resetPage('fotografiasArea');
    }
}
