<?php

namespace App\Modulos\Clinica\Presentacion\Livewire\Paginas;

use App\Models\Residente;
use App\Modulos\Clinica\Aplicacion\Consultas\ResumenSeguimientoResidente;
use Livewire\Component;

class SaludResumenPanel extends Component
{
    public Residente $adulto;

    public function mount(Residente $adulto): void
    {
        $this->adulto = $adulto;
    }

    public function render()
    {
        return view('livewire.clinica.salud-resumen-panel', app(ResumenSeguimientoResidente::class)->consultar($this->adulto))
            ->layout('layouts.sistema');
    }
}
