<?php

namespace App\Livewire\Admin\Actividades;

use App\Models\ActividadAdulto;
use App\Models\TipoActividadAdulto;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class AsistenciaPanel extends Component
{
    use WithPagination;

    // ── Filtros ───────────────────────────────────────────────────────────────
    public string $search           = '';
    public string $filtroTipo       = '';
    public string $filtroEstado     = '';
    public string $filtroFechaDesde = '';
    public string $filtroFechaHasta = '';

    // ── Modales ───────────────────────────────────────────────────────────────
    public bool $modalDetalle   = false;
    public bool $modalResultado = false;

    // ── Campos del formulario ─────────────────────────────────────────────────
    public string $estado = 'REALIZADA';
    public string $obs    = '';

    // ── Tracking ──────────────────────────────────────────────────────────────
    public ?int $editandoId = null;
    public ?int $detalleId  = null;

    protected function rules(): array
    {
        return [
            'estado' => 'required|string|in:PROGRAMADA,REALIZADA,CANCELADA,REPROGRAMADA',
            'obs'    => 'nullable|string|max:2000',
        ];
    }

    protected function messages(): array
    {
        return [
            'estado.required' => 'El estado es obligatorio.',
            'estado.in'       => 'Seleccione un estado válido.',
        ];
    }

    public function updatingSearch(): void           { $this->resetPage(); }
    public function updatingFiltroTipo(): void        { $this->resetPage(); }
    public function updatingFiltroEstado(): void      { $this->resetPage(); }
    public function updatingFiltroFechaDesde(): void  { $this->resetPage(); }
    public function updatingFiltroFechaHasta(): void  { $this->resetPage(); }

    // ── Apertura de modales ───────────────────────────────────────────────────
    public function abrirDetalle(int $id): void
    {
        $this->detalleId    = $id;
        $this->modalDetalle = true;
    }

    public function abrirResultado(int $id): void
    {
        $a = ActividadAdulto::findOrFail($id);
        $this->editandoId     = $id;
        $this->estado         = $a->estado;
        $this->obs            = $a->obs ?? '';
        $this->resetValidation();
        $this->modalResultado = true;
    }

    public function abrirReprogramar(int $id): void
    {
        $a = ActividadAdulto::findOrFail($id);
        $this->editandoId     = $id;
        $this->estado         = 'REPROGRAMADA';
        $this->obs            = $a->obs ?? '';
        $this->resetValidation();
        $this->modalResultado = true;
    }

    public function cerrarModales(): void
    {
        $this->modalDetalle   = false;
        $this->modalResultado = false;
        $this->resetForm();
    }

    // ── Acciones rápidas ──────────────────────────────────────────────────────
    public function marcarRealizada(int $id): void
    {
        ActividadAdulto::findOrFail($id)->update(['estado' => 'REALIZADA']);
        $this->dispatch('swal', [
            'icon'  => 'success',
            'title' => 'Actividad marcada como realizada. Cumplimiento institucional registrado.',
        ]);
    }

    public function marcarCancelada(int $id): void
    {
        ActividadAdulto::findOrFail($id)->update(['estado' => 'CANCELADA']);
        $this->dispatch('swal', [
            'icon'  => 'success',
            'title' => 'Actividad marcada como cancelada. El registro se conserva como historial.',
        ]);
    }

    // ── Guardar resultado ─────────────────────────────────────────────────────
    public function guardarResultado(): void
    {
        $this->validate();
        ActividadAdulto::findOrFail($this->editandoId)->update([
            'estado' => strtoupper(trim($this->estado)),
            'obs'    => $this->obs ?: null,
        ]);
        $this->modalResultado = false;
        $this->resetForm();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Resultado registrado correctamente.']);
    }

    public function limpiarFiltros(): void
    {
        $this->search           = '';
        $this->filtroTipo       = '';
        $this->filtroEstado     = '';
        $this->filtroFechaDesde = '';
        $this->filtroFechaHasta = '';
        $this->resetPage();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function resetForm(): void
    {
        $this->estado     = 'REALIZADA';
        $this->obs        = '';
        $this->editandoId = null;
        $this->detalleId  = null;
        $this->resetValidation();
    }

    private function tablaExiste(): bool
    {
        return Schema::hasTable('actividades_adulto');
    }

    private function getStats(): array
    {
        $zero = array_fill_keys(
            ['total', 'pendientes', 'realizadas', 'canceladas', 'reprogramadas', 'hoy', 'adultos_realizados', 'tipos_cumplidos'],
            0
        );
        if (! $this->tablaExiste()) {
            return $zero;
        }
        return [
            'total'              => ActividadAdulto::count(),
            'pendientes'         => ActividadAdulto::whereIn('estado', ['PROGRAMADA', 'PENDIENTE'])->count(),
            'realizadas'         => ActividadAdulto::whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA'])->count(),
            'canceladas'         => ActividadAdulto::whereIn('estado', ['CANCELADA', 'ANULADA'])->count(),
            'reprogramadas'      => ActividadAdulto::where('estado', 'REPROGRAMADA')->count(),
            'hoy'                => ActividadAdulto::whereDate('fecha', today())->count(),
            'adultos_realizados' => ActividadAdulto::whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA'])
                                        ->distinct('cod_am')->count('cod_am'),
            'tipos_cumplidos'    => ActividadAdulto::whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA'])
                                        ->distinct('cod_tipo_act')->count('cod_tipo_act'),
        ];
    }

    private function getRegistros()
    {
        if (! $this->tablaExiste()) {
            return ActividadAdulto::paginate(12);
        }
        return ActividadAdulto::with(['tipoActividad', 'adultoMayor'])
            ->when($this->search, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->where('nombres', 'ilike', '%' . $this->search . '%')
                      ->orWhere('ap_paterno', 'ilike', '%' . $this->search . '%')
                      ->orWhere('ap_materno', 'ilike', '%' . $this->search . '%')
                )
            )
            ->when($this->filtroTipo, fn($q) => $q->where('cod_tipo_act', (int) $this->filtroTipo))
            ->when($this->filtroEstado, function ($q) {
                return match ($this->filtroEstado) {
                    'PROGRAMADA'   => $q->whereIn('estado', ['PROGRAMADA', 'PENDIENTE']),
                    'REALIZADA'    => $q->whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA']),
                    'CANCELADA'    => $q->whereIn('estado', ['CANCELADA', 'ANULADA']),
                    'REPROGRAMADA' => $q->where('estado', 'REPROGRAMADA'),
                    default        => $q,
                };
            })
            ->when($this->filtroFechaDesde, fn($q) => $q->where('fecha', '>=', $this->filtroFechaDesde))
            ->when($this->filtroFechaHasta, fn($q) => $q->where('fecha', '<=', $this->filtroFechaHasta))
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->paginate(12);
    }

    private function getTipos()
    {
        return TipoActividadAdulto::orderBy('tipo')->get();
    }

    private function getDetalle(): ?ActividadAdulto
    {
        return $this->detalleId
            ? ActividadAdulto::with(['tipoActividad', 'adultoMayor'])->find($this->detalleId)
            : null;
    }

    public function render()
    {
        return view('livewire.admin.actividades.asistencia-panel', [
            'stats'     => $this->getStats(),
            'registros' => $this->getRegistros(),
            'tipos'     => $this->getTipos(),
            'detalle'   => $this->getDetalle(),
        ])->layout('layouts.sistema');
    }
}
