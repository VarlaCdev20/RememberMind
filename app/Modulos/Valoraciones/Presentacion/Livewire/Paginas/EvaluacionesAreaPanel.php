<?php

namespace App\Modulos\Valoraciones\Presentacion\Livewire\Paginas;

use App\Models\AplicacionInstrumento;
use App\Models\Residente;
use App\Models\User;
use App\Models\ValoracionPetersenOriginal;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Valoraciones\Aplicacion\Consultas\ExploradorInstrumentos;
use App\Modulos\Valoraciones\Aplicacion\Consultas\InstrumentosDelArea;
use App\Modulos\Valoraciones\Aplicacion\Consultas\PrepararCapturaInstrumental;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpKernel\Exception\HttpException;

class EvaluacionesAreaPanel extends Component
{
    use WithPagination;

    #[Locked]
    public string $codArea = '';

    public string $nombreArea = '';

    public string $descripcionArea = '';

    public array $areaConfig = [];

    #[Locked]
    public array $instrumentosCodes = [];

    public string $busqueda = '';

    public string $busquedaValoracion = '';

    public string $filtroAlerta = '';

    public string $filtroInstrumento = '';

    public string $buscarInstrumento = '';

    public string $biblioteca = 'catalogo';

    public string $dominio = '';

    public string $estadoInstrumento = '';

    public string $tipoInstrumento = '';

    public string $modalidadInstrumento = '';

    #[Locked]
    public ?array $guia = null;

    #[Locked]
    public bool $mostrarGuia = false;

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
        abort_unless(in_array($codArea, ['ARE_COG', 'ARE_AFE', 'ARE_FUN', 'ARE_SOC', 'ARE_NUT'], true), 404);
        abort_if($codArea === 'ARE_NUT' && ! auth()->user()->hasRole('SUPERADMINISTRADOR'), 403);
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

    public function updated(string $property): void
    {
        if (in_array($property, ['filtroAlerta', 'filtroInstrumento'], true)) {
            $this->resetPage();
        }
        if ($property === 'biblioteca') {
            $this->reset(['dominio', 'estadoInstrumento', 'tipoInstrumento', 'modalidadInstrumento']);
        }
    }

    public function limpiarExplorador(): void
    {
        $this->reset(['buscarInstrumento', 'dominio', 'estadoInstrumento', 'tipoInstrumento', 'modalidadInstrumento']);
    }

    public function abrirGuia(string $id): void
    {
        $this->autorizar();
        $explorador = app(ExploradorInstrumentos::class);
        $tarjeta = $explorador->tarjetas($this->codArea)->firstWhere('id', $id);
        abort_unless($tarjeta, 404);
        $this->guia = $explorador->guia($tarjeta);
        $this->mostrarGuia = true;
    }

    public function cerrarGuia(): void
    {
        $this->autorizar();
        $this->mostrarGuia = false;
    }

    public function nuevaEvaluacion(?string $codResidente = null, ?string $codInstrumento = null): void
    {
        $usuario = $this->autorizar();
        abort_unless($usuario->hasRole('PSICOLOGO/A') && $this->puedeCapturar($usuario), 403);
        if ($codResidente !== null) {
            app(TurnoEnfermeriaService::class)->autorizarConsultaResidente($codResidente, $usuario);
            app(PrepararCapturaInstrumental::class)->contexto($usuario, $codResidente);
        }
        $habilitados = app(InstrumentosDelArea::class)->activos($this->codArea)->whereHas('preguntas', fn ($q) => $q->where('estado', 'ACTIVA'));
        abort_unless(($codInstrumento === null ? $habilitados : $habilitados->whereKey($codInstrumento))->exists(), 422, 'No hay un instrumento con preguntas vigentes habilitado para esta área.');
        $this->dispatch('evaluacion-geriatrica-area-abrir', [
            'cod_residente' => $codResidente,
            'cod_area' => $this->codArea,
            'cod_instrumento' => $codInstrumento,
        ]);
    }

    public function render()
    {
        $usuario = $this->autorizar();
        $this->instrumentosCodes = app(InstrumentosDelArea::class)->catalogados($this->codArea)->pluck('cod_instrumento')->all();
        $residentesPermitidos = app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosQuery($usuario);
        if (! Gate::forUser($usuario)->allows('viewAny', Residente::class)) {
            $residentesPermitidos->whereRaw('1 = 0');
        }
        $base = AplicacionInstrumento::query()->whereIn('cod_instrumento', $this->instrumentosCodes)
            ->whereIn('cod_residente', (clone $residentesPermitidos)->select('cod_residente'));
        $query = (clone $base)->with(['adulto', 'instrumento', 'evaluador']);

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

        $evaluaciones = $query->orderByDesc('fecha_hora')->orderByDesc('cod_aplicacion')->paginate(15);

        $instrumentos = app(InstrumentosDelArea::class)->catalogados($this->codArea)
            ->withCount(['preguntas as preguntas_vigentes' => fn ($q) => $q->where('estado', 'ACTIVA')])
            ->get();
        $instrumentosActivos = $instrumentos->where('estado', 'ACTIVO')->where('preguntas_vigentes', '>', 0);

        $totalEvaluaciones = (clone $base)->count();
        $alertasCriticas = (clone $base)->where('clasificacion', 'CRITICO')->count();
        $pacientesCubiertos = (clone $base)->distinct()->count('cod_residente');
        $puedeCapturar = $this->puedeCapturar($usuario);
        $explorador = app(ExploradorInstrumentos::class);
        $todasTarjetas = $explorador->tarjetas($this->codArea);
        $tarjetas = $explorador->filtrar($todasTarjetas, $this->buscarInstrumento, $this->biblioteca, $this->dominio,
            $this->estadoInstrumento, $this->tipoInstrumento, $this->modalidadInstrumento);
        $tiposExplorador = $todasTarjetas->filter(fn ($t) => ($this->biblioteca === 'candidatos') === ($t['estado'] === 'CANDIDATO_INVESTIGADO'))->pluck('tipo')->unique();
        $modalidadesExplorador = $todasTarjetas->pluck('modalidad')->unique();
        $residentesContinuidad = collect();
        if (in_array($this->codArea, ['ARE_AFE', 'ARE_SOC', 'ARE_FUN'], true)) {
            $residentesContinuidad = (clone $residentesPermitidos)->where('estado', 'ADMITIDO')
                ->when($this->busquedaValoracion !== '', fn ($q) => $q->where(fn ($n) => $n->where('nombres', 'like', '%'.mb_substr($this->busquedaValoracion, 0, 80).'%')
                    ->orWhere('apellido_paterno', 'like', '%'.mb_substr($this->busquedaValoracion, 0, 80).'%')))
                ->orderBy('apellido_paterno')->orderBy('cod_residente')->limit(8)->get();
        }

        $residentesValoracion = collect();
        if ($this->codArea === 'ARE_COG' && $usuario->hasRole('PSICOLOGO/A')
            && $usuario->checkPermissionTo('valoraciones_petersen.crear', 'web')) {
            $busquedaValoracion = trim(mb_substr($this->busquedaValoracion, 0, 80));
            $residentesValoracion = (clone $residentesPermitidos)->where('estado', 'ADMITIDO')
                ->when($busquedaValoracion !== '', static fn ($query) => $query->where(static function ($q) use ($busquedaValoracion): void {
                    $q->where('nombres', 'like', "%{$busquedaValoracion}%")
                        ->orWhere('apellido_paterno', 'like', "%{$busquedaValoracion}%")
                        ->orWhere('apellido_materno', 'like', "%{$busquedaValoracion}%");
                }))
                ->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombres')
                ->limit(8)->get()
                ->filter(static fn (Residente $residente) => Gate::forUser($usuario)
                    ->allows('crearOriginal', [ValoracionPetersenOriginal::class, $residente]));
        }

        $residentesValoracion = collect();
        if ($this->codArea === 'ARE_COG' && $usuario->hasRole('PSICOLOGO/A')
            && $usuario->checkPermissionTo('valoraciones_petersen.crear', 'web')) {
            $busquedaValoracion = trim(mb_substr($this->busquedaValoracion, 0, 80));
            $residentesValoracion = Residente::query()->where('estado', 'ADMITIDO')
                ->when($busquedaValoracion !== '', static fn ($query) => $query->where(static function ($q) use ($busquedaValoracion): void {
                    $q->where('nombres', 'like', "%{$busquedaValoracion}%")
                        ->orWhere('apellido_paterno', 'like', "%{$busquedaValoracion}%")
                        ->orWhere('apellido_materno', 'like', "%{$busquedaValoracion}%");
                }))
                ->orderBy('apellido_paterno')->orderBy('apellido_materno')->orderBy('nombres')
                ->limit(8)->get()
                ->filter(static fn (Residente $residente) => Gate::forUser($usuario)
                    ->allows('crearOriginal', [ValoracionPetersenOriginal::class, $residente]));
        }

        return view('livewire.valoraciones.evaluaciones-area-panel', compact(
            'evaluaciones',
            'instrumentos',
            'instrumentosActivos',
            'totalEvaluaciones',
            'alertasCriticas',
            'pacientesCubiertos',
            'residentesValoracion', 'tarjetas', 'todasTarjetas', 'puedeCapturar', 'tiposExplorador', 'modalidadesExplorador', 'residentesContinuidad'
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
            && Gate::forUser($usuario)->allows('viewAny', Residente::class)
            && ($usuario->hasRole('SUPERADMINISTRADOR') || $usuario->personal()->where('estado', 'ACTIVO')->where('profesion', 'PSICOLOGIA')->exists()), 403);

        return $usuario;
    }

    private function puedeCapturar(User $usuario): bool
    {
        if (! $usuario->hasRole('PSICOLOGO/A') || $this->codArea === 'ARE_NUT') {
            return false;
        }
        try {
            app(PrepararCapturaInstrumental::class)->usuario($usuario);
        } catch (HttpException $e) {
            if ($e->getStatusCode() !== 403) {
                throw $e;
            }

            return false;
        }

        return true;
    }
}
