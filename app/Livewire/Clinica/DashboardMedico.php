<?php

namespace App\Livewire\Clinica;

use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\AdultoMayor;
use App\Models\SignosVitalesAdulto;
use App\Models\FichaMedicaAdulto;
use App\Models\ValoracionFuncionalAdulto;
use App\Models\NotaEvolucionMedica;
use App\Models\MedicacionAdulto;

class DashboardMedico extends Component
{
    // Sección activa (viene del querystring: ?seccion=valoraciones | decisiones | dashboard)
    public string $seccion = 'dashboard';
    protected $queryString = ['seccion'];

    // Cola de admisiones pendientes
    public $valoracionesPendientes = [];

    // KPIs
    public int $totalResidentes       = 0;
    public int $pendientesValoracion  = 0;
    public int $enSeguimientoActivo   = 0;
    public int $alertasCriticas       = 0;
    public int $notasHoy              = 0;
    public int $signosHoy             = 0;
    public int $totalMedicacionActiva = 0;

    // Datos para gráficos (arrays → @js en la vista)
    public array $chartEdad         = [];
    public array $chartDiagnosticos = [];
    public array $chartDependencia  = [];
    public array $chartImc          = [];
    public array $chartTendencia    = [];
    public array $chartEstados      = [];
    public array $chartNotasTipo    = [];

    // Vitales agregados para el panel de monitoreo
    public float|int $kpiPaMedia        = 0;
    public float|int $kpiFcMedia        = 0;
    public float|int $kpiSatMedia       = 0;
    public int $kpiSinRegistroHoy       = 0;
    public int $kpiAdvertenciaSignos    = 0;

    // Últimos signos registrados (tabla compacta en dashboard)
    public array $ultimosSignosDash = [];

    // Listas operativas
    public array $alertasPacientes = [];
    public array $notasRecientes   = [];

    protected $listeners = [
        'valoracionMedicaCompletada' => '$refresh',
        'nota-evolucion-guardada'    => '$refresh',
        'signos-actualizados'        => '$refresh',
    ];

    public function mount(): void
    {
        if (!auth()->user()->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA'])) {
            abort(403);
        }

        // Detectar sección según ruta de acceso (sin necesitar querystring manual)
        $routeName = request()->route()?->getName() ?? '';
        if ($routeName === 'admin.medico.valoraciones') {
            $this->seccion = 'valoraciones';
        } elseif ($routeName === 'admin.medico.decisiones') {
            $this->seccion = 'decisiones';
        }
    }

    public function render()
    {
        $estadosActivos    = ['ACTIVO', 'ADMITIDO', 'ASIGNADO', 'EN_SEGUIMIENTO_ACTIVO', 'OBSERVADO', 'SEGUIMIENTO_ESPECIAL'];
        $estadosPendientes = ['PENDIENTE_VALORACION_MEDICA', 'DECISION_ADMISION', 'VALORACION_MEDICA'];
        $hoy               = now()->toDateString();

        // ── Cola de valoraciones pendientes ──────────────────────────
        $this->valoracionesPendientes = AdultoMayor::whereHas('estado', fn($q) => $q->whereIn('estado', $estadosPendientes))
            ->with(['estado'])
            ->orderBy('created_at', 'desc')
            ->get();

        // ── KPIs ─────────────────────────────────────────────────────
        $this->totalResidentes      = AdultoMayor::whereHas('estado', fn($q) => $q->whereIn('estado', $estadosActivos))->count();
        $this->pendientesValoracion = $this->valoracionesPendientes->count();
        $this->enSeguimientoActivo  = AdultoMayor::whereHas('estado', fn($q) => $q->where('estado', 'EN_SEGUIMIENTO_ACTIVO'))->count();
        $this->totalMedicacionActiva = MedicacionAdulto::where('estado', 'ACTIVO')->count();
        $this->notasHoy  = NotaEvolucionMedica::where('fecha', $hoy)->where('estado', 'ACTIVO')->count();
        $this->signosHoy = SignosVitalesAdulto::where('fecha', $hoy)->where('estado', 'VIGENTE')->count();

        $this->alertasCriticas = SignosVitalesAdulto::where('estado', 'VIGENTE')
            ->where(fn($q) => $q
                ->where('presion_sistolica', '>', 160)
                ->orWhere('presion_sistolica', '<', 90)
                ->orWhere('saturacion', '<', 92)
            )
            ->distinct('cod_am')
            ->count('cod_am');

        // ── Datos de gráficos ─────────────────────────────────────────
        $this->chartEdad         = $this->computeEdad($estadosActivos);
        $this->chartDiagnosticos = $this->computeDiagnosticos();
        $this->chartDependencia  = $this->computeDependencia();
        $this->chartImc          = $this->computeImc();
        $this->chartTendencia    = $this->computeTendencia();
        $this->chartEstados      = $this->computeEstados();
        $this->chartNotasTipo    = $this->computeNotasTipo();

        // ── Alertas críticas de signos vitales ────────────────────────
        $this->alertasPacientes = SignosVitalesAdulto::where('estado', 'VIGENTE')
            ->where(fn($q) => $q
                ->where('presion_sistolica', '>', 160)
                ->orWhere('presion_sistolica', '<', 90)
                ->orWhere('saturacion', '<', 92)
            )
            ->with('adultoMayor:cod_am,nombres,ap_paterno')
            ->orderByDesc('fecha')
            ->get()
            ->unique('cod_am')
            ->take(8)
            ->map(fn($sv) => [
                'nombre' => trim(($sv->adultoMayor->nombres ?? '—') . ' ' . ($sv->adultoMayor->ap_paterno ?? '')),
                'cod_am' => $sv->cod_am,
                'pa'     => $sv->presion_sistolica ? "{$sv->presion_sistolica}/{$sv->presion_diastolica}" : '—',
                'sat'    => $sv->saturacion,
                'gluc'   => $sv->glucosa ? number_format((float)$sv->glucosa, 0) : null,
                'fecha'  => $sv->fecha instanceof Carbon ? $sv->fecha->format('d/m') : \Carbon\Carbon::parse($sv->fecha)->format('d/m'),
                'tipo'   => ($sv->presion_sistolica > 160) ? 'PA elevada'
                          : (($sv->presion_sistolica < 90 && $sv->presion_sistolica > 0) ? 'PA baja' : 'SpO2 baja'),
            ])->values()->toArray();

        // ── Notas médicas recientes ────────────────────────────────────
        $this->notasRecientes = NotaEvolucionMedica::where('estado', 'ACTIVO')
            ->with(['adulto:cod_am,nombres,ap_paterno', 'registrador:cod_usu,name'])
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->limit(6)
            ->get()
            ->map(fn($n) => [
                'tipo'     => $n->tipo_nota,
                'paciente' => trim(($n->adulto->nombres ?? '—') . ' ' . ($n->adulto->ap_paterno ?? '')),
                'cod_am'   => $n->cod_am,
                'plan'     => \Str::limit($n->plan ?? '', 90),
                'valoracion' => \Str::limit($n->valoracion ?? '', 70),
                'fecha'    => $n->fecha instanceof Carbon ? $n->fecha->format('d/m/Y') : \Carbon\Carbon::parse($n->fecha)->format('d/m/Y'),
                'hora'     => $n->hora ? substr((string)$n->hora, 0, 5) : '',
                'medico'   => $n->registrador->name ?? 'Sistema',
            ])->toArray();

        return view('livewire.clinica.dashboard-medico')
            ->layout('layouts.sistema');
    }

    // ── Métodos privados de cómputo de gráficos ───────────────────────

    private function computeEdad(array $estadosActivos): array
    {
        $adultos = AdultoMayor::whereHas('estado', fn($q) => $q->whereIn('estado', $estadosActivos))
            ->whereNotNull('fecha_nac')
            ->get(['fecha_nac', 'genero']);

        $g = [
            '60-69' => ['M' => 0, 'F' => 0],
            '70-79' => ['M' => 0, 'F' => 0],
            '80-89' => ['M' => 0, 'F' => 0],
            '90+'   => ['M' => 0, 'F' => 0],
        ];

        foreach ($adultos as $am) {
            $edad = Carbon::parse($am->fecha_nac)->age;
            $sexo = (strtoupper(substr($am->genero ?? 'M', 0, 1)) === 'F') ? 'F' : 'M';
            if ($edad >= 90)     $g['90+'][$sexo]++;
            elseif ($edad >= 80) $g['80-89'][$sexo]++;
            elseif ($edad >= 70) $g['70-79'][$sexo]++;
            else                 $g['60-69'][$sexo]++;
        }

        return [
            'labels'    => ['60-69 años', '70-79 años', '80-89 años', '90+ años'],
            'masculino' => array_column($g, 'M'),
            'femenino'  => array_column($g, 'F'),
        ];
    }

    private function computeDiagnosticos(): array
    {
        $fichas = FichaMedicaAdulto::where('estado', 'ACTIVO')
            ->get(['hipertension', 'diabetes', 'problemas_cardiacos', 'acv',
                   'parkinson', 'epilepsia', 'alzheimer_diagnosticado',
                   'depresion', 'ansiedad', 'dolor_cronico']);

        $map = [
            'hipertension'            => 'Hipertensión',
            'diabetes'                => 'Diabetes',
            'problemas_cardiacos'     => 'Cardiopatía',
            'acv'                     => 'ACV / Ictus',
            'alzheimer_diagnosticado' => 'Alzheimer',
            'parkinson'               => 'Parkinson',
            'depresion'               => 'Depresión',
            'dolor_cronico'           => 'Dolor crónico',
            'ansiedad'                => 'Ansiedad',
            'epilepsia'               => 'Epilepsia',
        ];

        $counts = [];
        foreach ($map as $campo => $label) {
            $counts[$label] = $fichas->where($campo, true)->count();
        }
        arsort($counts);

        return [
            'labels' => array_keys($counts),
            'values' => array_values($counts),
        ];
    }

    private function computeDependencia(): array
    {
        $datos = ValoracionFuncionalAdulto::select('nivel_dependencia', DB::raw('COUNT(*) as total'))
            ->whereNotNull('nivel_dependencia')
            ->where('nivel_dependencia', '!=', '')
            ->groupBy('nivel_dependencia')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $datos->pluck('nivel_dependencia')->toArray(),
            'values' => $datos->pluck('total')->map(fn($v) => (int)$v)->toArray(),
        ];
    }

    private function computeImc(): array
    {
        $imcs = SignosVitalesAdulto::where('estado', 'VIGENTE')
            ->whereNotNull('imc')
            ->where('imc', '>', 0)
            ->pluck('imc')
            ->map(fn($v) => (float)$v);

        $cats = [
            'Bajo peso'   => 0,
            'Normal'      => 0,
            'Sobrepeso'   => 0,
            'Obesidad I'  => 0,
            'Obesidad II+'=> 0,
        ];

        foreach ($imcs as $v) {
            if ($v < 18.5)     $cats['Bajo peso']++;
            elseif ($v < 25)   $cats['Normal']++;
            elseif ($v < 30)   $cats['Sobrepeso']++;
            elseif ($v < 35)   $cats['Obesidad I']++;
            else               $cats['Obesidad II+']++;
        }

        return [
            'labels' => array_keys($cats),
            'values' => array_values($cats),
        ];
    }

    private function computeTendencia(): array
    {
        $datos = SignosVitalesAdulto::where('fecha', '>=', now()->subDays(30)->toDateString())
            ->select(
                DB::raw('DATE(fecha) as dia'),
                DB::raw('ROUND(AVG(presion_sistolica)::numeric, 0) as avg_pa'),
                DB::raw('ROUND(AVG(CASE WHEN saturacion > 0 THEN saturacion ELSE NULL END)::numeric, 1) as avg_sat'),
                DB::raw('ROUND(AVG(CASE WHEN glucosa > 0 THEN glucosa ELSE NULL END)::numeric, 0) as avg_gluc')
            )
            ->groupBy(DB::raw('DATE(fecha)'))
            ->orderBy(DB::raw('DATE(fecha)'))
            ->get();

        return [
            'labels' => $datos->pluck('dia')
                ->map(fn($d) => Carbon::parse($d)->format('d/m'))->toArray(),
            'pa'   => $datos->pluck('avg_pa')
                ->map(fn($v) => $v !== null ? (int)$v : null)->toArray(),
            'sat'  => $datos->pluck('avg_sat')
                ->map(fn($v) => $v !== null ? (float)$v : null)->toArray(),
            'gluc' => $datos->pluck('avg_gluc')
                ->map(fn($v) => $v !== null ? (int)$v : null)->toArray(),
        ];
    }

    private function computeEstados(): array
    {
        $datos = DB::table('adulto_mayor as am')
            ->join('estado_adulto as ea', 'am.cod_est_adul', '=', 'ea.cod_est_adul')
            ->select('ea.estado', DB::raw('COUNT(*) as total'))
            ->groupBy('ea.estado')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $datos->pluck('estado')
                ->map(fn($e) => str_replace('_', ' ', $e))->toArray(),
            'values' => $datos->pluck('total')
                ->map(fn($v) => (int)$v)->toArray(),
        ];
    }

    private function computeNotasTipo(): array
    {
        $tipos = [
            'EVOLUCION'     => 'Evolución',
            'INGRESO'       => 'Ingreso',
            'EGRESO'        => 'Egreso',
            'INTERCONSULTA' => 'Interconsulta',
            'URGENCIA'      => 'Urgencia',
            'PROCEDIMIENTO' => 'Procedimiento',
        ];

        $datos = NotaEvolucionMedica::where('estado', 'ACTIVO')
            ->select('tipo_nota', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo_nota')
            ->get()
            ->keyBy('tipo_nota');

        $labels = [];
        $values = [];
        foreach ($tipos as $key => $label) {
            $labels[] = $label;
            $values[] = (int)($datos[$key]->total ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    // ── Acciones ──────────────────────────────────────────────────────

    public function iniciarValoracionMedica(string $cod_am): void
    {
        $this->dispatch('abrirValoracionMedica', $cod_am);
    }

    public function abrirDecisionAdmision(string $cod_am): void
    {
        $this->dispatch('abrirDecisionAdmision', $cod_am);
    }
}
