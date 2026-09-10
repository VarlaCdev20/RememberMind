<?php

namespace App\Livewire\Clinica;

use Livewire\Component;
use App\Models\AdultoMayor;
use App\Models\NotaEvolucionMedica;
use App\Models\SignosVitalesAdulto;
use App\Models\MedicacionAdulto;
use App\Models\ValoracionFuncionalAdulto;
use App\Models\EvaluacionGeriatrica;
use App\Models\FichaMedicaAdulto;

class FichaClinicaIntegradaPanel extends Component
{
    public AdultoMayor $adulto;
    public string $tab = 'resumen';

    protected $listeners = [
        'nota-evolucion-guardada'     => 'refreshData',
        'signos-actualizados'         => 'refreshData',
        'valoracion-barthel-guardada' => 'refreshData',
        'evaluacion-geriatrica-guardada' => 'refreshData',
    ];

    // Datos cargados
    public $fichaMedica         = null;
    public $ultimosSignos       = null;
    public $notasRecientes      = [];
    public $medicacionActiva    = [];
    public $valoracionFuncional = null;
    public $evalCognitiva       = null;
    public $evalAfectiva        = null;
    public int $edadPaciente    = 0;

    public function mount(AdultoMayor $adulto): void
    {
        if (!auth()->user()->hasAnyRole(['SUPERADMINISTRADOR', 'MEDICO GENERAL/GERIATRA'])) {
            abort(403);
        }
        $this->adulto = $adulto;
        $this->cargarDatos();
    }

    public function refreshData(): void
    {
        $this->adulto = $this->adulto->fresh(['estado']);
        $this->cargarDatos();
    }

    private function cargarDatos(): void
    {
        $cod = $this->adulto->cod_am;

        $this->fichaMedica = FichaMedicaAdulto::where('cod_am', $cod)
            ->where('estado', 'ACTIVO')
            ->latest()->first();

        $this->ultimosSignos = SignosVitalesAdulto::where('cod_am', $cod)
            ->where('estado', 'VIGENTE')
            ->orderByDesc('fecha')->orderByDesc('hora')
            ->first();

        $this->notasRecientes = NotaEvolucionMedica::where('cod_am', $cod)
            ->where('estado', 'ACTIVO')
            ->with('registrador')
            ->orderByDesc('fecha')->orderByDesc('hora')
            ->limit(20)->get()->toArray();

        $this->medicacionActiva = MedicacionAdulto::where('cod_am', $cod)
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre_medicamento')
            ->get()->toArray();

        $this->valoracionFuncional = ValoracionFuncionalAdulto::where('cod_am', $cod)
            ->vigente()
            ->latest('fecha_valoracion')
            ->first()?->toArray();

        $this->evalCognitiva = EvaluacionGeriatrica::where('cod_am', $cod)
            ->whereHas('instrumento', fn($q) => $q->where('cod_area', 'ARE_COG'))
            ->with('instrumento')
            ->latest('fecha_eval')->first();

        $this->evalAfectiva = EvaluacionGeriatrica::where('cod_am', $cod)
            ->whereHas('instrumento', fn($q) => $q->where('cod_area', 'ARE_AFE'))
            ->with('instrumento')
            ->latest('fecha_eval')->first();

        $this->edadPaciente = $this->adulto->fecha_nac
            ? \Carbon\Carbon::parse($this->adulto->fecha_nac)->age
            : 0;
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    public function nuevaNota(): void
    {
        $this->dispatch('abrir-nota-evolucion', cod_am: $this->adulto->cod_am);
    }

    public function nuevosSignos(): void
    {
        $this->dispatch('abrir-signos-vitales-medico', cod_am: $this->adulto->cod_am);
    }

    public function nuevaBarthel(): void
    {
        $this->dispatch('abrir-valoracion-barthel', cod_am: $this->adulto->cod_am);
    }

    public function nuevaEvaluacionGeriatrica(string $codArea): void
    {
        $this->dispatch('evaluacion-geriatrica-area-abrir', [
            'cod_am'   => $this->adulto->cod_am,
            'cod_area' => $codArea,
        ]);
    }

    public function render()
    {
        return view('livewire.clinica.ficha-clinica-integrada-panel')
            ->layout('layouts.sistema');
    }
}
