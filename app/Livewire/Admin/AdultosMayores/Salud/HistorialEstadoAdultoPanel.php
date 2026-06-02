<?php

namespace App\Livewire\Admin\AdultosMayores\Salud;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\HistorialEstadoAdulto;
use Illuminate\Support\Facades\Gate;

class HistorialEstadoAdultoPanel extends Component
{
    public $showModal = false;
    public $cod_am;

    protected $listeners = ['abrirModalHistorialEstado'];

    public function abrirModalHistorialEstado($cod_am)
    {
        $adulto = AdultoMayor::findOrFail($cod_am);
        Gate::authorize('viewClinicalData', $adulto);

        $this->cod_am = $cod_am;
        $this->showModal = true;
    }

    public function cerrarModal()
    {
        $this->showModal = false;
    }

    public function render()
    {
        $historial = [];
        if ($this->cod_am) {
            $historial = HistorialEstadoAdulto::with(['estadoAnteriorRelacion', 'estadoNuevoRelacion', 'cambiadoPor'])
                ->where('cod_am', $this->cod_am)
                ->orderBy('fecha_cambio', 'desc')
                ->get();
        }

        return view('livewire.admin.adultos-mayores.salud.historial-estado-adulto-panel', [
            'historial' => $historial
        ]);
    }
}
