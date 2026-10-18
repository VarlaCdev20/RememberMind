<?php

namespace App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\EjecutarObservaciones;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RevisarValoracionExperta;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarHallazgosObservacionales;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarObservacionesConservadas;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarRevisionesValoracionExperta;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\LecturaFuenteEvidencia;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\LecturaResultadosExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\LecturaSimulacionExperta;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\LenguajeResultadosExperto as L;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarInformeCognitivo;
use App\Modulos\ApoyoDecision\Dominio\Revision\DisciplinaRevision;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

/** Coordina lectura y solicitudes explícitas; el motor y la conservación permanecen en el backend. */
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
    public bool $psicologia = false;

    #[Locked]
    public ?string $revisionEvaluacion = null;

    #[Locked]
    public ?string $revisionAnterior = null;

    #[Locked]
    public ?string $disciplinaRevision = null;

    public string $decisionRevision = '';

    public string $justificacionRevision = '';

    #[Locked]
    public ?string $mensajeRevision = null;

    #[Locked]
    public bool $trazabilidadAbierta = false;

    /** Procedencia derivada del resultado leído; no es un selector ni un parámetro de navegación. */
    #[Locked]
    public bool $simulacion = false;

    #[Locked]
    public ?string $evaluacionObservacional = null;

    #[Locked]
    public ?string $solicitudObservacional = null;

    #[Locked]
    public ?string $mensajeObservacional = null;

    /** Conserva el residente y el contexto de navegación sin ampliar las competencias del rol. */
    public function mount(Residente $residente): void
    {
        $this->codResidente = $residente->getKey();
        $this->enfermeria = request()->routeIs('admin.enfermeria.*')
            || (auth()->user()?->hasRole('ENFERMEROS') && ! auth()->user()?->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'SUPERADMINISTRADOR']));
        $this->psicologia = request()->routeIs('admin.psicologia.*')
            || (auth()->user()?->hasRole('PSICOLOGO/A') && ! auth()->user()?->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'SUPERADMINISTRADOR', 'ENFERMEROS']));
        $this->autorizar();
    }

    /** Relee la cuenta antes de cada interacción para respetar bajas y permisos revocados. */
    private function autorizar(): Residente
    {
        $usuario = auth()->user();
        $usuario = $usuario ? User::query()->find($usuario->getKey()) : null;
        abort_unless($usuario, 403);
        auth()->setUser($usuario);
        $residente = Residente::query()->findOrFail($this->codResidente);
        Gate::authorize('consultarResultados', [EvaluacionExperta::class, $residente]);

        return $residente;
    }

    /** Muestra el resultado conservado disponible, sin selector de vista previa ni mezcla de fuentes. */
    private function leerResultados(Residente $residente, ?string $id, int $pagina = 1): array
    {
        if ($id !== null || $this->simulacion) {
            $datos = app(LecturaSimulacionExperta::class)->consultar(auth()->user(), $residente, $id, $this->criterio, $pagina);
            if ($datos !== null) {
                $this->simulacion = true;

                return $datos;
            }
            // Una capacidad revocada no convierte una instantánea abierta en otro resultado.
            abort_if($id === null && $this->simulacion, 404);
        }

        $datos = app(LecturaResultadosExperto::class)->consultar(auth()->user(), $residente, $id, $this->criterio, $pagina);
        if ($datos['evaluacion'] === null && $id === null) {
            $instantanea = app(LecturaSimulacionExperta::class)->consultar(auth()->user(), $residente, null, $this->criterio, $pagina);
            if ($instantanea !== null) {
                $this->simulacion = true;

                return $instantanea;
            }
        }
        $this->simulacion = false;

        return $datos;
    }

    /** Cambia el área conservando la evaluación seleccionada; descarta solo el detalle abierto. */
    public function seleccionarCriterio(string $codigo): void
    {
        $this->autorizar();
        abort_unless(isset(L::CRITERIOS[$codigo]), 422);
        $this->criterio = $codigo;
        $this->evidencia = null;
        $this->trazabilidadAbierta = false;
    }

    /** Valida acceso a la evaluación exacta antes de seleccionarla, sin reconstruir resultados. */
    public function seleccionarEvaluacion(string $id): void
    {
        $residente = $this->autorizar();
        if ($this->disciplinaRevision !== null) {
            $this->addError('revision', 'Guarde o cancele la revisión abierta antes de cambiar de evaluación.');

            return;
        }
        $this->leerResultados($residente, $id);
        $this->evaluacion = $id;
        $this->cerrarRevision();
        $this->evidencia = null;
        $this->trazabilidadAbierta = false;
    }

    /** Abre la traza almacenada del área actual en el drawer existente. */
    public function verTrazabilidad(): void
    {
        $residente = $this->autorizar();
        Gate::authorize('consultarTrazabilidad', [EvaluacionExperta::class, $residente]);
        $this->trazabilidadAbierta = true;
        $this->dispatch('expert-trace-opened');
    }

    /** Cierra el detalle y permite restaurar el foco al botón que lo abrió. */
    public function cerrarTrazabilidad(): void
    {
        $this->autorizar();
        $this->trazabilidadAbierta = false;
        $this->dispatch('expert-trace-closed');
    }

    /** Verifica pertenencia al área y evaluación antes de consultar la fuente actual. */
    public function verEvidencia(string $id): void
    {
        $residente = $this->autorizar();
        // Las referencias artificiales se consultan en su instantánea; no son fuentes clínicas actuales.
        abort_if($this->simulacion, 404);
        $datos = app(LecturaResultadosExperto::class)->consultar(auth()->user(), $residente, $this->evaluacion, $this->criterio);
        abort_unless(isset($datos['detalle']['evidencias'][$id]), 404);
        $this->evidencia = $this->evidencia === $id ? null : $id;
    }

    /** Reautoriza el reintento; render vuelve a realizar únicamente la lectura. */
    public function reintentar(): void
    {
        $this->autorizar();
    }

    /** El autor y los datos se obtienen de nuevo en backend; el cliente no elige profesional ni hechos. */
    public function ejecutarObservaciones(): void
    {
        $residente = $this->autorizar();
        Gate::authorize('ejecutarObservaciones', [EvaluacionExperta::class, $residente]);
        $this->solicitudObservacional ??= (string) Str::uuid();
        $this->mensajeObservacional = null;
        $this->resetErrorBag('observaciones');
        try {
            $this->evaluacionObservacional = app(EjecutarObservaciones::class)->ejecutar(auth()->user(), $residente, $this->solicitudObservacional);
            $this->mensajeObservacional = 'Evaluación observacional guardada con sus fuentes y decisiones originales.';
            $this->resetPage('observaciones');
        } catch (DomainException $e) {
            report($e);
            $this->addError('observaciones', 'No se pudo verificar y conservar la ejecución. No se ha confirmado el guardado.');
        } catch (QueryException $e) {
            report($e);
            $this->addError('observaciones', 'Error técnico al conservar la ejecución. Vuelva a intentarlo; no se ha confirmado el guardado.');
        }
    }

    public function seleccionarObservacion(string $id): void
    {
        $residente = $this->autorizar();
        app(ConsultarObservacionesConservadas::class)->consultar(auth()->user(), $residente, $id);
        $this->evaluacionObservacional = $id;
        $this->resetPage('observaciones');
    }

    /** Abre una revisión sobre la evaluación y cabeza exactas, obtenidas de nuevo del servidor. */
    public function prepararRevision(string $disciplina): void
    {
        $r = $this->autorizar();
        // Una simulación nunca admite firmas profesionales sobre una valoración operativa.
        abort_if($this->simulacion, 404);
        $d = DisciplinaRevision::tryFrom($disciplina);
        abort_unless($d, 422);
        $datos = app(LecturaResultadosExperto::class)->consultar(auth()->user(), $r, $this->evaluacion, $this->criterio);
        abort_unless($datos['evaluacion'] && ! $datos['prueba_tecnica'] && ! isset($datos['simulacion_local']), 422);
        $revisiones = app(ConsultarRevisionesValoracionExperta::class)->consultar(auth()->user(), $datos['evaluacion']['id']);
        abort_unless($revisiones && $revisiones[$d->value]['puede_firmar'], 403);
        $this->revisionEvaluacion = $datos['evaluacion']['id'];
        $this->revisionAnterior = $revisiones[$d->value]['cabeza'];
        $this->disciplinaRevision = $d->value;
        $this->decisionRevision = $this->justificacionRevision = '';
        $this->mensajeRevision = null;
        $this->resetValidation();
    }

    public function cerrarRevision(): void
    {
        $this->autorizar();
        $this->revisionEvaluacion = $this->revisionAnterior = $this->disciplinaRevision = null;
        $this->decisionRevision = $this->justificacionRevision = '';
        $this->resetValidation();
    }

    /** El caso de uso reautoriza y deriva el profesional; el cliente no recibe un campo de autor. */
    public function guardarRevision(): void
    {
        $r = $this->autorizar();
        $this->validate(['decisionRevision' => 'required|in:CONFIRMADA,RECHAZADA', 'justificacionRevision' => 'required|string|max:10000']);
        $d = DisciplinaRevision::tryFrom($this->disciplinaRevision ?? '');
        abort_unless($d && $this->revisionEvaluacion, 422);
        abort_unless(EvaluacionExperta::query()->whereKey($this->revisionEvaluacion)->where('cod_residente', $r->getKey())->exists(), 404);
        try {
            app(RevisarValoracionExperta::class)->ejecutar(auth()->user(), $this->revisionEvaluacion, $d,
                $this->decisionRevision, $this->justificacionRevision, $this->revisionAnterior);
            $this->cerrarRevision();
            $this->mensajeRevision = 'Revisión de '.($d === DisciplinaRevision::MEDICINA ? 'Medicina' : 'Psicología').' registrada.';
        } catch (DomainException $e) {
            $this->addError('revision', $e->getMessage());
        } catch (QueryException $e) {
            report($e);
            $this->addError('revision', 'No se pudo guardar la revisión. Recargue la valoración antes de volver a intentarlo.');
        }
    }

    /** Delega la redacción a Application y reporta fallos SQL con un mensaje seguro. */
    public function render()
    {
        $residente = $this->autorizar();
        $datos = null;
        $fuente = null;
        $errorLectura = false;
        $informe = null;
        $revisiones = null;
        $estadoRevision = null;
        $hallazgosObservacionales = null;
        $puedeAuditar = Gate::allows('consultarTrazabilidad', [EvaluacionExperta::class, $residente]);
        $puedeEjecutarObservaciones = Gate::allows('ejecutarObservaciones', [EvaluacionExperta::class, $residente]);
        abort_if($this->trazabilidadAbierta && ! $puedeAuditar, 404);
        try {
            $hallazgosObservacionales = app(ConsultarObservacionesConservadas::class)->consultar(auth()->user(), $residente,
                $this->evaluacionObservacional, $this->getPage('observaciones'), $this->getPage('historiaObservaciones'))
                ?? app(ConsultarHallazgosObservacionales::class)->consultar(auth()->user(), $residente, $this->getPage('observaciones'));
            $datos = $this->leerResultados($residente, $this->evaluacion, $this->getPage('historia'));
            $presentador = app(PresentarInformeCognitivo::class);
            $informe = $this->simulacion ? $presentador->presentarSimulacion($datos, $this->criterio)
                : $presentador->presentar($datos, $this->criterio);
            if ($datos['evaluacion'] && ! $datos['prueba_tecnica'] && ! isset($datos['simulacion_local'])) {
                $revisiones = app(ConsultarRevisionesValoracionExperta::class)->consultar(auth()->user(), $datos['evaluacion']['id']);
            }
            $estadoRevision = $presentador->resumirRevision($revisiones, $datos['evaluacion'] !== null, $this->simulacion);
            if ($this->evidencia && isset($datos['detalle']['evidencias'][$this->evidencia])) {
                $fuente = app(LecturaFuenteEvidencia::class)->consultar(auth()->user(), $residente, $datos['evaluacion']['id'], $this->evidencia);
            }
        } catch (QueryException $e) {
            report($e);
            $errorLectura = true;
        } catch (DomainException $e) {
            report($e);
            $errorLectura = true;
        }

        return view('livewire.medico.resultados-experto-residente', compact('datos', 'informe', 'fuente', 'errorLectura', 'residente', 'revisiones', 'estadoRevision', 'puedeAuditar', 'hallazgosObservacionales', 'puedeEjecutarObservaciones'))
            ->layout($this->enfermeria ? 'layouts.enfermeria' : 'layouts.sistema');
    }
}
