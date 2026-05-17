<?php

namespace App\Livewire\Admin\AreasInstitucionales;

use Livewire\Component;
use App\Models\AreaInstitucional;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class AreasInstitucionalesPanel extends Component
{
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
    public $roles_sugeridos = [];
    public $modulos_relacionados = [];
    public $color = '#2F3E5C';
    public $icono = 'ph-buildings';
    public $estado = 'ACTIVA';
    public $orden = 0;
    public $observaciones;

    // Ficha de detalle de área
    public $areaSeleccionada = null;

    // Opciones del sistema para el formulario
    public $rolesDisponibles = [];
    public $modulosDisponibles = [
        'usuarios' => 'Gestión de Usuarios',
        'roles_permisos' => 'Roles y Permisos',
        'areas' => 'Áreas Institucionales',
        'adultos_mayores' => 'Adultos Mayores',
        'familiares' => 'Familiares',
        'documentos' => 'Documentación',
        'ficha_medica' => 'Ficha Médica',
        'signos_vitales' => 'Signos Vitales',
        'medicacion' => 'Medicación y Alertas',
        'atenciones' => 'Atenciones e Incidentes',
        'evaluaciones' => 'Evaluaciones Cognitivas',
        'observaciones' => 'Observaciones Diarias',
        'actividades' => 'Actividades Recreativas',
        'voluntariado' => 'Gestión de Voluntarios',
        'asistencia' => 'Control de Asistencia',
        'bitacora' => 'Bitácora y Seguridad',
        'reportes' => 'Módulo de Reportes',
        'configuracion' => 'Configuración de Sistema',
    ];

    public function mount()
    {
        // Cargar roles del sistema
        $this->rolesDisponibles = Role::pluck('name')->toArray();
    }

    public function render()
    {
        // Consultar áreas institucionales con sus responsables y conteo de usuarios
        $query = AreaInstitucional::with(['responsable', 'usuarios'])
            ->withCount('usuarios');

        // Búsqueda por nombre
        if ($this->search) {
            $query->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('cod_area', 'like', '%' . $this->search . '%')
                  ->orWhere('tipo_area', 'like', '%' . $this->search . '%');
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

        // Cargar usuarios con privilegios institucionales para ser elegidos como responsables
        $responsablesDisponibles = User::where('estado', 'ACTIVO')
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['admin', 'personal_admin', 'personal_salud']);
            })
            ->get();

        // Calcular métricas generales para las tarjetas
        $totalAreas = AreaInstitucional::count();
        $areasActivas = AreaInstitucional::activas()->count();
        $usuariosVinculados = User::whereNotNull('cod_area')->count();
        $areasSinResponsable = AreaInstitucional::whereNull('responsable_id')->count();
        
        // Contar todos los módulos únicos utilizados
        $modulosUtilizadosCount = 0;
        $todasLasAreas = AreaInstitucional::select('modulos_relacionados')->get();
        $modulosUnicos = [];
        foreach ($todasLasAreas as $a) {
            if ($a->modulos_relacionados) {
                foreach ($a->modulos_relacionados as $mod) {
                    $modulosUnicos[$mod] = true;
                }
            }
        }
        $modulosUtilizadosCount = count($modulosUnicos);

        return view('livewire.admin.areas-institucionales.areas-institucionales-panel', [
            'areas' => $areas,
            'responsablesDisponibles' => $responsablesDisponibles,
            'totalAreas' => $totalAreas,
            'areasActivas' => $areasActivas,
            'usuariosVinculados' => $usuariosVinculados,
            'areasSinResponsable' => $areasSinResponsable,
            'modulosUtilizadosCount' => $modulosUtilizadosCount,
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

        $this->areaId = $area->cod_area;
        $this->nombre = $area->nombre;
        $this->tipo_area = $area->tipo_area;
        $this->descripcion = $area->descripcion;
        $this->responsable_id = $area->responsable_id;
        $this->roles_sugeridos = $area->roles_sugeridos ?? [];
        $this->modulos_relacionados = $area->modulos_relacionados ?? [];
        $this->color = $area->color ?? '#2F3E5C';
        $this->icono = $area->icono ?? 'ph-buildings';
        $this->estado = $area->estado;
        $this->orden = $area->orden;
        $this->observaciones = $area->observaciones;

        $this->isEdit = true;
        $this->mostrarFormulario = true;
        $this->mostrarFicha = false;
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
            'roles_sugeridos' => 'nullable|array',
            'modulos_relacionados' => 'nullable|array',
            'color' => 'required|string|max:20',
            'icono' => 'required|string|max:50',
            'estado' => 'required|in:ACTIVA,INACTIVA',
            'orden' => 'required|integer|min:0',
            'observaciones' => 'nullable|string',
        ];

        $validated = $this->validate($rules);
        $validated['slug'] = Str::slug($this->nombre);

        if ($this->isEdit) {
            $area = AreaInstitucional::findOrFail($this->areaId);
            $validated['actualizado_por'] = Auth::id();
            $area->update($validated);

            // Registrar acción en log de actividad
            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy(Auth::user())
                ->log("El usuario editó el área institucional {$area->nombre}.");

            $mensaje = 'Área institucional actualizada correctamente.';
        } else {
            $validated['creado_por'] = Auth::id();
            $area = AreaInstitucional::create($validated);

            // Registrar acción en log de actividad
            activity('AreasInstitucionales')
                ->performedOn($area)
                ->causedBy(Auth::user())
                ->log("El usuario creó una nueva área institucional llamada {$area->nombre}.");

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
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para consultar reportes de áreas.',
            ]);
            return;
        }

        $this->mostrarReportes = true;
        $this->reporteTipo = null;
        $this->reporteData = [];
    }

    public function cerrarReportes()
    {
        $this->mostrarReportes = false;
        $this->reporteTipo = null;
        $this->reporteData = [];
    }

    public function generarReporteGeneral()
    {
        $this->reporteTipo = 'general';
        $this->reporteData = AreaInstitucional::with('responsable')
            ->withCount('usuarios')
            ->orderBy('orden')
            ->get()
            ->toArray();
        
        activity('AreasInstitucionales')
            ->causedBy(Auth::user())
            ->log('Se generó el reporte general de áreas institucionales.');
    }

    public function generarReporteUsuarios()
    {
        $this->reporteTipo = 'usuarios';
        $this->reporteData = User::with('areaInstitucional')
            ->whereNotNull('cod_area')
            ->orderBy('cod_area')
            ->get()
            ->map(function($user) {
                return [
                    'nombre_completo' => $user->name,
                    'area' => $user->areaInstitucional->nombre ?? 'N/A',
                    'rol' => $user->getRoleNames()->first() ?? 'Sin Rol',
                    'estado' => $user->estado == 1 ? 'ACTIVO' : 'INACTIVO',
                    'ultimo_acceso' => $user->ultimo_acceso ? $user->ultimo_acceso->format('d/m/Y H:i') : 'Nunca',
                ];
            })
            ->toArray();

        activity('AreasInstitucionales')
            ->causedBy(Auth::user())
            ->log('Se generó el reporte de usuarios por área institucional.');
    }

    public function generarReporteDistribucion()
    {
        $this->reporteTipo = 'distribucion';
        
        $totalAreas = AreaInstitucional::count();
        $totalActivas = AreaInstitucional::activas()->count();
        $totalInactivas = AreaInstitucional::inactivas()->count();
        
        $areaConMasUsuarios = AreaInstitucional::withCount('usuarios')
            ->orderByDesc('usuarios_count')
            ->first();

        $areasSinResponsableCount = AreaInstitucional::whereNull('responsable_id')->count();

        // Distribución por tipos de áreas
        $tipos = AreaInstitucional::selectRaw('tipo_area, count(*) as total')
            ->groupBy('tipo_area')
            ->get()
            ->toArray();

        $this->reporteData = [
            'total_areas' => $totalAreas,
            'activas' => $totalActivas,
            'inactivas' => $totalInactivas,
            'mas_usuarios' => $areaConMasUsuarios ? "{$areaConMasUsuarios->nombre} ({$areaConMasUsuarios->usuarios_count} usuarios)" : 'Ninguna',
            'sin_responsable' => $areasSinResponsableCount,
            'tipos' => $tipos,
        ];

        activity('AreasInstitucionales')
            ->causedBy(Auth::user())
            ->log('Se generó el reporte de distribución institucional.');
    }

    public function generarReporteSinResponsable()
    {
        $this->reporteTipo = 'sin_responsable';
        $this->reporteData = AreaInstitucional::whereNull('responsable_id')
            ->withCount('usuarios')
            ->orderBy('nombre')
            ->get()
            ->toArray();

        activity('AreasInstitucionales')
            ->causedBy(Auth::user())
            ->log('Se generó el reporte de áreas sin responsable.');
    }

    public function generarReporteEspecifico($codArea)
    {
        if (!Auth::user()->can('areas.reportes')) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No tienes permiso para consultar reportes de áreas.',
            ]);
            return;
        }

        $area = AreaInstitucional::with(['responsable', 'usuarios'])->findOrFail($codArea);
        
        $this->mostrarReportes = true;
        $this->reporteTipo = 'especifico';
        
        $this->reporteData = [
            'nombre' => $area->nombre,
            'cod_area' => $area->cod_area,
            'tipo_area' => $area->tipo_area,
            'descripcion' => $area->descripcion,
            'responsable' => $area->responsable->name ?? 'Sin asignar',
            'estado' => $area->estado,
            'color' => $area->color,
            'icono' => $area->icono,
            'usuarios' => $area->usuarios->map(function($u) {
                return [
                    'nombre' => $u->name,
                    'rol' => $u->getRoleNames()->first() ?? 'Sin Rol',
                    'estado' => $u->estado == 1 ? 'ACTIVO' : 'INACTIVO',
                ];
            })->toArray()
        ];

        activity('AreasInstitucionales')
            ->performedOn($area)
            ->causedBy(Auth::user())
            ->log("Se generó el reporte institucional específico para el área {$area->nombre}.");
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
        $this->roles_sugeridos = [];
        $this->modulos_relacionados = [];
        $this->color = '#2F3E5C';
        $this->icono = 'ph-buildings';
        $this->estado = 'ACTIVA';
        $this->orden = 0;
        $this->observaciones = '';
    }
}
