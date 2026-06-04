<?php

namespace App\Livewire\Admin\Medico;

use Livewire\Component;
use App\Models\AdultoMayor;

class DashboardMedico extends Component
{
    public $valoracionesPendientes = [];

    protected $listeners = ['valoracionMedicaCompletada' => '$refresh'];

    public function mount()
    {
        // En un caso real, esto se llamaría en mount o render.
        // Validar que es medico o super-admin (o manejar por middleware route)
        if (!auth()->user()->hasRole(['Super-Admin', 'MÉDICO', 'MEDICO'])) {
            abort(403, 'Acceso denegado. Solo personal médico autorizado.');
        }
    }

    public function render()
    {
        $this->valoracionesPendientes = AdultoMayor::whereHas('estado', function($q) {
            $q->whereIn('estado', ['VALORACION_MEDICA', 'DECISION_ADMISION']);
        })
        ->with(['estado', 'familiares', 'asignacionTurnoActiva', 'documentos'])
        ->orderBy('created_at', 'desc')
        ->get();

        return view('livewire.admin.medico.dashboard-medico')
            ->layout('layouts.sistema');
    }

    public function iniciarValoracionMedica($cod_am)
    {
        $this->dispatch('abrirValoracionMedica', $cod_am);
    }

    public function abrirDecisionAdmision($cod_am)
    {
        $this->dispatch('abrirDecisionAdmision', $cod_am);
    }
}
