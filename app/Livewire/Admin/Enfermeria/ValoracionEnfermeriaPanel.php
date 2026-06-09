<?php

namespace App\Livewire\Admin\Enfermeria;

use App\Models\ValoracionEnfermeriaAdmision;
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
        $valoraciones = ValoracionEnfermeriaAdmision::with(['adultoMayor', 'preadmision', 'registradoPor'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->whereHas('adultoMayor', function ($sq) {
                        $sq->where('nombres', 'like', '%' . $this->search . '%')
                            ->orWhere('ap_paterno', 'like', '%' . $this->search . '%')
                            ->orWhere('ap_materno', 'like', '%' . $this->search . '%');
                    })->orWhereHas('preadmision', function ($sq) {
                        $sq->where('nombres', 'like', '%' . $this->search . '%')
                            ->orWhere('ap_paterno', 'like', '%' . $this->search . '%')
                            ->orWhere('ap_materno', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->when($this->filtroEstado !== '', fn ($q) => $q->where('estado', $this->filtroEstado))
            ->orderByDesc('fecha_valoracion')
            ->orderByDesc('hora_valoracion')
            ->paginate(12);

        return view('livewire.admin.enfermeria.valoracion-enfermeria-panel', [
            'valoraciones' => $valoraciones,
            'detalle' => $this->viendoId
                ? ValoracionEnfermeriaAdmision::with(['adultoMayor', 'preadmision', 'registradoPor'])->find($this->viendoId)
                : null,
        ])->layout('layouts.sistema');
    }
}
