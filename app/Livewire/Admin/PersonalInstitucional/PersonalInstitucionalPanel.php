<?php

namespace App\Livewire\Admin\PersonalInstitucional;

use App\Models\AsignacionTurno;
use App\Models\DocumentoUsuario;
use App\Models\PersonalAdmin;
use App\Models\PersonalSalud;
use App\Models\User;
use Livewire\Component;

class PersonalInstitucionalPanel extends Component
{
    public $tabActiva = 'resumen';
    public $busqueda = '';
    
    // Filtros adicionales
    public $filtroTipo = '';
    public $filtroRol = '';
    public $filtroEstado = '';
    public $filtroDisponibilidad = '';
    
    // Modal state
    public $modalGestionAbierto = false;
    public $usuarioSeleccionadoId = null;
    public string $modalTabInicial = 'informacion';
    public bool $abrirFormularioHorarioInicial = false;

    protected $listeners = [
        'cerrarModalGestion' => 'cerrarModal',
        'actualizarTablaPersonal' => '$refresh',
        'asignacionActualizada' => '$refresh',
        'abrirHorariosPersonal' => 'abrirHorariosPersonal',
        'personalRegistradoParaHorario' => 'continuarHorariosNuevoPersonal',
    ];

    public function abrirModalNuevo()
    {
        $this->usuarioSeleccionadoId = null;
        $this->modalTabInicial = 'informacion';
        $this->abrirFormularioHorarioInicial = false;
        $this->modalGestionAbierto = true;
    }

    public function abrirModalEdicion($usuarioId)
    {
        $this->usuarioSeleccionadoId = $usuarioId;
        $this->modalTabInicial = 'informacion';
        $this->abrirFormularioHorarioInicial = false;
        $this->modalGestionAbierto = true;
    }

    public function abrirHorariosPersonal(string $usuarioId): void
    {
        $usuarioExiste = User::where('cod_usu', $usuarioId)
            ->where(fn ($query) => $query->has('personalSalud')->orHas('personalAdmin'))
            ->exists();

        if (!$usuarioExiste) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Personal no disponible',
                'text' => 'No se encontró un registro institucional válido para asignar horarios.',
            ]);
            return;
        }

        $this->usuarioSeleccionadoId = $usuarioId;
        $this->modalTabInicial = 'horarios';
        $this->abrirFormularioHorarioInicial = false;
        $this->modalGestionAbierto = true;
    }

    public function continuarHorariosNuevoPersonal(string $usuarioId): void
    {
        $this->abrirHorariosPersonal($usuarioId);

        if ($this->modalGestionAbierto) {
            $this->abrirFormularioHorarioInicial = true;
        }
    }

    public function abrirModuloHorarios(): void
    {
        $this->cerrarModal();
        $this->tabActiva = 'horarios';
    }

    public function cerrarModal()
    {
        $this->modalGestionAbierto = false;
        $this->usuarioSeleccionadoId = null;
        $this->modalTabInicial = 'informacion';
        $this->abrirFormularioHorarioInicial = false;
    }
    
    public function toggleEstado($usuarioId)
    {
        $usuario = User::find($usuarioId);
        if ($usuario) {
            if ($usuario->estado === 'ACTIVO' || $usuario->estado == 1) {
                $usuario->estado = 'SUSPENDIDO';
            } else {
                $usuario->estado = 'ACTIVO';
            }
            $usuario->save();
            $this->dispatch('swal', ['icon' => 'success', 'title' => 'Estado actualizado']);
            $this->dispatch('actualizarTablaPersonal');
        }
    }
    
    public function setTab($tab)
    {
        $this->tabActiva = $tab;
    }

    public function render()
    {
        $query = User::with([
                'personalSalud.especialidad',
                'personalAdmin',
                'areaInstitucional',
                'roles',
                'asignacionesTurno.turno',
            ])
            ->withCount([
                'documentos as documentos_pendientes_count' => fn ($query) => $query->where('estado', 'PENDIENTE'),
            ])
            ->where(function($q) {
                $q->where('nombres', 'ilike', '%' . $this->busqueda . '%')
                  ->orWhere('ap_paterno', 'ilike', '%' . $this->busqueda . '%')
                  ->orWhere('correo', 'ilike', '%' . $this->busqueda . '%')
                  ->orWhere('cod_usu', 'ilike', '%' . $this->busqueda . '%');
            })
            ->where(function($q) {
                $q->has('personalSalud')->orHas('personalAdmin');
            });

        if ($this->tabActiva === 'salud') {
            $query->has('personalSalud');
        } elseif ($this->tabActiva === 'admin') {
            $query->has('personalAdmin');
        }

        if ($this->filtroTipo === 'salud') {
            $query->has('personalSalud');
        } elseif ($this->filtroTipo === 'admin') {
            $query->has('personalAdmin');
        }

        if ($this->filtroRol) {
            $query->whereHas('personalSalud', function($q) {
                $q->where('tipo_personal_salud', strtoupper($this->filtroRol));
            });
        }

        if ($this->filtroEstado) {
            if ($this->filtroEstado === 'activo') {
                $query->where(function($q) { $q->where('estado', 'ACTIVO')->orWhere('estado', 1); });
            } elseif ($this->filtroEstado === 'suspendido') {
                $query->where('estado', 'SUSPENDIDO');
            } elseif ($this->filtroEstado === 'inactivo') {
                $query->where(function($q) { $q->where('estado', 'INACTIVO')->orWhere('estado', 0); });
            }
        }

        if ($this->filtroDisponibilidad === 'ocupado') {
            $query->whereHas('asignacionesTurno', function($q) {
                $q->where('estado', 'ACTIVA');
            });
        } elseif ($this->filtroDisponibilidad === 'libre') {
            $query->whereDoesntHave('asignacionesTurno', function($q) {
                $q->where('estado', 'ACTIVA');
            });
        }

        $usuarios = in_array($this->tabActiva, ['resumen', 'salud', 'admin'], true)
            ? $query->orderBy('created_at', 'desc')->get()
            : collect();

        $estadisticas = [];
        $chartData = [];

        if ($this->tabActiva === 'resumen') {
            $baseInstitucional = User::where(function($q) {
                $q->has('personalSalud')->orHas('personalAdmin');
            });

            $totalUsuarios = (clone $baseInstitucional)->count();
            $totalActivos = (clone $baseInstitucional)->where(function($q){ $q->where('estado', 'ACTIVO')->orWhere('estado', 1); })->count();
            $totalInactivos = (clone $baseInstitucional)->where(function($q){ $q->where('estado', 'INACTIVO')->orWhere('estado', 0); })->count();
            $totalSuspendidos = (clone $baseInstitucional)->where('estado', 'SUSPENDIDO')->count();
            $totalRetirados = (clone $baseInstitucional)->where('estado', 'RETIRADO')->count();
            $totalSalud = PersonalSalud::count();
            $totalAdmin = PersonalAdmin::count();
            $totalDocsPendientes = DocumentoUsuario::where('estado', 'PENDIENTE')->whereIn('cod_usu', (clone $baseInstitucional)->select('cod_usu'))->count();
            $totalEnTurno = min(
                $totalActivos,
                AsignacionTurno::whereIn('estado', ['ACTIVO', 'ACTIVA'])
                    ->distinct('cod_usu')
                    ->count('cod_usu')
            );
            $totalFueraTurno = max(0, $totalActivos - $totalEnTurno);

            $estadisticas = [
                'total' => $totalUsuarios,
                'activos' => $totalActivos,
                'salud' => $totalSalud,
                'admin' => $totalAdmin,
                'medicos' => PersonalSalud::where('tipo_personal_salud', 'MEDICO')->count(),
                'enfermeros' => PersonalSalud::where('tipo_personal_salud', 'ENFERMERO')->count(),
                'psicologos' => PersonalSalud::where('tipo_personal_salud', 'PSICOLOGO')->count(),
                'fisioterapeutas' => PersonalSalud::where('tipo_personal_salud', 'FISIOTERAPEUTA')->count(),
                'nutricionistas' => PersonalSalud::where('tipo_personal_salud', 'NUTRICIONISTA')->count(),
                'suspendidos' => $totalSuspendidos,
                'en_turno' => $totalEnTurno,
                'fuera_turno' => $totalFueraTurno,
                'doc_pendiente' => $totalDocsPendientes,
            ];

            $turnosGrouped = AsignacionTurno::whereHas('usuario.personalSalud', function($query) {
                    $query->where('tipo_personal_salud', 'ENFERMERO');
                })
                ->with('turno')
                ->get()
                ->groupBy(fn ($asignacion) => $asignacion->turno?->nombre ?? 'Sin Turno');

            $docsGrouped = DocumentoUsuario::where('estado', 'PENDIENTE')
                ->get()
                ->groupBy(fn ($documento) => $documento->tipo_documento ?: 'Sin clasificar');

            $chartData = [
                'area_labels' => ['Salud', 'Administrativo'],
                'area_data' => [
                    $totalSalud,
                    $totalAdmin,
                ],
                'estado_labels' => ['Activo', 'Inactivo', 'Suspendido', 'Retirado'],
                'estado_data' => [
                    $totalActivos,
                    $totalInactivos,
                    $totalSuspendidos,
                    $totalRetirados,
                ],
                'turno_labels' => $turnosGrouped->keys()->toArray(),
                'turno_data' => $turnosGrouped->map->count()->values()->toArray(),
                'docs_labels' => $docsGrouped->keys()->toArray(),
                'docs_data' => $docsGrouped->map->count()->values()->toArray(),
                'disp_labels' => ['Disponible', 'En Turno', 'No Disponible'],
                'disp_data' => [
                    $totalFueraTurno,
                    $totalEnTurno,
                    $totalInactivos + $totalSuspendidos,
                ],
            ];
        }

        return view('livewire.admin.personal-institucional.personal-institucional-panel', [
            'usuarios' => $usuarios,
            'estadisticas' => $estadisticas,
            'chartData' => $chartData
        ])->layout('layouts.sistema');
    }
}
