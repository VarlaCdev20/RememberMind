<?php

namespace App\Livewire\Admin\SaludSeguimiento;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AdultoMayor;
use App\Models\AdministracionMedicacion;
use App\Models\FichaMedicaAdulto;
use App\Models\MedicacionAdulto;
use App\Models\ValoracionFuncionalAdulto;
use App\Models\SignosVitalesAdulto;
use App\Support\ClinicalAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SaludSeguimientoListPanel extends Component
{
    use WithPagination;

    public $search = '';

    protected $queryString = [
        'search' => ['except' => '']
    ];

    public string $seccionActiva = 'resumen';
    public ?AdultoMayor $adultoSeleccionadoParaModal = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function abrirExpediente(string $cod_am)
    {
        $adulto = AdultoMayor::query()
            ->visiblesClinicamentePara(Auth::user())
            ->where('cod_am', $cod_am)
            ->firstOrFail();

        Gate::authorize('viewClinicalData', $adulto);
        $this->adultoSeleccionadoParaModal = $adulto;
    }

    public function cerrarExpediente()
    {
        $this->adultoSeleccionadoParaModal = null;
    }

    public function mount()
    {
        if (request()->routeIs('*.ficha.index')) {
            $this->seccionActiva = 'ficha';
        } elseif (request()->routeIs('*.medicacion.index')) {
            $this->seccionActiva = 'medicacion';
        } elseif (request()->routeIs('*.administracion.index')) {
            $this->seccionActiva = 'administracion';
        } elseif (request()->routeIs('*.signos.index')) {
            $this->seccionActiva = 'signos';
        } elseif (request()->routeIs('*.valoracion.index')) {
            $this->seccionActiva = 'valoracion';
        } elseif (request()->routeIs('*.evaluaciones-geriatricas.index')) {
            $this->seccionActiva = 'evaluaciones';
        } elseif (request()->routeIs('*.alertas')) {
            $this->seccionActiva = 'alertas';
        } elseif (request()->routeIs('*.reportes')) {
            $this->seccionActiva = 'reportes';
        } else {
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
        $adultosBase = AdultoMayor::query()->visiblesClinicamentePara(Auth::user());

        $adultosActivos = (clone $adultosBase)->whereHas('estado', function ($query) {
            $query->whereNotIn('estado', ['ARCHIVADO', 'INACTIVO']);
        })->count();

        $adultosSinFicha = (clone $adultosBase)->whereHas('estado', function ($query) {
            $query->whereNotIn('estado', ['ARCHIVADO', 'INACTIVO']);
        })->whereDoesntHave('fichasMedicas', function ($query) {
            $query->whereIn('estado', ['ACTIVA', 'ACTIVO']);
        })->count();

        $signosConAlerta = $this->aplicarAlcanceCodAm(SignosVitalesAdulto::query(), 'cod_am')
            ->where('estado', 'VIGENTE')
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
            'total_fichas' => $this->aplicarAlcanceCodAm(FichaMedicaAdulto::query(), 'cod_am')->whereIn('estado', ['ACTIVA', 'ACTIVO'])->count(),
            'signos_recientes' => $this->aplicarAlcanceCodAm(SignosVitalesAdulto::query(), 'cod_am')
                ->where('estado', 'VIGENTE')
                ->where('fecha', '>=', now()->subDays(7)->toDateString())
                ->count(),
            'medicaciones_activas' => $this->aplicarAlcanceCodAm(MedicacionAdulto::query(), 'cod_am')->where('estado', 'ACTIVO')->count(),
            'controles_hoy' => $this->aplicarAlcanceCodAm(SignosVitalesAdulto::query(), 'cod_am')
                ->where('estado', 'VIGENTE')
                ->whereDate('fecha', today())
                ->count(),
            'valoraciones' => $this->aplicarAlcanceCodAm(ValoracionFuncionalAdulto::query(), 'cod_am')->where('fecha_valoracion', '>=', now()->subDays(30)->toDateString())->count(),
            'alertas_pendientes' => $adultosSinFicha + $signosConAlerta,
            'adultos_sin_ficha' => $adultosSinFicha,
        ];
    }

    public function getResumenDashboard(): array
    {
        $controlesRecientes = $this->aplicarAlcanceCodAm(SignosVitalesAdulto::with('adultoMayor'), 'cod_am')
            ->where('estado', 'VIGENTE')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->take(5)
            ->get();

        $medicaciones = $this->aplicarAlcanceCodAm(MedicacionAdulto::with('adultoMayor'), 'cod_am')
            ->where('estado', 'ACTIVO')
            ->latest()
            ->take(5)
            ->get();

        $valoraciones = $this->aplicarAlcanceCodAm(ValoracionFuncionalAdulto::with('adultoMayor'), 'cod_am')
            ->latest('fecha_valoracion')
            ->take(4)
            ->get();

        $alertasFicha = AdultoMayor::query()
            ->visiblesClinicamentePara(Auth::user())
            ->with('estado')
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

        $alertasSignos = $this->aplicarAlcanceCodAm(SignosVitalesAdulto::with('adultoMayor'), 'cod_am')
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
                ->when($this->codigosAdultosVisibles() !== null, fn ($query) => $query->whereIn('cod_am', $this->codigosAdultosVisibles()))
                ->whereDate('fecha', today())
                ->latest()
                ->take(5)
                ->get(),
        ];
    }

    public function render()
    {
        $query = AdultoMayor::query()
            ->visiblesClinicamentePara(Auth::user())
            ->with([
                'estado', 
                'fichasMedicas' => function($q) { $q->latest()->limit(1); },
                'medicaciones' => function($q) { $q->where('estado', 'ACTIVO'); },
                'valoracionesFuncionales' => function($q) { $q->latest('fecha_valoracion')->limit(1); },
                'signosVitales' => function($q) { $q->where('estado', 'VIGENTE')->latest('fecha')->limit(1); },
                'administracionesMedicacion' => function($q) { $q->latest('fecha')->limit(3); },
            ])
            ->where(function ($q) {
                $q->where('nombres', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ap_paterno', 'ilike', '%' . $this->search . '%')
                  ->orWhere('cod_am', 'ilike', '%' . $this->search . '%');
            });

        $adultos = $query->orderBy('ap_paterno')->paginate(12);
        
        return view('livewire.admin.salud-seguimiento.salud-seguimiento-list-panel', [
            'adultos' => $adultos,
            'contexto' => $this->getContext(),
            'stats' => $this->getGlobalStats(),
            'resumenData' => $this->getResumenDashboard(),
        ])->layout('layouts.sistema');
    }

    private function codigosAdultosVisibles(): ?array
    {
        return ClinicalAccess::visibleAdultCodes(Auth::user());
    }

    private function aplicarAlcanceCodAm(Builder $query, string $column): Builder
    {
        $codigos = $this->codigosAdultosVisibles();

        if ($codigos === null) {
            return $query;
        }

        return $query->whereIn($column, $codigos);
    }
}
