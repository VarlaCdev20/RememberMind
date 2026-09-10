<?php

namespace App\Livewire\Clinica;

use Livewire\Component;
use App\Models\HistorialEstadoAdulto;

class HistorialEstadoAdultoPanel extends Component
{
    public $showModal = false;
    public $cod_am;

    protected $listeners = ['abrirModalHistorialEstado'];

    public function abrirModalHistorialEstado($cod_am)
    {
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

        return view('livewire.clinica.historial-estado-adulto-panel', [
            'historial' => $historial
        ]);
    }
}
