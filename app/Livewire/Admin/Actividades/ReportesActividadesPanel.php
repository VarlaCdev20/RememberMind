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
    public string $fechaDesde   = '';
    public string $fechaHasta   = '';
    public string $filtroTipo   = '';
    public string $filtroEstado = '';
    public string $buscar       = '';

    public function limpiarFiltros(): void
    {
        $this->fechaDesde   = '';
        $this->fechaHasta   = '';
        $this->filtroTipo   = '';
        $this->filtroEstado = '';
        $this->buscar       = '';
        $this->dispatch('swal', ['icon' => 'info', 'title' => 'Filtros eliminados. Mostrando todos los datos.']);
    }

    // ── Filtros para query params en URLs ─────────────────────────────────────
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

    // ── Queries ───────────────────────────────────────────────────────────────

    // Sin filtro de estado — para distribución de estados en gráficos
    private function queryBase(): Builder
    {
        return ActividadAdulto::query()
            ->when($this->fechaDesde, fn($q) => $q->where('fecha', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn($q) => $q->where('fecha', '<=', $this->fechaHasta))
            ->when($this->filtroTipo, fn($q) => $q->where('cod_tipo_act', (int) $this->filtroTipo))
            ->when($this->buscar, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->where('nombres', 'ilike', '%' . $this->buscar . '%')
                      ->orWhere('ap_paterno', 'ilike', '%' . $this->buscar . '%')
                      ->orWhere('ap_materno', 'ilike', '%' . $this->buscar . '%')
                )
            );
    }

    // Con todos los filtros — para preview y exportaciones
    private function queryFiltrada(): Builder
    {
        return $this->queryBase()
            ->when($this->filtroEstado, function ($q) {
                return match ($this->filtroEstado) {
                    'PROGRAMADA'   => $q->whereIn('estado', ['PROGRAMADA', 'PENDIENTE']),
                    'REALIZADA'    => $q->whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA']),
                    'CANCELADA'    => $q->whereIn('estado', ['CANCELADA', 'ANULADA']),
                    'REPROGRAMADA' => $q->where('estado', 'REPROGRAMADA'),
                    default        => $q,
                };
            });
    }

    // ── Métricas ──────────────────────────────────────────────────────────────
    private function getStats(): array
    {
        $cero = [
            'total' => 0, 'programadas' => 0, 'realizadas' => 0, 'canceladas' => 0,
            'reprogramadas' => 0, 'adultos' => 0, 'tipos' => 0, 'periodo' => 'Todo el historial',
        ];

        if (! Schema::hasTable('actividades_adulto')) {
            return $cero;
        }

        $base = $this->queryBase();

        return [
            'total'         => (clone $base)->count(),
            'programadas'   => (clone $base)->whereIn('estado', ['PROGRAMADA', 'PENDIENTE'])->count(),
            'realizadas'    => (clone $base)->whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA'])->count(),
            'canceladas'    => (clone $base)->whereIn('estado', ['CANCELADA', 'ANULADA'])->count(),
            'reprogramadas' => (clone $base)->where('estado', 'REPROGRAMADA')->count(),
            'adultos'       => (clone $base)->distinct('cod_am')->count('cod_am'),
            'tipos'         => (clone $base)->distinct('cod_tipo_act')->count('cod_tipo_act'),
            'periodo'       => $this->describePeriodo(),
        ];
    }

    // ── Vista previa ──────────────────────────────────────────────────────────
    private function getPreview()
    {
        if (! Schema::hasTable('actividades_adulto')) {
            return collect();
        }
        return $this->queryFiltrada()
            ->with(['tipoActividad', 'adultoMayor'])
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->limit(15)
            ->get();
    }

    // ── Datos para gráficos CSS ───────────────────────────────────────────────
    private function getChartEstado(): array
    {
        if (! Schema::hasTable('actividades_adulto')) {
            return ['data' => [], 'maximo' => 1];
        }

        $resultados = $this->queryBase()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->orderByDesc('total')
            ->get();

        $mapaColores = [
            'COMPLETADA' => '#8DA280', 'REALIZADA' => '#8DA280', 'FINALIZADA' => '#8DA280',
            'PROGRAMADA' => '#D9A05B', 'PENDIENTE' => '#D9A05B',
            'CANCELADA'  => '#E27D60', 'ANULADA'   => '#E27D60',
            'REPROGRAMADA' => '#7A68B0',
        ];

        $data = $resultados->map(fn($r) => [
            'label' => ActividadAdulto::normalizarEstado($r->estado)['etiqueta'],
            'total' => (int) $r->total,
            'color' => $mapaColores[strtoupper($r->estado ?? '')] ?? '#C7B5A3',
        ])->all();

        return ['data' => $data, 'maximo' => max(1, (int) $resultados->max('total'))];
    }

    private function getChartMes(): array
    {
        if (! Schema::hasTable('actividades_adulto')) {
            return ['data' => [], 'maximo' => 1, 'anio' => now()->year];
        }

        $q = DB::table('actividades_adulto')
            ->whereNull('deleted_at')
            ->select(
                DB::raw("TO_CHAR(fecha, 'MM') as mes"),
                DB::raw("TO_CHAR(fecha, 'Mon') as mes_nombre"),
                DB::raw('COUNT(*) as total')
            )
            ->groupByRaw("TO_CHAR(fecha, 'MM'), TO_CHAR(fecha, 'Mon')")
            ->orderByRaw("TO_CHAR(fecha, 'MM')");

        if ($this->fechaDesde) $q->where('fecha', '>=', $this->fechaDesde);
        if ($this->fechaHasta) $q->where('fecha', '<=', $this->fechaHasta);
        if ($this->filtroTipo) $q->where('cod_tipo_act', (int) $this->filtroTipo);
        if (! $this->fechaDesde && ! $this->fechaHasta) {
            $q->whereYear('fecha', now()->year);
        }

        $resultados = $q->get();
        return [
            'data'   => $resultados->map(fn($r) => ['label' => $r->mes_nombre, 'total' => (int) $r->total])->all(),
            'maximo' => max(1, (int) $resultados->max('total')),
            'anio'   => now()->year,
        ];
    }

    private function getChartTipo(): array
    {
        if (! Schema::hasTable('actividades_adulto')) {
            return ['data' => [], 'maximo' => 1];
        }

        $q = DB::table('actividades_adulto as aa')
            ->leftJoin('tipo_actividades_adulto as ta', 'aa.cod_tipo_act', '=', 'ta.cod_tipo_act')
            ->whereNull('aa.deleted_at')
            ->select('ta.tipo', DB::raw('COUNT(*) as total'))
            ->groupBy('ta.tipo')
            ->orderByDesc('total')
            ->limit(8);

        if ($this->fechaDesde) $q->where('aa.fecha', '>=', $this->fechaDesde);
        if ($this->fechaHasta) $q->where('aa.fecha', '<=', $this->fechaHasta);
        if ($this->filtroTipo) $q->where('aa.cod_tipo_act', (int) $this->filtroTipo);

        $resultados = $q->get();
        return [
            'data'   => $resultados->map(fn($r) => ['label' => $r->tipo ?? 'Sin tipo', 'total' => (int) $r->total])->all(),
            'maximo' => max(1, (int) $resultados->max('total')),
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
        if ($this->fechaDesde) {
            return 'Desde ' . Carbon::parse($this->fechaDesde)->format('d/m/Y');
        }
        if ($this->fechaHasta) {
            return 'Hasta ' . Carbon::parse($this->fechaHasta)->format('d/m/Y');
        }
        return 'Todo el historial';
    }

    private function getTipos()
    {
        return TipoActividadAdulto::orderBy('tipo')->get();
    }

    // ── Render ────────────────────────────────────────────────────────────────
    public function render()
    {
        $filtros = $this->buildFiltros();

        return view('livewire.admin.actividades.reportes-actividades-panel', [
            'stats'       => $this->getStats(),
            'preview'     => $this->getPreview(),
            'chartEstado' => $this->getChartEstado(),
            'chartMes'    => $this->getChartMes(),
            'chartTipo'   => $this->getChartTipo(),
            'tipos'       => $this->getTipos(),
            'hasFiltros'  => ! empty($filtros),
            'urlPreview'  => route('admin.reportes.actividades.preview', $filtros),
            'urlPdf'      => route('admin.reportes.actividades.pdf', $filtros),
            'urlExcel'    => route('admin.reportes.actividades.excel', $filtros),
        ])->layout('layouts.sistema');
    }
}
