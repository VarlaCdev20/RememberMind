<?php

namespace App\Modulos\Reportes\Presentacion\Livewire\Paginas;

use App\Models\Residente;
use Livewire\Component;

class SaludReportesPanel extends Component
{
    public $adultoSeleccionado = '';

    public $tipoReporte = '';

    public $fechaInicio = '';

    public $fechaFin = '';

    public function render()
    {
        $adultos = Residente::orderBy('apellido_paterno')->get();

        return view('livewire.reportes.salud-reportes-panel', [
            'adultos' => $adultos,
        ]);
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
