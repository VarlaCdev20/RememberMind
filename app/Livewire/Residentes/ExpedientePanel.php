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
            'atenciones' => $this->residente->atenciones()->with(['area', 'personal', 'notas'])->latest('fecha_hora')->limit(20)->get(),
            'prescripciones' => $this->residente->prescripciones()->with(['medicamento', 'horarios'])->latest('fecha_hora_prescripcion')->limit(20)->get(),
        ]);
    }
}
