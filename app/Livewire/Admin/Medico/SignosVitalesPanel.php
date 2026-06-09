<?php

namespace App\Livewire\Admin\Medico;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\SignosVitalesAdulto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SignosVitalesPanel extends Component
{
    public string $busqueda     = '';
    public string $filtroAlerta = ''; // '' | 'critico' | 'advertencia' | 'normal'
    public bool   $soloHoy      = false;

    protected $queryString = ['busqueda', 'filtroAlerta'];

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

    public function updatingBusqueda(): void { /* resetPage no aplica sin paginación */ }

    public function setFiltro(string $nivel): void
    {
        $this->filtroAlerta = ($this->filtroAlerta === $nivel) ? '' : $nivel;
    }

    public function abrirRegistroSignos(string $codAm): void
    {
        $this->dispatch('abrir-signos-vitales-medico', cod_am: $codAm);
    }

    public function abrirFicha(string $codAm): void
    {
        $this->redirect(route('admin.medico.paciente.ficha', $codAm));
    }

    // ── Clasificador de alerta por signo ─────────────────────────────────

    public static function alertaPA(?int $sist, ?int $diast): string
    {
        if (!$sist) return 'sin_dato';
        if ($sist >= 180 || $diast >= 110) return 'critico';
        if ($sist >= 160 || $sist < 90 || $diast >= 100) return 'advertencia';
        return 'normal';
    }

    public static function alertaFC(?int $fc): string
    {
        if (!$fc) return 'sin_dato';
        if ($fc >= 130 || $fc < 40) return 'critico';
        if ($fc >= 100 || $fc < 55) return 'advertencia';
        return 'normal';
    }

    public static function alertaFR(?int $fr): string
    {
        if (!$fr) return 'sin_dato';
        if ($fr >= 28 || $fr < 8) return 'critico';
        if ($fr >= 22 || $fr < 12) return 'advertencia';
        return 'normal';
    }

    public static function alertaTemp(?float $temp): string
    {
        if (!$temp) return 'sin_dato';
        if ($temp >= 39.5 || $temp < 35.0) return 'critico';
        if ($temp >= 38.0 || $temp < 36.0) return 'advertencia';
        return 'normal';
    }

    public static function alertaSat(?int $sat): string
    {
        if (!$sat) return 'sin_dato';
        if ($sat < 88) return 'critico';
        if ($sat < 92) return 'advertencia';
        return 'normal';
    }

    public static function alertaGluc(?float $gluc): string
    {
        if (!$gluc) return 'sin_dato';
        if ($gluc < 50 || $gluc >= 400) return 'critico';
        if ($gluc < 70 || $gluc >= 250) return 'advertencia';
        return 'normal';
    }

    public static function nivelGlobal(?int $sist, ?int $diast, ?int $fc, ?int $fr, ?float $temp, ?int $sat, ?float $gluc): string
    {
        $niveles = [
            self::alertaPA($sist, $diast),
            self::alertaFC($fc),
            self::alertaFR($fr),
            self::alertaTemp($temp),
            self::alertaSat($sat),
            self::alertaGluc($gluc),
        ];
        if (in_array('critico', $niveles))     return 'critico';
        if (in_array('advertencia', $niveles)) return 'advertencia';
        return 'normal';
    }

    public function render()
    {
        $estadosActivos = ['ACTIVO', 'ADMITIDO', 'ASIGNADO', 'EN_SEGUIMIENTO_ACTIVO', 'OBSERVADO', 'SEGUIMIENTO_ESPECIAL'];
        $hoy = now()->toDateString();

        // ── Todos los pacientes activos ────────────────────────────────────
        $queryBase = AdultoMayor::with(['estado'])
            ->whereHas('estado', fn($q) => $q->whereIn('estado', $estadosActivos))
            ->whereNull('archivado_en');

        if ($this->busqueda) {
            $b = $this->busqueda;
            $queryBase->where(fn($q) =>
                $q->where('nombres', 'like', "%{$b}%")
                  ->orWhere('ap_paterno', 'like', "%{$b}%")
                  ->orWhere('ci', 'like', "%{$b}%")
            );
        }

        $todosLosPacientes = $queryBase->orderBy('nombres')->get();
        $codAms = $todosLosPacientes->pluck('cod_am')->toArray();

        // ── Último signo vital por paciente ────────────────────────────────
        $ultimosSignos = SignosVitalesAdulto::whereIn('cod_am', $codAms)
            ->where('estado', 'VIGENTE')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->get()
            ->unique('cod_am')
            ->keyBy('cod_am');

        // ── KPIs de monitoreo global ───────────────────────────────────────
        $conSignos = $ultimosSignos->values();
        $kpiPaMedia  = $conSignos->filter(fn($sv) => $sv->presion_sistolica > 0)->avg('presion_sistolica');
        $kpiFcMedia  = $conSignos->filter(fn($sv) => $sv->frecuencia_cardiaca > 0)->avg('frecuencia_cardiaca');
        $kpiSatMedia = $conSignos->filter(fn($sv) => $sv->saturacion > 0)->avg('saturacion');

        $kpiCriticos    = 0;
        $kpiAdvertencia = 0;
        $kpiNormales    = 0;
        $kpiSinDatos    = 0;

        foreach ($todosLosPacientes as $pac) {
            $sv = $ultimosSignos[$pac->cod_am] ?? null;
            if (!$sv) { $kpiSinDatos++; continue; }
            $nivel = self::nivelGlobal(
                $sv->presion_sistolica, $sv->presion_diastolica,
                $sv->frecuencia_cardiaca, $sv->frecuencia_respiratoria,
                $sv->temperatura ? (float)$sv->temperatura : null,
                $sv->saturacion,
                $sv->glucosa ? (float)$sv->glucosa : null
            );
            match($nivel) {
                'critico'     => $kpiCriticos++,
                'advertencia' => $kpiAdvertencia++,
                default       => $kpiNormales++,
            };
        }

        $kpiSinRegistroHoy = $todosLosPacientes->filter(function($pac) use ($ultimosSignos, $hoy) {
            $sv = $ultimosSignos[$pac->cod_am] ?? null;
            return !$sv || (string)$sv->fecha !== $hoy;
        })->count();

        // ── Filtro por nivel de alerta ─────────────────────────────────────
        $pacientesFiltrados = $todosLosPacientes;
        if ($this->filtroAlerta !== '') {
            $pacientesFiltrados = $todosLosPacientes->filter(function($pac) use ($ultimosSignos) {
                $sv = $ultimosSignos[$pac->cod_am] ?? null;
                if (!$sv) return $this->filtroAlerta === 'sin_dato';
                $nivel = self::nivelGlobal(
                    $sv->presion_sistolica, $sv->presion_diastolica,
                    $sv->frecuencia_cardiaca, $sv->frecuencia_respiratoria,
                    $sv->temperatura ? (float)$sv->temperatura : null,
                    $sv->saturacion,
                    $sv->glucosa ? (float)$sv->glucosa : null
                );
                return $nivel === $this->filtroAlerta;
            })->values();
        }

        // Ordenar: críticos primero, luego advertencia, luego normal
        $pacientesFiltrados = $pacientesFiltrados->sortBy(function($pac) use ($ultimosSignos) {
            $sv = $ultimosSignos[$pac->cod_am] ?? null;
            if (!$sv) return 99;
            $nivel = self::nivelGlobal(
                $sv->presion_sistolica, $sv->presion_diastolica,
                $sv->frecuencia_cardiaca, $sv->frecuencia_respiratoria,
                $sv->temperatura ? (float)$sv->temperatura : null,
                $sv->saturacion,
                $sv->glucosa ? (float)$sv->glucosa : null
            );
            return match($nivel) { 'critico' => 0, 'advertencia' => 1, default => 2 };
        })->values();

        // ── Tendencia 7 días ───────────────────────────────────────────────
        $tendencia7d = SignosVitalesAdulto::where('fecha', '>=', now()->subDays(6)->toDateString())
            ->where('estado', 'VIGENTE')
            ->select(
                DB::raw('DATE(fecha) as dia'),
                DB::raw('ROUND(AVG(presion_sistolica)::numeric, 0) as avg_pa'),
                DB::raw('ROUND(AVG(saturacion)::numeric, 1) as avg_sat'),
                DB::raw('ROUND(AVG(frecuencia_cardiaca)::numeric, 0) as avg_fc'),
                DB::raw('ROUND(AVG(CASE WHEN glucosa > 0 THEN glucosa ELSE NULL END)::numeric, 0) as avg_gluc')
            )
            ->groupBy(DB::raw('DATE(fecha)'))
            ->orderBy(DB::raw('DATE(fecha)'))
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

        return view('livewire.admin.medico.signos-vitales-panel', compact(
            'pacientesFiltrados', 'ultimosSignos', 'hoy',
            'kpiPaMedia', 'kpiFcMedia', 'kpiSatMedia',
            'kpiCriticos', 'kpiAdvertencia', 'kpiNormales', 'kpiSinDatos', 'kpiSinRegistroHoy',
            'chartTendencia7d', 'chartDistPA'
        ))->layout('layouts.sistema');
    }
}
