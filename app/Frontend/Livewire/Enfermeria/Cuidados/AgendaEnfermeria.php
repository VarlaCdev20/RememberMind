<?php

namespace App\Frontend\Livewire\Enfermeria\Cuidados;

use App\Models\Alerta;
use App\Models\AsignacionPersonal;
use App\Models\AsignacionResidenteJornada;
use App\Models\EjecucionCuidado;
use App\Models\IntervencionCuidado;
use App\Models\Jornada;
use App\Models\PlanCuidado;
use App\Models\ProgramacionCuidado;
use App\Models\Residente;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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
            'hora_programada' => $horaProgramada ?? ($programacion?->hora_programada ? substr((string)$programacion->hora_programada, 0, 5) : now()->format('H:i')),
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

        if ($this->esNoRealizada || in_array(strtolower($this->resultado), ['no realizada', 'omitida', 'no realizada / omitida'])) {
            if (mb_strlen(trim($this->motivoOmision)) < 5) {
                $this->addError('motivoOmision', 'El motivo de omisión es obligatorio cuando la intervención no se realiza (mínimo 5 caracteres).');
                return;
            }
        }

        $this->procesandoRegistro = true;

        $user = Auth::user();
        $turno = app(TurnoEnfermeriaService::class)->autorizarMutacionEnfermeria(
            $this->residenteId,
            'ejecuciones_cuidado.crear',
            $user,
        );
        $personal = $user?->personal()
            ->where('estado', 'ACTIVO')
            ->first();
        abort_unless($personal, 409, 'El usuario no tiene un perfil de personal activo.');

        $asignacion = AsignacionResidenteJornada::query()
            ->with('jornada')
            ->where('cod_residente', $this->residenteId)
            ->where('cod_personal', $personal->cod_personal)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO', 'ASIGNADO'])
            ->whereHas('jornada', fn ($query) => $query
                ->where('cod_turno', $turno->cod_turno)
                ->whereDate('fecha_jornada', today())
                ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO']))
            ->latest('fecha_hora')
            ->first();
        abort_unless($asignacion?->jornada, 409, 'No existe una jornada activa asignada para registrar el cuidado.');

        $intervencionValida = IntervencionCuidado::query()
            ->where('cod_intervencion', $this->intervencionId)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->whereHas('plan', fn ($query) => $query
                ->where('cod_residente', $this->residenteId)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA']))
            ->exists();
        abort_unless($intervencionValida, 422, 'La intervención no pertenece al plan activo del residente.');

        $codPersonal = $personal->cod_personal;
        $codJornada = $asignacion->cod_jornada;

        $horaProg = $this->datosModal['hora_programada'] ?? now()->format('H:i');
        $fechaHoraProgramada = Carbon::parse(today()->toDateString() . ' ' . $horaProg);

        // Idempotencia: evitar doble ejecución de la misma intervención y programación en el mismo turno
        $existente = EjecucionCuidado::where('cod_intervencion', $this->intervencionId)
            ->where('cod_residente', $this->residenteId)
            ->where('cod_jornada', $codJornada)
            ->where('fecha_hora_programada', $fechaHoraProgramada)
            ->first();

        if ($existente) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Ya registrada',
                'text' => 'Esta intervención ya cuenta con un registro para el horario programado de hoy.',
            ]);
            $this->cerrarModalRegistrar();
            return;
        }

        $estadoEjecucion = ($this->esNoRealizada || in_array(strtolower($this->resultado), ['no realizada', 'omitida', 'no realizada / omitida'])) 
            ? 'NO_REALIZADA' 
            : 'REALIZADA';

        DB::transaction(function () use ($codPersonal, $codJornada, $fechaHoraProgramada, $estadoEjecucion) {
            EjecucionCuidado::create([
                'cod_ejecucion' => 'EJC_' . strtoupper(Str::random(10)),
                'cod_intervencion' => $this->intervencionId,
                'cod_residente' => $this->residenteId,
                'cod_jornada' => $codJornada,
                'cod_personal' => $codPersonal,
                'fecha_hora_programada' => $fechaHoraProgramada,
                'fecha_hora_ejecucion' => now(),
                'resultado' => $this->resultado,
                'motivo_omision' => $this->motivoOmision ?: null,
                'estado' => $estadoEjecucion,
                'observacion' => $this->observacion ?: null,
            ]);
        });

        $this->cerrarModalRegistrar();
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Cuidado registrado',
            'text' => 'La ejecución de la intervención ha sido registrada exitosamente en la bitácora clínica.',
        ]);
    }

    public function recibirTurno(): void
    {
        $turnos = app(TurnoEnfermeriaService::class);
        abort_unless(Auth::user()?->hasRole('ENFERMEROS') && Auth::user()?->can('enfermeria.ver_dashboard'), 403, 'La recepción corresponde al personal operativo de Enfermería.');
        $turno = $turnos->obtenerTurnoActivo(Auth::user(), today()->toDateString());
        abort_unless($turno, 409, 'No tiene un turno activo asignado para recibir.');

        $codPersonal = Auth::user()?->personal?->cod_personal;
        abort_unless($codPersonal, 409, 'El usuario no tiene un perfil de personal activo.');

        $jornada = Jornada::where('cod_turno', $turno->cod_turno)
            ->whereDate('fecha_jornada', today())
            ->first();
        abort_unless($jornada, 409, 'La jornada del turno no está formalizada. Contacte a administración.');

        DB::transaction(function () use ($jornada, $codPersonal) {
            $asignacion = AsignacionPersonal::where('cod_jornada', $jornada->cod_jornada)
                ->where('cod_personal', $codPersonal)
                ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
                ->lockForUpdate()
                ->first();
            abort_unless($asignacion, 409, 'No existe una asignación institucional activa para recibir este turno.');

            $notaRecepcion = trim($this->observacionRecepcion) ?: 'Recepción de turno confirmada';
            $asignacion->update([
                'observacion' => trim(implode(' | ', array_filter([
                    $asignacion->observacion,
                    $notaRecepcion.' por '.$codPersonal.' el '.now()->format('Y-m-d H:i'),
                ]))),
            ]);
        });

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
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION', 'ACTIVA'])
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
            }
        ])
        ->whereIn('cod_residente', $codigosResidentes)
        ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
        ->get();

        $itemsAgenda = collect();

        foreach ($planes as $plan) {
            $residente = $residentes->get($plan->cod_residente);
            if (!$residente) continue;

            $habTexto = 'Sin asignar';
            if ($residente->cama?->habitacion) {
                $habTexto = 'Hab. '.($residente->cama->habitacion->codigo ?? $residente->cama->habitacion->nombre).
                            ' / Cama '.($residente->cama->codigo ?? 'Sin asignar');
            }

            foreach ($plan->intervenciones as $intervencion) {
                $programaciones = $intervencion->programaciones;
                $progsArray = $programaciones->isNotEmpty() ? $programaciones : [null];

                foreach ($progsArray as $prog) {
                    $horaProg = $prog?->hora_programada ? substr((string)$prog->hora_programada, 0, 5) : '08:00';
                    $frecuencia = $prog?->frecuencia ?? 'Diario según turno';
                    $prioridad = strtoupper($intervencion->prioridad ?? 'MEDIA');

                    // Comprobar ejecucion hoy
                    $ejecucion = $ejecucionesHoy->get($intervencion->cod_intervencion);

                    // Alerta vinculada específicamente a este cuidado
                    $alertaVinculada = $alertas->first(function ($a) use ($intervencion) {
                        return ($a->cod_registro === $intervencion->cod_intervencion)
                            || (str_contains(strtolower((string)$a->modulo), 'cuidado') && 
                                str_contains(strtolower((string)$a->titulo . ' ' . (string)$a->descripcion), strtolower((string)$intervencion->nombre)));
                    });

                    // Alerta a nivel de residente
                    $residenteConAlerta = $alertasPorResidente->has($residente->cod_residente);

                    // Determinación de estado clínico
                    if ($ejecucion) {
                        $estado = $ejecucion->estado === 'NO_REALIZADA' || in_array(strtolower((string)$ejecucion->resultado), ['omitida', 'no realizada'])
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
                    } elseif ($prioridad === 'ALTA' && !in_array($estado, ['REALIZADA', 'NO_REALIZADA'])) {
                        $rank = 2;
                    } elseif ($estado === 'VENCIDA') {
                        $rank = 3;
                    } elseif ($estado === 'PENDIENTE') {
                        $rank = 4;
                    } else {
                        $rank = 5;
                    }

                    $itemsAgenda->push([
                        'id' => $intervencion->cod_intervencion . '_' . ($prog?->cod_programacion ?? 'P0'),
                        'cod_intervencion' => $intervencion->cod_intervencion,
                        'cod_programacion' => $prog?->cod_programacion,
                        'cod_residente' => $residente->cod_residente,
                        'nombre_residente' => trim("{$residente->nombres} {$residente->apellido_paterno} {$residente->apellido_materno}"),
                        'iniciales' => strtoupper(substr((string)$residente->nombres, 0, 1) . substr((string)($residente->apellido_paterno ?? $residente->ap_paterno ?? 'R'), 0, 1)),
                        'ubicacion' => $habTexto,
                        'nombre_intervencion' => $intervencion->nombre,
                        'descripcion' => $intervencion->descripcion,
                        'nombre_plan' => $plan->nombre,
                        'prioridad' => $prioridad,
                        'frecuencia' => $frecuencia,
                        'hora_programada' => $horaProg,
                        'estado' => $estado,
                        'alerta_vinculada' => (bool)$alertaVinculada,
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
        if (!empty($this->buscar)) {
            $busq = mb_strtolower(trim($this->buscar));
            $itemsAgenda = $itemsAgenda->filter(function ($i) use ($busq) {
                return str_contains(mb_strtolower($i['nombre_residente']), $busq)
                    || str_contains(mb_strtolower($i['nombre_intervencion']), $busq)
                    || str_contains(mb_strtolower($i['nombre_plan']), $busq);
            })->values();
        }

        if (!empty($this->filtroPrioridad)) {
            $itemsAgenda = $itemsAgenda->where('prioridad', $this->filtroPrioridad)->values();
        }

        if (!empty($this->filtroEstado)) {
            if ($this->filtroEstado === 'ALERTA') {
                $itemsAgenda = $itemsAgenda->where('alerta_vinculada', true)->values();
            } else {
                $itemsAgenda = $itemsAgenda->where('estado', $this->filtroEstado)->values();
            }
        }

        if (!empty($this->filtroResidente)) {
            $itemsAgenda = $itemsAgenda->where('cod_residente', $this->filtroResidente)->values();
        }

        // 4. Historial clínico completo (sin "Ver Detalle")
        $queryHistorial = EjecucionCuidado::with([
            'residente.cama.habitacion',
            'intervencion.plan',
            'personal'
        ])
        ->whereIn('cod_residente', $codigosResidentes);

        if (!empty($this->buscarHistorial)) {
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

        if (!empty($this->filtroHistorialResidente)) {
            $queryHistorial->where('cod_residente', $this->filtroHistorialResidente);
        }

        if (!empty($this->filtroHistorialFecha)) {
            $queryHistorial->whereDate('fecha_hora_ejecucion', $this->filtroHistorialFecha);
        }

        $historial = $queryHistorial->latest('fecha_hora_ejecucion')->take(40)->get();

        $codPersonalActual = $user?->personal?->cod_personal;
        $recepcion = !$esSuperAdmin && $turno && $codPersonalActual ? AsignacionPersonal::where('cod_personal', $codPersonalActual)
            ->whereHas('jornada', fn ($q) => $q->where('cod_turno', $turno->cod_turno)->whereDate('fecha_jornada', today()))
            ->first() : null;

        return view('livewire.cuidados.agenda-enfermeria', [
            'itemsAgenda' => $itemsAgenda,
            'historial' => $historial,
            'residentes' => $residentes,
            'esSuperAdmin' => $esSuperAdmin,
            'turno' => $turno,
            'recepcion' => $recepcion,
        ])->layout('layouts.enfermeria');
    }
}
