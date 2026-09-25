<?php

namespace App\Frontend\Livewire\Compartido\Alertas;

use App\Models\Alerta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class PanelAlertas extends Component
{
    use AuthorizesRequests;

    public function render()
    {
        abort_unless(auth()->user()?->can('alertas.ver'), 403);
        return view('livewire.alertas.panel-alertas', ['alertas' => Alerta::query()->with(['residente', 'eventos'])->whereNotIn('estado', ['CERRADA', 'ANULADA'])->latest('fecha_hora')->limit(30)->get()]);
    }
}
