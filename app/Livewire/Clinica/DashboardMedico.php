<?php

namespace App\Livewire\Clinica;

use App\Models\AdultoMayor;
use App\Models\Alerta;
use App\Models\NotaClinica;
use App\Models\Prescripcion;
use App\Models\SignoVital;
use App\Models\ValoracionFuncional;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardMedico extends Component
{
    public string $seccion = 'dashboard';
    protected $queryString = ['seccion'];
    public $valoracionesPendientes = [];
    public int $totalResidentes = 0;
    public int $pendientesValoracion = 0;
    public int $enSeguimientoActivo = 0;
    public int $alertasCriticas = 0;
    public int $notasHoy = 0;
    public int $signosHoy = 0;
    public int $totalMedicacionActiva = 0;
    public array $chartEdad = [];
    public array $chartDiagnosticos = [];
    public array $chartDependencia = [];
    public array $chartImc = [];
    public array $chartTendencia = [];
    public array $chartEstados = [];
    public array $chartNotasTipo = [];
    public float|int $kpiPaMedia = 0;
    public float|int $kpiFcMedia = 0;
    public float|int $kpiSatMedia = 0;
    public int $kpiSinRegistroHoy = 0;
    public int $kpiAdvertenciaSignos = 0;
    public array $ultimosSignosDash = [];
    public array $alertasPacientes = [];
    public array $notasRecientes = [];

    protected $listeners = [
        'valoracionMedicaCompletada' => '$refresh',
        'nota-evolucion-guardada' => '$refresh',
        'signos-actualizados' => '$refresh',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA']), 403);
        $routeName = request()->route()?->getName() ?? '';
        if ($routeName === 'admin.medico.valoraciones') $this->seccion = 'valoraciones';
        if ($routeName === 'admin.medico.decisiones') $this->seccion = 'decisiones';
    }

    public function render()
    {
        $this->cargarDatosV2();
        return view('livewire.clinica.dashboard-medico')->layout('layouts.sistema');
    }

    private function cargarDatosV2(): void
    {
        $pendientes = ['PENDIENTE_VALORACION_MEDICA', 'DECISION_ADMISION', 'VALORACION_MEDICA'];
        $this->valoracionesPendientes = AdultoMayor::query()
            ->whereIn('estado', $pendientes)
            ->orderBy('apellido_paterno')
            ->get();
        $this->totalResidentes = AdultoMayor::query()->whereNotIn('estado', ['INACTIVO', 'EGRESADO', 'FALLECIDO'])->count();
        $this->pendientesValoracion = $this->valoracionesPendientes->count();
        $this->enSeguimientoActivo = AdultoMayor::query()->whereIn('estado', ['ACTIVO', 'ADMITIDO', 'EN_SEGUIMIENTO_ACTIVO'])->count();
        $this->totalMedicacionActiva = Prescripcion::query()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->count();
        $this->notasHoy = NotaClinica::query()->whereDate('fecha_hora', today())->count();
        $this->signosHoy = SignoVital::query()->whereDate('fecha_hora', today())->count();
        $this->alertasCriticas = Alerta::query()->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->whereIn('prioridad', ['ALTA', 'CRITICA', 'CRITICO'])->count();

        $dependencia = ValoracionFuncional::query()
            ->select('nivel_dependencia', DB::raw('COUNT(*) as total'))
            ->whereNotNull('nivel_dependencia')
            ->groupBy('nivel_dependencia')
            ->orderByDesc('total')
            ->get();
        $this->chartDependencia = [
            'labels' => $dependencia->pluck('nivel_dependencia')->all(),
            'values' => $dependencia->pluck('total')->map(fn ($value) => (int) $value)->all(),
        ];

        $estados = AdultoMayor::query()->select('estado', DB::raw('COUNT(*) as total'))->groupBy('estado')->get();
        $this->chartEstados = ['labels' => $estados->pluck('estado')->all(), 'values' => $estados->pluck('total')->map(fn ($value) => (int) $value)->all()];
        $this->chartEdad = ['labels' => [], 'masculino' => [], 'femenino' => []];
        $this->chartDiagnosticos = ['labels' => [], 'values' => []];
        $this->chartImc = ['labels' => [], 'values' => []];
        $this->chartTendencia = ['labels' => [], 'pa' => [], 'fc' => [], 'sat' => []];

        $tiposNota = NotaClinica::query()->select('tipo_nota', DB::raw('COUNT(*) as total'))->groupBy('tipo_nota')->get();
        $this->chartNotasTipo = ['labels' => $tiposNota->pluck('tipo_nota')->all(), 'values' => $tiposNota->pluck('total')->map(fn ($value) => (int) $value)->all()];

        $this->ultimosSignosDash = SignoVital::query()->with('residente')->latest('fecha_hora')->limit(8)->get()->map(fn ($signo) => [
            'nombre' => $signo->residente?->nombre_completo ?? 'Residente',
            'cod_am' => $signo->cod_residente,
            'pa' => $signo->presion_sistolica ? "{$signo->presion_sistolica}/{$signo->presion_diastolica}" : '—',
            'sat' => $signo->saturacion_oxigeno,
            'gluc' => $signo->glucemia,
            'fecha' => $signo->fecha_hora?->format('d/m'),
        ])->all();
        $this->alertasPacientes = [];
        $this->notasRecientes = NotaClinica::query()->with(['residente', 'personal'])->latest('fecha_hora')->limit(6)->get()->map(fn ($nota) => [
            'tipo' => $nota->tipo_nota,
            'paciente' => $nota->residente?->nombre_completo ?? 'Residente',
            'cod_am' => $nota->cod_residente,
            'plan' => str($nota->contenido)->limit(90)->toString(),
            'valoracion' => str($nota->contenido)->limit(70)->toString(),
            'fecha' => $nota->fecha_hora?->format('d/m/Y'),
            'hora' => $nota->fecha_hora?->format('H:i'),
            'medico' => $nota->personal?->usuario?->name ?? 'Sistema',
        ])->all();
    }

    public function iniciarValoracionMedica(string $cod_am): void
    {
        $this->dispatch('abrirValoracionMedica', $cod_am);
    }

    public function abrirDecisionAdmision(string $cod_am): void
    {
        $this->dispatch('abrirDecisionAdmision', $cod_am);
    }
}
