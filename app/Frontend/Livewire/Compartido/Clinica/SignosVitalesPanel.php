<?php

namespace App\Frontend\Livewire\Compartido\Clinica;

use App\Backend\Modulos\Clinica\Servicios\ClasificacionSignosVitalesService;
use Livewire\Component;
use App\Models\Residente;
use App\Models\SignoVital;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SignosVitalesPanel extends Component
{
    public string $busqueda       = '';
    public string $filtroAlerta   = ''; // '' | 'critico' | 'advertencia' | 'normal'
    public string $filtroRegistro = ''; // '' | 'con_registro_hoy' | 'sin_registro_hoy'
    public bool   $soloHoy        = false;

    protected $queryString = ['busqueda', 'filtroAlerta', 'filtroRegistro'];

    protected $listeners = [
        'signos-actualizados' => '$refresh',
        'signos-vitales-guardados' => '$refresh',
    ];

    public function mount(): void
    {
        if (!auth()->user()->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA'])) {
            abort(403);
        }
    }

    public function updatingBusqueda(): void { /* no paginate */ }

    public function setFiltro(string $nivel): void
    {
        $this->filtroAlerta = ($this->filtroAlerta === $nivel) ? '' : $nivel;
    }

    public function limpiarFiltros(): void
    {
        $this->busqueda = '';
        $this->filtroAlerta = '';
        $this->filtroRegistro = '';
    }

    public function limpiarFiltro(string $campo): void
    {
        if ($campo === 'busqueda') {
            $this->busqueda = '';
        } elseif ($campo === 'filtroAlerta') {
            $this->filtroAlerta = '';
        } elseif ($campo === 'filtroRegistro') {
            $this->filtroRegistro = '';
        }
    }

    public function abrirRegistroSignos(string $codResidente): void
    {
        $this->dispatch('abrir-signos-vitales-medico', cod_residente: $codResidente);
    }

    public function abrirFicha(string $codResidente): void
    {
        $this->redirect(route('admin.medico.paciente.ficha', $codResidente));
    }

    public function render()
    {
        $hoy = today()->toDateString();

        $queryBase = Residente::query()
            ->whereIn('estado', ['ACTIVO', 'ADMITIDO', 'ASIGNADO', 'EN_SEGUIMIENTO_ACTIVO', 'OBSERVADO', 'SEGUIMIENTO_ESPECIAL']);

        if ($this->busqueda) {
            $b = trim($this->busqueda);
            $queryBase->where(fn($q) =>
                $q->where('nombres', 'like', "%{$b}%")
                  ->orWhere('apellido_paterno', 'like', "%{$b}%")
                  ->orWhere('apellido_materno', 'like', "%{$b}%")
                  ->orWhere('numero_documento', 'like', "%{$b}%")
            );
        }

        $todosLosPacientes = $queryBase->orderBy('nombres')->get();
        $codResidentes = $todosLosPacientes->pluck('cod_residente')->toArray();

        // ── Último signo vital por paciente ──────────────────────────────
        $ultimosSignos = SignoVital::whereIn('cod_residente', $codResidentes)
            ->with('residente.objetivosSignosVitales')
            ->whereIn('estado', ['VIGENTE', 'ACTIVO'])
            ->orderByDesc('fecha_hora')
            ->get()
            ->unique('cod_residente')
            ->keyBy('cod_residente');

        $nivelesPorResidente = $ultimosSignos->map(
            fn (SignoVital $signo) => ClasificacionSignosVitalesService::evaluarRegistro($signo)
        );
        $leyendaAvisos = ClasificacionSignosVitalesService::leyendaAvisos();

        // ── KPIs de monitoreo global ──────────────────────────────────────
        $conSignos = $ultimosSignos->values();
        $kpiPaMedia  = $conSignos->filter(fn($sv) => $sv->presion_sistolica > 0)->avg('presion_sistolica');
        $kpiFcMedia  = $conSignos->filter(fn($sv) => $sv->frecuencia_cardiaca > 0)->avg('frecuencia_cardiaca');
        $kpiSatMedia = $conSignos->filter(fn($sv) => $sv->saturacion > 0)->avg('saturacion');

        $kpiCriticos    = 0;
        $kpiAdvertencia = 0;
        $kpiNormales    = 0;
        $kpiSinDatos    = 0;

        foreach ($todosLosPacientes as $pac) {
            $sv = $ultimosSignos[$pac->cod_residente] ?? null;
            if (!$sv) { $kpiSinDatos++; continue; }
            $nivel = $nivelesPorResidente[$pac->cod_residente]['global'];
            match($nivel) {
                'critico'     => $kpiCriticos++,
                'advertencia' => $kpiAdvertencia++,
                'normal'      => $kpiNormales++,
                default       => $kpiSinDatos++,
            };
        }

        $kpiSinRegistroHoy = $todosLosPacientes->filter(function($pac) use ($ultimosSignos, $hoy) {
            $sv = $ultimosSignos[$pac->cod_residente] ?? null;
            return !$sv || (string)$sv->fecha !== $hoy;
        })->count();

        // ── Filtro por nivel de alerta ────────────────────────────────────
        $pacientesFiltrados = $todosLosPacientes;
        if ($this->filtroAlerta !== '') {
            $pacientesFiltrados = $pacientesFiltrados->filter(function($pac) use ($nivelesPorResidente) {
                $nivel = $nivelesPorResidente[$pac->cod_residente]['global'] ?? 'sin_dato';
                return $nivel === $this->filtroAlerta;
            })->values();
        }

        // ── Filtro por registro de hoy ───────────────────────────────────
        if ($this->filtroRegistro === 'con_registro_hoy') {
            $pacientesFiltrados = $pacientesFiltrados->filter(function($pac) use ($ultimosSignos, $hoy) {
                $sv = $ultimosSignos[$pac->cod_residente] ?? null;
                return $sv && (string)$sv->fecha === $hoy;
            })->values();
        } elseif ($this->filtroRegistro === 'sin_registro_hoy') {
            $pacientesFiltrados = $pacientesFiltrados->filter(function($pac) use ($ultimosSignos, $hoy) {
                $sv = $ultimosSignos[$pac->cod_residente] ?? null;
                return !$sv || (string)$sv->fecha !== $hoy;
            })->values();
        }

        // Ordenar: críticos primero, luego advertencia, luego normal
        $pacientesFiltrados = $pacientesFiltrados->sortBy(function($pac) use ($nivelesPorResidente) {
            $nivel = $nivelesPorResidente[$pac->cod_residente]['global'] ?? 'sin_dato';
            return match($nivel) { 'critico' => 0, 'advertencia' => 1, 'normal' => 2, default => 99 };
        })->values();

        // ── Tendencia 7 días ──────────────────────────────────────────────
        $tendencia7d = SignoVital::where('fecha_hora', '>=', now()->subDays(6)->toDateString())
            ->whereIn('estado', ['VIGENTE', 'ACTIVO'])
            ->select(
                DB::raw('DATE(fecha_hora) as dia'),
                DB::raw('ROUND(CAST(AVG(presion_sistolica) AS DECIMAL(12,2)), 0) as avg_pa'),
                DB::raw('ROUND(CAST(AVG(saturacion_oxigeno) AS DECIMAL(12,2)), 1) as avg_sat'),
                DB::raw('ROUND(CAST(AVG(frecuencia_cardiaca) AS DECIMAL(12,2)), 0) as avg_fc'),
                DB::raw('ROUND(CAST(AVG(CASE WHEN glucemia > 0 THEN glucemia ELSE NULL END) AS DECIMAL(12,2)), 0) as avg_gluc')
            )
            ->groupBy(DB::raw('DATE(fecha_hora)'))
            ->orderBy(DB::raw('DATE(fecha_hora)'))
            ->get();

        $chartTendencia7d = [
            'labels' => $tendencia7d->pluck('dia')->map(fn($d) => Carbon::parse($d)->format('d/m'))->toArray(),
            'pa'   => $tendencia7d->pluck('avg_pa')->map(fn($v) => $v !== null ? (int)$v : null)->toArray(),
            'sat'  => $tendencia7d->pluck('avg_sat')->map(fn($v) => $v !== null ? (float)$v : null)->toArray(),
            'fc'   => $tendencia7d->pluck('avg_fc')->map(fn($v) => $v !== null ? (int)$v : null)->toArray(),
            'gluc' => $tendencia7d->pluck('avg_gluc')->map(fn($v) => $v !== null ? (int)$v : null)->toArray(),
        ];

        // ── Distribución de PA (sistólica) ────────────────────────────────
        $distPA = [
            'Hipotensión (<90)' => $conSignos->filter(fn($sv) => $sv->presion_sistolica > 0 && $sv->presion_sistolica < 90)->count(),
            'Normal (90-139)'   => $conSignos->filter(fn($sv) => $sv->presion_sistolica >= 90 && $sv->presion_sistolica < 140)->count(),
            'Pre-HTA (140-159)' => $conSignos->filter(fn($sv) => $sv->presion_sistolica >= 140 && $sv->presion_sistolica < 160)->count(),
            'HTA I (160-179)'   => $conSignos->filter(fn($sv) => $sv->presion_sistolica >= 160 && $sv->presion_sistolica < 180)->count(),
            'HTA II (≥180)'     => $conSignos->filter(fn($sv) => $sv->presion_sistolica >= 180)->count(),
        ];

        $chartDistPA = [
            'labels' => array_keys($distPA),
            'values' => array_values($distPA),
        ];

        return view('livewire.clinica.signos-vitales-panel', compact(
            'pacientesFiltrados', 'ultimosSignos', 'nivelesPorResidente', 'leyendaAvisos', 'hoy',
            'kpiPaMedia', 'kpiFcMedia', 'kpiSatMedia',
            'kpiCriticos', 'kpiAdvertencia', 'kpiNormales', 'kpiSinDatos', 'kpiSinRegistroHoy',
            'chartTendencia7d', 'chartDistPA'
        ))->layout('layouts.sistema');
    }
}
