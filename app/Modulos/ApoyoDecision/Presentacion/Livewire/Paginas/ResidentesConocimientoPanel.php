<?php

declare(strict_types=1);

namespace App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\CapturasInstrumentalesResidente;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\LecturaResultadosExperto;
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
        $query = Residente::query()->where('estado', 'ADMITIDO')->with('cama.habitacion');
        foreach (preg_split('/\s+/u', mb_substr(trim($this->buscar), 0, 100), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $termino) {
            $query->where(fn ($q) => $q->whereLike('nombres', "%{$termino}%")
                ->orWhereLike('apellido_paterno', "%{$termino}%")->orWhereLike('apellido_materno', "%{$termino}%")
                ->orWhereLike('cod_residente', "%{$termino}%"));
        }
        $residentes = $query->orderBy('apellido_paterno')->orderBy('nombres')->orderBy('cod_residente')
            ->paginate(8, ['*'], 'residentesConocimiento');
        $residente = $lectura = $capturas = $detalleFuente = null;
        $conexionesResidente = ['disponible' => false];
        $errorLectura = $errorFuentes = false;
        $puedeFuentes = collect(CapturasInstrumentalesResidente::PERMISOS_LECTURA)
            ->every(fn ($permiso) => $usuario->checkPermissionTo($permiso, 'web'));
        if ($this->seleccionado) {
            $residente = Residente::query()->with('cama.habitacion')->where('estado', 'ADMITIDO')->findOrFail($this->seleccionado);
            Gate::forUser($usuario)->authorize('consultarResultados', [EvaluacionExperta::class, $residente]);
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
            compact('residentes', 'residente', 'lectura', 'capturas', 'detalleFuente', 'conexionesResidente', 'puedeFuentes', 'errorLectura', 'errorFuentes'));
    }
}
