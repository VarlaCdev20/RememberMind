<?php

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Paginas;

use App\Models\AplicacionInstrumento;
use App\Models\User;
use App\Modulos\Valoraciones\Aplicacion\Consultas\InstrumentosDelArea;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class EvaluacionesAreaPanel extends Component
{
    use WithPagination;

    public string $codArea = '';

    public string $nombreArea = '';

    public string $descripcionArea = '';

    public array $areaConfig = [];

    #[Locked]
    public array $instrumentosCodes = [];

    public string $busqueda = '';

    public string $filtroAlerta = '';

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
        $this->autorizar();

        // Fallback: detectar área desde el nombre de la ruta actual
        if (! $codArea) {
            $routeName = request()->route()?->getName() ?? '';
            $codArea = match (true) {
                str_ends_with($routeName, '.cognitiva') => 'ARE_COG',
                str_ends_with($routeName, '.afectiva') => 'ARE_AFE',
                str_ends_with($routeName, '.funcionamiento') => 'ARE_FUN',
                str_ends_with($routeName, '.nutricional') => 'ARE_NUT',
                str_ends_with($routeName, '.entorno') => 'ARE_SOC',
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
        $this->areaConfig = self::AREA_CONFIGS[$codArea]
            ?? ['icono' => 'ph-list', 'color_bg' => 'bg-fondo-panel', 'color_txt' => 'text-titulo'];

        $this->instrumentosCodes = app(InstrumentosDelArea::class)->catalogados($codArea)
            ->pluck('cod_instrumento')
            ->toArray();
    }

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function nuevaEvaluacion(?string $codResidente = null): void
    {
        $usuario = $this->autorizar();
        abort_unless($usuario->can('aplicaciones_instrumento.crear'), 403);
        abort_unless(app(InstrumentosDelArea::class)->activos($this->codArea)->exists(), 422, 'No hay instrumentos habilitados para esta área.');
        $this->dispatch('evaluacion-geriatrica-area-abrir', [
            'cod_residente' => $codResidente,
            'cod_area' => $this->codArea,
        ]);
    }

    public function render()
    {
        $this->autorizar();
        $query = AplicacionInstrumento::with(['adulto', 'instrumento', 'evaluador.usuario'])
            ->whereIn('cod_instrumento', $this->instrumentosCodes);

        if ($this->busqueda) {
            $busq = $this->busqueda;
            $query->whereHas('adulto', fn ($q) => $q->where('nombres', 'like', "%{$busq}%")
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

        $instrumentos = app(InstrumentosDelArea::class)->catalogados($this->codArea)
            ->get();
        $instrumentosActivos = $instrumentos->where('estado', 'ACTIVO');

        $totalEvaluaciones = AplicacionInstrumento::whereIn('cod_instrumento', $this->instrumentosCodes)->count();
        $alertasCriticas = AplicacionInstrumento::whereIn('cod_instrumento', $this->instrumentosCodes)
            ->where('clasificacion', 'CRITICO')->count();
        $pacientesCubiertos = AplicacionInstrumento::whereIn('cod_instrumento', $this->instrumentosCodes)
            ->distinct('cod_residente')->count('cod_residente');

        return view('livewire.valoraciones.evaluaciones-area-panel', compact(
            'evaluaciones',
            'instrumentos',
            'instrumentosActivos',
            'totalEvaluaciones',
            'alertasCriticas',
            'pacientesCubiertos'
        ))->layout('layouts.sistema');
    }

    private function autorizar(): User
    {
        $usuario = auth()->user() ? User::query()->find(auth()->id()) : null;
        abort_unless($usuario?->estado === 'ACTIVO'
            && $usuario->hasAnyRole(['SUPERADMINISTRADOR', 'PSICOLOGO/A'])
            && ! $usuario->hasRole('FAMILIAR')
            && $usuario->checkPermissionTo('aplicaciones_instrumento.ver', 'web')
            && $usuario->checkPermissionTo('instrumentos.ver', 'web')
            && ($usuario->hasRole('SUPERADMINISTRADOR') || $usuario->personal()->where('estado', 'ACTIVO')->exists()), 403);

        return $usuario;
    }
}
