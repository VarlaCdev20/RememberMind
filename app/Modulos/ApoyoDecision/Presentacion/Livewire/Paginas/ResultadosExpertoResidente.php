<?php

namespace App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas;

use App\Models\EvaluacionExperta;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\EjecutarObservaciones;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\RevisarValoracionExperta;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\CapturasInstrumentalesResidente;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarActividadCognitiva;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarContinuidadAsistencialExperta;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarExploracionOrion;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarHallazgosObservacionales;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarObservacionesConservadas;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarPerfilInstrumental;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarRevisionesValoracionExperta;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\LecturaFuenteEvidencia;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\LecturaResultadosExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\PrepararEvaluacionInstitucional;
use App\Modulos\ApoyoDecision\Aplicacion\Contratos\LecturaSimulacionExperta;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ConsultaCognitivaEnfermeria;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\LenguajeResultadosExperto as L;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarInformeCognitivo;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResumirFuentesCognitivas;
use App\Modulos\ApoyoDecision\Dominio\Revision\DisciplinaRevision;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\CohorteCognitivaLocal;
use App\Modulos\ApoyoDecision\Presentacion\Soporte\ConexionesResidenteVisible;
use App\Modulos\ApoyoDecision\Presentacion\Soporte\ExploracionClinicaVisible;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

    #[Locked]
    public bool $revisionObservacional = false;

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

    #[Locked]
    public bool $significadoAbierto = false;

    #[Locked]
    public bool $informeCompletoAbierto = false;

    #[Locked]
    public bool $consultaAbierta = false;

    #[Locked]
    public string $pestanaConsulta = 'interpretacion';

    #[Locked]
    public ?string $nodoGrafo = null;

    #[Locked]
    public string $tipoGrafo = 'inferencia';

    #[Locked]
    public bool $detalleAreaAbierto = false;

    #[Locked]
    public bool $evolucionAbierta = false;

    #[Locked]
    public ?string $cuidadoSeleccionado = null;

    #[Locked]
    public ?string $comparacionInicial = null;

    #[Locked]
    public ?string $comparacionReciente = null;

    #[Locked]
    public ?string $serieComparacion = null;

    #[Locked]
    public ?string $puntoMedicion = null;

    #[Locked]
    public ?string $componenteRegistro = null;

    #[Locked]
    public bool $analisisEjecutado = false;

    #[Locked]
    public ?string $fechaAnalisis = null;

    /** Caché de esta petición: el resultado ejecutado se presenta sin una segunda adquisición. */
    private ?array $perfilAnalizado = null;

    private bool $analisisSolicitado = false;

    public ?string $mensajeAnalisis = null;

    public bool $procesandoAnalisis = false;

    public function hydrate(): void
    {
        // Sin conservación SQL, una petición posterior es una nueva consulta de fuentes mutables.
        $this->analisisEjecutado = false;
        $this->fechaAnalisis = null;
        $this->mensajeAnalisis = null;
        $this->perfilAnalizado = null;
        $this->analisisSolicitado = false;
    }

    public function analizarEvidencia(): void
    {
        $residente = $this->autorizar();
        abort_if($this->enfermeria, 403, 'El análisis del sistema experto está reservado a Medicina y Psicología.');

        $this->procesandoAnalisis = true;
        $this->analisisEjecutado = false;
        $this->fechaAnalisis = null;
        $this->perfilAnalizado = null;
        $this->analisisSolicitado = true;
        $this->mensajeAnalisis = null;
        $this->resetValidation();

        try {
            $corte = CarbonImmutable::now();
            $evaluacion = app(PrepararEvaluacionInstitucional::class)->preparar(auth()->user(), $residente, $corte);
            $perfil = app(ConsultarPerfilInstrumental::class)->desdePreparacion($evaluacion);
            $this->perfilAnalizado = $perfil;
            $this->fechaAnalisis = $corte->format('Y-m-d\TH:i:s.uP');
            $this->analisisEjecutado = true;
            $this->mensajeAnalisis = 'Análisis completado con la información disponible en la fecha indicada.';
        } catch (DomainException $e) {
            report($e);
            $this->addError('analisis', 'No se pudo verificar toda la información necesaria para el análisis. Actualiza la consulta y vuelve a intentarlo.');
        } catch (AuthorizationException|HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->addError('analisis', 'No se pudo completar el análisis de evidencia disponible.');
        } finally {
            $this->procesandoAnalisis = false;
        }
    }

    private function cerrarVentanasConsulta(): void
    {
        $this->significadoAbierto = $this->informeCompletoAbierto = $this->detalleAreaAbierto = $this->evolucionAbierta = $this->consultaAbierta = $this->trazabilidadAbierta = false;
        $this->cuidadoSeleccionado = $this->puntoMedicion = null;
        $this->componenteRegistro = null;
    }

    public function abrirDetalleArea(): void
    {
        $this->autorizar();
        $this->cerrarVentanasConsulta();
        $this->detalleAreaAbierto = true;
    }

    public function cerrarDetalleArea(): void
    {
        $this->autorizar();
        $this->detalleAreaAbierto = false;
    }

    public function abrirEvolucion(): void
    {
        $residente = $this->autorizar();
        $datos = $this->leerResultados($residente, $this->evaluacion, $this->getPage('historia'));
        $this->cerrarVentanasConsulta();
        $historia = collect($datos['historial']->items());
        $this->comparacionInicial = $historia->last()['id'] ?? null;
        $this->comparacionReciente = $historia->count() > 1 ? $historia->first()['id'] : null;
        $this->serieComparacion = null;
        $this->evolucionAbierta = true;
    }

    public function cerrarEvolucion(): void
    {
        $this->autorizar();
        $this->evolucionAbierta = false;
        $this->puntoMedicion = null;
    }

    public function cambiarComparacion(string $campo, string $valor): void
    {
        $residente = $this->autorizar();
        abort_unless(in_array($campo, ['inicial', 'reciente', 'area', 'serie'], true), 422);
        if ($campo === 'area') {
            abort_unless(isset(L::CRITERIOS[$valor]), 422);
            $this->criterio = $valor;
            $this->serieComparacion = $this->puntoMedicion = null;
        } elseif ($campo === 'serie') {
            $perfil = app(CapturasInstrumentalesResidente::class)->consultar(auth()->user(), $residente, CarbonImmutable::now());
            $series = (new ConsultaCognitivaEnfermeria)->series($perfil, $this->criterio);
            abort_unless(collect($series)->contains('id', $valor), 404);
            $this->serieComparacion = $valor;
        } else {
            $simulacionOriginal = $this->simulacion;
            try {
                $nueva = $this->leerResultados($residente, $valor);
            } finally {
                $this->simulacion = $simulacionOriginal;
            }
            $otra = $campo === 'inicial' ? $this->comparacionReciente : $this->comparacionInicial;
            if ($valor === $otra) {
                $this->addError('comparacion', 'Selecciona dos evaluaciones distintas.');

                return;
            }
            if ($otra) {
                try {
                    $previa = $this->leerResultados($residente, $otra);
                } finally {
                    $this->simulacion = $simulacionOriginal;
                }
                $fechaNueva = $this->fechaEvaluacion($nueva['evaluacion']['fecha'] ?? null);
                $fechaOtra = $this->fechaEvaluacion($previa['evaluacion']['fecha'] ?? null);
                if (! $fechaNueva || ! $fechaOtra) {
                    $this->addError('comparacion', 'No se puede ordenar la comparación: falta una fecha de evaluación válida.');

                    return;
                }
                if (($campo === 'inicial' && $fechaNueva->gt($fechaOtra)) || ($campo === 'reciente' && $fechaNueva->lt($fechaOtra))) {
                    $this->addError('comparacion', 'La evaluación inicial debe ser anterior o de la misma fecha que la reciente.');

                    return;
                }
            }
            $this->{$campo === 'inicial' ? 'comparacionInicial' : 'comparacionReciente'} = $valor;
        }
        $this->resetErrorBag('comparacion');
        $this->dispatch('comparacion-actualizada');
    }

    public function verMedicion(string $id): void
    {
        $residente = $this->autorizar();
        $capturas = app(CapturasInstrumentalesResidente::class)->consultar(auth()->user(), $residente, CarbonImmutable::now());
        $series = (new ConsultaCognitivaEnfermeria)->series($capturas, $this->criterio);
        abort_unless(collect($series)->flatMap(fn ($s) => $s['puntos'])->contains('id', $id), 404);
        $this->puntoMedicion = $id;
    }

    private function fechaEvaluacion(?string $valor): ?CarbonImmutable
    {
        if ($valor === null) {
            return null;
        }
        $fecha = \DateTimeImmutable::createFromFormat('!d/m/Y H:i', $valor);
        $errores = \DateTimeImmutable::getLastErrors();

        return $fecha && ($errores === false || ($errores['warning_count'] === 0 && $errores['error_count'] === 0))
            ? CarbonImmutable::instance($fecha) : null;
    }

    public function verCuidado(string $clave): void
    {
        $residente = $this->autorizar();
        $continuidad = app(ConsultarContinuidadAsistencialExperta::class)->consultar(auth()->user(), $residente, now());
        abort_unless($this->enfermeria && $continuidad['puedeVerCuidados'] && $continuidad['cuidadosPendientesHoy']->concat($continuidad['otrosCuidadosVigentes'])->contains('clave', $clave), 404);
        $this->cerrarVentanasConsulta();
        $this->cuidadoSeleccionado = $clave;
    }

    public function cerrarCuidado(): void
    {
        $this->autorizar();
        $this->cuidadoSeleccionado = null;
        $this->abrirConsulta('cuidados');
    }

    public function abrirRegistroComponente(string $codigo): void
    {
        $residente = $this->autorizar();
        $perfil = app(ConsultarPerfilInstrumental::class)->consultarMediciones(auth()->user(), $residente, CarbonImmutable::now());
        abort_unless(! empty($perfil['dominios'][$this->criterio]['componentes'][$codigo]['mediciones']), 404);
        $this->abrirConsulta('registro');
        $this->componenteRegistro = $codigo;
    }

    public function abrirNodoGrafo(string $tipo, string $id): void
    {
        $residente = $this->autorizar();
        abort_unless(in_array($tipo, ['inferencia', 'semantico'], true), 422);
        abort_if($this->simulacion, 404);
        $lectura = app(LecturaResultadosExperto::class)->consultar(auth()->user(), $residente, $this->evaluacion, $this->criterio);
        $grafos = app(ConsultarExploracionOrion::class)->consultar(auth()->user(), $residente, $lectura, $this->criterio);
        abort_unless(collect($grafos[$tipo]['nodos'])->contains('id', $id), 404);
        $this->abrirConsulta('reglas');
        $this->tipoGrafo = $tipo;
        $this->nodoGrafo = $id;
    }

    public function abrirSignificado(): void
    {
        $this->autorizar();
        $this->cerrarVentanasConsulta();
        $this->significadoAbierto = true;
    }

    public function abrirInformeCompleto(): void
    {
        $this->autorizar();
        abort_if($this->enfermeria, 403);
        $this->cerrarVentanasConsulta();
        $this->informeCompletoAbierto = true;
    }

    public function cerrarInformeCompleto(): void
    {
        $this->autorizar();
        $this->informeCompletoAbierto = false;
    }

    public function cerrarSignificado(): void
    {
        $this->autorizar();
        $this->significadoAbierto = false;
    }

    public function irASeccion(string $seccion): void
    {
        $this->autorizar();
        abort_unless(in_array($seccion, ['orion-desglose', 'orion-evolucion', 'hallazgos-observacionales', 'fuentes-disponibles', 'aplicaciones-instrumentales', 'orion-petersen-criterios', 'orion-revision-profesional'], true), 422);
        $this->cerrarVentanasConsulta();
        $this->dispatch('orion-navigate', seccion: $seccion);
    }

    public function abrirConsulta(string $pestana = 'interpretacion'): void
    {
        $residente = $this->autorizar();
        $permitidas = $this->enfermeria ? ['interpretacion', 'registro', 'cuidados'] : ['interpretacion', 'registro', 'reglas'];
        abort_unless(in_array($pestana, $permitidas, true), 422);
        if ($pestana === 'reglas') {
            Gate::authorize('consultarTrazabilidad', [EvaluacionExperta::class, $residente]);
        }
        $this->cerrarVentanasConsulta();
        $this->pestanaConsulta = $pestana;
        $this->nodoGrafo = null;
        $this->consultaAbierta = true;
    }

    public function cerrarConsulta(): void
    {
        $this->autorizar();
        $this->consultaAbierta = false;
        $this->evidencia = null;
    }

    /** Conserva el residente y el contexto de navegación sin ampliar las competencias del rol. */
    public function mount(Residente $residente): void
    {
        $this->codResidente = $residente->getKey();
        $this->enfermeria = request()->routeIs('admin.enfermeria.*')
            || (auth()->user()?->hasRole('ENFERMEROS') && ! auth()->user()?->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'SUPERADMINISTRADOR']));
        $this->psicologia = request()->routeIs('admin.psicologia.*')
            || (auth()->user()?->hasRole('PSICOLOGO/A') && ! auth()->user()?->hasAnyRole(['MEDICO GENERAL/GERIATRA', 'SUPERADMINISTRADOR', 'ENFERMEROS']));
        // La consulta clínica ordinaria nunca selecciona una demostración por ausencia de resultados.
        $this->simulacion = request()->query('simulacion') === '1'
            && app()->environment(['local', 'testing'])
            && config('sistema_experto.simulacion_local.habilitada') === true;
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
        if ($this->simulacion) {
            $datos = app(LecturaSimulacionExperta::class)->consultar(auth()->user(), $residente, $id, $this->criterio, $pagina);
            if ($datos !== null) {
                $this->simulacion = true;

                return $datos;
            }
            // Una capacidad revocada no convierte una instantánea abierta en otro resultado.
            abort_if($id === null && $this->simulacion, 404);
        }

        $datos = app(LecturaResultadosExperto::class)->consultar(auth()->user(), $residente, $id, $this->criterio, $pagina);
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
        $this->cerrarVentanasConsulta();
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
        $this->cerrarVentanasConsulta();
        $this->cerrarRevision();
        $this->evidencia = null;
        $this->trazabilidadAbierta = false;
    }

    /** Abre la traza almacenada del área actual en el drawer existente. */
    public function verTrazabilidad(): void
    {
        $residente = $this->autorizar();
        Gate::authorize('consultarTrazabilidad', [EvaluacionExperta::class, $residente]);
        $this->cerrarVentanasConsulta();
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

    public function abrirRegistro(string $id, ?string $area = null): void
    {
        if ($area !== null && $area !== $this->criterio) {
            $residente = $this->autorizar();
            abort_if($this->simulacion, 404);
            abort_unless(isset(L::CRITERIOS[$area]), 422);
            $lectura = app(LecturaResultadosExperto::class)->consultar(auth()->user(), $residente, $this->evaluacion, $area);
            abort_unless(isset($lectura['detalle']['evidencias'][$id]), 404);
            $this->seleccionarCriterio($area);
        }
        $this->abrirConsulta('registro');
        $this->verEvidencia($id);
    }

    /** El resultado conservado publicable del lector D-137 corresponde a aprendizaje y memoria. */
    public function abrirConsultaResultado(string $pestana): void
    {
        abort_unless(in_array($pestana, ['interpretacion', 'registro'], true), 422);
        $this->seleccionarCriterio('COG-MEM');
        $this->abrirConsulta($pestana);
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
        if ($this->disciplinaRevision !== null) {
            $this->addError('revision', 'Guarde o cancele la revisión abierta antes de ejecutar otra evaluación.');

            return;
        }
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
        if ($this->disciplinaRevision !== null) {
            $this->addError('revision', 'Guarde o cancele la revisión abierta antes de cambiar de evaluación.');

            return;
        }
        app(ConsultarObservacionesConservadas::class)->consultar(auth()->user(), $residente, $id);
        $this->evaluacionObservacional = $id;
        $this->resetPage('observaciones');
    }

    /** Abre una revisión sobre la evaluación y cabeza exactas, obtenidas de nuevo del servidor. */
    public function prepararRevision(string $disciplina): void
    {
        $r = $this->autorizar();
        if ($this->disciplinaRevision !== null) {
            $this->addError('revision', 'Guarde o cancele la revisión abierta antes de preparar otra.');

            return;
        }
        // Una simulación nunca admite firmas profesionales sobre una valoración operativa.
        abort_if($this->simulacion, 404);
        $d = DisciplinaRevision::tryFrom($disciplina);
        abort_unless($d, 422);
        $datos = app(LecturaResultadosExperto::class)->consultar(auth()->user(), $r, $this->evaluacion, $this->criterio);
        abort_unless($datos['evaluacion'] && ! $datos['prueba_tecnica'] && ! isset($datos['simulacion_local']), 422);
        $revisiones = app(ConsultarRevisionesValoracionExperta::class)->consultar(auth()->user(), $datos['evaluacion']['id']);
        abort_unless($revisiones && $revisiones[$d->value]['puede_firmar'], 403);
        $this->revisionEvaluacion = $datos['evaluacion']['id'];
        $this->evaluacion = $this->revisionEvaluacion;
        $this->revisionObservacional = false;
        $this->revisionAnterior = $revisiones[$d->value]['cabeza'];
        $this->disciplinaRevision = $d->value;
        $this->decisionRevision = $this->justificacionRevision = '';
        $this->mensajeRevision = null;
        $this->resetValidation();
    }

    /** La firma observacional refiere a la instantánea seleccionada, sin sustituir una valoración cognitiva. */
    public function prepararRevisionObservacional(string $disciplina): void
    {
        $r = $this->autorizar();
        if ($this->disciplinaRevision !== null) {
            $this->addError('revision', 'Guarde o cancele la revisión abierta antes de preparar otra.');

            return;
        }
        $d = DisciplinaRevision::tryFrom($disciplina);
        abort_unless($d, 422);
        $resultado = app(ConsultarObservacionesConservadas::class)->consultar(auth()->user(), $r, $this->evaluacionObservacional);
        abort_unless($resultado && $resultado['persistido'], 422);
        $revisiones = app(ConsultarRevisionesValoracionExperta::class)->consultar(auth()->user(), $resultado['id']);
        abort_unless($revisiones && $revisiones[$d->value]['puede_firmar'], 403);
        $this->revisionEvaluacion = $resultado['id'];
        $this->evaluacionObservacional = $this->revisionEvaluacion;
        $this->revisionAnterior = $revisiones[$d->value]['cabeza'];
        $this->disciplinaRevision = $d->value;
        $this->revisionObservacional = true;
        $this->decisionRevision = $this->justificacionRevision = '';
        $this->mensajeRevision = null;
        $this->resetValidation();
    }

    public function cerrarRevision(): void
    {
        $this->autorizar();
        $this->revisionEvaluacion = $this->revisionAnterior = $this->disciplinaRevision = null;
        $this->revisionObservacional = false;
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
        $cohorteEntrenamiento = CohorteCognitivaLocal::esResidente($residente);
        $residente->loadMissing('cama.habitacion');
        $datos = null;
        $fuente = null;
        $errorLectura = false;
        $errorInstrumental = false;
        $errorObservacional = false;
        $informe = null;
        $revisiones = null;
        $revisionesObservacionales = null;
        $estadoRevision = null;
        $hallazgosObservacionales = null;
        $capturaInstrumental = null;
        $perfilInstrumental = null;
        $conexionesResidente = null;
        $exploracion = null;
        $puedeAuditar = Gate::allows('consultarTrazabilidad', [EvaluacionExperta::class, $residente]);
        $puedeEjecutarObservaciones = Gate::allows('ejecutarObservaciones', [EvaluacionExperta::class, $residente]);
        abort_if($this->trazabilidadAbierta && ! $puedeAuditar, 404);
        abort_if($this->consultaAbierta && $this->pestanaConsulta === 'reglas' && ! $puedeAuditar, 404);
        try {
            $consultaPerfil = app(ConsultarPerfilInstrumental::class);
            if ($this->analisisSolicitado) {
                $perfilInstrumental = $this->perfilAnalizado;
                $errorInstrumental = ! $this->analisisEjecutado;
            } else {
                $corte = $this->fechaAnalisis ? CarbonImmutable::parse($this->fechaAnalisis) : CarbonImmutable::now();
                $perfilInstrumental = $this->enfermeria
                    ? $consultaPerfil->consultarMediciones(auth()->user(), $residente, $corte)
                    : $consultaPerfil->consultar(auth()->user(), $residente, $corte);
            }
            $capturaInstrumental = $perfilInstrumental['capturas_originales'] ?? null;
            if (! $this->enfermeria) {
                $conexionesResidente = (new ConexionesResidenteVisible)->presentar($residente->getKey(), $perfilInstrumental['petersen_institucional'] ?? null, $capturaInstrumental);
            }
        } catch (QueryException|DomainException $e) {
            report($e);
            $errorInstrumental = true;
            $this->analisisEjecutado = false;
            $this->fechaAnalisis = null;
            $this->mensajeAnalisis = null;
        }
        if (! $this->enfermeria) {
            try {
                $hallazgosObservacionales = app(ConsultarObservacionesConservadas::class)->consultar(auth()->user(), $residente,
                    $this->evaluacionObservacional, $this->getPage('observaciones'), $this->getPage('historiaObservaciones'))
                    ?? app(ConsultarHallazgosObservacionales::class)->consultar(auth()->user(), $residente, $this->getPage('observaciones'));
                if ($hallazgosObservacionales && $hallazgosObservacionales['persistido']) {
                    $revisionesObservacionales = app(ConsultarRevisionesValoracionExperta::class)->consultar(auth()->user(), $hallazgosObservacionales['id']);
                }
            } catch (QueryException|DomainException $e) {
                report($e);
                $hallazgosObservacionales = $revisionesObservacionales = null;
                $errorObservacional = true;
            }
        }
        try {
            $datos = $this->leerResultados($residente, $this->evaluacion, $this->getPage('historia'));
            $presentador = app(PresentarInformeCognitivo::class);
            $informe = $this->simulacion ? $presentador->presentarSimulacion($datos, $this->criterio)
                : $presentador->presentar($datos, $this->criterio);
            if ($this->enfermeria) {
                foreach ($informe['areas'] as $codigo => &$area) {
                    $area['nombre'] = L::CRITERIOS[$codigo]['nombre'];
                }
                unset($area);
                $informe['actual'] = $informe['areas'][$this->criterio];
            }
            if ($datos['evaluacion'] && ! $datos['prueba_tecnica'] && ! isset($datos['simulacion_local'])) {
                $revisiones = app(ConsultarRevisionesValoracionExperta::class)->consultar(auth()->user(), $datos['evaluacion']['id']);
            }
            $estadoRevision = $presentador->resumirRevision($revisiones, $datos['evaluacion'] !== null, $this->simulacion);
            if ($puedeAuditar && ! $this->simulacion && ! $datos['prueba_tecnica']) {
                $exploracion = app(ConsultarExploracionOrion::class)->consultar(auth()->user(), $residente, $datos, $this->criterio);
            }
            if ($this->evidencia && isset($datos['detalle']['evidencias'][$this->evidencia])) {
                $fuente = app(LecturaFuenteEvidencia::class)->consultar(auth()->user(), $residente, $datos['evaluacion']['id'], $this->evidencia);
            }
        } catch (QueryException|DomainException $e) {
            report($e);
            $datos = $informe = $revisiones = $fuente = $estadoRevision = null;
            $errorLectura = true;
        }

        $continuidad = $this->enfermeria
            ? app(ConsultarContinuidadAsistencialExperta::class)
                ->consultar(auth()->user(), $residente, now())
            : ['puedeVerDiagnosticos' => false, 'puedeVerCuidados' => false, 'diagnosticosMedicos' => collect(),
                'cuidadosPendientesHoy' => collect(), 'otrosCuidadosVigentes' => collect()];

        $consultaEnfermeria = $datos && $informe ? (new ConsultaCognitivaEnfermeria)->presentar($datos, $informe, $perfilInstrumental) : null;
        $exploracionClinica = (new ExploracionClinicaVisible)->presentar($perfilInstrumental, $informe, $consultaEnfermeria, $datos, $this->enfermeria, $cohorteEntrenamiento);
        $resumenFuentes = ! $this->enfermeria && ! $errorInstrumental
            ? (new ResumirFuentesCognitivas)->presentar($capturaInstrumental, $perfilInstrumental, $hallazgosObservacionales)
            : null;
        $registroComponente = collect($consultaEnfermeria['componentes'][$this->criterio] ?? [])->firstWhere('id', $this->componenteRegistro);
        abort_if($this->componenteRegistro && ! $errorInstrumental && ! $registroComponente, 404);
        $seriesEvolucion = $actividadCognitiva = $comparaciones = [];
        $errorEvolucion = false;
        if ($this->psicologia && ! $this->evolucionAbierta) {
            try {
                $actividadCognitiva = app(ConsultarActividadCognitiva::class)->consultar(auth()->user(), $residente);
            } catch (QueryException|DomainException $e) {
                report($e);
                $errorEvolucion = true;
            }
        }
        if ($this->evolucionAbierta) {
            $simulacionActual = $this->simulacion;
            try {
                if ($errorInstrumental) {
                    throw new DomainException('Las mediciones instrumentales no pudieron verificarse.');
                }
                $seriesEvolucion = (new ConsultaCognitivaEnfermeria)->series($capturaInstrumental, $this->criterio, $perfilInstrumental['dominios'][$this->criterio]['componentes'] ?? []);
                $actividadCognitiva = app(ConsultarActividadCognitiva::class)->consultar(auth()->user(), $residente);
                foreach (['inicial' => $this->comparacionInicial, 'reciente' => $this->comparacionReciente] as $lado => $id) {
                    if ($id) {
                        $lectura = $this->leerResultados($residente, $id);
                        $comparaciones[$lado] = ['evaluacion' => $lectura['evaluacion'], 'areas' => array_map(fn ($d) => ['nombre' => $d['nombre'], 'estado' => $d['estado']], $lectura['perfil'])];
                    }
                }
                if (isset($comparaciones['inicial'], $comparaciones['reciente'])) {
                    $desde = $this->fechaEvaluacion($comparaciones['inicial']['evaluacion']['fecha'] ?? null);
                    $hasta = $this->fechaEvaluacion($comparaciones['reciente']['evaluacion']['fecha'] ?? null);
                    if (! $desde || ! $hasta || $desde->gt($hasta)) {
                        throw new DomainException('Las evaluaciones seleccionadas no tienen un intervalo temporal verificable.');
                    }
                    foreach ($seriesEvolucion as &$serie) {
                        $serie['puntos'] = array_values(array_filter($serie['puntos'], fn ($p) => CarbonImmutable::parse($p['fecha'])->betweenIncluded($desde->startOfMinute(), $hasta->endOfMinute())));
                    }
                    unset($serie);
                }
            } catch (QueryException|DomainException $e) {
                report($e);
                $errorEvolucion = true;
                $seriesEvolucion = $actividadCognitiva = $comparaciones = [];
            } finally {
                $this->simulacion = $simulacionActual;
            }
        }
        $cuidadoDetalle = $this->cuidadoSeleccionado ? $continuidad['cuidadosPendientesHoy']->concat($continuidad['otrosCuidadosVigentes'])->firstWhere('clave', $this->cuidadoSeleccionado) : null;
        abort_if($this->cuidadoSeleccionado && ! $cuidadoDetalle, 404);

        return view('livewire.medico.resultados-experto-residente', compact('datos', 'informe', 'fuente', 'errorLectura', 'residente', 'revisiones', 'estadoRevision', 'puedeAuditar', 'hallazgosObservacionales', 'puedeEjecutarObservaciones', 'capturaInstrumental', 'perfilInstrumental', 'revisionesObservacionales', 'errorInstrumental', 'errorObservacional', 'exploracion', 'consultaEnfermeria', 'resumenFuentes', 'seriesEvolucion', 'actividadCognitiva', 'comparaciones', 'errorEvolucion', 'cuidadoDetalle', 'registroComponente'), $continuidad)
            ->with('cohorteEntrenamiento', $cohorteEntrenamiento)
            ->with('conexionesResidente', $conexionesResidente)
            ->with('exploracionClinica', $exploracionClinica)
            ->layout($this->enfermeria ? 'layouts.enfermeria' : 'layouts.sistema');
    }
}
