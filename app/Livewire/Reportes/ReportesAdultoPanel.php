<?php

namespace App\Livewire\Reportes;

use Livewire\Component;
use App\Models\AdultoMayor;

class ReportesAdultoPanel extends Component
{
    public AdultoMayor $adultoMayor;
    public $fecha_inicio;
    public $fecha_fin;
    public $chartSignos = [];
    public $chartCognitivo = [];

    public function mount(AdultoMayor $adultoMayor)
    {
        $this->adultoMayor = $adultoMayor;
        $this->fecha_inicio = now()->subMonths(6)->format('Y-m-d');
        $this->fecha_fin = now()->format('Y-m-d');
    }

    public function render()
    {
        $start = \Carbon\Carbon::parse($this->fecha_inicio)->startOfDay();
        $end = \Carbon\Carbon::parse($this->fecha_fin)->endOfDay();

        // Signos Vitales Data
        $signosVitales = $this->adultoMayor->signosVitales()
            ->whereBetween('fecha', [$start, $end])
            ->orderBy('fecha')->orderBy('hora')->get();
        
        $this->chartSignos = [
            'labels' => $signosVitales->map(fn($s) => $s->fecha->format('d/m') . ' ' . substr($s->hora, 0, 5))->toArray(),
            'fc' => $signosVitales->pluck('frecuencia_cardiaca')->toArray(),
            'temp' => $signosVitales->pluck('temperatura')->toArray(),
            'sat' => $signosVitales->pluck('saturacion_oxigeno')->toArray(),
        ];

        // Evaluaciones Cognitivas Data
        $evaluaciones = $this->adultoMayor->evaluacionesCognitivas()
            ->with('tipoEvaluacion')
            ->whereBetween('fecha_eval', [$start, $end])
            ->orderBy('fecha_eval')->get();
            
        $this->chartCognitivo = [
            'labels' => $evaluaciones->map(fn($e) => $e->fecha_eval->format('d/m/Y') . ' (' . $e->tipoEvaluacion->siglas . ')')->toArray(),
            'puntajes' => $evaluaciones->pluck('puntaje_total')->toArray(),
        ];

        return view('livewire.reportes.reportes-adulto-panel');
    }
}
