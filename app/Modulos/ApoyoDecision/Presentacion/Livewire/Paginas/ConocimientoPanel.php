<?php

namespace App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas;

use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarConocimientoAdministrativo;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ExplorarConocimientoExperto;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarResultadoLaboratorio;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ProyectarRedConocimiento;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\EscenariosPerfilExperimental;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\SesionLaboratorioExperimental;
use DomainException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use MotorExperto\investigacion\dcl\CatalogoPerfilCincoDominios;
use MotorExperto\investigacion\g09\CatalogoReglasG09Experimental;

final class ConocimientoPanel extends Component
{
    use WithPagination;

    #[Locked]
    public ?string $version = null;

    #[Locked]
    public ?string $ejecucion = null;

    #[Locked]
    public string $seccion = 'gobierno';

    #[Locked]
    public ?string $regla = null;

    #[Locked]
    public ?string $nodo = null;

    #[Locked]
    public ?string $errorOperacion = null;

    public string $escenarioSeleccionado = 'memoria-atencion';

    public string $buscarCasos = '';

    public string $tipoCaso = '';

    public string $ordenCasos = 'asc';

    public string $buscarReglas = '';

    public string $dominio = '';

    public string $estadoRegla = '';

    public string $subcomponente = '';

    public string $modoConocimiento = 'reglas';

    public string $versionComparada = '';

    public string $dominioRed = '';

    public string $buscarRed = '';

    public string $tipoRed = '';

    public string $relacionRed = '';

    public string $estadoRed = '';

    public string $tablaCatalogo = 'nodos_semanticos';

    public string $buscarCatalogo = '';

    #[Locked]
    public int $paginaRed = 1;

    #[Locked]
    public bool $detalleAbierto = false;

    #[Locked]
    public ?string $registroCatalogo = null;

    public function mount(): void
    {
        $this->autorizar();
    }

    public function ir(string $seccion): void
    {
        $this->autorizar();
        abort_unless(in_array($seccion, ['gobierno', 'laboratorio', 'casos', 'resultados', 'conocimiento', 'verificacion'], true), 422);
        $this->seccion = $seccion;
    }

    public function explorarCategoria(string $tabla): void
    {
        $this->autorizar();
        abort_unless(isset(PaqueteConocimiento::CLAVES[$tabla]), 422);
        $this->seccion = 'conocimiento';
        $this->modoConocimiento = 'catalogo';
        $this->tablaCatalogo = $tabla;
        $this->buscarCatalogo = '';
        $this->registroCatalogo = null;
        $this->detalleAbierto = false;
        $this->resetPage('catalogoPage');
    }

    public function explorarRed(): void
    {
        $this->autorizar();
        $this->seccion = 'conocimiento';
        $this->modoConocimiento = 'red';
        $this->limpiarRed();
    }

    public function seleccionarVersion(string $id): void
    {
        $this->autorizar();
        abort_unless($id === '' || app(ConsultarConocimientoAdministrativo::class)->existeVersion($id), 404);
        $this->version = $id === '' ? null : $id;
        $this->regla = $this->nodo = null;
        $this->detalleAbierto = false;
        $this->registroCatalogo = null;
        $this->paginaRed = 1;
        $this->dominioRed = '';
        $this->resetPage('reglasPage');
        $this->resetPage('catalogoPage');
    }

    public function ejecutarPerfil(): void
    {
        $this->autorizar();
        $this->validate(['escenarioSeleccionado' => ['required', Rule::in(array_keys(EscenariosPerfilExperimental::NOMBRES))]]);
        $this->errorOperacion = null;
        try {
            $this->ejecucion = app(SesionLaboratorioExperimental::class)->ejecutar(Auth::user(), $this->escenarioSeleccionado);
            $this->seccion = 'resultados';
        } catch (DomainException $e) {
            report($e);
            $this->errorOperacion = 'No se pudo completar la evaluación. El resultado anterior se conserva. Revise la integridad del caso con el equipo técnico e inténtelo de nuevo.';
        }
    }

    public function elegirCaso(string $caso): void
    {
        $this->autorizar();
        abort_unless(isset(EscenariosPerfilExperimental::NOMBRES[$caso]), 422);
        $this->escenarioSeleccionado = $caso;
    }

    public function abrirEjecucion(string $id): void
    {
        $this->autorizar();
        app(SesionLaboratorioExperimental::class)->consultar(Auth::user(), $id);
        $this->ejecucion = $id;
        $this->seccion = 'resultados';
    }

    public function abrirRegla(string $id): void
    {
        $this->autorizar();
        $c = app(ExplorarConocimientoExperto::class)->obtener($this->version);
        abort_unless(isset($c['reglas'][$id]), 404);
        $this->regla = $id;
        $this->nodo = $this->registroCatalogo = null;
        $this->detalleAbierto = true;
    }

    public function abrirNodo(string $id): void
    {
        $this->autorizar();
        $c = app(ExplorarConocimientoExperto::class)->obtener($this->version);
        abort_unless(isset($c['nodos'][$id]), 404);
        $this->nodo = $id;
    }

    public function cerrarDetalle(): void
    {
        $this->autorizar();
        $this->detalleAbierto = false;
        $this->dispatch('expert-detail-closed');
    }

    public function abrirRegistro(string $id): void
    {
        $this->autorizar();
        $c = app(ExplorarConocimientoExperto::class)->obtener($this->version);
        $pk = PaqueteConocimiento::CLAVES[$this->tablaCatalogo] ?? null;
        abort_unless($pk && collect($c['tablas'][$this->tablaCatalogo] ?? [])->contains($pk, $id), 404);
        $this->registroCatalogo = $id;
        $this->regla = $this->nodo = null;
        $this->detalleAbierto = true;
    }

    public function cambiarPaginaRed(int $pagina): void
    {
        $this->autorizar();
        $this->paginaRed = max(1, $pagina);
        $this->nodo = null;
    }

    public function limpiarRed(): void
    {
        $this->autorizar();
        $this->buscarRed = $this->tipoRed = $this->relacionRed = $this->estadoRed = $this->dominioRed = '';
        $this->paginaRed = 1;
        $this->nodo = null;
    }

    public function updated(string $campo): void
    {
        $this->autorizar();
        if (in_array($campo, ['buscarCasos', 'tipoCaso', 'ordenCasos'], true)) {
            $this->resetPage('casosPage');
        }
        if (in_array($campo, ['buscarReglas', 'dominio', 'estadoRegla', 'subcomponente'], true)) {
            $this->resetPage('reglasPage');
        }
        if (in_array($campo, ['buscarRed', 'tipoRed', 'relacionRed', 'estadoRed', 'dominioRed'], true)) {
            $this->paginaRed = 1;
            $this->nodo = null;
        }
        if (in_array($campo, ['tablaCatalogo', 'buscarCatalogo'], true)) {
            $this->resetPage('catalogoPage');
            $this->detalleAbierto = false;
            $this->registroCatalogo = null;
        }
    }

    public function render()
    {
        $this->autorizar();
        $versiones = app(ConsultarConocimientoAdministrativo::class)->versiones();
        $laboratorioDisponible = app()->environment(['local', 'testing']);
        $historial = $laboratorioDisponible ? app(SesionLaboratorioExperimental::class)->historial(Auth::user()) : [];
        $informe = null;
        if ($laboratorioDisponible && $this->ejecucion !== null) {
            $informe = app(PresentarResultadoLaboratorio::class)->construir(app(SesionLaboratorioExperimental::class)->consultar(Auth::user(), $this->ejecucion));
        }
        $casos = [];
        foreach (EscenariosPerfilExperimental::NOMBRES as $id => $nombre) {
            $entrada = EscenariosPerfilExperimental::entrada($id);
            $ultima = collect($historial)->firstWhere('escenario', $id);
            $tipo = match ($id) {
                'incompleto', 'iadl-incompleta', 'diferencial', 'sin-referencias' => 'insuficiente',
                'contradictorio' => 'contradictorio', default => 'petersen',
                'sin-hallazgos' => 'negativo', 'no-interpretable' => 'no interpretable',
            };
            $casos[] = ['id' => $id, 'nombre' => $nombre, 'corte' => $entrada['contexto']['fecha_corte'],
                'tipo' => $tipo, 'ultima' => $ultima, 'version' => CatalogoReglasG09Experimental::VERSION];
        }
        $casos = collect($casos)->filter(fn ($c) => ($this->tipoCaso === '' || $c['tipo'] === $this->tipoCaso)
            && str_contains(mb_strtolower($c['id'].' '.$c['nombre']), mb_strtolower(mb_substr($this->buscarCasos, 0, 120))))
            ->sortBy('nombre', SORT_NATURAL, $this->ordenCasos === 'desc');
        $casos = $this->paginar($casos->values()->all(), 'casosPage');
        $conocimiento = $errorConocimiento = $red = null;
        $comparacion = [];
        try {
            $explorador = app(ExplorarConocimientoExperto::class);
            $conocimiento = $explorador->obtener($this->version);
            $red = (new ProyectarRedConocimiento)->presentar($conocimiento, $this->version, [
                'buscar' => $this->buscarRed, 'tipo' => $this->tipoRed, 'relacion' => $this->relacionRed,
                'estado' => $this->estadoRed, 'criterio' => $this->dominioRed,
            ], $this->paginaRed);
            if ($this->seccion === 'conocimiento' && $this->modoConocimiento === 'versiones' && $this->versionComparada !== '') {
                abort_unless(app(ConsultarConocimientoAdministrativo::class)->existeVersion($this->versionComparada), 404);
                $comparacion = $explorador->comparar($this->version, $this->versionComparada);
            }
        } catch (DomainException $e) {
            report($e);
            $errorConocimiento = 'La versión no superó los controles de integridad. No se mostrará contenido parcial; seleccione otra versión o solicite su revisión técnica.';
            $conocimiento = $red = null;
        }
        $reglas = collect($conocimiento['reglas'] ?? [])->filter(fn ($r) => ($this->dominio === '' || $r['dominio'] === $this->dominio)
            && ($this->estadoRegla === '' || $r['estado'] === $this->estadoRegla)
            && ($this->subcomponente === '' || $r['subcomponente'] === $this->subcomponente)
            && str_contains(mb_strtolower($r['codigo'].' '.($r['nombre'] ?? '').' '.($r['fundamento'] ?? '')), mb_strtolower(mb_substr($this->buscarReglas, 0, 120))))->values()->all();
        $reglas = $this->paginar($reglas, 'reglasPage');
        $dominios = CatalogoPerfilCincoDominios::DOMINIOS;
        $escenariosPerfil = EscenariosPerfilExperimental::NOMBRES;
        $pkCatalogo = PaqueteConocimiento::CLAVES[$this->tablaCatalogo] ?? null;
        $filasCatalogo = collect($pkCatalogo ? ($conocimiento['tablas'][$this->tablaCatalogo] ?? []) : [])
            ->filter(fn ($r) => str_contains(mb_strtolower(implode(' ', array_filter($r, 'is_scalar'))), mb_strtolower(mb_substr($this->buscarCatalogo, 0, 120))));
        $detalleRegistro = $this->registroCatalogo && $pkCatalogo ? $filasCatalogo->firstWhere($pkCatalogo, $this->registroCatalogo) : null;
        $fuentesNodo = $conocimiento && $this->nodo ? (new ProyectarRedConocimiento)->fuentesNodo($conocimiento, $this->nodo) : [];
        $filasCatalogo = $this->paginar($filasCatalogo->values()->all(), 'catalogoPage');

        return view('livewire.superadministrador.sistema-experto.conocimiento-panel', compact('versiones', 'laboratorioDisponible', 'historial', 'informe', 'casos', 'conocimiento', 'errorConocimiento', 'reglas', 'dominios', 'escenariosPerfil', 'comparacion', 'red', 'filasCatalogo', 'pkCatalogo', 'detalleRegistro', 'fuentesNodo'))
            ->layout('layouts.sistema');
    }

    private function paginar(array $filas, string $nombre): LengthAwarePaginator
    {
        $pagina = max(1, (int) $this->getPage($nombre));

        return new LengthAwarePaginator(array_slice($filas, ($pagina - 1) * 6, 6), count($filas), 6, $pagina,
            ['path' => request()->url(), 'pageName' => $nombre]);
    }

    private function autorizar(): void
    {
        $actual = User::query()->findOrFail(Auth::id());
        Gate::forUser($actual)->authorize('consultar', VersionModeloExperto::class);
    }
}
