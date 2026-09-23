<?php

namespace App\Livewire\Admin\Actividades;

use App\Models\Actividad;
use App\Models\ParticipanteActividad;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class ReportesActividadesPanel extends Component
{
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

    private function queryBase(): Builder
    {
        return Actividad::query()
            ->when($this->fechaDesde, fn($q) => $q->whereDate('fecha_hora', '>=', $this->fechaDesde))
            ->when($this->fechaHasta, fn($q) => $q->whereDate('fecha_hora', '<=', $this->fechaHasta))
            ->when($this->filtroTipo, fn($q) => $q->where('tipo', $this->filtroTipo))
            ->when($this->buscar, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->where('nombres', 'like', '%' . $this->buscar . '%')
                      ->orWhere('apellido_paterno', 'like', '%' . $this->buscar . '%')
                      ->orWhere('apellido_materno', 'like', '%' . $this->buscar . '%')
                )
            );
    }

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

    private function getStats(): array
    {
        $base = $this->queryBase();

        return [
            'total'         => (clone $base)->count(),
            'programadas'   => (clone $base)->whereIn('estado', ['PROGRAMADA', 'PENDIENTE'])->count(),
            'realizadas'    => (clone $base)->whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA'])->count(),
            'canceladas'    => (clone $base)->whereIn('estado', ['CANCELADA', 'ANULADA'])->count(),
            'reprogramadas' => (clone $base)->where('estado', 'REPROGRAMADA')->count(),
            'adultos'       => ParticipanteActividad::whereIn('cod_actividad', (clone $base)->select('cod_actividad'))->distinct('cod_residente')->count('cod_residente'),
            'tipos'         => (clone $base)->distinct('tipo')->count('tipo'),
            'periodo'       => $this->describePeriodo(),
        ];
    }

    private function getDistribucionEstado(): array
    {
        $base = $this->queryBase();
        $resultados = (clone $base)
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->get();

        $data = $resultados->map(fn($r) => [
            'estado' => $r->estado,
            'label'  => Actividad::normalizarEstado($r->estado)['etiqueta'],
            'total'  => (int) $r->total,
        ])->all();

        return ['data' => $data, 'maximo' => max(1, (int) $resultados->max('total'))];
    }

    private function getChartMes(): array
    {
        $q = DB::table('actividades')
            ->select(
                DB::raw("TO_CHAR(fecha_hora, 'YYYY-MM') as mes"),
                DB::raw("TO_CHAR(fecha_hora, 'Mon YYYY') as mes_nombre"),
                DB::raw('COUNT(*) as total')
            )
            ->groupByRaw("TO_CHAR(fecha_hora, 'YYYY-MM'), TO_CHAR(fecha_hora, 'Mon YYYY')")
            ->orderByRaw("TO_CHAR(fecha_hora, 'YYYY-MM')");

        if ($this->fechaDesde) $q->whereDate('fecha_hora', '>=', $this->fechaDesde);
        if ($this->fechaHasta) $q->whereDate('fecha_hora', '<=', $this->fechaHasta);
        if ($this->filtroTipo) $q->where('tipo', $this->filtroTipo);
        if (! $this->fechaDesde && ! $this->fechaHasta) {
            $q->whereYear('fecha_hora', now()->year);
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
        $q = DB::table('actividades')
            ->select('tipo', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo')
            ->orderByDesc('total')
            ->limit(8);

        if ($this->fechaDesde) $q->whereDate('fecha_hora', '>=', $this->fechaDesde);
        if ($this->fechaHasta) $q->whereDate('fecha_hora', '<=', $this->fechaHasta);
        if ($this->filtroTipo) $q->where('tipo', $this->filtroTipo);

        $resultados = $q->get();
        return [
            'data'   => $resultados->map(fn($r) => ['label' => Str::headline($r->tipo ?? 'Sin tipo'), 'total' => (int) $r->total])->all(),
            'maximo' => max(1, (int) $resultados->max('total')),
        ];
    }

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
        $tipos = Actividad::query()->select('tipo')->distinct()->orderBy('tipo')->pluck('tipo');
        if ($tipos->isEmpty()) {
            $tipos = collect(['RECREATIVA','COGNITIVA','FISICA','SOCIAL','EDUCATIVA','TERAPEUTICA']);
        }
        return $tipos->map(fn (string $tipo) => (object) [
            'cod_tipo_act' => $tipo,
            'tipo'         => Str::headline($tipo),
            'nombre'       => Str::headline($tipo),
        ]);
    }

    public function render()
    {
        $filtros = $this->buildFiltros();
        return view('livewire.actividades.reportes-actividades-panel', [
            'stats'              => $this->getStats(),
            'preview'            => $this->queryFiltrada()->with(['participantes', 'adultoMayor'])->orderByDesc('fecha_hora')->limit(50)->get(),
            'hasFiltros'         => !empty($filtros),
            'tipos'              => $this->getTipos(),
            'chartEstado' => $this->getDistribucionEstado(),
            'chartMes'    => $this->getChartMes(),
            'chartTipo'   => $this->getChartTipo(),
            'urlPreview'         => route('admin.reportes.actividades.preview', $filtros),
            'urlPdf'             => route('admin.reportes.actividades.pdf', $filtros),
            'urlExcel'           => route('admin.reportes.actividades.excel', $filtros),
        ])->layout('layouts.sistema');
    }
}