<?php

namespace App\Modulos\Alertas\Presentacion\Livewire\Paginas;

use App\Modulos\Alertas\Aplicacion\Consultas\AlertasOrientativasSalud;
use Livewire\Component;

class SaludAlertasPanel extends Component
{
    public $filtroTipo = '';

    public function render()
    {
        return view('livewire.alertas.salud-alertas-panel', app(AlertasOrientativasSalud::class)->consultar(auth()->user(), $this->filtroTipo));
    }
}
