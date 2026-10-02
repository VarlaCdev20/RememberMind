<?php

namespace App\Livewire\Residentes;

use App\Models\Residente;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ExpedientePanel extends Component
{
    use AuthorizesRequests;

    public Residente $residente;

    public function mount(Residente $residente): void
    {
        $this->authorize('view', $residente);
        $this->residente = $residente;
    }

    public function render()
    {
        return view('livewire.residentes.expediente-panel', [
            'atenciones' => auth()->user()->can('atenciones.ver')
                ? $this->residente->atenciones()->with(['area', 'personal'])->latest('fecha_hora')->limit(20)->get()
                : collect(),
            'prescripciones' => auth()->user()->can('prescripciones.ver')
                ? $this->residente->prescripciones()->with('medicamento')->latest('fecha_hora_prescripcion')->limit(20)->get()
                : collect(),
        ]);
    }
}
