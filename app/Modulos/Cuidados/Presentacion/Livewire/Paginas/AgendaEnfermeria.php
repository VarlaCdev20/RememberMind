<?php

namespace App\Modulos\Cuidados\Presentacion\Livewire\Paginas;

use App\Models\Alerta;
use App\Models\AsignacionPersonal;
use App\Models\EjecucionCuidado;
use App\Models\IntervencionCuidado;
use App\Models\PlanCuidado;
use App\Models\ProgramacionCuidado;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Cuidados\Aplicacion\Acciones\GestionarEjecucionesAgenda;
use App\Modulos\Cuidados\Aplicacion\Consultas\OcurrenciasCuidado;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class AgendaEnfermeria extends Component
{
    // Navegación por pestañas: AGENDA | HISTORIAL
    public string $tab = 'agenda'; // 'agenda', 'historial'

    // Filtros rápidos de Agenda
    public string $buscar = '';

    public string $filtroPrioridad = ''; // '', 'ALTA', 'MEDIA', 'BAJA'

    public string $filtroEstado = ''; // '', 'PENDIENTE', 'VENCIDA', 'REALIZADA', 'ALERTA'

    public string $filtroResidente = '';

    // Filtros de Historial
    public string $buscarHistorial = '';

    public string $filtroHistorialResidente = '';

    public string $filtroHistorialFecha = '';

    // Modal de Registro de Cuidado
    public bool $modalRegistrarAbierto = false;

    public ?string $intervencionId = null;

    public ?string $programacionId = null;

    public ?string $residenteId = null;

    public array $datosModal = [];

    // Formulario de Registro
    public ?bool $fueRealizado = null;

    public string $resultado = '';

    public string $observacion = '';

    public string $motivoOmision = '';

    public bool $esNoRealizada = false;

    public bool $pasoRevision = false;

    public bool $procesandoRegistro = false;

    // Panel de inspección previo al registro
    public ?string $itemDetalleId = null;

    // Confirmación compacta de registro exitoso
    public ?array $ultimoRegistro = null;

    // Recepción de turno (para compatibilidad de rol enfermero)
    public string $observacionRecepcion = '';

    protected $rules = [
        'resultado' => 'required|string|min:3|max:60',
        'observacion' => 'nullable|string|max:500',
        'motivoOmision' => 'nullable|string|max:500',
    ];

    public function mount(): void
    {
        abort_unless(Auth::user()?->can('enfermeria.ver_dashboard'), 403);

        if (request()->filled('residente')) {
            $this->filtroResidente = (string) request('residente');
        }
        if (request()->filled('estado')) {
            $this->filtroEstado = (string) request('estado');
        }
    }

    public function cambiarTab(string $nuevoTab): void
    {
        if (in_array($nuevoTab, ['agenda', 'historial'])) {
            $this->tab = $nuevoTab;
        }
    }

    public function toggleDetalleItem(string $itemId): void
    {
        $this->itemDetalleId = ($this->itemDetalleId === $itemId) ? null : $itemId;
    }

    public function setFueRealizado(bool $valor): void
    {
        $this->fueRealizado = $valor;
        $this->esNoRealizada = ! $valor;
        $this->resetErrorBag(['fueRealizado', 'resultado', 'motivoOmision', 'observacion']);
        if (! $valor) {
            if (empty($this->resultado) || $this->resultado === 'Satisfactorio') {
                $this->resultado = 'No realizada / Omitida';
            }
        } else {
            if ($this->resultado === 'No realizada / Omitida' || in_array(strtolower($this->resultado), ['omitida', 'no realizada'])) {
                $this->resultado = '';
            }
            $this->motivoOmision = '';
        }
    }

    public function updatedEsNoRealizada($value): void
    {
        $this->setFueRealizado(! $value);
    }

    public function abrirModalRegistrar(
        string $codIntervencion,
        string $codResidente,
        ?string $codProgramacion = null,
        ?string $horaProgramada = null
    ): void {
        $this->autorizarLectura();
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria($codResidente, 'ejecuciones_cuidado.crear', Auth::user());
        $this->resetValidation();
        $this->intervencionId = $codIntervencion;
        $this->residenteId = $codResidente;
        $this->programacionId = $codProgramacion;
        $this->fueRealizado = null;
        $this->resultado = '';
        $this->observacion = '';
        $this->motivoOmision = '';
        $this->esNoRealizada = false;
        $this->pasoRevision = false;
        $this->procesandoRegistro = false;

        $residente = Residente::with(['cama.habitacion'])->where('cod_residente', $codResidente)->first();
        $intervencion = IntervencionCuidado::with('plan')->where('cod_intervencion', $codIntervencion)
            ->whereHas('plan', fn ($q) => $q->where('cod_residente', $codResidente))->firstOrFail();
        $programacion = $codProgramacion ? ProgramacionCuidado::where('cod_programacion', $codProgramacion)
            ->where('cod_intervencion', $intervencion->getKey())->firstOrFail() : null;

        $habTexto = 'Sin asignar';
        if ($residente?->cama?->habitacion) {
            $habTexto = 'Hab. '.($residente->cama->habitacion->codigo ?? $residente->cama->habitacion->nombre).
                        ' / Cama '.($residente->cama->codigo ?? 'Sin asignar');
        }

        // Obtener última ejecución previa registrada de esta intervención
        $ultimaEjecucion = EjecucionCuidado::with(['personal'])
            ->where('cod_intervencion', $codIntervencion)
            ->where('cod_residente', $codResidente)
            ->latest('fecha_hora_ejecucion')
            ->first();

        $user = $this->autorizarLectura();
        $responsableNombre = $user?->personal
            ? trim("{$user->personal->nombres} {$user->personal->apellido_paterno}")
            : ($user?->name ?? 'Enfermería en turno');

        $this->datosModal = [
            'residente_nombre' => $residente ? trim("{$residente->nombres} {$residente->apellido_paterno} {$residente->apellido_materno}") : 'Residente asignado',
            'residente_nhc' => $residente->historial_clinico ?? $residente->cod_residente ?? 'NHC-000',
            'ubicacion' => $habTexto,
            'plan_nombre' => $intervencion?->plan?->nombre ?? 'Plan de Cuidado Integral de Enfermería',
            'plan_objetivo' => $intervencion?->plan?->objetivo_general ?? 'Mantenimiento y cuidado integral del residente',
            'intervencion_nombre' => $intervencion?->nombre ?? 'Intervención de enfermería programada',
            'intervencion_descripcion' => $intervencion?->descripcion ?? 'Realizar procedimiento clínico de cuidado según protocolo institucional.',
            'prioridad' => strtoupper($intervencion?->prioridad ?? 'MEDIA'),
            'frecuencia' => $programacion?->frecuencia ?? 'Según turno asignado',
            'hora_programada' => $horaProgramada ?? ($programacion?->hora_programada ? substr((string) $programacion->hora_programada, 0, 5) : now()->format('H:i')),
            'cod_programacion' => $codProgramacion,
            'responsable' => $responsableNombre,
            'ultima_ejecucion' => $ultimaEjecucion ? [
                'fecha_hora' => $ultimaEjecucion->fecha_hora_ejecucion ? Carbon::parse($ultimaEjecucion->fecha_hora_ejecucion)->format('d/m/Y H:i') : null,
                'resultado' => $ultimaEjecucion->resultado,
                'estado' => $ultimaEjecucion->estado,
                'personal' => $ultimaEjecucion->personal ? trim("{$ultimaEjecucion->personal->nombres} {$ultimaEjecucion->personal->apellido_paterno}") : 'Personal',
                'motivo_omision' => $ultimaEjecucion->motivo_omision,
                'observacion' => $ultimaEjecucion->observacion,
            ] : null,
        ];

        $this->modalRegistrarAbierto = true;
    }

    public function cerrarModalRegistrar(): void
    {
        $this->modalRegistrarAbierto = false;
        $this->intervencionId = null;
        $this->residenteId = null;
        $this->programacionId = null;
        $this->datosModal = [];
        $this->pasoRevision = false;
        $this->procesandoRegistro = false;
    }

    public function revisarAntesDeConfirmar(): void
    {
        $this->resetErrorBag();

        if ($this->fueRealizado === null) {
            $this->addError('fueRealizado', 'Debe indicar expresamente si el cuidado fue realizado o no.');

            return;
        }

        if (empty(trim($this->resultado))) {
            $this->addError('resultado', 'Debe seleccionar o indicar el resultado del cuidado.');

            return;
        }

        if (mb_strlen(trim($this->resultado)) < 3 || mb_strlen(trim($this->resultado)) > 60) {
            $this->addError('resultado', 'El resultado debe tener entre 3 y 60 caracteres.');

            return;
        }

        if ($this->fueRealizado) {
            $resLower = mb_strtolower($this->resultado);
            $requiereObs = str_contains($resLower, 'dificultad')
                || str_contains($resLower, 'parcial')
                || str_contains($resLower, 'favorable')
                || str_contains($resLower, 'cambio');

            if ($requiereObs && mb_strlen(trim($this->observacion)) < 5) {
                $this->addError('observacion', 'Debe ingresar una observación clínica descriptiva cuando se reporta dificultad, colaboración parcial, evolución favorable o cambio relevante (mínimo 5 y máximo 500 caracteres).');

                return;
            }
        } else {
            if (mb_strlen(trim($this->motivoOmision)) < 5) {
                $this->addError('motivoOmision', 'El motivo de omisión es obligatorio cuando la intervención no se realiza (mínimo 5 y máximo 500 caracteres).');

                return;
            }
            if (mb_strlen(trim($this->observacion)) < 5) {
                $this->addError('observacion', 'Las observaciones clínicas son obligatorias cuando la intervención no se realiza (mínimo 5 y máximo 500 caracteres).');

                return;
            }
        }

        if (mb_strlen($this->observacion) > 500) {
            $this->addError('observacion', 'La observación clínica no puede superar los 500 caracteres.');

            return;
        }

        if (mb_strlen($this->motivoOmision) > 500) {
            $this->addError('motivoOmision', 'El motivo de omisión no puede superar los 500 caracteres.');

            return;
        }

        $this->pasoRevision = true;
    }

    public function volverAEditar(): void
    {
        $this->pasoRevision = false;
    }

    public function registrarEjecucion(): void
    {
        if ($this->procesandoRegistro) {
            return;
        }

        // Si fueRealizado no fue establecido explícitamente pero resultado sí (compatibilidad pruebas)
        if ($this->fueRealizado === null) {
            if (! empty($this->resultado)) {
                $esOmitida = $this->esNoRealizada || in_array(strtolower($this->resultado), ['no realizada', 'omitida', 'no realizada / omitida']);
                $this->fueRealizado = ! $esOmitida;
            } else {
                $this->addError('fueRealizado', 'Debe indicar expresamente si el cuidado fue realizado o no.');
                $this->pasoRevision = false;

                return;
            }
        }

        $this->procesandoRegistro = true;

        try {
            $registrada = app(GestionarEjecucionesAgenda::class)->registrarEjecucion(auth()->user(), [
                'esNoRealizada' => ! $this->fueRealizado,
                'resultado' => $this->resultado,
                'motivoOmision' => $this->motivoOmision,
                'residenteId' => $this->residenteId,
                'intervencionId' => $this->intervencionId,
                'programacionId' => $this->programacionId,
                'datosModal' => $this->datosModal,
                'observacion' => $this->observacion,
            ]);

            if (! $registrada) {
                $this->addError('errorModal', 'Esta intervención ya cuenta con un registro para el horario programado de hoy.');
                $this->procesandoRegistro = false;
                $this->pasoRevision = false;

                return;
            }

            $residenteNombre = $this->datosModal['residente_nombre'] ?? 'Residente';
            $intervencionNombre = $this->datosModal['intervencion_nombre'] ?? 'Cuidado';
            $estadoRegistrado = $this->fueRealizado ? 'REALIZADA' : 'NO_REALIZADA';

            $this->ultimoRegistro = [
                'residente' => $residenteNombre,
                'intervencion' => $intervencionNombre,
                'estado' => $estadoRegistrado,
                'resultado' => $this->resultado,
                'hora' => now()->format('H:i'),
            ];

            $this->cerrarModalRegistrar();

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Cuidado registrado',
                'text' => "Se registró la intervención {$intervencionNombre} para {$residenteNombre} ({$estadoRegistrado}).",
            ]);
        } catch (ValidationException $e) {
            $this->procesandoRegistro = false;
            $this->pasoRevision = false;
            foreach ($e->errors() as $campo => $mensajes) {
                foreach ($mensajes as $m) {
                    $this->addError($campo, $m);
                }
            }
        } catch (\Throwable $e) {
            $this->procesandoRegistro = false;
            $this->pasoRevision = false;
            $this->addError('errorModal', 'Error al registrar: '.$e->getMessage());
        }
    }

    public function recibirTurno(): void
    {
        app(GestionarEjecucionesAgenda::class)->recibirTurno(auth()->user(), [
            'observacionRecepcion' => $this->observacionRecepcion,
            'observacion' => $this->observacion,
        ]);

        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Turno recibido', 'text' => 'La recepción quedó registrada con fecha, hora y responsable.']);
    }

    private function autorizarLectura(): User
    {
        $usuario = auth()->id() ? User::query()->findOrFail(auth()->id()) : null;
        abort_unless($usuario?->estado === 'ACTIVO' && $usuario->can('enfermeria.ver_dashboard')
            && collect(['planes_cuidado.ver', 'intervenciones_cuidado.ver', 'programaciones_cuidado.ver', 'ejecuciones_cuidado.ver'])
                ->every(fn ($permiso) => $usuario->checkPermissionTo($permiso, 'web')), 403);
        Auth::setUser($usuario);

        return $usuario;
    }

    public function render()
    {
        $turnosService = app(TurnoEnfermeriaService::class);
        $user = $this->autorizarLectura();
        $esSuperAdmin = $turnosService->esSuperAdmin($user);
        $turno = $turnosService->obtenerTurnoActivo($user, today()->toDateString());

        // Residentes asignados según turno y rol
        $codigosResidentes = $esSuperAdmin
            ? $turnosService->obtenerPacientesAsignadosIds($user)
            : $turnosService->obtenerPacientesAsignadosIds($user, $turno?->cod_turno);

        $residentes = Residente::with(['cama.habitacion'])
            ->whereIn('cod_residente', $codigosResidentes)
            ->get()
            ->keyBy('cod_residente');

        // 1. Alertas clínicas activas
        $alertas = Alerta::whereIn('cod_residente', $codigosResidentes)
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION', 'ACTIVA'])
            ->get();

        $alertasPorResidente = $alertas->groupBy('cod_residente');

        // 2. Ejecuciones ya realizadas hoy (sin keyBy destructivo para soportar múltiples horarios)
        $ejecucionesHoy = EjecucionCuidado::with(['personal'])
            ->whereIn('cod_residente', $codigosResidentes)
            ->where(function ($q) {
                $q->whereDate('fecha_hora_programada', today())
                    ->orWhereDate('fecha_hora_ejecucion', today());
            })
            ->get();

        // Últimas ejecuciones históricas para panel de detalle
        $ultimasEjecuciones = EjecucionCuidado::with(['personal'])
            ->whereIn('cod_residente', $codigosResidentes)
            ->latest('fecha_hora_ejecucion')
            ->get()
            ->groupBy(fn ($e) => $e->cod_residente.'_'.$e->cod_intervencion);

        // 3. Cargar Planes, Intervenciones y Programaciones
        $planes = PlanCuidado::with([
            'intervenciones' => function ($q) {
                $q->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                    ->with(['programaciones' => function ($pq) {
                        $pq->whereIn('estado', ['ACTIVO', 'ACTIVA']);
                    }]);
            },
        ])
            ->whereIn('cod_residente', $codigosResidentes)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->get();

        $itemsAgenda = collect();

        foreach ($planes as $plan) {
            $residente = $residentes->get($plan->cod_residente);
            if (! $residente) {
                continue;
            }

            $habTexto = 'Sin asignar';
            if ($residente->cama?->habitacion) {
                $habTexto = 'Hab. '.($residente->cama->habitacion->codigo ?? $residente->cama->habitacion->nombre).
                            ' / Cama '.($residente->cama->codigo ?? 'Sin asignar');
            }

            foreach ($plan->intervenciones as $intervencion) {
                $programaciones = $intervencion->programaciones;
                $progsArray = $programaciones->isNotEmpty() ? $programaciones : [null];

                foreach ($progsArray as $prog) {
                    $ocurrencias = app(OcurrenciasCuidado::class);
                    $momentoProgramado = $prog ? $ocurrencias->momento($prog, now(), $turno) : null;
                    if ($momentoProgramado === null || ($plan->fecha_hora_apertura && $momentoProgramado->lt($plan->fecha_hora_apertura))
                        || ($plan->fecha_hora_cierre && $momentoProgramado->gt($plan->fecha_hora_cierre))) {
                        continue;
                    }
                    $horaProg = $momentoProgramado->format('H:i');
                    $frecuencia = $prog->frecuencia;
                    $prioridad = strtoupper($intervencion->prioridad ?? 'MEDIA');
                    $ejecucion = $ejecucionesHoy->first(fn ($e) => $ocurrencias->corresponde(
                        $e, $residente->getKey(), $intervencion->getKey(), $momentoProgramado));
                    // Última ejecución histórica de esta intervención
                    $ultimaPrevia = $ultimasEjecuciones->get($residente->cod_residente.'_'.$intervencion->cod_intervencion)?->first();

                    // Alerta vinculada específicamente a este cuidado
                    $alertaVinculada = $alertas->first(function ($a) use ($intervencion) {
                        return ($a->cod_registro === $intervencion->cod_intervencion)
                            || (str_contains(strtolower((string) $a->modulo), 'cuidado') &&
                                str_contains(strtolower((string) $a->titulo.' '.(string) $a->descripcion), strtolower((string) $intervencion->nombre)));
                    });

                    // Alerta a nivel de residente
                    $residenteConAlerta = $alertasPorResidente->has($residente->cod_residente);

                    $estado = $ocurrencias->estado($ejecucion);
                    if ($estado === 'PENDIENTE' && $momentoProgramado->lt(now()->subMinutes(30))) {
                        $estado = 'VENCIDA';
                    }
                    // Rank de orden obligatorio:
                    // 1. Alertas activas vinculadas
                    // 2. Prioridad más alta (sin alerta vinculada)
                    // 3. Vencidas
                    // 4. Próximas por hora programada
                    // 5. Realizadas / No realizadas (al final)
                    if ($alertaVinculada) {
                        $rank = 1;
                    } elseif ($prioridad === 'ALTA' && ! in_array($estado, ['REALIZADA', 'NO_REALIZADA', 'CANCELADA'])) {
                        $rank = 2;
                    } elseif ($estado === 'VENCIDA') {
                        $rank = 3;
                    } elseif ($estado === 'PENDIENTE') {
                        $rank = 4;
                    } else {
                        $rank = 5;
                    }

                    $itemsAgenda->push([
                        'id' => $intervencion->cod_intervencion.'_'.($prog?->cod_programacion ?? 'P0').'_'.str_replace(':', '', $horaProg),
                        'cod_intervencion' => $intervencion->cod_intervencion,
                        'cod_programacion' => $prog?->cod_programacion,
                        'cod_residente' => $residente->cod_residente,
                        'nombre_residente' => trim("{$residente->nombres} {$residente->apellido_paterno} {$residente->apellido_materno}"),
                        'iniciales' => strtoupper(substr((string) $residente->nombres, 0, 1).substr((string) ($residente->apellido_paterno ?? $residente->ap_paterno ?? 'R'), 0, 1)),
                        'ubicacion' => $habTexto,
                        'nombre_intervencion' => $intervencion->nombre,
                        'descripcion' => $intervencion->descripcion,
                        'nombre_plan' => $plan->nombre,
                        'prioridad' => $prioridad,
                        'frecuencia' => $frecuencia,
                        'hora_programada' => $horaProg,
                        'estado' => $estado,
                        'alerta_vinculada' => (bool) $alertaVinculada,
                        'alerta_descripcion' => $alertaVinculada?->descripcion ?? null,
                        'residente_con_alerta' => $residenteConAlerta,
                        'ejecucion' => $ejecucion,
                        'ultima_ejecucion' => $ultimaPrevia ? [
                            'fecha_hora' => $ultimaPrevia->fecha_hora_ejecucion ? Carbon::parse($ultimaPrevia->fecha_hora_ejecucion)->format('d/m/Y H:i') : null,
                            'resultado' => $ultimaPrevia->resultado,
                            'estado' => $ultimaPrevia->estado,
                            'personal' => $ultimaPrevia->personal ? trim("{$ultimaPrevia->personal->nombres} {$ultimaPrevia->personal->apellido_paterno}") : 'Personal',
                            'motivo_omision' => $ultimaPrevia->motivo_omision,
                            'observacion' => $ultimaPrevia->observacion,
                        ] : null,
                        'responsable_nombre' => $user?->personal ? trim("{$user->personal->nombres} {$user->personal->apellido_paterno}") : ($user?->name ?? 'Enfermería en turno'),
                        'rank' => $rank,
                    ]);
                }
            }
        }

        // Ordenamiento Estricto Requerido
        $itemsAgenda = $itemsAgenda->sort(function ($a, $b) {
            if ($a['rank'] !== $b['rank']) {
                return $a['rank'] <=> $b['rank'];
            }

            return strcmp($a['hora_programada'], $b['hora_programada']);
        })->values();

        // Contadores institucionales antes del filtro de estado
        $contadores = [
            'por_atender' => $itemsAgenda->filter(fn ($i) => in_array($i['estado'], ['PENDIENTE', 'VENCIDA']))->count(),
            'no_realizadas' => $itemsAgenda->where('estado', 'NO_REALIZADA')->count(),
            'realizadas' => $itemsAgenda->where('estado', 'REALIZADA')->count(),
            'todas' => $itemsAgenda->count(),
        ];

        // Filtros de Agenda
        if (! empty($this->buscar)) {
            $busq = mb_strtolower(trim($this->buscar));
            $itemsAgenda = $itemsAgenda->filter(function ($i) use ($busq) {
                return str_contains(mb_strtolower($i['nombre_residente']), $busq)
                    || str_contains(mb_strtolower($i['nombre_intervencion']), $busq)
                    || str_contains(mb_strtolower($i['nombre_plan']), $busq);
            })->values();
        }

        if (! empty($this->filtroPrioridad)) {
            $itemsAgenda = $itemsAgenda->where('prioridad', $this->filtroPrioridad)->values();
        }

        // Filtro de Estado institucional con las 4 categorías requeridas
        if (! empty($this->filtroEstado)) {
            $filtro = strtolower(trim($this->filtroEstado));
            if ($filtro === 'por_atender' || $filtro === 'por atender') {
                $itemsAgenda = $itemsAgenda->filter(fn ($i) => in_array($i['estado'], ['PENDIENTE', 'VENCIDA']))->values();
            } elseif ($filtro === 'no_realizadas' || $filtro === 'no realizadas' || $filtro === 'no_realizada') {
                $itemsAgenda = $itemsAgenda->where('estado', 'NO_REALIZADA')->values();
            } elseif ($filtro === 'realizadas' || $filtro === 'realizada') {
                $itemsAgenda = $itemsAgenda->where('estado', 'REALIZADA')->values();
            } elseif ($filtro === 'alerta') {
                $itemsAgenda = $itemsAgenda->where('alerta_vinculada', true)->values();
            } elseif ($filtro === 'vencida') {
                $itemsAgenda = $itemsAgenda->where('estado', 'VENCIDA')->values();
            } elseif ($filtro === 'pendiente') {
                $itemsAgenda = $itemsAgenda->where('estado', 'PENDIENTE')->values();
            }
        }

        if (! empty($this->filtroResidente)) {
            $itemsAgenda = $itemsAgenda->where('cod_residente', $this->filtroResidente)->values();
        }

        // 4. Historial clínico directo
        $queryHistorial = EjecucionCuidado::with([
            'residente.cama.habitacion',
            'intervencion.plan',
            'personal',
        ])
            ->whereIn('cod_residente', $codigosResidentes);

        if (! empty($this->buscarHistorial)) {
            $bH = mb_strtolower(trim($this->buscarHistorial));
            $queryHistorial->where(function ($q) use ($bH) {
                $q->whereHas('residente', function ($rq) use ($bH) {
                    $rq->where('nombres', 'like', "%{$bH}%")
                        ->orWhere('apellido_paterno', 'like', "%{$bH}%");
                })->orWhereHas('intervencion', function ($iq) use ($bH) {
                    $iq->where('nombre', 'like', "%{$bH}%");
                });
            });
        }

        if (! empty($this->filtroHistorialResidente)) {
            $queryHistorial->where('cod_residente', $this->filtroHistorialResidente);
        }

        if (! empty($this->filtroHistorialFecha)) {
            $queryHistorial->whereDate('fecha_hora_ejecucion', $this->filtroHistorialFecha);
        }

        $historial = $queryHistorial->latest('fecha_hora_ejecucion')->take(40)->get();

        $codPersonalActual = $user?->personal?->cod_personal;
        $recepcion = ! $esSuperAdmin && $turno && $codPersonalActual ? AsignacionPersonal::where('cod_personal', $codPersonalActual)
            ->whereHas('jornada', fn ($q) => $q->where('cod_turno', $turno->cod_turno)->whereDate('fecha_jornada', today()))
            ->first() : null;

        return view('livewire.cuidados.agenda-enfermeria', [
            'itemsAgenda' => $itemsAgenda,
            'contadores' => $contadores,
            'historial' => $historial,
            'residentes' => $residentes,
            'esSuperAdmin' => $esSuperAdmin,
            'puedeRegistrar' => $turno !== null && $user?->can('ejecuciones_cuidado.crear'),
            'turno' => $turno,
            'recepcion' => $recepcion,
        ])->layout('layouts.enfermeria');
    }
}
