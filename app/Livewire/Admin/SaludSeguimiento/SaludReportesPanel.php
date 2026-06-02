<?php

namespace App\Livewire\Admin\SaludSeguimiento;

use Livewire\Component;
use App\Models\AdultoMayor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class SaludReportesPanel extends Component
{
    public $adultoSeleccionado = '';
    public $tipoReporte = '';
    public $fechaInicio = '';
    public $fechaFin = '';

    public function render()
    {
        $adultos = AdultoMayor::query()
            ->visiblesClinicamentePara(Auth::user())
            ->orderBy('ap_paterno')
            ->get();

        return view('livewire.admin.salud-seguimiento.salud-reportes-panel', [
            'adultos' => $adultos
        ]);
    }

    public function generarVistaPrevia()
    {
        $this->validate([
            'tipoReporte' => 'required',
            'adultoSeleccionado' => 'required',
        ]);

        $adulto = AdultoMayor::query()
            ->visiblesClinicamentePara(Auth::user())
            ->findOrFail($this->adultoSeleccionado);

        Gate::authorize('viewClinicalData', $adulto);

        $this->dispatch('swal:info', [
            'title' => 'Vista Previa Generada',
            'text' => 'Los datos han sido preparados para la impresión en HTML.',
        ]);
    }
}
