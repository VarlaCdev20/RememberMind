<?php

namespace App\Modulos\Cuidados\Presentacion\Livewire\Paginas;

use App\Models\Alerta;
use App\Models\AsignacionPersonal;
use App\Models\EjecucionCuidado;
use App\Models\IntervencionCuidado;
use App\Models\PlanCuidado;
use App\Models\ProgramacionCuidado;
use App\Models\Residente;
use App\Modulos\Cuidados\Aplicacion\Acciones\GestionarEjecucionesAgenda;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
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
    public string $resultado = 'Satisfactorio';

    public string $observacion = '';

    public string $motivoOmision = '';

    public bool $esNoRealizada = false;

    public bool $procesandoRegistro = false;

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
    }

    public function cambiarTab(string $nuevoTab): void
    {
        if (in_array($nuevoTab, ['agenda', 'historial'])) {
            $this->tab = $nuevoTab;
        }
    }

    public function abrirModalRegistrar(
        string $codIntervencion,
        string $codResidente,
        ?string $codProgramacion = null,
        ?string $horaProgramada = null
    ): void {
        app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria($codResidente, 'ejecuciones_cuidado.crear', Auth::user());
        $this->resetValidation();
        $this->intervencionId = $codIntervencion;
        $this->residenteId = $codResidente;
        $this->programacionId = $codProgramacion;
        $this->resultado = 'Satisfactorio';
        $this->observacion = '';
        $this->motivoOmision = '';
        $this->esNoRealizada = false;
        $this->procesandoRegistro = false;

        $residente = Residente::with(['cama.habitacion'])->where('cod_residente', $codResidente)->first();
        $intervencion = IntervencionCuidado::with(['plan'])->where('cod_intervencion', $codIntervencion)->first();
        $programacion = $codProgramacion ? ProgramacionCuidado::where('cod_programacion', $codProgramacion)->first() : null;

        $habTexto = 'Sin asignar';
        if ($residente?->cama?->habitacion) {
            $habTexto = 'Hab. '.($residente->cama->habitacion->codigo ?? $residente->cama->habitacion->nombre).
                        ' / Cama '.($residente->cama->codigo ?? 'Sin asignar');
        }

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
        $this->procesandoRegistro = false;
    }

    public function updatedEsNoRealizada($value): void
    {
        if ($value) {
            $this->resultado = 'No realizada / Omitida';
        } else {
            $this->resultado = 'Satisfactorio';
            $this->motivoOmision = '';
        }
    }

    public function registrarEjecucion(): void
    {
        if ($this->procesandoRegistro) {
            return;
        }
        $this->validate();
        if (($this->esNoRealizada || in_array(strtolower($this->resultado), ['no realizada', 'omitida', 'no realizada / omitida'])) && mb_strlen(trim($this->motivoOmision)) < 5) {
            $this->addError('motivoOmision', 'El motivo de omisión es obligatorio cuando la intervención no se realiza (mínimo 5 caracteres).');

            return;
        }
        $this->procesandoRegistro = true;
        $registrada = app(GestionarEjecucionesAgenda::class)->registrarEjecucion(auth()->user(), [
            'procesandoRegistro' => $this->procesandoRegistro,
            'esNoRealizada' => $this->esNoRealizada,
            'resultado' => $this->resultado,
            'motivoOmision' => $this->motivoOmision,
            'residenteId' => $this->residenteId,
            'intervencionId' => $this->intervencionId,
            'datosModal' => $this->datosModal,
            'observacion' => $this->observacion,
        ]);
        if (! $registrada) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Ya registrada',
                'text' => 'Esta intervención ya cuenta con un registro para el horario programado de hoy.',
            ]);
            $this->cerrarModalRegistrar();

            return;
        }

        $this->cerrarModalRegistrar();
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Cuidado registrado',
            'text' => 'La ejecución de la intervención ha sido registrada exitosamente en la bitácora clínica.',
        ]);
    }

    public function recibirTurno(): void
    {
        app(GestionarEjecucionesAgenda::class)->recibirTurno(auth()->user(), [
            'observacionRecepcion' => $this->observacionRecepcion,
            'observacion' => $this->observacion,
        ]);

        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Turno recibido', 'text' => 'La recepción quedó registrada con fecha, hora y responsable.']);
    }

    public function render()
    {
        $turnosService = app(TurnoEnfermeriaService::class);
        $user = Auth::user();
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
            ->whereIn('estado', Alerta::ESTADOS_ACTIVOS)
            ->get();

        $alertasPorResidente = $alertas->groupBy('cod_residente');

        // 2. Ejecuciones ya realizadas hoy
        $ejecucionesHoy = EjecucionCuidado::with(['personal'])
            ->whereIn('cod_residente', $codigosResidentes)
            ->where(function ($q) {
                $q->whereDate('fecha_hora_programada', today())
                    ->orWhereDate('fecha_hora_ejecucion', today());
            })
            ->get()
            ->keyBy('cod_intervencion');

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
                    $horaProg = $prog?->hora_programada ? substr((string) $prog->hora_programada, 0, 5) : '08:00';
                    $frecuencia = $prog?->frecuencia ?? 'Diario según turno';
                    $prioridad = strtoupper($intervencion->prioridad ?? 'MEDIA');

                    // Comprobar ejecucion hoy
                    $ejecucion = $ejecucionesHoy->get($intervencion->cod_intervencion);

                    // Alerta vinculada específicamente a este cuidado
                    $alertaVinculada = $alertas->first(function ($a) use ($intervencion) {
                        return ($a->cod_registro === $intervencion->cod_intervencion)
                            || (str_contains(strtolower((string) $a->modulo), 'cuidado') &&
                                str_contains(strtolower((string) $a->titulo.' '.(string) $a->descripcion), strtolower((string) $intervencion->nombre)));
                    });

                    // Alerta a nivel de residente
                    $residenteConAlerta = $alertasPorResidente->has($residente->cod_residente);

                    // Determinación de estado clínico
                    if ($ejecucion) {
                        $estado = $ejecucion->estado === 'NO_REALIZADA' || in_array(strtolower((string) $ejecucion->resultado), ['omitida', 'no realizada'])
                            ? 'NO_REALIZADA'
                            : 'REALIZADA';
                    } else {
                        try {
                            $horaDosis = Carbon::createFromFormat('H:i', $horaProg);
                            if ($horaDosis->lessThan(now()->subMinutes(30))) {
                                $estado = 'VENCIDA';
                            } else {
                                $estado = 'PENDIENTE';
                            }
                        } catch (\Throwable $t) {
                            $estado = 'PENDIENTE';
                        }
                    }

                    // Rank de orden obligatorio:
                    // 1. Alertas activas vinculadas
                    // 2. Prioridad más alta (sin alerta vinculada)
                    // 3. Vencidas
                    // 4. Próximas por hora programada
                    // 5. Realizadas / No realizadas (al final)
                    if ($alertaVinculada) {
                        $rank = 1;
                    } elseif ($prioridad === 'ALTA' && ! in_array($estado, ['REALIZADA', 'NO_REALIZADA'])) {
                        $rank = 2;
                    } elseif ($estado === 'VENCIDA') {
                        $rank = 3;
                    } elseif ($estado === 'PENDIENTE') {
                        $rank = 4;
                    } else {
                        $rank = 5;
                    }

                    $itemsAgenda->push([
                        'id' => $intervencion->cod_intervencion.'_'.($prog?->cod_programacion ?? 'P0'),
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

        if (! empty($this->filtroEstado)) {
            if ($this->filtroEstado === 'ALERTA') {
                $itemsAgenda = $itemsAgenda->where('alerta_vinculada', true)->values();
            } else {
                $itemsAgenda = $itemsAgenda->where('estado', $this->filtroEstado)->values();
            }
        }

        if (! empty($this->filtroResidente)) {
            $itemsAgenda = $itemsAgenda->where('cod_residente', $this->filtroResidente)->values();
        }

        // 4. Historial clínico completo (sin "Ver Detalle")
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
            'historial' => $historial,
            'residentes' => $residentes,
            'esSuperAdmin' => $esSuperAdmin,
            'puedeRegistrar' => $turno !== null && $user?->can('ejecuciones_cuidado.crear'),
            'turno' => $turno,
            'recepcion' => $recepcion,
        ])->layout('layouts.enfermeria');
    }
}
