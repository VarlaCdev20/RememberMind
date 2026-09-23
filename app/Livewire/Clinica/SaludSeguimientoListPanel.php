<?php

namespace App\Livewire\Clinica;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AdultoMayor;
use App\Models\AdministracionMedicacion;
use App\Models\Prescripcion;
use App\Models\ValoracionFuncionalAdulto;
use App\Models\SignoVital;

class SaludSeguimientoListPanel extends Component
{
    use WithPagination;

    public $search = '';
    public string $filtroEstado = '';
    public string $seccionActiva = 'resumen';
    public ?AdultoMayor $adultoSeleccionadoParaModal = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filtroEstado' => ['except' => ''],
        'seccionActiva' => ['except' => 'resumen', 'as' => 'tab'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtroEstado = '';
        $this->resetPage();
    }

    public function limpiarFiltro(string $campo): void
    {
        if ($campo === 'search') {
            $this->search = '';
        } elseif ($campo === 'filtroEstado') {
            $this->filtroEstado = '';
        }
        $this->resetPage();
    }

    public function abrirExpediente(string $cod_am)
    {
        $this->adultoSeleccionadoParaModal = AdultoMayor::where('cod_am', $cod_am)->firstOrFail();
    }

    public function cerrarExpediente()
    {
        $this->adultoSeleccionadoParaModal = null;
    }

    public function mount()
    {
        $this->seccionActiva = request()->route('seccion') ?: $this->seccionActiva;
        $this->validarPermisoSeccion();
    }

    public function updatedSeccionActiva()
    {
        $this->validarPermisoSeccion();
        $this->resetPage();
    }

    protected function validarPermisoSeccion()
    {
        $user = auth()->user();
        if ($user->hasRole('SUPERADMINISTRADOR')) return;

        $permitido = match($this->seccionActiva) {
            'ficha' => $user->can('salud.ficha.ver') || $user->can('ficha_medica.crear'),
            'signos' => $user->can('salud.signos.ver') || $user->can('signos_vitales.ver'),
            'medicacion' => $user->can('salud.medicacion.ver') || $user->can('medicacion.ver'),
            'administracion' => $user->can('salud.medicacion.ver') || $user->can('administracion_medicacion.registrar'),
            'valoracion' => $user->can('salud.ver') || $user->can('valoracion_funcional.crear'),
            'evaluaciones' => $user->can('evaluaciones.ver'),
            'nutricion' => $user->can('nutricion.ver'),
            'alertas' => $user->can('salud.alertas.ver') || $user->can('alertas.ver'),
            'reportes' => $user->can('salud.reportes.ver') || $user->can('reportes.ver'),
            default => true, // resumen
        };

        if (!$permitido) {
            $this->seccionActiva = 'resumen';
        }
    }

    public function cambiarSeccion(string $seccion): void
    {
        $this->seccionActiva = $seccion;
        $this->resetPage();
    }

    public function getContext()
    {
        $context = [
            'titulo' => 'Resumen de salud',
            'descripcion' => 'Seleccione un adulto mayor para consultar su expediente de salud y seguimiento.',
            'boton' => 'Ver resumen de salud',
            'ruta_destino' => 'admin.salud-seguimiento.resumen',
            'icono' => 'ph-heartbeat',
        ];

        if ($this->seccionActiva === 'ficha') {
            $context = [
                'titulo' => 'Ficha médica',
                'descripcion' => 'Seleccione un adulto mayor para registrar, actualizar o consultar su ficha médica.',
                'boton' => 'Gestionar ficha médica',
                'ruta_destino' => 'admin.salud-seguimiento.ficha',
                'icono' => 'ph-file-text',
            ];
        } elseif ($this->seccionActiva === 'medicacion') {
            $context = [
                'titulo' => 'Medicación',
                'descripcion' => 'Seleccione un adulto mayor para gestionar su medicación registrada.',
                'boton' => 'Gestionar medicación',
                'ruta_destino' => 'admin.salud-seguimiento.medicacion',
                'icono' => 'ph-pill',
            ];
        } elseif ($this->seccionActiva === 'administracion') {
            $context = [
                'titulo' => 'Administración de medicación',
                'descripcion' => 'Seleccione un adulto mayor para registrar o consultar administraciones de medicación.',
                'boton' => 'Registrar administración',
                'ruta_destino' => 'admin.salud-seguimiento.administracion',
                'icono' => 'ph-prescription',
            ];
        } elseif ($this->seccionActiva === 'signos') {
            $context = [
                'titulo' => 'Signos vitales',
                'descripcion' => 'Seleccione un adulto mayor para registrar o revisar controles de signos vitales.',
                'boton' => 'Registrar signos vitales',
                'ruta_destino' => 'admin.salud-seguimiento.signos',
                'icono' => 'ph-activity',
            ];
        } elseif ($this->seccionActiva === 'valoracion') {
            $context = [
                'titulo' => 'Valoración funcional',
                'descripcion' => 'Seleccione un adulto mayor para registrar o consultar su autonomía, dependencia y riesgo funcional.',
                'boton' => 'Gestionar valoración funcional',
                'ruta_destino' => 'admin.salud-seguimiento.valoracion',
                'icono' => 'ph-person-simple-walk',
            ];
        } elseif ($this->seccionActiva === 'evaluaciones') {
            $context = [
                'titulo' => 'Evaluaciones geriátricas',
                'descripcion' => 'Seleccione un adulto mayor para registrar o consultar su evaluación multidimensional geriátrica.',
                'boton' => 'Gestionar evaluaciones',
                'ruta_destino' => 'admin.salud-seguimiento.evaluaciones-geriatricas',
                'icono' => 'ph-list-magnifying-glass',
            ];
        }

        return $context;
    }

    public function render()
    {
        $query = AdultoMayor::query()
            ->with([
                'fichasMedicas' => function($q) { $q->latest('fecha_hora')->limit(1); },
                'medicaciones' => function($q) { $q->whereIn('estado', ['ACTIVA', 'ACTIVO']); },
                'valoracionesFuncionales' => function($q) { $q->latest('fecha_hora')->limit(1); },
                'signosVitales' => function($q) { $q->where('estado', '!=', 'ANULADO')->latest('fecha_hora')->limit(1); },
                'administracionesMedicacion' => function($q) { $q->latest('fecha_hora_programada')->limit(3); },
            ])
            ->where(function ($q) {
                $term = trim($this->search);
                $q->where('nombres', 'like', "%{$term}%")
                  ->orWhere('apellido_paterno', 'like', "%{$term}%")
                  ->orWhere('apellido_materno', 'like', "%{$term}%")
                  ->orWhere('numero_documento', 'like', "%{$term}%");
            });

        if (!empty($this->filtroEstado)) {
            $query->where('estado', $this->filtroEstado);
        }

        $adultos = $query->orderBy('apellido_paterno')->paginate(12);

        $stats = [
            'seguimientos_activos' => AdultoMayor::whereNotIn('estado', ['ARCHIVADO', 'INACTIVO'])->count(),
            'total_fichas' => \App\Models\Atencion::count(),
            'signos_recientes' => \App\Models\SignoVital::where('fecha_hora', '>=', now()->subDays(7))->count(),
            'medicaciones_activas' => \App\Models\Prescripcion::whereIn('estado', ['ACTIVA', 'ACTIVO'])->count(),
            'controles_hoy' => \App\Models\SignoVital::whereDate('fecha_hora', today())->count(),
            'valoraciones' => \App\Models\ValoracionFuncional::where('fecha_hora', '>=', now()->subDays(30))->count(),
            'alertas_pendientes' => \App\Models\Alerta::whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->count(),
            'adultos_sin_ficha' => 0,
        ];
        
        return view('livewire.clinica.salud-seguimiento-list-panel', [
            'adultos' => $adultos,
            'contexto' => $this->getContext(),
            'stats' => $stats,
            'resumenData' => ['controlesRecientes' => collect(), 'medicaciones' => collect(), 'alertas' => collect()],
        ])->layout('layouts.sistema');
    }
}
