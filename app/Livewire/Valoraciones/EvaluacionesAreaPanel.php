<?php

namespace App\Livewire\Valoraciones;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AplicacionInstrumento;
use App\Models\Instrumento;

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
        $nombres = [
            'ARE_COG' => 'Evaluación cognitiva', 'ARE_AFE' => 'Evaluación afectiva',
            'ARE_FUN' => 'Funcionamiento', 'ARE_NUT' => 'Evaluación nutricional',
            'ARE_SOC' => 'Entorno social',
        ];
        $this->nombreArea = $nombres[$codArea] ?? 'Instrumentos de valoración';
        $this->descripcionArea = 'Aplicaciones registradas con el catálogo de instrumentos V2.';
        $this->areaConfig     = self::AREA_CONFIGS[$codArea]
            ?? ['icono' => 'ph-list', 'color_bg' => 'bg-fondo-panel', 'color_txt' => 'text-titulo'];

        $this->instrumentosCodes = Instrumento::query()
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
        $query = AplicacionInstrumento::with(['adulto', 'instrumento', 'evaluador.usuario'])
            ->whereIn('cod_instrumento', $this->instrumentosCodes);

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->whereHas('adulto', fn($q) =>
                $q->where('nombres', 'like', "%{$busq}%")
                  ->orWhere('apellido_paterno', 'like', "%{$busq}%")
                  ->orWhere('numero_documento', 'like', "%{$busq}%")
            );
        }

        if ($this->filtroAlerta) {
            $query->where('clasificacion', $this->filtroAlerta);
        }

        if ($this->filtroInstrumento) {
            $query->where('cod_instrumento', $this->filtroInstrumento);
        }

        $evaluaciones = $query->orderByDesc('fecha_hora')->paginate(15);

        $instrumentos = Instrumento::query()
            ->where('estado', 'ACTIVO')
            ->get();

        $totalEvaluaciones = AplicacionInstrumento::whereIn('cod_instrumento', $this->instrumentosCodes)->count();
        $alertasCriticas = AplicacionInstrumento::whereIn('cod_instrumento', $this->instrumentosCodes)
            ->where('clasificacion', 'CRITICO')->count();
        $pacientesCubiertos = AplicacionInstrumento::whereIn('cod_instrumento', $this->instrumentosCodes)
            ->distinct('cod_residente')->count('cod_residente');

        return view('livewire.valoraciones.evaluaciones-area-panel', compact(
            'evaluaciones',
            'instrumentos',
            'totalEvaluaciones',
            'alertasCriticas',
            'pacientesCubiertos'
        ))->layout('layouts.sistema');
    }
}
