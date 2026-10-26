<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\CapturasInstrumentalesResidente;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\LecturaResultadosExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\PanoramaResidentes;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\CohorteCognitivaLocal;
use App\Modulos\ApoyoDecision\Presentacion\Soporte\ConexionesResidenteVisible;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

/** Consulta administrativa del expediente; reutiliza las Policies clínicas de lectura. */
final class ResidentesConocimientoPanel extends Component
{
    use WithPagination;

    public string $buscar = '';

    #[Locked]
    public ?string $seleccionado = null;

    #[Locked]
    public ?string $fuente = null;

    #[Locked]
    public string $campoPanorama = 'diagnosticos';

    #[Locked]
    public ?string $patronPanorama = null;

    #[Locked]
    public bool $soloInvestigacion = false;

    public function cambiarPoblacion(bool $investigacion): void
    {
        $this->autorizar();
        abort_if($investigacion && ! CohorteCognitivaLocal::habilitada(), 404);
        $this->soloInvestigacion = $investigacion;
        $this->buscar = '';
        $this->patronPanorama = $this->seleccionado = $this->fuente = null;
        $this->resetPage('residentesConocimiento');
    }

    public function cambiarCampoPanorama(string $campo): void
    {
        $usuario = $this->autorizar();
        abort_unless(isset(PanoramaResidentes::CAMPOS[$campo]), 422);
        abort_unless($usuario->checkPermissionTo($campo.'.ver', 'web'), 403);
        $this->campoPanorama = $campo;
        $this->patronPanorama = null;
        $this->resetPage('residentesConocimiento');
    }

    public function filtrarPatron(?string $patron = null): void
    {
        $usuario = $this->autorizar();
        $panorama = app(PanoramaResidentes::class)->consultar($usuario, $this->buscar, $this->campoPanorama, $patron, 1, $this->soloInvestigacion);
        abort_unless($patron === null || $patron === 'sin-registro' || $panorama['activa'] !== null, 404);
        $this->patronPanorama = $patron;
        $this->seleccionado = $this->fuente = null;
        $this->resetPage('residentesConocimiento');
    }

    public function mount(): void
    {
        $this->autorizar();
    }

    private function autorizar(): User
    {
        $usuario = User::query()->findOrFail(auth()->id());
        Gate::forUser($usuario)->authorize('consultar', VersionModeloExperto::class);
        Gate::forUser($usuario)->authorize('viewAny', Residente::class);

        return $usuario;
    }

    public function updatedBuscar(): void
    {
        $this->autorizar();
        $this->patronPanorama = null;
        $this->seleccionado = $this->fuente = null;
        $this->resetPage('residentesConocimiento');
    }

    public function seleccionar(string $id): void
    {
        $usuario = $this->autorizar();
        $residente = Residente::query()->where('estado', 'ADMITIDO')->findOrFail($id);
        Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
        $this->seleccionado = $id;
        $this->fuente = null;
        $this->resetPage('historia');
    }

    public function abrirFuente(string $id): void
    {
        $usuario = $this->autorizar();
        $residente = Residente::query()->where('estado', 'ADMITIDO')->findOrFail($this->seleccionado);
        $capturas = app(CapturasInstrumentalesResidente::class)->consultar($usuario, $residente, CarbonImmutable::now());
        abort_unless(collect($capturas['capturas'])->contains('cod_aplicacion', $id), 404);
        $this->fuente = $id;
    }

    public function reintentar(): void
    {
        $this->autorizar();
    }

    public function render()
    {
        $usuario = $this->autorizar();
        $panorama = null;
        $detallePanorama = [];
        $errorPanorama = false;
        $puedePanorama = collect(['residentes.ver', 'valoracion_medica.ver', 'controles_cognitivos.ver', $this->campoPanorama.'.ver'])
            ->every(fn ($permiso) => $usuario->checkPermissionTo($permiso, 'web'));
        if ($puedePanorama) {
            try {
                $panorama = app(PanoramaResidentes::class)->consultar($usuario, $this->buscar, $this->campoPanorama, $this->patronPanorama, $this->getPage('residentesConocimiento'), $this->soloInvestigacion);
            } catch (QueryException $e) {
                report($e);
                $errorPanorama = true;
            }
        }
        if ($panorama) {
            $residentes = $panorama['residentes'];
        } else {
            // El acceso al directorio no depende del permiso del campo estadístico.
            $query = PanoramaResidentes::limitarPoblacion(Residente::query()->where('estado', 'ADMITIDO')->with('cama.habitacion'), $this->soloInvestigacion);
            foreach (preg_split('/\s+/u', mb_substr(trim($this->buscar), 0, 100), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $termino) {
                $query->where(fn ($q) => $q->whereLike('nombres', "%{$termino}%")
                    ->orWhereLike('apellido_paterno', "%{$termino}%")->orWhereLike('apellido_materno', "%{$termino}%")
                    ->orWhereLike('cod_residente', "%{$termino}%"));
            }
            $residentes = $query->orderBy('apellido_paterno')->orderBy('nombres')->orderBy('cod_residente')
                ->paginate(8, ['*'], 'residentesConocimiento');
        }
        $residente = $lectura = $capturas = $detalleFuente = null;
        $conexionesResidente = ['disponible' => false];
        $errorLectura = $errorFuentes = false;
        $puedeFuentes = collect(CapturasInstrumentalesResidente::PERMISOS_LECTURA)
            ->every(fn ($permiso) => $usuario->checkPermissionTo($permiso, 'web'));
        if ($this->seleccionado) {
            $residente = Residente::query()->with('cama.habitacion')->where('estado', 'ADMITIDO')->findOrFail($this->seleccionado);
            Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
            if ($puedePanorama && ! $errorPanorama) {
                try {
                    $detallePanorama = app(PanoramaResidentes::class)->detalle($usuario, $residente, $this->campoPanorama);
                } catch (QueryException $e) {
                    report($e);
                    $errorPanorama = true;
                }
            }
            try {
                $lectura = app(LecturaResultadosExperto::class)->consultar($usuario, $residente, null, 'COG-MEM', $this->getPage('historia'));
            } catch (QueryException|DomainException $e) {
                report($e);
                $errorLectura = true;
            }
            if ($puedeFuentes) {
                try {
                    $capturas = app(CapturasInstrumentalesResidente::class)->consultar($usuario, $residente, CarbonImmutable::now());
                    $conexionesResidente = (new ConexionesResidenteVisible)->presentar($residente->getKey(), null, $capturas);
                    $detalleFuente = $this->fuente ? collect($capturas['capturas'])->firstWhere('cod_aplicacion', $this->fuente) : null;
                } catch (QueryException|DomainException $e) {
                    report($e);
                    $errorFuentes = true;
                }
            }
        }

        return view('apoyodecision::livewire.superadministrador.sistema-experto.residentes-conocimiento',
            compact('residentes', 'residente', 'lectura', 'capturas', 'detalleFuente', 'conexionesResidente', 'puedeFuentes', 'errorLectura', 'errorFuentes', 'panorama', 'detallePanorama', 'errorPanorama', 'puedePanorama'));
    }
}
