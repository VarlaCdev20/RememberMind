<?php

namespace App\Frontend\Livewire\Compartido\Valoraciones;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Residente;
use App\Models\AplicacionInstrumento;

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
