<?php

namespace App\Livewire\Admin\SaludSeguimiento;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AdultoMayor;
use Illuminate\Support\Facades\Gate;

class SaludEvaluacionesGeriatricasPanel extends Component
{
    use WithPagination;

    public AdultoMayor $adulto;
    public $evaluaciones = [];

    public function mount(AdultoMayor $adulto)
    {
        Gate::authorize('viewClinicalData', $adulto);
        $this->adulto = $adulto;
        // The EvaluacionGeriatrica table might exist according to AdultoMayorController.
        // We will load the data securely via a query if the model exists.
        if (class_exists(\App\Models\EvaluacionGeriatrica::class)) {
            $this->evaluaciones = \App\Models\EvaluacionGeriatrica::with('instrumento')
                ->where('cod_am', $this->adulto->cod_am)
                ->whereNull('deleted_at')
                ->orderByDesc('fecha_eval')
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.admin.salud-seguimiento.salud-evaluaciones-geriatricas-panel')
            ->layout('layouts.sistema');
    }
}
