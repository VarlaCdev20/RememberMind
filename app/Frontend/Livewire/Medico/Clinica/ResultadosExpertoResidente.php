<?php

namespace App\Frontend\Livewire\Medico\Clinica;

use App\Backend\Modulos\SistemaExperto\Presentacion\LenguajeResultadosExperto as L;
use App\Backend\Modulos\SistemaExperto\Servicios\LecturaFuenteEvidencia;
use App\Backend\Modulos\SistemaExperto\Servicios\LecturaResultadosExperto;
use App\Models\EvaluacionExperta;
use App\Models\Residente;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class ResultadosExpertoResidente extends Component
{
    use WithPagination;

    #[Locked]
    public string $codResidente;

    #[Locked]
    public ?string $evaluacion = null;

    #[Locked]
    public string $criterio = 'COG-MEM';

    #[Locked]
    public ?string $evidencia = null;

    #[Locked]
    public bool $enfermeria = false;

    #[Locked]
    public bool $trazabilidadAbierta = false;

    public function mount(Residente $residente): void
    {
        $this->codResidente = $residente->getKey();
        $this->enfermeria = request()->routeIs('admin.enfermeria.*')
            || (auth()->user()?->hasRole('ENFERMEROS') && ! auth()->user()?->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'SUPERADMINISTRADOR']));
        $this->autorizar();

    }

    private function autorizar(): Residente
    {
        $residente = Residente::query()->findOrFail($this->codResidente);
        Gate::authorize('consultarResultados', [EvaluacionExperta::class, $residente]);

        return $residente;
    }

    public function seleccionarCriterio(string $codigo): void
    {
        $this->autorizar();
        abort_unless(isset(L::CRITERIOS[$codigo]), 422);
        $this->criterio = $codigo;
        $this->evidencia = null;
        $this->trazabilidadAbierta = false;
    }

    public function seleccionarEvaluacion(string $id): void
    {
        $residente = $this->autorizar();
        app(LecturaResultadosExperto::class)->consultar(auth()->user(), $residente, $id, $this->criterio);
        $this->evaluacion = $id;
        $this->evidencia = null;
        $this->trazabilidadAbierta = false;
    }

    public function verTrazabilidad(): void
    {
        $this->autorizar();
        $this->trazabilidadAbierta = true;
        $this->dispatch('expert-trace-opened');
    }

    public function cerrarTrazabilidad(): void
    {
        $this->autorizar();
        $this->trazabilidadAbierta = false;
        $this->dispatch('expert-trace-closed');
    }

    public function verEvidencia(string $id): void
    {
        $residente = $this->autorizar();
        $datos = app(LecturaResultadosExperto::class)->consultar(auth()->user(), $residente, $this->evaluacion, $this->criterio);
        abort_unless(isset($datos['detalle']['evidencias'][$id]), 404);
        $this->evidencia = $this->evidencia === $id ? null : $id;
    }

    public function reintentar(): void
    {
        $this->autorizar();
    }

    public function render()
    {
        $residente = $this->autorizar();
        $datos = null;
        $fuente = null;
        $errorLectura = false;
        try {
            $datos = app(LecturaResultadosExperto::class)->consultar(auth()->user(), $residente, $this->evaluacion, $this->criterio, $this->getPage('historia'));
            if ($this->evidencia && isset($datos['detalle']['evidencias'][$this->evidencia])) {
                $fuente = app(LecturaFuenteEvidencia::class)->consultar(auth()->user(), $residente, $datos['evaluacion']['id'], $this->evidencia);
            }
        } catch (QueryException $e) {
            report($e);
            $errorLectura = true;
        }

        return view('livewire.medico.resultados-experto-residente', compact('datos', 'fuente', 'errorLectura', 'residente'))
            ->layout($this->enfermeria ? 'layouts.enfermeria' : 'layouts.sistema');
    }
}
