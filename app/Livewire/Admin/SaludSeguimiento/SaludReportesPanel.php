<?php

namespace App\Livewire\Admin\SaludSeguimiento;

use Livewire\Component;
use App\Models\AdultoMayor;

class SaludReportesPanel extends Component
{
    public $adultoSeleccionado = '';
    public $tipoReporte = '';
    public $fechaInicio = '';
    public $fechaFin = '';

    public function render()
    {
        $adultos = AdultoMayor::orderBy('ap_paterno')->get();

        return view('livewire.admin.salud-seguimiento.salud-reportes-panel', [
            'adultos' => $adultos
        ])->layout('layouts.sistema');
    }

    public function generarVistaPrevia()
    {
        $this->validate([
            'tipoReporte' => 'required',
            'adultoSeleccionado' => 'required',
        ]);

        $this->dispatch('swal:info', [
            'title' => 'Vista Previa Generada',
            'text' => 'Los datos han sido preparados para la impresión en HTML.',
        ]);
    }
}
