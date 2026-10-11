<?php

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Paginas;

use App\Models\AplicacionInstrumento;
use App\Models\Residente;
use Livewire\Component;
use Livewire\WithPagination;

class SaludEvaluacionesGeriatricasPanel extends Component
{
    use WithPagination;

    public Residente $adulto;

    public $evaluaciones = [];

    public function mount(Residente $adulto)
    {
        $this->adulto = $adulto;
        $this->evaluaciones = AplicacionInstrumento::query()
            ->with('instrumento')
            ->where('cod_residente', $this->adulto->cod_residente)
            ->orderByDesc('fecha_hora')
            ->get();
    }

    public function render()
    {
        return view('livewire.valoraciones.salud-evaluaciones-geriatricas-panel')
            ->layout('layouts.sistema');
    }
}
