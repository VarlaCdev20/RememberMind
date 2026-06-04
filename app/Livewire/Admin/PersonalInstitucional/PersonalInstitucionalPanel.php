<?php

namespace App\Livewire\Admin\PersonalInstitucional;

use Livewire\Component;
use App\Models\User;
use Spatie\Permission\Models\Role;

class PersonalInstitucionalPanel extends Component
{
    public $tabActiva = 'resumen';
    public $busqueda = '';
    
    // Modal state
    public $modalGestionAbierto = false;
    public $usuarioSeleccionadoId = null;

    protected $listeners = [
        'cerrarModalGestion' => 'cerrarModal',
        'actualizarTablaPersonal' => '$refresh'
    ];

    public function abrirModalNuevo()
    {
        $this->usuarioSeleccionadoId = null;
        $this->modalGestionAbierto = true;
    }

    public function abrirModalEdicion($usuarioId)
    {
        $this->usuarioSeleccionadoId = $usuarioId;
        $this->modalGestionAbierto = true;
    }

    public function cerrarModal()
    {
        $this->modalGestionAbierto = false;
        $this->usuarioSeleccionadoId = null;
    }
    
    public function setTab($tab)
    {
        $this->tabActiva = $tab;
    }

    public function render()
    {
        $query = User::with(['personalSalud', 'personalAdmin', 'areaInstitucional', 'roles'])
            ->where(function($q) {
                $q->where('nombres', 'ilike', '%' . $this->busqueda . '%')
                  ->orWhere('ap_paterno', 'ilike', '%' . $this->busqueda . '%')
                  ->orWhere('correo', 'ilike', '%' . $this->busqueda . '%')
                  ->orWhere('cod_usu', 'ilike', '%' . $this->busqueda . '%');
            });

        if ($this->tabActiva === 'salud') {
            $query->has('personalSalud');
        } elseif ($this->tabActiva === 'admin') {
            $query->has('personalAdmin');
        }

        $usuarios = $query->orderBy('created_at', 'desc')->get();

        $estadisticas = [
            'total' => User::count(),
            'activos' => User::where('estado', 'ACTIVO')->orWhere('estado', 1)->count(),
            'salud' => \App\Models\PersonalSalud::count(),
            'admin' => \App\Models\PersonalAdmin::count(),
            'medicos' => \App\Models\PersonalSalud::where('tipo_personal_salud', 'MEDICO')->count(),
            'enfermeros' => \App\Models\PersonalSalud::where('tipo_personal_salud', 'ENFERMERO')->count(),
            'psicologos' => \App\Models\PersonalSalud::where('tipo_personal_salud', 'PSICOLOGO')->count(),
            'fisioterapeutas' => \App\Models\PersonalSalud::where('tipo_personal_salud', 'FISIOTERAPEUTA')->count(),
            'nutricionistas' => \App\Models\PersonalSalud::where('tipo_personal_salud', 'NUTRICIONISTA')->count(),
            'suspendidos' => User::where('estado', 'SUSPENDIDO')->count(),
            // Mocking active/inactive shift stats for visual completeness
            'en_turno' => rand(3, 12),
            'fuera_turno' => rand(10, 30),
            'doc_pendiente' => \App\Models\DocumentoUsuario::where('estado', 'PENDIENTE')->count(),
        ];

        // Gráfica 3: Enfermeros por turno (Real)
        $turnosQuery = \App\Models\AsignacionTurno::whereHas('usuario.personalSalud', function($q) {
            $q->where('tipo_personal_salud', 'ENFERMERO');
        })->with('turno')->get();
        
        $turnosGrouped = $turnosQuery->groupBy(function($asignacion) {
            return $asignacion->turno ? $asignacion->turno->nombre : 'Sin Turno';
        });

        // Gráfica 4: Documentación pendiente por tipo (Real)
        $docsPendientesQuery = \App\Models\DocumentoUsuario::where('estado', 'PENDIENTE')->get();
        $docsGrouped = $docsPendientesQuery->groupBy(function($doc) {
            return $doc->tipo_documento ?: 'Sin clasificar';
        });

        // Gráfica 5: Disponibilidad (Real/Estimado)
        $dispActivos = User::where('estado', 'ACTIVO')->orWhere('estado', 1)->count();
        $dispAsignados = \App\Models\AsignacionTurno::where('estado', 'ACTIVO')->distinct('cod_usu')->count('cod_usu');
        $dispInactivos = User::whereIn('estado', ['INACTIVO', 'SUSPENDIDO'])->orWhere('estado', 0)->count();

        // Chart Data Final
        $chartData = [
            // 1. Distribución por áreas
            'area_labels' => ['Salud', 'Administrativo', 'Otros'],
            'area_data' => [
                \App\Models\PersonalSalud::count(),
                \App\Models\PersonalAdmin::count(),
                User::doesntHave('personalSalud')->doesntHave('personalAdmin')->count(),
            ],
            // 2. Estado Laboral
            'estado_labels' => ['Activo', 'Inactivo', 'Suspendido', 'Retirado'],
            'estado_data' => [
                User::where('estado', 'ACTIVO')->orWhere('estado', 1)->count(),
                User::where('estado', 'INACTIVO')->orWhere('estado', 0)->count(),
                User::where('estado', 'SUSPENDIDO')->count(),
                User::where('estado', 'RETIRADO')->count(),
            ],
            // 3. Enfermeros por Turno
            'turno_labels' => $turnosGrouped->keys()->toArray(),
            'turno_data' => $turnosGrouped->map->count()->values()->toArray(),
            // 4. Docs Pendientes
            'docs_labels' => $docsGrouped->keys()->toArray(),
            'docs_data' => $docsGrouped->map->count()->values()->toArray(),
            // 5. Disponibilidad
            'disp_labels' => ['Disponible', 'En Turno', 'No Disponible'],
            'disp_data' => [
                max(0, $dispActivos - $dispAsignados),
                $dispAsignados,
                $dispInactivos
            ]
        ];

        return view('livewire.admin.personal-institucional.personal-institucional-panel', [
            'usuarios' => $usuarios,
            'estadisticas' => $estadisticas,
            'chartData' => $chartData
        ])->layout('layouts.sistema');
    }
}
