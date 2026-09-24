<?php

namespace App\Livewire\Valoraciones;

use App\Models\ValoracionFuncional;
use Livewire\Component;
use Livewire\WithPagination;

class ValoracionEnfermeriaPanel extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filtroEstado = '';
    public bool $modalVer = false;
    public ?string $viendoId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltro(string $campo): void
    {
        if (property_exists($this, $campo)) {
            $this->$campo = "";
            $this->resetPage();
        }
    }

    public function limpiarFiltros(): void
    {
        $this->search = "";
        $this->filtroEstado = "";
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function abrirVer(string $id): void
    {
        $this->viendoId = $id;
        $this->modalVer = true;
    }

    public function cerrarModales(): void
    {
        $this->modalVer = false;
        $this->viendoId = null;
    }

    public function render()
    {
        $valoraciones = ValoracionFuncional::with(['residente', 'registradoPor'])
            ->when($this->search, function ($q) {
                $q->whereHas('residente', function ($sq) {
                    $sq->where('nombres', 'like', '%' . $this->search . '%')
                        ->orWhere('apellido_paterno', 'like', '%' . $this->search . '%')
                        ->orWhere('apellido_materno', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filtroEstado !== '', fn ($q) => $q->where('estado', $this->filtroEstado))
            ->orderByDesc('fecha_hora')
            ->paginate(12);

        return view('livewire.valoraciones.valoracion-enfermeria-panel', [
            'valoraciones' => $valoraciones,
            'detalle' => $this->viendoId
                ? ValoracionFuncional::with(['residente', 'registradoPor'])->find($this->viendoId)
                : null,
        ])->layout('layouts.sistema');
    }
}