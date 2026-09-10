<?php

namespace App\Livewire\Valoraciones;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EvaluacionGeriatrica;
use App\Models\AreaGeriatrica;
use App\Models\InstrumentoGeriatrico;

class EvaluacionesAreaPanel extends Component
{
    use WithPagination;

    public string $codArea    = '';
    public string $nombreArea = '';
    public string $descripcionArea = '';
    public array  $areaConfig = [];
    public array  $instrumentosCodes = [];

    public string $busqueda          = '';
    public string $filtroAlerta      = '';
    public string $filtroInstrumento = '';

    protected $listeners = ['evaluacion-geriatrica-guardada' => '$refresh'];
    protected $queryString = ['busqueda', 'filtroAlerta', 'filtroInstrumento'];

    private const AREA_CONFIGS = [
        'ARE_COG' => ['icono' => 'ph-brain',              'color_bg' => 'bg-boton-acento/10',      'color_txt' => 'text-boton-acento'],
        'ARE_AFE' => ['icono' => 'ph-heart',              'color_bg' => 'bg-estado-peligroBg',     'color_txt' => 'text-estado-peligro'],
        'ARE_FUN' => ['icono' => 'ph-person-simple-walk', 'color_bg' => 'bg-estado-exitoBg',       'color_txt' => 'text-estado-exito'],
        'ARE_NUT' => ['icono' => 'ph-apple-logo',         'color_bg' => 'bg-estado-advertenciaBg', 'color_txt' => 'text-estado-advertencia'],
        'ARE_SOC' => ['icono' => 'ph-users-four',         'color_bg' => 'bg-estado-infoBg',        'color_txt' => 'text-estado-info'],
    ];

    public function mount(string $codArea = ''): void
    {
        if (!auth()->user()->hasAnyRole(['SUPERADMINISTRADOR', 'PSICOLOGO/A'])) {
            abort(403);
        }

        // Fallback: detectar área desde el nombre de la ruta actual
        if (!$codArea) {
            $routeName = request()->route()?->getName() ?? '';
            $codArea = match(true) {
                str_ends_with($routeName, '.cognitiva')       => 'ARE_COG',
                str_ends_with($routeName, '.afectiva')        => 'ARE_AFE',
                str_ends_with($routeName, '.funcionamiento')  => 'ARE_FUN',
                str_ends_with($routeName, '.nutricional')     => 'ARE_NUT',
                str_ends_with($routeName, '.entorno')         => 'ARE_SOC',
                default => '',
            };
        }

        $this->codArea = $codArea;
        $area = AreaGeriatrica::find($codArea);
        $this->nombreArea     = $area?->nombre      ?? ucfirst(strtolower($codArea));
        $this->descripcionArea = $area?->descripcion ?? '';
        $this->areaConfig     = self::AREA_CONFIGS[$codArea]
            ?? ['icono' => 'ph-list', 'color_bg' => 'bg-fondo-panel', 'color_txt' => 'text-titulo'];

        $this->instrumentosCodes = InstrumentoGeriatrico::where('cod_area', $codArea)
            ->where('estado', 'ACTIVO')
            ->pluck('cod_instrumento')
            ->toArray();
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function nuevaEvaluacion(?string $codAm = null): void
    {
        $this->dispatch('evaluacion-geriatrica-area-abrir', [
            'cod_am'   => $codAm,
            'cod_area' => $this->codArea,
        ]);
    }

    public function render()
    {
        $query = EvaluacionGeriatrica::with(['adulto', 'instrumento', 'evaluador'])
            ->whereNull('deleted_at')
            ->whereIn('cod_instrumento', $this->instrumentosCodes);

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->whereHas('adulto', fn($q) =>
                $q->where('nombres', 'like', "%{$busq}%")
                  ->orWhere('ap_paterno', 'like', "%{$busq}%")
                  ->orWhere('ci', 'like', "%{$busq}%")
            );
        }

        if ($this->filtroAlerta) {
            $query->where('nivel_alerta', $this->filtroAlerta);
        }

        if ($this->filtroInstrumento) {
            $query->where('cod_instrumento', $this->filtroInstrumento);
        }

        $evaluaciones = $query->orderByDesc('fecha_eval')->paginate(15);

        $instrumentos = InstrumentoGeriatrico::where('cod_area', $this->codArea)
            ->where('estado', 'ACTIVO')
            ->get();

        $totalEvaluaciones = EvaluacionGeriatrica::whereIn('cod_instrumento', $this->instrumentosCodes)
            ->whereNull('deleted_at')->count();
        $alertasCriticas   = EvaluacionGeriatrica::whereIn('cod_instrumento', $this->instrumentosCodes)
            ->whereNull('deleted_at')->where('nivel_alerta', 'CRITICO')->count();
        $pacientesCubiertos = EvaluacionGeriatrica::whereIn('cod_instrumento', $this->instrumentosCodes)
            ->whereNull('deleted_at')->distinct('cod_am')->count('cod_am');

        return view('livewire.valoraciones.evaluaciones-area-panel', compact(
            'evaluaciones',
            'instrumentos',
            'totalEvaluaciones',
            'alertasCriticas',
            'pacientesCubiertos'
        ))->layout('layouts.sistema');
    }
}
