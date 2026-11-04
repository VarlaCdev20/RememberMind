<?php

namespace App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas;

use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarConocimientoAdministrativo;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ExplorarConocimientoExperto;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ProyectarRedConocimiento;
use App\Modulos\ApoyoDecision\Presentacion\Soporte\DatosConocimientoVisible;
use DomainException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use MotorExperto\conocimiento\PaqueteConocimiento;

final class ConocimientoPanel extends Component
{
    use WithPagination;

    #[Locked]
    public ?string $version = null;

    #[Locked]
    public string $seccion = 'gobierno';

    #[Locked]
    public ?string $regla = null;

    #[Locked]
    public ?string $nodo = null;

    #[Locked]
    public ?string $errorOperacion = null;

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

    #[Locked]
    public array $recorridoDatos = [];

    public function mount(): void
    {
        $this->autorizar();
        $version = request()->query('version');
        if ($version !== null) {
            abort_unless(is_string($version), 422);
            $this->seleccionarVersion($version);
        }
        $dato = request()->query('dato');
        if ($dato !== null) {
            $categoria = request()->query('categoria', 'reglas_expertas');
            abort_unless(is_string($dato) && is_string($categoria), 422);
            $this->explorarCategoria($categoria);
            $this->abrirDato($categoria, $dato);
        }
    }

    public function ir(string $seccion): void
    {
        $this->autorizar();
        abort_unless(in_array($seccion, ['gobierno', 'conocimiento', 'residentes', 'verificacion'], true), 422);
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
        $this->recorridoDatos = [];
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
        $this->recorridoDatos = [];
        $this->regla = $this->nodo = null;
        $this->detalleAbierto = false;
        $this->registroCatalogo = null;
        $this->paginaRed = 1;
        $this->dominioRed = '';
        $this->resetPage('reglasPage');
        $this->resetPage('catalogoPage');
    }

    public function abrirRegla(string $id): void
    {
        $this->autorizar();
        $c = app(ExplorarConocimientoExperto::class)->obtener($this->version);
        abort_unless(isset($c['reglas'][$id]), 404);
        $this->regla = $id;
        $this->nodo = $this->registroCatalogo = null;
        $this->detalleAbierto = true;
        $this->registrarPaso('reglas_expertas', $id);
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
        $this->registrarPaso($this->tablaCatalogo, $id);
    }

    public function abrirDato(string $tabla, string $id): void
    {
        $this->autorizar();
        abort_unless(isset(DatosConocimientoVisible::CATEGORIAS[$tabla]), 422);
        $conocimiento = app(ExplorarConocimientoExperto::class)->obtener($this->version);
        $pk = PaqueteConocimiento::CLAVES[$tabla];
        abort_unless(collect($conocimiento['tablas'][$tabla] ?? [])->contains($pk, $id), 404);
        $this->tablaCatalogo = $tabla;
        $this->registroCatalogo = $id;
        $this->regla = null;
        $this->detalleAbierto = true;
        $this->registrarPaso($tabla, $id);
    }

    public function volverDato(): void
    {
        $this->autorizar();
        if (count($this->recorridoDatos) < 2) {
            return;
        }
        array_pop($this->recorridoDatos);
        $paso = array_pop($this->recorridoDatos);
        $this->abrirDato($paso['tabla'], $paso['id']);
    }

    private function registrarPaso(string $tabla, string $id): void
    {
        $paso = compact('tabla', 'id');
        if (end($this->recorridoDatos) !== $paso) {
            $this->recorridoDatos[] = $paso;
            $this->recorridoDatos = array_slice($this->recorridoDatos, -12);
        }
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
        $dominios = ['COG-MEM' => 'Memoria', 'COG-ATE' => 'Atención', 'COG-EJE' => 'Funciones ejecutivas', 'COG-LEN' => 'Lenguaje', 'COG-VIS' => 'Visuoespacial'];
        $pkCatalogo = PaqueteConocimiento::CLAVES[$this->tablaCatalogo] ?? null;
        $filasCatalogo = collect($pkCatalogo ? ($conocimiento['tablas'][$this->tablaCatalogo] ?? []) : [])
            ->filter(fn ($r) => str_contains(mb_strtolower(implode(' ', array_filter($r, 'is_scalar'))), mb_strtolower(mb_substr($this->buscarCatalogo, 0, 120))));
        $detalleRegistro = $this->registroCatalogo && $pkCatalogo ? collect($conocimiento['tablas'][$this->tablaCatalogo] ?? [])->firstWhere($pkCatalogo, $this->registroCatalogo) : null;
        $presentadorDatos = new DatosConocimientoVisible;
        $categoriasDatos = $presentadorDatos->categorias($conocimiento['tablas'] ?? []);
        $fichaDato = null;
        if ($conocimiento && ($this->regla || $detalleRegistro)) {
            $fichaDato = $presentadorDatos->detalle($conocimiento['tablas'], $this->regla ? 'reglas_expertas' : $this->tablaCatalogo,
                $this->regla ?? $this->registroCatalogo);
        }
        $fuentesNodo = $conocimiento && $this->nodo ? (new ProyectarRedConocimiento)->fuentesNodo($conocimiento, $this->nodo) : [];
        $filasCatalogo = $this->paginar($filasCatalogo->values()->all(), 'catalogoPage');

        return view('livewire.superadministrador.sistema-experto.conocimiento-panel', compact('versiones', 'conocimiento', 'errorConocimiento', 'reglas', 'dominios', 'comparacion', 'red', 'filasCatalogo', 'pkCatalogo', 'detalleRegistro', 'fuentesNodo', 'categoriasDatos', 'fichaDato'))
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
