<?php

namespace App\Modulos\Alertas\Presentacion\Livewire\Paginas;

use App\Modulos\Alertas\Aplicacion\Consultas\BandejaAlertas;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class PanelAlertas extends Component
{
    use AuthorizesRequests;

    public function render()
    {
        abort_unless(auth()->user()?->can('alertas.ver'), 403);

        return view('livewire.alertas.panel-alertas', ['alertas' => app(BandejaAlertas::class)->pendientes(auth()->user())->with(['residente', 'eventos'])->latest('fecha_hora')->limit(30)->get()]);
    }
}
