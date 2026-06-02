<?php

namespace App\Livewire\Admin\Actividades;

use App\Models\ActividadAdulto;
use App\Models\TipoActividadAdulto;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class ReportesActividadesPanel extends Component
{
    // ── Filtros ───────────────────────────────────────────────────────────────
    public string $fechaDesde          = '';
    public string $fechaHasta          = '';
    public string $filtroTipo          = '';
    public string $filtroCategoria     = '';
    public string $filtroEstado        = '';
    public string $filtroAsistencia    = '';
    public string $filtroNivel         = '';
    public string $buscar              = '';
    public bool   $soloConSeguimiento  = false;
    public bool   $soloConIncidencias  = false;
    public bool   $soloEvaluadas       = false;

    // ── Paleta institucional ─────────────────────────────────────────────────
    private const COLORES_ESTADO = [
        'BORRADOR'    => '#9CA3AF',
        'PROGRAMADA'  => '#4A90D9',
        'PENDIENTE'   => '#4A90D9',
        'EN_CURSO'    => '#8DA280',
        'REPROGRAMADA'=> '#D9A05B',
        'REALIZADA'   => '#2A9D8F',
        'COMPLETADA'  => '#2A9D8F',
        'FINALIZADA'  => '#2A9D8F',
        'EVALUADA'    => '#5A8A70',
        'CANCELADA'   => '#E27D60',
        'ANULADA'     => '#E27D60',
    ];

    // ── Hooks de actualización ────────────────────────────────────────────────

    public function updated(string $property): void
    {
        $this->dispatch('charts-data-updated', payload: $this->buildChartData());
    }

    public function limpiarFiltros(): void
    {
        $this->fechaDesde           = '';
        $this->fechaHasta           = '';
        $this->filtroTipo           = '';
        $this->filtroCategoria      = '';
        $this->filtroEstado         = '';
        $this->filtroAsistencia     = '';
        $this->filtroNivel          = '';
        $this->buscar               = '';
        $this->soloConSeguimiento   = false;
        $this->soloConIncidencias   = false;
        $this->soloEvaluadas        = false;
        $this->dispatch('charts-data-updated', payload: $this->buildChartData());
        $this->dispatch('swal', ['icon' => 'info', 'title' => 'Filtros eliminados. Mostrando todos los datos.']);
    }

    // ── Queries base ──────────────────────────────────────────────────────────

    private function queryBase(): Builder
    {
        return ActividadAdulto::query()
            ->when($this->fechaDesde, fn($q) => $q->whereDate('fecha', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn($q) => $q->whereDate('fecha', '<=', $this->fechaHasta))
            ->when($this->filtroTipo, fn($q) => $q->where('cod_tipo_act', (int) $this->filtroTipo))
            ->when($this->filtroCategoria, fn($q) =>
                $q->whereHas('tipoActividad', fn($sq) =>
                    $sq->where('categoria', $this->filtroCategoria)
                )
            )
            ->when($this->soloConIncidencias, fn($q) => $q->whereNotNull('incidencias'))
            ->when($this->soloEvaluadas, fn($q) => $q->where('estado', 'EVALUADA'))
            ->when($this->buscar, fn($q) =>
                $q->where(fn($sq) =>
                    $sq->where('nombre', 'ilike', '%' . $this->buscar . '%')
                      ->orWhereHas('tipoActividad', fn($ta) =>
                          $ta->where('tipo', 'ilike', '%' . $this->buscar . '%')
                      )
                      ->orWhere('lugar', 'ilike', '%' . $this->buscar . '%')
                )
            );
    }

    private function queryFiltrada(): Builder
    {
        $q = $this->queryBase()
            ->when($this->filtroEstado, function ($q) {
                return match ($this->filtroEstado) {
                    'PROGRAMADA'   => $q->whereIn('estado', ['PROGRAMADA', 'PENDIENTE']),
                    'EN_CURSO'     => $q->where('estado', 'EN_CURSO'),
                    'REALIZADA'    => $q->whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA']),
                    'EVALUADA'     => $q->where('estado', 'EVALUADA'),
                    'CANCELADA'    => $q->whereIn('estado', ['CANCELADA', 'ANULADA']),
                    'REPROGRAMADA' => $q->where('estado', 'REPROGRAMADA'),
                    default        => $q,
                };
            });

        // Filtro por asistencia/nivel requiere JOIN con participantes
        if ($this->filtroAsistencia || $this->filtroNivel || $this->soloConSeguimiento) {
            $q->whereHas('participantes', function ($pq) {
                if ($this->filtroAsistencia) {
                    $pq->where('estado_asistencia', $this->filtroAsistencia);
                }
                if ($this->filtroNivel) {
                    $pq->where('nivel_participacion', $this->filtroNivel);
                }
                if ($this->soloConSeguimiento) {
                    $pq->where('requiere_seguimiento', true);
                }
            });
        }

        return $q;
    }

    // ── KPIs ──────────────────────────────────────────────────────────────────

    private function getStats(): array
    {
        if (! Schema::hasTable('actividades_adulto')) {
            return array_fill_keys([
                'total','programadas','en_curso','realizadas','evaluadas',
                'canceladas','reprogramadas','adultos','tipos','periodo',
            ], 0);
        }

        $base = $this->queryBase();

        return [
            'total'         => (clone $base)->count(),
            'programadas'   => (clone $base)->whereIn('estado', ['PROGRAMADA', 'PENDIENTE'])->count(),
            'en_curso'      => (clone $base)->where('estado', 'EN_CURSO')->count(),
            'realizadas'    => (clone $base)->whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA'])->count(),
            'evaluadas'     => (clone $base)->where('estado', 'EVALUADA')->count(),
            'canceladas'    => (clone $base)->whereIn('estado', ['CANCELADA', 'ANULADA'])->count(),
            'reprogramadas' => (clone $base)->where('estado', 'REPROGRAMADA')->count(),
            'con_incidencias'=> (clone $base)->whereNotNull('incidencias')->count(),
            'pendientes_evaluacion' => (clone $base)
                ->whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA'])
                ->whereNull('evaluacion_final')
                ->count(),
            'adultos'       => (clone $base)->whereNotNull('cod_am')->distinct('cod_am')->count('cod_am'),
            'tipos'         => (clone $base)->distinct('cod_tipo_act')->count('cod_tipo_act'),
            'periodo'       => $this->describePeriodo(),
        ];
    }

    private function getKPIsParticipacion(): array
    {
        if (! Schema::hasTable('actividad_participantes')) {
            return ['total_inscritos' => 0, 'asistencias' => 0, 'faltas' => 0, 'justificados' => 0,
                    'seguimiento' => 0, 'tasa_asistencia' => 0, 'nivel_alta' => 0, 'nivel_media' => 0, 'nivel_baja' => 0];
        }

        // Ids de actividades bajo filtro actual
        $actividadesIds = $this->queryBase()->pluck('cod_act_adul');

        $row = DB::table('actividad_participantes')
            ->whereNull('deleted_at')
            ->whereIn('cod_act_adul', $actividadesIds)
            ->selectRaw("
                COUNT(DISTINCT cod_am) AS total_inscritos,
                SUM(CASE WHEN estado_asistencia = 'ASISTIO'     THEN 1 ELSE 0 END) AS asistencias,
                SUM(CASE WHEN estado_asistencia = 'FALTO'       THEN 1 ELSE 0 END) AS faltas,
                SUM(CASE WHEN estado_asistencia = 'JUSTIFICADO' THEN 1 ELSE 0 END) AS justificados,
                SUM(CASE WHEN requiere_seguimiento = true        THEN 1 ELSE 0 END) AS seguimiento,
                SUM(CASE WHEN nivel_participacion = 'ALTA'       THEN 1 ELSE 0 END) AS nivel_alta,
                SUM(CASE WHEN nivel_participacion = 'MEDIA'      THEN 1 ELSE 0 END) AS nivel_media,
                SUM(CASE WHEN nivel_participacion = 'BAJA'       THEN 1 ELSE 0 END) AS nivel_baja,
                COUNT(*) AS total_participaciones
            ")
            ->first();

        $total = (int) ($row->total_participaciones ?? 0);
        $asistencias = (int) ($row->asistencias ?? 0);
        $tasa = $total > 0 ? round(($asistencias / $total) * 100, 1) : 0;

        return [
            'total_inscritos'    => (int) ($row->total_inscritos ?? 0),
            'asistencias'        => $asistencias,
            'faltas'             => (int) ($row->faltas ?? 0),
            'justificados'       => (int) ($row->justificados ?? 0),
            'seguimiento'        => (int) ($row->seguimiento ?? 0),
            'tasa_asistencia'    => $tasa,
            'nivel_alta'         => (int) ($row->nivel_alta ?? 0),
            'nivel_media'        => (int) ($row->nivel_media ?? 0),
            'nivel_baja'         => (int) ($row->nivel_baja ?? 0),
        ];
    }

    // ── Datos Chart.js ────────────────────────────────────────────────────────

    private function getChartEstadoJS(): array
    {
        if (! Schema::hasTable('actividades_adulto')) {
            return ['labels' => [], 'data' => [], 'colors' => []];
        }
        $rows = $this->queryBase()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')->orderByDesc('total')->get();

        return [
            'labels' => $rows->map(fn($r) => ActividadAdulto::normalizarEstado($r->estado)['etiqueta'])->values()->all(),
            'data'   => $rows->pluck('total')->map(fn($v) => (int) $v)->values()->all(),
            'colors' => $rows->map(fn($r) => self::COLORES_ESTADO[strtoupper($r->estado ?? '')] ?? '#C7B5A3')->values()->all(),
        ];
    }

    private function getChartTipoJS(): array
    {
        if (! Schema::hasTable('actividades_adulto')) {
            return ['labels' => [], 'data' => [], 'colors' => []];
        }
        $rows = DB::table('actividades_adulto as aa')
            ->leftJoin('tipo_actividades_adulto as ta', 'aa.cod_tipo_act', '=', 'ta.cod_tipo_act')
            ->whereNull('aa.deleted_at')
            ->when($this->fechaDesde, fn($q) => $q->where('aa.fecha', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn($q) => $q->where('aa.fecha', '<=', $this->fechaHasta))
            ->when($this->filtroTipo, fn($q) => $q->where('aa.cod_tipo_act', (int) $this->filtroTipo))
            ->select('ta.tipo', DB::raw('COUNT(*) as total'))
            ->groupBy('ta.tipo')->orderByDesc('total')->limit(8)->get();

        $palette = ['#4A90D9','#8DA280','#D9A05B','#E27D60','#7A68B0','#2A9D8F','#5A8A70','#9CA3AF'];

        return [
            'labels' => $rows->pluck('tipo')->map(fn($t) => $t ?? 'Sin tipo')->values()->all(),
            'data'   => $rows->pluck('total')->map(fn($v) => (int) $v)->values()->all(),
            'colors' => array_slice($palette, 0, $rows->count()),
        ];
    }

    private function getChartMesJS(): array
    {
        if (! Schema::hasTable('actividades_adulto')) {
            return ['labels' => [], 'data' => [], 'anio' => now()->year];
        }
        $q = DB::table('actividades_adulto')->whereNull('deleted_at')
            ->select(DB::raw("TO_CHAR(fecha,'MM') as mes"), DB::raw("TO_CHAR(fecha,'Mon') as mes_nombre"), DB::raw('COUNT(*) as total'))
            ->groupByRaw("TO_CHAR(fecha,'MM'), TO_CHAR(fecha,'Mon')")->orderByRaw("TO_CHAR(fecha,'MM')");

        if ($this->fechaDesde)  $q->where('fecha', '>=', $this->fechaDesde);
        if ($this->fechaHasta)  $q->where('fecha', '<=', $this->fechaHasta);
        if ($this->filtroTipo)  $q->where('cod_tipo_act', (int) $this->filtroTipo);
        if (! $this->fechaDesde && ! $this->fechaHasta) $q->whereYear('fecha', now()->year);

        $rows = $q->get();
        return [
            'labels' => $rows->pluck('mes_nombre')->values()->all(),
            'data'   => $rows->pluck('total')->map(fn($v) => (int) $v)->values()->all(),
            'anio'   => now()->year,
        ];
    }

    private function getChartAsistenciaJS(): array
    {
        if (! Schema::hasTable('actividad_participantes')) {
            return ['labels' => [], 'asistio' => [], 'falto' => [], 'justificado' => []];
        }

        $rows = DB::table('actividad_participantes as ap')
            ->join('actividades_adulto as aa', 'ap.cod_act_adul', '=', 'aa.cod_act_adul')
            ->whereNull('ap.deleted_at')->whereNull('aa.deleted_at')
            ->when($this->fechaDesde, fn($q) => $q->where('aa.fecha', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn($q) => $q->where('aa.fecha', '<=', $this->fechaHasta))
            ->when(! $this->fechaDesde && ! $this->fechaHasta, fn($q) => $q->whereYear('aa.fecha', now()->year))
            ->selectRaw("
                TO_CHAR(aa.fecha, 'Mon') as mes,
                TO_CHAR(aa.fecha, 'MM') as mes_num,
                SUM(CASE WHEN ap.estado_asistencia = 'ASISTIO'     THEN 1 ELSE 0 END) as asistio,
                SUM(CASE WHEN ap.estado_asistencia = 'FALTO'       THEN 1 ELSE 0 END) as falto,
                SUM(CASE WHEN ap.estado_asistencia = 'JUSTIFICADO' THEN 1 ELSE 0 END) as justificado
            ")
            ->groupByRaw("TO_CHAR(aa.fecha,'Mon'), TO_CHAR(aa.fecha,'MM')")
            ->orderByRaw("TO_CHAR(aa.fecha,'MM')")
            ->get();

        return [
            'labels'     => $rows->pluck('mes')->values()->all(),
            'asistio'    => $rows->pluck('asistio')->map(fn($v) => (int) $v)->values()->all(),
            'falto'      => $rows->pluck('falto')->map(fn($v) => (int) $v)->values()->all(),
            'justificado'=> $rows->pluck('justificado')->map(fn($v) => (int) $v)->values()->all(),
        ];
    }

    private function getChartNivelJS(): array
    {
        if (! Schema::hasTable('actividad_participantes')) {
            return ['labels' => ['Alta', 'Media', 'Baja', 'No aplica'], 'data' => [0, 0, 0, 0], 'colors' => []];
        }
        $actIds = $this->queryBase()->pluck('cod_act_adul');
        $row = DB::table('actividad_participantes')->whereNull('deleted_at')->whereIn('cod_act_adul', $actIds)
            ->selectRaw("
                SUM(CASE WHEN nivel_participacion = 'ALTA'     THEN 1 ELSE 0 END) as alta,
                SUM(CASE WHEN nivel_participacion = 'MEDIA'    THEN 1 ELSE 0 END) as media,
                SUM(CASE WHEN nivel_participacion = 'BAJA'     THEN 1 ELSE 0 END) as baja,
                SUM(CASE WHEN nivel_participacion = 'NO_APLICA' THEN 1 ELSE 0 END) as no_aplica
            ")->first();

        return [
            'labels' => ['Alta', 'Media', 'Baja', 'No aplica'],
            'data'   => [(int)($row->alta??0), (int)($row->media??0), (int)($row->baja??0), (int)($row->no_aplica??0)],
            'colors' => ['#2A9D8F', '#D9A05B', '#E27D60', '#9CA3AF'],
        ];
    }

    private function getChartSeguimientoJS(): array
    {
        if (! Schema::hasTable('actividad_participantes')) {
            return ['labels' => [], 'seguimiento' => []];
        }
        $rows = DB::table('actividad_participantes as ap')
            ->join('actividades_adulto as aa', 'ap.cod_act_adul', '=', 'aa.cod_act_adul')
            ->whereNull('ap.deleted_at')->whereNull('aa.deleted_at')->where('ap.requiere_seguimiento', true)
            ->when(! $this->fechaDesde && ! $this->fechaHasta, fn($q) => $q->whereYear('aa.fecha', now()->year))
            ->when($this->fechaDesde, fn($q) => $q->where('aa.fecha', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn($q) => $q->where('aa.fecha', '<=', $this->fechaHasta))
            ->selectRaw("TO_CHAR(aa.fecha,'Mon') as mes, TO_CHAR(aa.fecha,'MM') as mes_num, COUNT(*) as total")
            ->groupByRaw("TO_CHAR(aa.fecha,'Mon'), TO_CHAR(aa.fecha,'MM')")
            ->orderByRaw("TO_CHAR(aa.fecha,'MM')")->get();

        return [
            'labels'     => $rows->pluck('mes')->values()->all(),
            'seguimiento'=> $rows->pluck('total')->map(fn($v) => (int) $v)->values()->all(),
        ];
    }

    // ── Ensamblar todos los datos de charts para dispatch ─────────────────────

    private function buildChartData(): array
    {
        return [
            'estado'      => $this->getChartEstadoJS(),
            'tipo'        => $this->getChartTipoJS(),
            'mes'         => $this->getChartMesJS(),
            'asistencia'  => $this->getChartAsistenciaJS(),
            'nivel'       => $this->getChartNivelJS(),
            'seguimiento' => $this->getChartSeguimientoJS(),
        ];
    }

    // ── Vista previa (tabla enriquecida) ──────────────────────────────────────

    private function getPreview()
    {
        if (! Schema::hasTable('actividades_adulto')) {
            return collect();
        }

        $ids = $this->queryFiltrada()->pluck('cod_act_adul');

        return DB::table('actividades_adulto as aa')
            ->leftJoin('tipo_actividades_adulto as ta', 'aa.cod_tipo_act', '=', 'ta.cod_tipo_act')
            ->leftJoin('adulto_mayor as am', 'aa.cod_am', '=', 'am.cod_am')
            ->leftJoin(DB::raw("(
                SELECT
                    cod_act_adul,
                    COUNT(*)                                                          AS total_part,
                    SUM(CASE WHEN estado_asistencia = 'ASISTIO'      THEN 1 ELSE 0 END) AS asistieron,
                    SUM(CASE WHEN estado_asistencia = 'FALTO'        THEN 1 ELSE 0 END) AS faltaron,
                    SUM(CASE WHEN estado_asistencia = 'JUSTIFICADO'  THEN 1 ELSE 0 END) AS justificados,
                    SUM(CASE WHEN requiere_seguimiento = true         THEN 1 ELSE 0 END) AS seguimiento
                FROM actividad_participantes
                WHERE deleted_at IS NULL
                GROUP BY cod_act_adul
            ) AS ap_agg"), 'ap_agg.cod_act_adul', '=', 'aa.cod_act_adul')
            ->whereNull('aa.deleted_at')
            ->whereIn('aa.cod_act_adul', $ids)
            ->select(
                'aa.cod_act_adul', 'aa.nombre', 'aa.fecha', 'aa.hora', 'aa.hora_fin',
                'aa.lugar', 'aa.responsable_id', 'aa.estado',
                'aa.resultado_general', 'aa.nivel_cumplimiento', 'aa.incidencias',
                'aa.evaluacion_final',
                'ta.tipo as tipo_actividad', 'ta.categoria',
                DB::raw("COALESCE(am.nombres || ' ' || am.ap_paterno, NULL) AS adulto"),
                DB::raw('COALESCE(ap_agg.total_part, 0)   AS total_participantes'),
                DB::raw('COALESCE(ap_agg.asistieron, 0)   AS asistieron'),
                DB::raw('COALESCE(ap_agg.faltaron, 0)     AS faltaron'),
                DB::raw('COALESCE(ap_agg.justificados, 0) AS justificados'),
                DB::raw('COALESCE(ap_agg.seguimiento, 0)  AS seguimiento')
            )
            ->orderByDesc('aa.fecha')->orderByDesc('aa.hora')
            ->limit(20)->get();
    }

    // ── Dashboard institucional ───────────────────────────────────────────────

    private function getDashboardData(): array
    {
        if (! Schema::hasTable('actividades_adulto')) {
            return [];
        }

        // Tipo más frecuente
        $tipoMasFrecuente = DB::table('actividades_adulto as aa')
            ->leftJoin('tipo_actividades_adulto as ta', 'aa.cod_tipo_act', '=', 'ta.cod_tipo_act')
            ->whereNull('aa.deleted_at')
            ->selectRaw('ta.tipo, COUNT(*) as total')
            ->groupBy('ta.tipo')->orderByDesc('total')->first();

        // Próximas actividades
        $proximas = DB::table('actividades_adulto as aa')
            ->leftJoin('tipo_actividades_adulto as ta', 'aa.cod_tipo_act', '=', 'ta.cod_tipo_act')
            ->whereNull('aa.deleted_at')
            ->whereIn('aa.estado', ['PROGRAMADA', 'EN_CURSO'])
            ->where('aa.fecha', '>=', today())
            ->select('aa.cod_act_adul', 'aa.nombre', 'aa.fecha', 'aa.hora', 'ta.tipo', 'aa.lugar')
            ->orderBy('aa.fecha')->orderBy('aa.hora')->limit(5)->get();

        // Pendientes de evaluación
        $pendientesEval = DB::table('actividades_adulto')
            ->whereNull('deleted_at')
            ->whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA'])
            ->whereNull('evaluacion_final')
            ->count();

        // Adulto con mayor asistencia
        $adultoMasActivo = null;
        if (Schema::hasTable('actividad_participantes')) {
            $adultoMasActivo = DB::table('actividad_participantes as ap')
                ->join('adulto_mayor as am', 'ap.cod_am', '=', 'am.cod_am')
                ->whereNull('ap.deleted_at')
                ->where('ap.estado_asistencia', 'ASISTIO')
                ->selectRaw("ap.cod_am, CONCAT(am.nombres,' ',am.ap_paterno) as nombre, COUNT(*) as total")
                ->groupBy('ap.cod_am', 'am.nombres', 'am.ap_paterno')
                ->orderByDesc('total')->first();
        }

        // Alertas por baja participación (adultos con ≥3 registros BAJA)
        $alertasBajaParticipacion = 0;
        if (Schema::hasTable('actividad_participantes')) {
            $alertasBajaParticipacion = DB::table('actividad_participantes')
                ->whereNull('deleted_at')->where('nivel_participacion', 'BAJA')
                ->selectRaw('cod_am, COUNT(*) as total')
                ->groupBy('cod_am')->having('total', '>=', 3)
                ->count();
        }

        return [
            'tipo_mas_frecuente'       => $tipoMasFrecuente,
            'proximas'                 => $proximas,
            'pendientes_evaluacion'    => $pendientesEval,
            'adulto_mas_activo'        => $adultoMasActivo,
            'alertas_baja_participacion'=> $alertasBajaParticipacion,
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function describePeriodo(): string
    {
        if ($this->fechaDesde && $this->fechaHasta) {
            return Carbon::parse($this->fechaDesde)->format('d/m/Y')
                . ' — '
                . Carbon::parse($this->fechaHasta)->format('d/m/Y');
        }
        if ($this->fechaDesde) return 'Desde ' . Carbon::parse($this->fechaDesde)->format('d/m/Y');
        if ($this->fechaHasta) return 'Hasta ' . Carbon::parse($this->fechaHasta)->format('d/m/Y');
        return 'Todo el historial';
    }

    private function getTipos()
    {
        return TipoActividadAdulto::orderBy('tipo')->get();
    }

    private function buildFiltros(): array
    {
        return array_filter([
            'fecha_desde' => $this->fechaDesde  ?: null,
            'fecha_hasta' => $this->fechaHasta  ?: null,
            'tipo'        => $this->filtroTipo  ?: null,
            'estado'      => $this->filtroEstado ?: null,
            'buscar'      => $this->buscar       ?: null,
        ]);
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $filtros    = $this->buildFiltros();
        $chartData  = $this->buildChartData();
        $stats      = $this->getStats();
        $kpisPartic = $this->getKPIsParticipacion();

        return view('livewire.admin.actividades.reportes-actividades-panel', [
            'stats'         => $stats,
            'kpisPartic'    => $kpisPartic,
            'preview'       => $this->getPreview(),
            'dashboard'     => $this->getDashboardData(),
            'chartData'     => $chartData,  // JSON para init de Chart.js
            'tipos'         => $this->getTipos(),
            'categorias'    => \App\Models\TipoActividadAdulto::categorias(),
            'hasFiltros'    => ! empty($filtros) || $this->soloConSeguimiento || $this->soloConIncidencias || $this->soloEvaluadas,
            'urlPreview'    => route('admin.reportes.actividades.preview', $filtros),
            'urlPdf'        => route('admin.reportes.actividades.pdf', $filtros),
            'urlExcel'      => route('admin.reportes.actividades.excel', $filtros),
        ])->layout('layouts.sistema');
    }
}
