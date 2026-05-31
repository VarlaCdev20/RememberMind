<?php

namespace App\Livewire\Admin\TurnosAsignaciones;

use Livewire\Component;
use App\Models\TurnoInstitucional;
use App\Models\AsignacionTurno;
use App\Models\AreaInstitucional;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\Reports\ReportExportService;
use App\Services\Reports\ReportFileNameService;
use App\Exports\TurnosAsignacionesExport;
use App\Exports\AsignacionesPorAreaExport;
use App\Exports\UsuariosSinTurnoExport;

class TurnosAsignacionesPanel extends Component
{
    // Filtros y búsquedas
    public $search = '';
    public $filtroArea = '';
    public $filtroTurno = '';
    public $filtroEstado = '';
    public $filtroDia = '';
    public $filtroTipo = '';

    // Modales y control de vista
    public $vistaActual = 'calendario'; // calendario, cards, tabla
    public $mostrarFormularioTurno = false;
    public $mostrarFormularioAsignacion = false;
    public $mostrarFichaAsignacion = false;
    public $mostrarReportes = false;
    public $isEdit = false;

    // IDs para edición
    public $turnoId;
    public $asignacionId;

    // Propiedades del formulario Turno
    public $turno_nombre;
    public $turno_hora_inicio;
    public $turno_hora_fin;
    public $turno_descripcion;
    public $turno_color = '#3B82F6';
    public $turno_estado = 'ACTIVO';
    public $turno_observaciones;

    // Propiedades del formulario Asignación
    public $asig_cod_usu;
    public $asig_cod_area;
    public $asig_cod_turno;
    public $asig_dias_semana = [];
    public $asig_fecha_inicio;
    public $asig_fecha_fin;
    public $asig_tipo_asignacion = 'REGULAR';
    public $asig_estado = 'ACTIVA';
    public $asig_observaciones;
    public $asig_apoyo_temporal = false; // Checkbox para omitir restricción cruzada de áreas

    // Detalle seleccionado
    public $asignacionSeleccionada = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroArea' => ['except' => ''],
        'filtroTurno' => ['except' => ''],
        'filtroEstado' => ['except' => ''],
        'filtroDia' => ['except' => ''],
        'filtroTipo' => ['except' => ''],
        'vistaActual' => ['except' => 'calendario'],
    ];

    public function mount()
    {
        $this->asig_fecha_inicio = now()->format('Y-m-d');
    }

    public function render()
    {
        // 1. Obtener todas las áreas activas
        $areasDisponibles = AreaInstitucional::activas()->orderBy('nombre')->get();

        // 2. Obtener turnos activos
        $turnosDisponibles = TurnoInstitucional::activos()->orderBy('nombre')->get();

        // 3. Obtener todos los usuarios activos
        $usuariosDisponibles = User::where(function($q) {
                $q->where('estado', 'ACTIVO')->orWhere('estado', 1)->orWhere('estado', '1');
            })
            ->orderBy('nombres')
            ->get();

        // 4. Query de asignaciones activas
        $query = AsignacionTurno::with(['usuario', 'area', 'turno']);

        if ($this->search) {
            $query->whereHas('usuario', function($q) {
                $q->where('nombres', 'like', '%' . $this->search . '%')
                  ->orWhere('ap_paterno', 'like', '%' . $this->search . '%')
                  ->orWhere('ap_materno', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filtroArea) {
            $query->where('cod_area', $this->filtroArea);
        }

        if ($this->filtroTurno) {
            $query->where('cod_turno', $this->filtroTurno);
        }

        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }

        if ($this->filtroTipo) {
            $query->where('tipo_asignacion', $this->filtroTipo);
        }

        if ($this->filtroDia) {
            // Días de la semana almacenados en JSON
            $query->whereJsonContains('dias_semana', strtoupper($this->filtroDia));
        }

        $asignaciones = $query->orderBy('fecha_inicio', 'desc')->get();

        // 5. Estadísticas reales
        $totalTurnos = TurnoInstitucional::count();
        $totalAsignados = AsignacionTurno::activas()->distinct('cod_usu')->count();
        $areasCubiertas = AsignacionTurno::activas()->distinct('cod_area')->count();
        $areasSinCobertura = AreaInstitucional::activas()
            ->whereDoesntHave('asignacionesTurno', function($q) {
                $q->where('estado', 'ACTIVA');
            })
            ->count();
        $asignacionesActivas = AsignacionTurno::activas()->count();
        $usuariosSinTurno = User::where(function($q) {
                $q->where('estado', 'ACTIVO')->orWhere('estado', 1)->orWhere('estado', '1');
            })
            ->whereDoesntHave('asignacionesTurno', function($q) {
                $q->where('estado', 'ACTIVA');
            })
            ->count();

        // 6. Datos para Gráficos
        $distribucionAreas = [];
        $distribucionTurnos = [];

        foreach ($areasDisponibles as $a) {
            $count = AsignacionTurno::activas()->where('cod_area', $a->cod_area)->count();
            if ($count > 0) {
                $distribucionAreas[$a->nombre] = $count;
            }
        }

        foreach (TurnoInstitucional::all() as $t) {
            $count = AsignacionTurno::activas()->where('cod_turno', $t->cod_turno)->count();
            if ($count > 0) {
                $distribucionTurnos[$t->nombre] = $count;
            }
        }

        return view('livewire.admin.turnos-asignaciones.turnos-asignaciones-panel', [
            'asignaciones' => $asignaciones,
            'areasDisponibles' => $areasDisponibles,
            'turnosDisponibles' => $turnosDisponibles,
            'usuariosDisponibles' => $usuariosDisponibles,
            'totalTurnos' => $totalTurnos,
            'totalAsignados' => $totalAsignados,
            'areasCubiertas' => $areasCubiertas,
            'areasSinCobertura' => $areasSinCobertura,
            'asignacionesActivas' => $asignacionesActivas,
            'usuariosSinTurno' => $usuariosSinTurno,
            'distribucionAreas' => $distribucionAreas,
            'distribucionTurnos' => $distribucionTurnos,
            'turnosLista' => TurnoInstitucional::orderBy('nombre')->get()
        ]);
    }

    public function cambiarVista($vista)
    {
        $this->vistaActual = $vista;
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->filtroArea = '';
        $this->filtroTurno = '';
        $this->filtroEstado = '';
        $this->filtroDia = '';
        $this->filtroTipo = '';
    }

    // ──────────────────────────────────────────────
    // ACCIONES CRUD: TURNOS
    // ──────────────────────────────────────────────

    public function abrirModalTurno()
    {
        if (!Auth::user()->can('turnos.crear')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para registrar turnos.']);
            return;
        }

        $this->resetErrorBag();
        $this->resetFormTurno();
        $this->isEdit = false;
        $this->mostrarFormularioTurno = true;
    }

    public function cargarTurno($codTurno)
    {
        if (!Auth::user()->can('turnos.editar')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para editar turnos.']);
            return;
        }

        $this->resetErrorBag();
        $turno = TurnoInstitucional::findOrFail($codTurno);
        $this->turnoId = $turno->cod_turno;
        $this->turno_nombre = $turno->nombre;
        $this->turno_hora_inicio = $turno->hora_inicio;
        $this->turno_hora_fin = $turno->hora_fin;
        $this->turno_descripcion = $turno->descripcion;
        $this->turno_color = $turno->color ?: '#3B82F6';
        $this->turno_estado = $turno->estado;
        $this->turno_observaciones = $turno->observaciones;

        $this->isEdit = true;
        $this->mostrarFormularioTurno = true;
    }

    public function guardarTurno()
    {
        if ($this->isEdit) {
            if (!Auth::user()->can('turnos.editar')) {
                $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para editar turnos.']);
                return;
            }
        } else {
            if (!Auth::user()->can('turnos.crear')) {
                $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para registrar turnos.']);
                return;
            }
        }

        $validated = $this->validate([
            'turno_nombre' => 'required|string|max:100',
            'turno_hora_inicio' => 'nullable',
            'turno_hora_fin' => 'nullable',
            'turno_descripcion' => 'nullable|string',
            'turno_color' => 'nullable|string|max:20',
            'turno_estado' => 'required|string|in:ACTIVO,INACTIVO',
            'turno_observaciones' => 'nullable|string',
        ]);

        if ($this->isEdit) {
            $turno = TurnoInstitucional::findOrFail($this->turnoId);
            $turno->update([
                'nombre' => $this->turno_nombre,
                'hora_inicio' => $this->turno_hora_inicio ?: null,
                'hora_fin' => $this->turno_hora_fin ?: null,
                'descripcion' => $this->turno_descripcion,
                'color' => $this->turno_color,
                'estado' => $this->turno_estado,
                'observaciones' => $this->turno_observaciones,
                'actualizado_por' => Auth::user()->cod_usu
            ]);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Turno actualizado',
                'text' => 'El turno institucional se ha modificado correctamente.'
            ]);
        } else {
            TurnoInstitucional::create([
                'nombre' => $this->turno_nombre,
                'hora_inicio' => $this->turno_hora_inicio ?: null,
                'hora_fin' => $this->turno_hora_fin ?: null,
                'descripcion' => $this->turno_descripcion,
                'color' => $this->turno_color,
                'estado' => $this->turno_estado,
                'observaciones' => $this->turno_observaciones,
                'creado_por' => Auth::user()->cod_usu,
                'actualizado_por' => Auth::user()->cod_usu
            ]);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Turno registrado',
                'text' => 'Se ha creado el nuevo turno institucional de forma exitosa.'
            ]);
        }

        $this->mostrarFormularioTurno = false;
        $this->resetFormTurno();
    }

    public function eliminarTurno($codTurno)
    {
        if (!Auth::user()->can('turnos.cambiar_estado')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para archivar turnos.']);
            return;
        }

        $turno = TurnoInstitucional::findOrFail($codTurno);
        $turno->delete(); // Soft delete

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Turno archivado',
            'text' => 'El turno ha sido archivado exitosamente.'
        ]);
    }

    private function resetFormTurno()
    {
        $this->turnoId = null;
        $this->turno_nombre = '';
        $this->turno_hora_inicio = '';
        $this->turno_hora_fin = '';
        $this->turno_descripcion = '';
        $this->turno_color = '#3B82F6';
        $this->turno_estado = 'ACTIVO';
        $this->turno_observaciones = '';
    }

    // ──────────────────────────────────────────────
    // ACCIONES CRUD: ASIGNACIONES
    // ──────────────────────────────────────────────

    public function abrirModalAsignacion()
    {
        if (!Auth::user()->can('turnos.asignar')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para asignar turnos.']);
            return;
        }

        $this->resetErrorBag();
        $this->resetFormAsignacion();
        $this->isEdit = false;
        $this->mostrarFormularioAsignacion = true;
    }

    public function cargarAsignacion($codAsignacion)
    {
        $this->resetErrorBag();
        $asig = AsignacionTurno::with(['usuario', 'area', 'turno'])->findOrFail($codAsignacion);
        $this->asignacionId = $asig->cod_asignacion;
        $this->asig_cod_usu = $asig->cod_usu;
        $this->asig_cod_area = $asig->cod_area;
        $this->asig_cod_turno = $asig->cod_turno;
        $this->asig_dias_semana = $asig->dias_semana ?: [];
        $this->asig_fecha_inicio = $asig->fecha_inicio ? $asig->fecha_inicio->format('Y-m-d') : '';
        $this->asig_fecha_fin = $asig->fecha_fin ? $asig->fecha_fin->format('Y-m-d') : '';
        $this->asig_tipo_asignacion = $asig->tipo_asignacion ?: 'REGULAR';
        $this->asig_estado = $asig->estado;
        $this->asig_observaciones = $asig->observaciones;

        // Comprobación inicial de área temporal
        if ($asig->usuario && $asig->usuario->cod_area !== $asig->cod_area) {
            $this->asig_apoyo_temporal = true;
        } else {
            $this->asig_apoyo_temporal = false;
        }

        $this->isEdit = true;
        $this->mostrarFormularioAsignacion = true;
    }

    public function verFichaAsignacion($codAsignacion)
    {
        $this->asignacionSeleccionada = AsignacionTurno::with(['usuario.areaInstitucional', 'area', 'turno', 'creador', 'editor'])
            ->findOrFail($codAsignacion);
        $this->mostrarFichaAsignacion = true;
    }

    public function guardarAsignacion()
    {
        if (!Auth::user()->can('turnos.asignar')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para realizar asignaciones.']);
            return;
        }

        $this->validate([
            'asig_cod_usu' => 'required|string|exists:users,cod_usu',
            'asig_cod_area' => 'required|string|exists:areas_institucionales,cod_area',
            'asig_cod_turno' => 'required|string|exists:turnos_institucionales,cod_turno',
            'asig_dias_semana' => 'required|array|min:1',
            'asig_fecha_inicio' => 'required|date',
            'asig_fecha_fin' => 'nullable|date|after_or_equal:asig_fecha_inicio',
            'asig_tipo_asignacion' => 'required|string',
            'asig_estado' => 'required|string|in:ACTIVA,INACTIVA,FINALIZADA',
            'asig_observaciones' => 'nullable|string',
        ]);

        // REGLA DE NEGOCIO: Cruce de áreas
        $usuario = User::findOrFail($this->asig_cod_usu);
        $cruzandoArea = ($usuario->cod_area !== $this->asig_cod_area);

        if ($cruzandoArea) {
            if (!$this->asig_apoyo_temporal) {
                // Bloquear guardado y lanzar advertencia SWAL
                $this->dispatch('swal', [
                    'icon' => 'warning',
                    'title' => 'Asignación no permitida',
                    'text' => 'El usuario pertenece a otra área. Para asignarlo a esta área debe marcar la asignación como apoyo temporal.'
                ]);
                return;
            } else {
                // Forzar tipo de asignación a APOYO o COBERTURA si cruza área
                if ($this->asig_tipo_asignacion === 'REGULAR') {
                    $this->asig_tipo_asignacion = 'APOYO';
                }
            }
        }

        if ($this->isEdit) {
            $asig = AsignacionTurno::findOrFail($this->asignacionId);
            $asig->update([
                'cod_usu' => $this->asig_cod_usu,
                'cod_area' => $this->asig_cod_area,
                'cod_turno' => $this->asig_cod_turno,
                'dias_semana' => $this->asig_dias_semana,
                'fecha_inicio' => $this->asig_fecha_inicio,
                'fecha_fin' => $this->asig_fecha_fin ?: null,
                'tipo_asignacion' => $this->asig_tipo_asignacion,
                'estado' => $this->asig_estado,
                'observaciones' => $this->asig_observaciones,
                'actualizado_por' => Auth::user()->cod_usu
            ]);

            if ($cruzandoArea && $this->asig_apoyo_temporal) {
                $this->dispatch('swal', [
                    'icon' => 'info',
                    'title' => 'Guardado con Advertencia',
                    'text' => 'Asignación registrada como apoyo temporal en área ajena al usuario.'
                ]);
            } else {
                $this->dispatch('swal', [
                    'icon' => 'success',
                    'title' => 'Asignación actualizada',
                    'text' => 'La asignación de turno se actualizó exitosamente.'
                ]);
            }
        } else {
            AsignacionTurno::create([
                'cod_usu' => $this->asig_cod_usu,
                'cod_area' => $this->asig_cod_area,
                'cod_turno' => $this->asig_cod_turno,
                'dias_semana' => $this->asig_dias_semana,
                'fecha_inicio' => $this->asig_fecha_inicio,
                'fecha_fin' => $this->asig_fecha_fin ?: null,
                'tipo_asignacion' => $this->asig_tipo_asignacion,
                'estado' => $this->asig_estado,
                'observaciones' => $this->asig_observaciones,
                'creado_por' => Auth::user()->cod_usu,
                'actualizado_por' => Auth::user()->cod_usu
            ]);

            if ($cruzandoArea && $this->asig_apoyo_temporal) {
                $this->dispatch('swal', [
                    'icon' => 'info',
                    'title' => 'Asignación de Apoyo Registrada',
                    'text' => 'Asignación registrada como apoyo temporal en área ajena al usuario.'
                ]);
            } else {
                $this->dispatch('swal', [
                    'icon' => 'success',
                    'title' => 'Asignación registrada',
                    'text' => 'Se ha asignado el turno al colaborador de forma exitosa.'
                ]);
            }
        }

        $this->mostrarFormularioAsignacion = false;
        $this->resetFormAsignacion();
    }

    public function finalizarAsignacion($codAsignacion)
    {
        if (!Auth::user()->can('turnos.finalizar')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para finalizar asignaciones.']);
            return;
        }

        $asig = AsignacionTurno::findOrFail($codAsignacion);
        $asig->update([
            'estado' => 'FINALIZADA',
            'fecha_fin' => now()->format('Y-m-d'),
            'actualizado_por' => Auth::user()->cod_usu
        ]);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Asignación Finalizada',
            'text' => 'La asignación de turno ha sido catalogada como FINALIZADA con fecha de hoy.'
        ]);

        if ($this->mostrarFichaAsignacion && $this->asignacionSeleccionada && $this->asignacionSeleccionada->cod_asignacion === $codAsignacion) {
            $this->verFichaAsignacion($codAsignacion);
        }
    }

    public function eliminarAsignacion($codAsignacion)
    {
        if (!Auth::user()->can('turnos.finalizar')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para archivar asignaciones.']);
            return;
        }

        $asig = AsignacionTurno::findOrFail($codAsignacion);
        $asig->delete(); // Soft delete

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Asignación Archivada',
            'text' => 'El registro de asignación ha sido archivado exitosamente.'
        ]);

        $this->mostrarFichaAsignacion = false;
    }

    private function resetFormAsignacion()
    {
        $this->asignacionId = null;
        $this->asig_cod_usu = '';
        $this->asig_cod_area = '';
        $this->asig_cod_turno = '';
        $this->asig_dias_semana = [];
        $this->asig_fecha_inicio = now()->format('Y-m-d');
        $this->asig_fecha_fin = '';
        $this->asig_tipo_asignacion = 'REGULAR';
        $this->asig_estado = 'ACTIVA';
        $this->asig_observaciones = '';
        $this->asig_apoyo_temporal = false;
    }

    // ──────────────────────────────────────────────
    // GENERACIÓN DE REPORTES PDF Y EXCEL
    // ──────────────────────────────────────────────

    public function exportarReporteGeneralPdf()
    {
        if (!Auth::user()->can('turnos.reportes')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para exportar reportes.']);
            return;
        }

        $asignaciones = AsignacionTurno::with(['usuario', 'area', 'turno'])
            ->where('estado', 'ACTIVA')
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        $totalTurnos = TurnoInstitucional::count();
        $totalAsignados = AsignacionTurno::where('estado', 'ACTIVA')->distinct('cod_usu')->count();
        $areasCubiertas = AsignacionTurno::where('estado', 'ACTIVA')->distinct('cod_area')->count();
        $areasSinCobertura = AreaInstitucional::activas()
            ->whereDoesntHave('asignacionesTurno', function($q) {
                $q->where('estado', 'ACTIVA');
            })
            ->count();
        $nombresAreasSinCobertura = AreaInstitucional::activas()
            ->whereDoesntHave('asignacionesTurno', function($q) {
                $q->where('estado', 'ACTIVA');
            })
            ->pluck('nombre')
            ->toArray();
        $asignacionesActivas = AsignacionTurno::where('estado', 'ACTIVA')->count();
        $usuariosSinTurno = User::where(function($q) {
                $q->where('estado', 'ACTIVO')->orWhere('estado', 1)->orWhere('estado', '1');
            })
            ->whereDoesntHave('asignacionesTurno', function($q) {
                $q->where('estado', 'ACTIVA');
            })
            ->count();

        $data = [
            'asignaciones' => $asignaciones,
            'totalTurnos' => $totalTurnos,
            'totalAsignados' => $totalAsignados,
            'areasCubiertas' => $areasCubiertas,
            'areasSinCobertura' => $areasSinCobertura,
            'nombresAreasSinCobertura' => $nombresAreasSinCobertura,
            'asignacionesActivas' => $asignacionesActivas,
            'usuariosSinTurno' => $usuariosSinTurno,
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => Auth::user()->name
        ];

        $filename = app(ReportFileNameService::class)->generate('turnos_y_asignaciones_general', 'pdf');

        // Log de actividad
        activity('TurnosAsignaciones')
            ->causedBy(Auth::user())
            ->withProperties(['formato' => 'PDF', 'tipo_reporte' => 'general'])
            ->log("Se exportó el reporte general de turnos y asignaciones en formato PDF.");

        return app(ReportExportService::class)->exportPdf('reports.turnos.general', $data, $filename);
    }

    public function exportarReporteAreaPdf($codArea)
    {
        if (!Auth::user()->can('turnos.reportes')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para exportar reportes.']);
            return;
        }

        $area = AreaInstitucional::findOrFail($codArea);
        $asignaciones = AsignacionTurno::with(['usuario', 'turno'])
            ->where('cod_area', $codArea)
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        $data = [
            'area' => $area,
            'asignaciones' => $asignaciones,
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => Auth::user()->name
        ];

        $filename = app(ReportFileNameService::class)->generate('cobertura_area_' . str_replace(' ', '_', strtolower($area->nombre)), 'pdf');

        // Log de actividad
        activity('TurnosAsignaciones')
            ->causedBy(Auth::user())
            ->withProperties(['formato' => 'PDF', 'tipo_reporte' => 'especifico_area', 'area' => $area->nombre])
            ->log("Se exportó el reporte de asignaciones de turnos para el área: {$area->nombre} en formato PDF.");

        return app(ReportExportService::class)->exportPdf('reports.turnos.area', $data, $filename);
    }

    public function exportarCoberturaSemanalPdf()
    {
        if (!Auth::user()->can('turnos.reportes')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para exportar reportes.']);
            return;
        }

        $asignaciones = AsignacionTurno::with(['usuario', 'area', 'turno'])
            ->where('estado', 'ACTIVA')
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        $totalAsignaciones = AsignacionTurno::where('estado', 'ACTIVA')->count();
        $totalColaboradores = AsignacionTurno::where('estado', 'ACTIVA')->distinct('cod_usu')->count();
        $totalAreas = AsignacionTurno::where('estado', 'ACTIVA')->distinct('cod_area')->count();
        $areasSinCobertura = AreaInstitucional::activas()
            ->whereDoesntHave('asignacionesTurno', function($q) {
                $q->where('estado', 'ACTIVA');
            })
            ->count();

        $data = [
            'asignaciones' => $asignaciones,
            'totalAsignaciones' => $totalAsignaciones,
            'totalColaboradores' => $totalColaboradores,
            'totalAreas' => $totalAreas,
            'areasSinCobertura' => $areasSinCobertura,
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => Auth::user()->name
        ];

        $filename = app(ReportFileNameService::class)->generate('cobertura_semanal_turnos', 'pdf');

        // Log de actividad
        activity('TurnosAsignaciones')
            ->causedBy(Auth::user())
            ->withProperties(['formato' => 'PDF', 'tipo_reporte' => 'cobertura_semanal'])
            ->log("Se exportó la grilla de cobertura semanal de turnos en formato PDF.");

        return app(ReportExportService::class)->exportPdf('reports.turnos.cobertura-semanal', $data, $filename);
    }

    public function exportarReporteGeneralExcel()
    {
        if (!Auth::user()->can('turnos.reportes')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para exportar reportes.']);
            return;
        }

        $filename = app(ReportFileNameService::class)->generate('turnos_y_asignaciones_general', 'xlsx');

        activity('TurnosAsignaciones')
            ->causedBy(Auth::user())
            ->withProperties(['formato' => 'Excel', 'tipo_reporte' => 'general'])
            ->log("Se exportó el reporte general de turnos y asignaciones en formato Excel.");

        return app(ReportExportService::class)->exportExcel(new TurnosAsignacionesExport, $filename);
    }

    public function exportarReporteAreaExcel($codArea)
    {
        if (!Auth::user()->can('turnos.reportes')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para exportar reportes.']);
            return;
        }

        $area = AreaInstitucional::findOrFail($codArea);
        $filename = app(ReportFileNameService::class)->generate('cobertura_area_' . str_replace(' ', '_', strtolower($area->nombre)), 'xlsx');

        activity('TurnosAsignaciones')
            ->causedBy(Auth::user())
            ->withProperties(['formato' => 'Excel', 'tipo_reporte' => 'especifico_area', 'area' => $area->nombre])
            ->log("Se exportó el reporte de asignaciones de turnos para el área {$area->nombre} en formato Excel.");

        return app(ReportExportService::class)->exportExcel(new AsignacionesPorAreaExport($codArea), $filename);
    }

    public function exportarPersonalSinTurnoExcel()
    {
        if (!Auth::user()->can('turnos.reportes')) {
            $this->dispatch('swal', ['icon' => 'error', 'title' => 'Acceso denegado', 'text' => 'No tienes permiso para exportar reportes.']);
            return;
        }

        $filename = app(ReportFileNameService::class)->generate('personal_sin_turno_asignado', 'xlsx');

        activity('TurnosAsignaciones')
            ->causedBy(Auth::user())
            ->withProperties(['formato' => 'Excel', 'tipo_reporte' => 'personal_sin_turno'])
            ->log("Se exportó la lista de personal activo sin turno asignado en formato Excel.");

        return app(ReportExportService::class)->exportExcel(new UsuariosSinTurnoExport, $filename);
    }
}
