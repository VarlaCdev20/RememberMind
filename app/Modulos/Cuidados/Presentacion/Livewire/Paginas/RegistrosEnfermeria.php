<?php

namespace App\Modulos\Cuidados\Presentacion\Livewire\Paginas;

use App\Models\Herida;
use App\Models\IntervencionCuidado;
use App\Models\RegistroHidratacion;
use App\Models\RegistroSueno;
use App\Models\Residente;
use App\Modulos\Alertas\Aplicacion\Acciones\GestionarIncidentesEnfermeria;
use App\Modulos\Clinica\Aplicacion\Acciones\GestionarDispositivosClinicos;
use App\Modulos\Cuidados\Aplicacion\Acciones\CuidadosEnfermeriaService;
use App\Modulos\Cuidados\Aplicacion\Acciones\LesionesEnfermeriaService;
use App\Modulos\Cuidados\Aplicacion\Acciones\PersistirFotosHerida;
use App\Modulos\Cuidados\Aplicacion\Acciones\RegistrarCuidadosDeTurno;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use App\Modulos\Cuidados\Aplicacion\Consultas\PrepararEvolucionHerida;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Cuidados\Presentacion\Navegacion\NavegacionCuidadosService;
use App\Modulos\Valoraciones\Aplicacion\Acciones\RegistrarValoracionDolor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RegistrosEnfermeria extends Component
{
    use WithFileUploads { _startUpload as protected iniciarCargaTemporal; }

    public array $fotografias = [];

    public array $loteFotografias = [];

    public ?int $reemplazarFotografiaIndice = null;

    public bool $registrandoNuevaHerida = false;

    #[Locked]
    public int $revisionFotos = 0;

    public array $descripcionesFotos = [];

    public string $olorLesion = '';

    public string $materialesLesion = '';

    public string $respuestaLesion = '';

    public string $nuevaHeridaTipo = '';

    public string $nuevaHeridaUbicacion = '';

    public string $nuevaHeridaClasificacion = '';

    public string $nuevaHeridaCausa = '';

    public string $nuevaHeridaObservacion = '';

    #[Locked]
    public array $resultadoHerida = [];

    #[Locked]
    public bool $emergente = false;

    public bool $mostrarFormularioHerida = false;

    #[Renderless]
    public function _startUpload($name, $fileInfo, $isMultiple)
    {
        abort_unless(in_array($name, ['loteFotografias', 'fotografias'], true), 403);
        $residente = Residente::findOrFail($this->codResidente);
        $permiso = $this->registrandoNuevaHerida ? 'heridas.crear' : 'curaciones_herida.crear';
        Gate::authorize('adjuntar-evidencia-herida', [$residente, $permiso]);
        if (! $this->registrandoNuevaHerida) {
            abort_unless(Herida::whereKey($this->lesionId)->where('cod_residente', $residente->getKey())->where('estado', 'ACTIVA')->exists(), 403);
        }
        $this->iniciarCargaTemporal($name, $fileInfo, $isMultiple);
    }

    public function updatedFotografias(): void
    {
        app(PersistirFotosHerida::class)->validar($this->fotografias, $this->descripcionesFotos);
    }

    public function updatedLoteFotografias(): void
    {
        $candidatas = $this->fotografias;
        if ($this->reemplazarFotografiaIndice !== null && isset($candidatas[$this->reemplazarFotografiaIndice]) && count($this->loteFotografias) === 1) {
            $candidatas[$this->reemplazarFotografiaIndice] = $this->loteFotografias[0];
        } else {
            $candidatas = array_merge($candidatas, $this->loteFotografias);
        }
        app(PersistirFotosHerida::class)->validar($candidatas, $this->descripcionesFotos);
        foreach (array_keys($this->getErrorBag()->getMessages()) as $campo) {
            if (in_array($campo, ['loteFotografias', 'fotografias'], true) || str_starts_with($campo, 'fotografias.')) {
                $this->resetValidation($campo);
            }
        }
        $this->fotografias = $candidatas;
        $this->revisionFotos++;
        $this->loteFotografias = [];
        $this->reemplazarFotografiaIndice = null;
    }

    public function quitarFotografia(int $indice): void
    {
        unset($this->fotografias[$indice], $this->descripcionesFotos[$indice]);
        $this->descripcionesFotos = array_map(fn ($key) => $this->descripcionesFotos[$key] ?? '', array_keys($this->fotografias));
        $this->fotografias = array_values($this->fotografias);
        $this->resetValidation();
    }

    public function descartarFotosHerida(): void
    {
        $this->reset('fotografias', 'loteFotografias', 'descripcionesFotos', 'reemplazarFotografiaIndice');
        $this->resetValidation();
    }

    public function seleccionarHerida(string $codigo): void
    {
        $this->autorizar($this->codResidente);
        abort_unless(strlen($codigo) <= 20, 404);
        Herida::query()->where('cod_residente', $this->codResidente)->findOrFail($codigo);
        $this->reset('largoLesion', 'anchoLesion', 'profundidadLesion', 'dolorLesion', 'exudadoLesion', 'aspectoLesion', 'accionLesion', 'observacionLesion', 'olorLesion', 'materialesLesion', 'respuestaLesion');
        $this->descartarFotosHerida();
        $this->lesionId = $codigo;
    }

    public function guardarNuevaHerida(): void
    {
        $this->resetValidation();
        $this->validate(['nuevaHeridaTipo' => 'required|string|max:60', 'nuevaHeridaUbicacion' => 'required|string|max:120', 'nuevaHeridaClasificacion' => 'nullable|string|max:60', 'nuevaHeridaCausa' => 'nullable|string|max:120', 'nuevaHeridaObservacion' => 'nullable|string|max:1000']);
        $herida = app(LesionesEnfermeriaService::class)->registrar($this->codResidente, [
            'tipo' => $this->nuevaHeridaTipo, 'zona_corporal' => $this->nuevaHeridaUbicacion,
            'causa_probable' => $this->nuevaHeridaCausa, 'estadio_upp' => $this->nuevaHeridaClasificacion,
            'aspecto_inicial' => $this->nuevaHeridaObservacion, 'fotografias' => $this->fotografias, 'descripcionesFotos' => $this->descripcionesFotos,
        ], auth()->user());
        $this->resultadoHerida = ['tipo' => 'herida', 'cod_herida' => $herida->getKey(), 'tipo_herida' => $herida->tipo_herida, 'ubicacion' => $herida->ubicacion, 'clasificacion' => $herida->clasificacion, 'causa' => $herida->causa, 'observacion' => $herida->observacion, 'fecha' => $herida->fecha_hora_identificacion->format('d/m/Y H:i'), 'fotografias' => count($this->fotografias)];
        $this->lesionId = $herida->getKey();
        $this->reset('fotografias', 'descripcionesFotos', 'nuevaHeridaTipo', 'nuevaHeridaUbicacion', 'nuevaHeridaClasificacion', 'nuevaHeridaCausa', 'nuevaHeridaObservacion');
        $this->dispatch('herida-evidencia-registrada');
    }

    protected function validationAttributes(): array
    {
        return ['nuevaHeridaTipo' => 'tipo de herida', 'nuevaHeridaUbicacion' => 'ubicación', 'nuevaHeridaClasificacion' => 'clasificación', 'nuevaHeridaCausa' => 'causa', 'nuevaHeridaObservacion' => 'observación inicial'];
    }

    public function prepararNuevaHerida(): void
    {
        $this->autorizar($this->codResidente);
        Gate::authorize('adjuntar-evidencia-herida', [Residente::findOrFail($this->codResidente), 'heridas.crear']);
        $this->reset('lesionId', 'largoLesion', 'anchoLesion', 'profundidadLesion', 'dolorLesion', 'exudadoLesion', 'aspectoLesion', 'accionLesion', 'observacionLesion', 'olorLesion', 'materialesLesion', 'respuestaLesion', 'nuevaHeridaTipo', 'nuevaHeridaUbicacion', 'nuevaHeridaClasificacion', 'nuevaHeridaCausa', 'nuevaHeridaObservacion');
        $this->descartarFotosHerida();
        $this->registrandoNuevaHerida = true;
    }

    public function prepararPrimeraCuracion(): void
    {
        $this->autorizar($this->codResidente);
        abort_unless(($this->resultadoHerida['tipo'] ?? '') === 'herida' && ($this->resultadoHerida['cod_herida'] ?? '') === $this->lesionId, 409);
        $herida = Herida::where('cod_residente', $this->codResidente)->where('estado', 'ACTIVA')->findOrFail($this->lesionId);
        Gate::authorize('adjuntar-evidencia-herida', [$herida->residente, 'curaciones_herida.crear']);
        $this->reset('resultadoHerida', 'largoLesion', 'anchoLesion', 'profundidadLesion', 'dolorLesion', 'exudadoLesion', 'aspectoLesion', 'accionLesion', 'observacionLesion', 'olorLesion', 'materialesLesion', 'respuestaLesion');
        $this->descartarFotosHerida();
        $this->registrandoNuevaHerida = false;
    }

    #[Url(as: 'adulto')]
    public string $codResidente = '';

    #[Url]
    public string $cuidado = '';

    #[Url]
    public string $seccion = 'CUIDADOS';

    #[Url]
    public string $tipo = 'ALIMENTACION';

    public string $subtipo = '';

    public string $nivelAyuda = '';

    public string $tolerancia = '';

    public string $resultado = '';

    public ?int $porcentaje = null;

    public ?int $cantidadMl = null;

    public ?int $cantidadDespertares = null;

    public ?int $dolor = null;

    public string $consistencia = '';

    public string $ayudaTecnica = '';

    public string $dispositivoOtro = '';

    public function updatedAyudaTecnica(string $value): void
    {
        if ($value !== 'OTRO') {
            $this->dispositivoOtro = '';
        }
    }

    public string $cambioBasal = 'SIN_CAMBIOS';

    public string $calidad = '';

    public string $estadoGeneral = 'SIN_CAMBIOS';

    public string $conciencia = '';

    public string $cognicion = '';

    public string $conducta = '';

    public string $respiracion = '';

    public ?bool $esContinente = null;

    public ?bool $presentaDificultad = null;

    public ?bool $presentaDolor = null;

    public ?bool $usaDispositivo = null;

    public bool $deambulacionNocturna = false;

    public bool $agitacion = false;

    public string $horaInicio = '';

    public string $horaFin = '';

    public string $motivo = '';

    public string $observacion = '';

    public string $intervencionId = '';

    public string $tipoDispositivo = 'OXIGENO';

    public string $ubicacionDispositivo = '';

    public string $indicacionDispositivo = '';

    public string $motivoRetiroDispositivo = '';

    public string $tipoIncidente = 'CAIDA';

    public string $lugarIncidente = '';

    public string $actividadPrevia = '';

    public string $testigo = '';

    public string $descripcionIncidente = '';

    public bool $presenciado = false;

    public bool $hayLesion = false;

    public bool $cambioCognitivo = false;

    public bool $medicoInformado = false;

    public bool $familiarInformado = false;

    public bool $requiereSeguimiento = true;

    public string $movilidadPosterior = '';

    public ?int $dolorIncidente = null;

    public string $tipoLesion = '';

    public string $zonaLesion = '';

    public string $lateralidad = '';

    public string $lesionId = '';

    public string $exudadoLesion = '';

    public string $pielLesion = '';

    public string $aspectoLesion = '';

    public string $accionLesion = '';

    public string $observacionLesion = '';

    public ?float $largoLesion = null;

    public ?float $anchoLesion = null;

    public ?float $profundidadLesion = null;

    public ?int $dolorLesion = null;

    public bool $lesionMedible = true;

    public string $incidenteId = '';

    public string $seguimientoIncidente = '';

    public string $evaluacionFinalIncidente = '';

    public string $resultadoIncidente = '';

    public string $resultadoCierreLesion = '';

    public string $motivoCierreLesion = '';

    public string $faseDolor = 'VALORACION';

    public string $valoracionDolorId = '';

    public string $detalleDolor = '';

    public string $resultadoDolor = '';

    public ?int $intensidadDolor = null;

    public string $registroRectificarId = '';

    public string $motivoRectificacion = '';

    public function mount(?string $codResidente = null, bool $emergente = false): void
    {
        abort_unless(Auth::user()?->canAny(['atenciones.ver', 'enfermeria.ver_ficha_paciente']), 403);
        $this->codResidente = $codResidente ?: $this->codResidente;

        if ($this->codResidente) {
            $this->autorizar($this->codResidente);
        }
        $this->emergente = $emergente;
        if ($emergente) {
            $this->cuidado = 'heridas';
            $this->seccion = 'HERIDAS';
            $residente = Residente::findOrFail($this->codResidente);
            $this->lesionId = Herida::where('cod_residente', $this->codResidente)->where('estado', 'ACTIVA')->latest('fecha_hora_identificacion')->value('cod_herida') ?? '';
            Gate::authorize('adjuntar-evidencia-herida', [$residente, $this->lesionId !== '' ? 'curaciones_herida.crear' : 'heridas.crear']);
            $this->mostrarFormularioHerida = true;
        }
    }

    private function autorizar(string $codResidente): void
    {
        app(TurnoEnfermeriaService::class)->autorizarConsultaResidente($codResidente, Auth::user());
    }

    private function autorizarMutacion(string $codResidente): void
    {
        app(TurnoEnfermeriaService::class)
            ->autorizarMutacionPaciente($codResidente, 'atenciones.crear', Auth::user());
    }

    private function turnoActual(): ?string
    {
        return app(TurnoEnfermeriaService::class)->obtenerTurnoActivo(Auth::user(), today()->toDateString())?->cod_turno;
    }

    public function updatedCodResidente(string $valor): void
    {
        if ($valor) {
            $this->autorizar($valor);
        }
        $this->intervencionId = '';
    }

    public function guardarCuidado(): void
    {
        $this->resetValidation();
        $this->autorizarMutacion($this->codResidente);
        $permiso = match ($this->tipo) {
            'ALIMENTACION' => 'registros_ingesta.crear',
            'HIDRATACION' => 'registros_hidratacion.crear',
            'ELIMINACION' => 'registros_eliminacion.crear',
            'MOVILIDAD' => 'registros_movilidad.crear',
            'SUENO' => 'registros_sueno.crear',
            default => 'atenciones.crear',
        };
        abort_unless(Auth::user()?->can($permiso), 403);

        $user = Auth::user();
        $personal = $user?->personal;
        abort_unless($personal, 403, 'Personal clínico no vinculado.');

        $miTurnoService = app(MiTurnoService::class);
        $jornada = $miTurnoService->resolverJornadaActual($personal, now());
        if (! $jornada) {
            throw ValidationException::withMessages([
                'jornada' => 'No existe una jornada activa asignada al personal clínico.',
            ]);
        }

        $tipoNorm = strtoupper(trim($this->tipo));

        if ($tipoNorm === 'ELIMINACION') {
            throw ValidationException::withMessages(['tipo' => 'Registra la eliminación desde Mis residentes, con el formulario estructurado.']);
        }
        if ($tipoNorm === 'HIDRATACION') {
            try {
                app(CuidadosEnfermeriaService::class)->registrarHidratacion($this->codResidente, [
                    'cantidad_ml' => $this->cantidadMl, 'tipo_liquido' => $this->subtipo,
                    'tolerancia' => $this->tolerancia, 'observacion' => $this->observacion,
                ], $user);
            } catch (ValidationException $exception) {
                throw ValidationException::withMessages(collect($exception->errors())->mapWithKeys(
                    fn ($messages, $key) => [match ($key) {
                        'cantidad_ml' => 'cantidadMl', 'tipo_liquido' => 'subtipo', default => $key
                    } => $messages]
                )->all());
            }
            $this->reset(['cantidadMl', 'subtipo', 'tolerancia', 'observacion']);
            $this->dispatch('swal', icon: 'success', title: 'Hidratación registrada', text: 'El aporte quedó guardado en el historial del residente.');

            return;
        }

        if ($tipoNorm === 'MOVILIDAD') {
            try {
                app(CuidadosEnfermeriaService::class)->registrarMovilidad($this->codResidente, [
                    'marcha' => $this->subtipo,
                    'tipo_apoyo' => $this->nivelAyuda ?: null,
                    'dispositivo' => $this->ayudaTecnica ?: null,
                    'dispositivo_otro' => $this->dispositivoOtro ?: null,
                    'observacion' => $this->observacion ?: $this->motivo ?: null,
                ], $user);
            } catch (ValidationException $exception) {
                throw ValidationException::withMessages(collect($exception->errors())->mapWithKeys(
                    fn ($messages, $key) => [match ($key) {
                        'marcha' => 'subtipo', 'tipo_apoyo' => 'nivelAyuda', 'dispositivo' => 'ayudaTecnica', 'dispositivo_otro' => 'dispositivoOtro', default => $key
                    } => $messages]
                )->all());
            }
            $this->reset(['subtipo', 'nivelAyuda', 'ayudaTecnica', 'dispositivoOtro', 'observacion', 'motivo']);
            $this->dispatch('swal', icon: 'success', title: 'Movilidad registrada', text: 'El registro quedó guardado en el historial del residente.');

            return;
        }

        $reglas = [
            'tipo' => 'required|in:ALIMENTACION,HIDRATACION,ELIMINACION,HIGIENE,MOVILIDAD,SUENO,VALORACION_RAPIDA,PROCEDIMIENTO',
            'subtipo' => 'required|string|max:60',
            'porcentaje' => 'nullable|integer|in:0,25,50,75,100',
            'cantidadMl' => 'required_if:tipo,HIDRATACION|nullable|integer|min:1|max:10000',
            'cantidadDespertares' => 'nullable|integer|min:0|max:30',
            'dolor' => 'nullable|integer|min:0|max:10',
            'motivo' => 'nullable|string|max:2000',
            'observacion' => 'nullable|string|max:5000',
            'intervencionId' => in_array($tipoNorm, ['ALIMENTACION', 'HIDRATACION', 'ELIMINACION', 'MOVILIDAD', 'SUENO'], true)
                ? 'nullable|string|max:20'
                : 'required|string|max:20',
        ];
        $this->validate($reglas, [
            'subtipo.required' => 'Describa el cuidado realmente realizado.',
            'cantidadMl.required_if' => 'Registre la cantidad real administrada en mililitros.',
            'intervencionId.required' => 'Seleccione la intervención vigente del plan de cuidados.',
        ]);

        if ($tipoNorm === 'ALIMENTACION' && $this->porcentaje !== null && $this->porcentaje < 50 && empty(trim($this->motivo))) {
            $this->addError('motivo', 'Indique el motivo de la baja ingesta.');

            return;
        }

        app(RegistrarCuidadosDeTurno::class)->guardarCuidado(auth()->user(), [
            'codResidente' => $this->codResidente,
            'tipo' => $this->tipo,
            'subtipo' => $this->subtipo,
            'porcentaje' => $this->porcentaje,
            'estadoGeneral' => $this->estadoGeneral,
            'tolerancia' => $this->tolerancia,
            'presentaDificultad' => $this->presentaDificultad,
            'observacion' => $this->observacion,
            'motivo' => $this->motivo,
            'cantidadDespertares' => $this->cantidadDespertares,
            'agitacion' => $this->agitacion,
            'calidad' => $this->calidad,
            'intervencionId' => $this->intervencionId,
            'resultado' => $this->resultado,
            'dolor' => $this->dolor,
            'cambioBasal' => $this->cambioBasal,
        ]);

        $this->resetFormularioCuidado();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Cuidado registrado', 'text' => 'El registro quedó firmado y disponible en la evolución del residente.']);
    }

    public function limpiarErroresCaptura(): void
    {
        abort_unless(auth()->user()?->estado === 'ACTIVO', 403);
        $this->resetValidation();
    }

    private function resetFormularioCuidado(): void
    {
        $this->reset('dispositivoOtro');
        $this->reset(['intervencionId', 'subtipo', 'nivelAyuda', 'tolerancia', 'resultado', 'porcentaje', 'cantidadMl', 'cantidadDespertares', 'dolor', 'consistencia', 'ayudaTecnica', 'calidad', 'esContinente', 'presentaDificultad', 'presentaDolor', 'usaDispositivo', 'deambulacionNocturna', 'agitacion', 'horaInicio', 'horaFin', 'motivo', 'observacion', 'conciencia', 'cognicion', 'conducta', 'respiracion']);
        $this->cambioBasal = 'SIN_CAMBIOS';
        $this->estadoGeneral = 'SIN_CAMBIOS';
    }

    public function guardarDispositivo(): void
    {
        $this->resetValidation();
        app(GestionarDispositivosClinicos::class)->guardarDispositivo(auth()->user(), [
            'codResidente' => $this->codResidente,
            'tipoDispositivo' => $this->tipoDispositivo,
            'ubicacionDispositivo' => $this->ubicacionDispositivo,
            'indicacionDispositivo' => $this->indicacionDispositivo,
        ]);

        $this->reset(['ubicacionDispositivo', 'indicacionDispositivo']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Dispositivo registrado', 'text' => 'El dispositivo ya aparece en la identificación segura del residente.']);
    }

    public function retirarDispositivo(string $codigo): void
    {
        $this->resetValidation();
        app(GestionarDispositivosClinicos::class)->retirarDispositivo(auth()->user(), [
            'codResidente' => $this->codResidente,
            'motivoRetiroDispositivo' => $this->motivoRetiroDispositivo,
        ], $codigo);

        $this->motivoRetiroDispositivo = '';
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Dispositivo retirado']);
    }

    public function guardarIncidente(): void
    {
        $this->resetValidation();
        app(GestionarIncidentesEnfermeria::class)->guardarIncidente(auth()->user(), [
            'codResidente' => $this->codResidente,
            'movilidadPosterior' => $this->movilidadPosterior,
            'dolorIncidente' => $this->dolorIncidente,
            'hayLesion' => $this->hayLesion,
            'presenciado' => $this->presenciado,
            'testigo' => $this->testigo,
            'familiarInformado' => $this->familiarInformado,
            'tipoIncidente' => $this->tipoIncidente,
            'lugarIncidente' => $this->lugarIncidente,
            'descripcionIncidente' => $this->descripcionIncidente,
            'medicoInformado' => $this->medicoInformado,
            'requiereSeguimiento' => $this->requiereSeguimiento,
            'tipoLesion' => $this->tipoLesion,
            'zonaLesion' => $this->zonaLesion,
            'actividadPrevia' => $this->actividadPrevia,
        ]);

        $this->reset(['lugarIncidente', 'actividadPrevia', 'presenciado', 'testigo', 'descripcionIncidente', 'dolorIncidente', 'hayLesion', 'movilidadPosterior', 'cambioCognitivo', 'medicoInformado', 'familiarInformado', 'tipoLesion', 'zonaLesion', 'lateralidad']);
        $this->requiereSeguimiento = true;
        $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Incidente registrado', 'text' => 'El incidente quedó registrado en el expediente V2 del residente.']);
    }

    public function seguimientoIncidente(): void
    {
        $this->resetValidation();
        app(GestionarIncidentesEnfermeria::class)->seguimientoIncidente(auth()->user(), [
            'codResidente' => $this->codResidente,
            'incidenteId' => $this->incidenteId,
            'seguimientoIncidente' => $this->seguimientoIncidente,
        ]);

        $this->reset(['incidenteId', 'seguimientoIncidente']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Seguimiento registrado']);
    }

    public function cerrarIncidente(): void
    {
        $this->resetValidation();
        app(GestionarIncidentesEnfermeria::class)->cerrarIncidente(auth()->user(), [
            'codResidente' => $this->codResidente,
            'incidenteId' => $this->incidenteId,
            'evaluacionFinalIncidente' => $this->evaluacionFinalIncidente,
            'resultadoIncidente' => $this->resultadoIncidente,
        ]);

        $this->reset(['incidenteId', 'evaluacionFinalIncidente', 'resultadoIncidente']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Incidente cerrado']);
    }

    public function guardarSeguimientoLesion(): void
    {
        $this->resetValidation();
        $curacion = app(RegistrarCuidadosDeTurno::class)->guardarSeguimientoLesion(auth()->user(), [
            'codResidente' => $this->codResidente,
            'lesionId' => $this->lesionId,
            'largoLesion' => $this->largoLesion,
            'anchoLesion' => $this->anchoLesion,
            'profundidadLesion' => $this->profundidadLesion,
            'aspectoLesion' => $this->aspectoLesion,
            'exudadoLesion' => $this->exudadoLesion,
            'dolorLesion' => $this->dolorLesion,
            'accionLesion' => $this->accionLesion,
            'observacionLesion' => $this->observacionLesion,
            'olorLesion' => $this->olorLesion,
            'materialesLesion' => $this->materialesLesion,
            'respuestaLesion' => $this->respuestaLesion,
            'fotografias' => $this->fotografias,
            'descripcionesFotos' => $this->descripcionesFotos,
        ]);
        $this->resultadoHerida = ['tipo' => 'curacion', 'ubicacion' => $curacion->herida->ubicacion, 'fecha' => $curacion->fecha_hora->format('d/m/Y H:i'), 'longitud' => $curacion->longitud, 'ancho' => $curacion->ancho, 'profundidad' => $curacion->profundidad, 'fotografias' => count($this->fotografias)];
        $this->reset('fotografias', 'descripcionesFotos', 'olorLesion', 'materialesLesion', 'respuestaLesion');
        $this->reset(['largoLesion', 'anchoLesion', 'profundidadLesion', 'dolorLesion', 'exudadoLesion', 'pielLesion', 'aspectoLesion', 'accionLesion', 'observacionLesion']);
        $this->lesionMedible = true;
        $this->dispatch('herida-evidencia-registrada');
    }

    public function cerrarLesion(string $codigo): void
    {
        $this->resetValidation();
        app(RegistrarCuidadosDeTurno::class)->cerrarLesion(auth()->user(), [
            'codResidente' => $this->codResidente,
            'resultadoCierreLesion' => $this->resultadoCierreLesion,
            'motivoCierreLesion' => $this->motivoCierreLesion,
        ], $codigo);

        $this->reset(['resultadoCierreLesion', 'motivoCierreLesion']);
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Herida cerrada', 'text' => 'La evolución histórica permanece disponible.']);
    }

    public function guardarDolor(): void
    {
        $this->resetValidation();
        app(RegistrarValoracionDolor::class)->guardarDolor(auth()->user(), [
            'codResidente' => $this->codResidente,
            'intensidadDolor' => $this->intensidadDolor,
            'detalleDolor' => $this->detalleDolor,
            'resultadoDolor' => $this->resultadoDolor,
            'faseDolor' => $this->faseDolor,
        ]);

        $this->reset(['valoracionDolorId', 'detalleDolor', 'resultadoDolor', 'intensidadDolor']);
        $this->faseDolor = 'VALORACION';
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Dolor registrado', 'text' => 'La secuencia clínica quedó vinculada.']);
    }

    public function rectificarCuidado(): void
    {
        $this->resetValidation();
        app(RegistrarCuidadosDeTurno::class)->rectificarCuidado(auth()->user(), [
            'codResidente' => $this->codResidente,
            'registroRectificarId' => $this->registroRectificarId,
            'motivoRectificacion' => $this->motivoRectificacion,
        ]);

        $this->reset(['registroRectificarId', 'motivoRectificacion']);
        $this->resetFormularioCuidado();
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Rectificación registrada', 'text' => 'El original permanece visible con la anotación de rectificación.']);
    }

    public function puedeMutarRegistro(string $permiso): bool
    {
        if (! $this->codResidente || ! Auth::user()?->can($permiso)) {
            return false;
        }
        try {
            $this->autorizarMutacion($this->codResidente);

            return true;
        } catch (HttpException $e) {
            if (in_array($e->getStatusCode(), [403, 409], true)) {
                return false;
            }
            throw $e;
        }
    }

    public function render()
    {
        if ($this->cuidado !== '') {
            $opcion = NavegacionCuidadosService::opcion($this->cuidado);
            abort_unless(in_array($this->cuidado, ['sueno', 'hidratacion', 'heridas'], true), 404);
            abort_unless(Auth::user()?->can($opcion['permission']), 403);
        }
        abort_unless(in_array($this->seccion, ['CUIDADOS', 'DOLOR', 'DISPOSITIVOS', 'INCIDENTES', 'HISTORIAL', 'HERIDAS'], true), 404);
        if ($this->seccion === 'HERIDAS') {
            abort_unless(Auth::user()?->can('heridas.ver'), 403);
        }
        if ($this->codResidente) {
            $this->autorizar($this->codResidente);
        }
        $turnos = app(TurnoEnfermeriaService::class);
        $esSuperAdmin = $turnos->esSuperAdmin(Auth::user());
        $ids = $turnos->obtenerPacientesAsignadosIds(Auth::user());
        $pacientes = Residente::whereIn('cod_residente', $ids)->orderBy('apellido_paterno')->get();
        $adulto = $this->codResidente ? Residente::with([
            'cama.habitacion',
            'dispositivosActivos',
            'heridas.curaciones.documentosClinicos.personal',
            'heridas.curaciones.personal',
            'heridas.documentosClinicos.personal',
            'ejecucionesCuidado' => fn ($q) => $q->latest('fecha_hora_programada')->limit(30),
            'incidentesClinicos' => fn ($q) => $q->latest('fecha_hora')->limit(20),
        ])->find($this->codResidente) : null;

        $intervencionesActivas = $this->codResidente ? IntervencionCuidado::whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])
            ->whereHas('plan', fn ($q) => $q->where('cod_residente', $this->codResidente)->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE']))
            ->get() : collect();

        $registrosCuidado = collect();
        if ($this->codResidente && in_array($this->cuidado, ['sueno', 'hidratacion'], true)) {
            $modelo = $this->cuidado === 'sueno' ? RegistroSueno::class : RegistroHidratacion::class;
            $fecha = $this->cuidado === 'sueno' ? 'fecha' : 'fecha_hora';
            $registrosCuidado = $modelo::where('cod_residente', $this->codResidente)->latest($fecha)->limit(12)->get();
        }

        $modoConsulta = Auth::user()?->hasRole('ENFERMEROS')
            && (! $this->codResidente || ! $turnos->esPacienteAsignado($this->codResidente, Auth::user()));

        $heridaSeleccionada = $adulto?->heridas->firstWhere('cod_herida', $this->lesionId);
        $galeriaHerida = collect();
        $evolucionHerida = [];
        if ($heridaSeleccionada) {
            $curaciones = $heridaSeleccionada->curaciones->sortByDesc('fecha_hora');
            $documentos = $heridaSeleccionada->documentosClinicos->concat($curaciones->flatMap->documentosClinicos)->sortByDesc('fecha_hora');
            $puedeConsultar = Gate::allows('consultar-contexto-evidencia-herida', $heridaSeleccionada);
            $puedeCuraciones = Auth::user()->can('curaciones_herida.ver');
            $galeriaHerida = $documentos->filter(fn ($doc) => $puedeConsultar && $doc->estado === 'VIGENTE'
                && $doc->tipo_documento === 'FOTO_HERIDA' && $doc->cod_residente === $adulto->getKey()
                && (($doc->cod_herida === $heridaSeleccionada->getKey() && $doc->cod_curacion === null)
                    || ($doc->cod_herida === null && $puedeCuraciones && $curaciones->contains('cod_curacion', $doc->cod_curacion))))->map(function ($doc) use ($heridaSeleccionada, $curaciones) {
                        $curacion = $curaciones->firstWhere('cod_curacion', $doc->cod_curacion);

                        return ['codigo' => $doc->getKey(), 'curacion' => $doc->cod_curacion, 'url' => route('admin.cuidados.fotografias.ver', [$doc->cod_residente, $doc->getKey()]), 'fecha' => $doc->fecha_hora->format('d/m/Y H:i'), 'profesional' => trim(($doc->personal?->nombres ?? '').' '.($doc->personal?->apellido_paterno ?? '')), 'descripcion' => $doc->descripcion ?? '', 'ubicacion' => $heridaSeleccionada->ubicacion, 'dimensiones' => $curacion && $curacion->longitud !== null && $curacion->ancho !== null ? $curacion->longitud.' × '.$curacion->ancho.' cm' : 'Sin medidas registradas', 'grupo' => ! $doc->cod_curacion ? 'Herida original' : ($curaciones->first()?->getKey() === $doc->cod_curacion ? 'Curación actual' : 'Curación anterior')];
                    })->values();
        }

        $datosHerida = app(PrepararEvolucionHerida::class)->desde($heridaSeleccionada && Auth::user()->can('curaciones_herida.ver') ? $heridaSeleccionada->curaciones : collect());
        $evolucionHerida = $datosHerida['serie'];
        $jornadaHerida = $this->cuidado === 'heridas' && Auth::user()?->personal ? app(MiTurnoService::class)->resolverJornadaActual(Auth::user()->personal, now()) : null;

        return view('livewire.cuidados.registros-enfermeria', compact('pacientes', 'adulto', 'esSuperAdmin', 'intervencionesActivas', 'registrosCuidado', 'modoConsulta', 'heridaSeleccionada', 'galeriaHerida', 'evolucionHerida', 'datosHerida', 'jornadaHerida'))
            ->layout('layouts.enfermeria');
    }
}
