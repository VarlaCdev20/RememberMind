<?php

namespace App\Livewire\Clinica;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AdultoMayor;
use App\Models\AdministracionMedicacion;
use App\Models\FichaMedicaAdulto;
use App\Models\MedicacionAdulto;
use App\Models\ValoracionFuncionalAdulto;
use App\Models\SignosVitalesAdulto;

class SaludSeguimientoListPanel extends Component
{
    use WithPagination;

    public $search = '';
    public string $seccionActiva = 'resumen';
    public ?AdultoMayor $adultoSeleccionadoParaModal = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'seccionActiva' => ['except' => 'resumen', 'as' => 'tab'],
    ];

    public function updatingSearch()
    {
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
        // Validación de permisos por tab. 
        // Si el usuario entra directamente a un tab sin permiso, lo regresamos al que sí pueda ver o a resumen.
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

    public function getGlobalStats()
    {
        $adultosActivos = AdultoMayor::whereHas('estado', function ($query) {
            $query->whereNotIn('estado', ['ARCHIVADO', 'INACTIVO']);
        })->count();

        $adultosSinFicha = AdultoMayor::whereHas('estado', function ($query) {
            $query->whereNotIn('estado', ['ARCHIVADO', 'INACTIVO']);
        })->whereDoesntHave('fichasMedicas', function ($query) {
            $query->whereIn('estado', ['ACTIVA', 'ACTIVO']);
        })->count();

        $signosConAlerta = SignosVitalesAdulto::where('estado', 'VIGENTE')
            ->where(function ($query) {
                $query->where('fecha', '>=', now()->subDays(7)->toDateString())
                    ->where(function ($subQuery) {
                        $subQuery->where('saturacion', '<', 92)
                            ->orWhere('temperatura', '>=', 38)
                            ->orWhere('presion_sistolica', '>=', 140)
                            ->orWhere('presion_sistolica', '<=', 90);
                    });
            })
            ->count();

        return [
            'seguimientos_activos' => $adultosActivos,
            'total_fichas' => FichaMedicaAdulto::whereIn('estado', ['ACTIVA', 'ACTIVO'])->count(),
            'signos_recientes' => SignosVitalesAdulto::where('estado', 'VIGENTE')
                ->where('fecha', '>=', now()->subDays(7)->toDateString())
                ->count(),
            'medicaciones_activas' => MedicacionAdulto::where('estado', 'ACTIVO')->count(),
            'controles_hoy' => SignosVitalesAdulto::where('estado', 'VIGENTE')
                ->whereDate('fecha', today())
                ->count(),
            'valoraciones' => ValoracionFuncionalAdulto::where('fecha_valoracion', '>=', now()->subDays(30)->toDateString())->count(),
            'alertas_pendientes' => $adultosSinFicha + $signosConAlerta,
            'adultos_sin_ficha' => $adultosSinFicha,
        ];
    }

    public function getResumenDashboard(): array
    {
        $controlesRecientes = SignosVitalesAdulto::with('adultoMayor')
            ->where('estado', 'VIGENTE')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->take(5)
            ->get();

        $medicaciones = MedicacionAdulto::with('adultoMayor')
            ->where('estado', 'ACTIVO')
            ->latest()
            ->take(5)
            ->get();

        $valoraciones = ValoracionFuncionalAdulto::with('adultoMayor')
            ->latest('fecha_valoracion')
            ->take(4)
            ->get();

        $alertasFicha = AdultoMayor::with('estado')
            ->whereHas('estado', function ($query) {
                $query->whereNotIn('estado', ['ARCHIVADO', 'INACTIVO']);
            })
            ->whereDoesntHave('fichasMedicas', function ($query) {
                $query->whereIn('estado', ['ACTIVA', 'ACTIVO']);
            })
            ->orderBy('ap_paterno')
            ->take(3)
            ->get()
            ->map(fn ($adulto) => [
                'tipo' => 'Ficha médica',
                'nivel' => 'preventiva',
                'icono' => 'ph-file-dashed',
                'titulo' => trim("{$adulto->nombres} {$adulto->ap_paterno}"),
                'detalle' => 'Expediente clínico base pendiente.',
                'adulto_id' => $adulto->cod_am,
            ]);

        $alertasSignos = SignosVitalesAdulto::with('adultoMayor')
            ->where('estado', 'VIGENTE')
            ->where('fecha', '>=', now()->subDays(7)->toDateString())
            ->where(function ($query) {
                $query->where('saturacion', '<', 92)
                    ->orWhere('temperatura', '>=', 38)
                    ->orWhere('presion_sistolica', '>=', 140)
                    ->orWhere('presion_sistolica', '<=', 90);
            })
            ->orderByDesc('fecha')
            ->take(3)
            ->get()
            ->map(fn ($signo) => [
                'tipo' => 'Signos vitales',
                'nivel' => 'importante',
                'icono' => 'ph-warning-circle',
                'titulo' => trim(($signo->adultoMayor?->nombres ?? 'Adulto mayor') . ' ' . ($signo->adultoMayor?->ap_paterno ?? '')),
                'detalle' => 'Control reciente con valor fuera del rango orientativo.',
                'adulto_id' => $signo->cod_am,
            ]);

        return [
            'controlesRecientes' => $controlesRecientes,
            'medicaciones' => $medicaciones,
            'valoraciones' => $valoraciones,
            'alertas' => $alertasFicha->concat($alertasSignos)->take(5),
            'administracionesHoy' => AdministracionMedicacion::with(['adultoMayor', 'medicacion'])
                ->whereDate('fecha', today())
                ->latest()
                ->take(5)
                ->get(),
        ];
    }

    public function render()
    {
        $query = AdultoMayor::query()
            ->with([
                'estado', 
                'fichasMedicas' => function($q) { $q->latest()->limit(1); },
                'medicaciones' => function($q) { $q->where('estado', 'ACTIVO'); },
                'valoracionesFuncionales' => function($q) { $q->latest('fecha_valoracion')->limit(1); },
                'signosVitales' => function($q) { $q->where('estado', 'VIGENTE')->latest('fecha')->limit(1); },
                'administracionesMedicacion' => function($q) { $q->latest('fecha')->limit(3); },
            ])
            ->where(function ($q) {
                $q->whereLike('nombres', '%' . $this->search . '%')
                  ->orWhereLike('ap_paterno', '%' . $this->search . '%')
                  ->orWhereLike('cod_am', '%' . $this->search . '%');
            });

        $adultos = $query->orderBy('ap_paterno')->paginate(12);
        
        return view('livewire.clinica.salud-seguimiento-list-panel', [
            'adultos' => $adultos,
            'contexto' => $this->getContext(),
            'stats' => $this->getGlobalStats(),
            'resumenData' => $this->getResumenDashboard(),
        ])->layout('layouts.sistema');
    }
}
