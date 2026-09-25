<?php

namespace App\Frontend\Livewire\Administracion\Actividades;

use App\Models\Actividad;
use App\Models\ParticipanteActividad;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class AsistenciaPanel extends Component
{
    use WithPagination;

    public string $search           = '';
    public string $filtroTipo       = '';
    public string $filtroEstado     = '';
    public string $filtroFechaDesde = '';
    public string $filtroFechaHasta = '';

    public bool $modalDetalle   = false;
    public bool $modalResultado = false;

    public string $estado = 'REALIZADA';
    public string $obs    = '';

    public ?string $editandoId = null;
    public ?string $detalleId  = null;

    protected function rules(): array
    {
        return [
            'estado' => 'required|string|max:50',
            'obs'    => 'nullable|string|max:2000',
        ];
    }

    protected function messages(): array
    {
        return [
            'estado.required' => 'El estado es obligatorio.',
            'obs.max'         => 'La observación no puede superar los 2000 caracteres.',
        ];
    }

    public function updatingSearch(): void           { $this->resetPage(); }
    public function updatingFiltroTipo(): void        { $this->resetPage(); }
    public function updatingFiltroEstado(): void      { $this->resetPage(); }
    public function updatingFiltroFechaDesde(): void  { $this->resetPage(); }
    public function updatingFiltroFechaHasta(): void  { $this->resetPage(); }

    public function abrirDetalle(string $id): void
    {
        $this->detalleId    = $id;
        $this->modalDetalle = true;
    }

    public function abrirResultado(string $id): void
    {
        $a = Actividad::findOrFail($id);
        $this->editandoId = $id;
        $this->estado     = in_array($a->estado, ['REALIZADA', 'COMPLETADA']) ? 'REALIZADA' : $a->estado;
        $this->obs        = $a->obs ?? '';
        $this->resetValidation();
        $this->modalResultado = true;
    }

    public function cerrarModales(): void
    {
        $this->modalDetalle   = false;
        $this->modalResultado = false;
        $this->resetForm();
    }

    public function marcarRealizada(string $id): void
    {
        Actividad::findOrFail($id)->update(['estado' => 'REALIZADA']);
        $this->dispatch('swal', [
            'icon'  => 'success',
            'title' => 'Actividad marcada como realizada. Cumplimiento institucional registrado.',
        ]);
    }

    public function marcarCancelada(string $id): void
    {
        Actividad::findOrFail($id)->update(['estado' => 'CANCELADA']);
        $this->dispatch('swal', [
            'icon'  => 'success',
            'title' => 'Actividad marcada como cancelada. El registro se conserva como historial.',
        ]);
    }

    public function guardarResultado(): void
    {
        $this->validate();
        Actividad::findOrFail($this->editandoId)->update([
            'estado'      => strtoupper(trim($this->estado)),
            'observacion' => $this->obs ?: null,
            'descripcion' => $this->obs ?: null,
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

    private function resetForm(): void
    {
        $this->estado     = 'REALIZADA';
        $this->obs        = '';
        $this->editandoId = null;
        $this->detalleId  = null;
        $this->resetValidation();
    }

    private function getStats(): array
    {
        return [
            'total'              => Actividad::count(),
            'pendientes'         => Actividad::whereIn('estado', ['PROGRAMADA', 'PENDIENTE'])->count(),
            'realizadas'         => Actividad::whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA'])->count(),
            'canceladas'         => Actividad::whereIn('estado', ['CANCELADA', 'ANULADA'])->count(),
            'reprogramadas'      => Actividad::where('estado', 'REPROGRAMADA')->count(),
            'hoy'                => Actividad::whereDate('fecha_hora', today())->count(),
            'adultos_realizados' => ParticipanteActividad::whereHas('actividad', fn($q) => $q->whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA']))
                                        ->distinct('cod_residente')->count('cod_residente'),
            'tipos_cumplidos'    => Actividad::whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA'])
                                        ->distinct('tipo')->count('tipo'),
        ];
    }

    private function getRegistros()
    {
        return Actividad::with(['participantes', 'adultoMayor'])
            ->when($this->search, fn($q) =>
                $q->whereHas('adultoMayor', fn($sq) =>
                    $sq->where('nombres', 'like', '%' . $this->search . '%')
                      ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                      ->orWhere('apellido_materno', 'like', '%' . $this->search . '%')
                )
            )
            ->when($this->filtroTipo, fn($q) => $q->where('tipo', $this->filtroTipo))
            ->when($this->filtroEstado, function ($q) {
                return match ($this->filtroEstado) {
                    'PROGRAMADA'   => $q->whereIn('estado', ['PROGRAMADA', 'PENDIENTE']),
                    'REALIZADA'    => $q->whereIn('estado', ['REALIZADA', 'COMPLETADA', 'FINALIZADA']),
                    'CANCELADA'    => $q->whereIn('estado', ['CANCELADA', 'ANULADA']),
                    'REPROGRAMADA' => $q->where('estado', 'REPROGRAMADA'),
                    default        => $q,
                };
            })
            ->when($this->filtroFechaDesde, fn($q) => $q->whereDate('fecha_hora', '>=', $this->filtroFechaDesde))
            ->when($this->filtroFechaHasta, fn($q) => $q->whereDate('fecha_hora', '<=', $this->filtroFechaHasta))
            ->orderByDesc('fecha_hora')
            ->paginate(12);
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

    private function getDetalle(): ?Actividad
    {
        return $this->detalleId
            ? Actividad::with(['participantes', 'adultoMayor'])->find($this->detalleId)
            : null;
    }

    public function render()
    {
        return view('livewire.actividades.asistencia-panel', [
            'stats'     => $this->getStats(),
            'registros' => $this->getRegistros(),
            'tipos'     => $this->getTipos(),
            'detalle'   => $this->getDetalle(),
        ])->layout('layouts.sistema');
    }
}