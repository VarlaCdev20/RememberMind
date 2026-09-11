<?php

namespace App\Livewire\Cuidados;

use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\MedicacionAdulto;
use App\Models\SeguimientoDiario;
use App\Models\SignosVitalesAdulto;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use App\Services\Clinica\ValidacionSignosVitalesService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class MisPacientes extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filtroEstado = 'TODOS'; // 'TODOS' | 'ESTABLE' | 'VIGILANCIA' | 'REQUIERE_ATENCION'
    public string $filtroTurno = '';
    public string $filtroEnfermero = '';
    public string $filtroRapido = 'TODOS';
    public string $vistaModo = 'tabla'; // 'tabla' (Lista) | 'tarjetas' (Tarjetas)

    protected ?TurnoEnfermeriaService $turnoService = null;

    // Modales rápidos operativos
    public bool $modalSignos = false;
    public ?string $modalCodAm = null;
    public string $signoPA = '', $signoFC = '', $signoFR = '', $signoTemp = '', $signoSat = '', $signoGlucosa = '', $signoObs = '';
    public bool $signoConfirmarAtipico = false;

    public bool $modalSeguimiento = false;
    public string $segEstado = 'ESTABLE', $segAlimentacion = 'COMPLETA', $segMovilidad = 'INDEPENDIENTE', $segSueno = 'NORMAL';
    public bool $segIncidente = false, $segRequiereMedico = false;
    public string $segObs = '';

    public bool $modalMed = false;
    public ?string $medCodMed = null;
    public bool $medAdministrado = true;
    public string $medMotivoOmision = '';
    public $medicacionesPaciente = [];

    public bool $modalAlerta = false;
    public string $alertaTipo = 'INCIDENTE', $alertaNivel = 'ALTO', $alertaMotivo = '';

    protected function getTurnoService(): TurnoEnfermeriaService
    {
        if (!$this->turnoService) {
            $this->turnoService = app(TurnoEnfermeriaService::class);
        }
        return $this->turnoService;
    }

    public function mount(): void
    {
        $service = $this->getTurnoService();
        $user = Auth::user();

        if ($service->esSuperAdmin($user)) {
            $this->filtroEnfermero = '';
            $this->filtroTurno = '';
        } else {
            $this->filtroEnfermero = (string) $user?->cod_usu;
            $turnoActual = $service->obtenerTurnoActivo($user);
            if ($turnoActual) {
                $this->filtroTurno = (string) $turnoActual->cod_turno;
            }
        }
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFiltroRapido(): void { $this->resetPage(); }
    public function updatingFiltroTurno(): void { $this->resetPage(); }
    public function updatingFiltroEnfermero(): void { $this->resetPage(); }
    public function updatingFiltroEstado(): void { $this->resetPage(); }
    public function updatingVistaModo(): void { $this->resetPage(); }

    // ─── ACCIONES RÁPIDAS MODALES ───────────────────────────────────

    public function abrirRegistrarSignos(string $codAm): void
    {
        abort_unless(auth()->user()?->can('signos_vitales.crear'), 403);
        $this->getTurnoService()->autorizarAccionPaciente($codAm);
        $this->modalCodAm = $codAm;
        $this->reset(['signoPA', 'signoFC', 'signoFR', 'signoTemp', 'signoSat', 'signoGlucosa', 'signoObs', 'signoConfirmarAtipico']);
        $this->modalSignos = true;
    }

    public function abrirModalSignos(string $codAm): void
    {
        $this->abrirRegistrarSignos($codAm);
    }

    public function guardarSignos(): void
    {
        abort_unless(auth()->user()?->can('signos_vitales.crear'), 403);
        $this->getTurnoService()->autorizarAccionPaciente($this->modalCodAm);
        $this->validate([
            'signoPA' => 'nullable|string|max:20',
            'signoFC' => 'nullable|integer|min:' . ValidacionSignosVitalesService::FC_MIN . '|max:' . ValidacionSignosVitalesService::FC_MAX,
            'signoFR' => 'nullable|integer|min:' . ValidacionSignosVitalesService::FR_MIN . '|max:' . ValidacionSignosVitalesService::FR_MAX,
            'signoTemp' => 'nullable|numeric|min:' . ValidacionSignosVitalesService::TEMP_MIN . '|max:' . ValidacionSignosVitalesService::TEMP_MAX,
            'signoSat' => 'nullable|integer|min:' . ValidacionSignosVitalesService::SPO2_MIN . '|max:' . ValidacionSignosVitalesService::SPO2_MAX,
            'signoGlucosa' => 'nullable|numeric|min:' . ValidacionSignosVitalesService::GLUCOSA_MIN,
            'signoObs' => 'nullable|string|max:1000',
        ]);

        if (empty($this->signoPA) && empty($this->signoFC) && empty($this->signoFR) && empty($this->signoTemp) && empty($this->signoSat) && empty($this->signoGlucosa)) {
            $this->addError('signoPA', 'Registre al menos un parámetro de signos vitales.');
            return;
        }

        $sis = null; $dia = null;
        if (!empty($this->signoPA) && !preg_match('/^\s*(\d{2,3})\s*\/\s*(\d{2,3})\s*$/', $this->signoPA, $presion)) {
            $this->addError('signoPA', 'La presión arterial debe tener el formato sistólica/diastólica, por ejemplo 120/80.');
            return;
        }
        if (!empty($this->signoPA)) {
            $sis = (int) $presion[1];
            $dia = (int) $presion[2];
            if ($sis < ValidacionSignosVitalesService::PAS_MIN || $sis > ValidacionSignosVitalesService::PAS_MAX ||
                $dia < ValidacionSignosVitalesService::PAD_MIN || $dia > ValidacionSignosVitalesService::PAD_MAX) {
                $this->addError('signoPA', 'La presión arterial está fuera de los rangos biológicos admitidos.');
                return;
            }
            if ($sis <= $dia && ! $this->signoConfirmarAtipico) {
                $this->addError('signoPA', 'La presión sistólica es menor o igual a la diastólica. Repita la medición y confirme expresamente si el valor es correcto.');
                return;
            }
        }

        SignosVitalesAdulto::create([
            'cod_am' => $this->modalCodAm,
            'fecha' => today()->toDateString(),
            'hora' => now()->format('H:i:s'),
            'presion_arterial' => $this->signoPA ?: null,
            'presion_sistolica' => $sis,
            'presion_diastolica' => $dia,
            'frecuencia_cardiaca' => $this->signoFC ?: null,
            'frecuencia_respiratoria' => $this->signoFR ?: null,
            'temperatura' => $this->signoTemp ?: null,
            'saturacion' => $this->signoSat ?: null,
            'glucosa' => $this->signoGlucosa ?: null,
            'observacion' => $this->signoObs ?: null,
            'registrado_por' => Auth::id(),
            'estado' => 'VIGENTE',
            'valor_atipico_confirmado' => $this->signoConfirmarAtipico,
        ]);

        $this->modalSignos = false;
        $this->modalCodAm = null;
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Signos registrados', 'text' => 'Control de signos guardado correctamente.']);
    }

    public function abrirRegistrarSeguimiento(string $codAm): void
    {
        abort_unless(auth()->user()?->can('seguimiento.crear'), 403);
        $this->getTurnoService()->autorizarAccionPaciente($codAm);
        $this->modalCodAm = $codAm;
        $this->segEstado = 'ESTABLE';
        $this->segAlimentacion = 'COMPLETA';
        $this->segMovilidad = 'INDEPENDIENTE';
        $this->segSueno = 'NORMAL';
        $this->segIncidente = false;
        $this->segRequiereMedico = false;
        $this->segObs = '';
        $this->modalSeguimiento = true;
    }

    public function abrirModalSeguimiento(string $codAm): void
    {
        $this->abrirRegistrarSeguimiento($codAm);
    }

    public function guardarSeguimiento(): void
    {
        abort_unless(auth()->user()?->can('seguimiento.crear'), 403);
        $this->getTurnoService()->autorizarAccionPaciente($this->modalCodAm);
        $service = $this->getTurnoService();
        $turno = $service->obtenerTurnoActivo(Auth::user());
        $this->validate([
            'segEstado' => 'required|in:ESTABLE,VIGILANCIA,DELICADO,CRITICO',
            'segAlimentacion' => 'required|in:COMPLETA,PARCIAL,RECHAZADA,AYUNO',
            'segMovilidad' => 'required|in:INDEPENDIENTE,ASISTIDA,SILLA_RUEDAS,ENCAMADO',
            'segSueno' => 'required|in:NORMAL,INTERRUMPIDO,INSOMNIO,SOMNOLENCIA',
            'segObs' => 'nullable|string|max:1000',
        ]);
        if (($this->segIncidente || $this->segRequiereMedico) && mb_strlen(trim($this->segObs)) < 15) {
            $this->addError('segObs', 'Describa la situación clínica y las medidas iniciales con al menos 15 caracteres.');
            return;
        }
        if (!$turno) {
            $this->addError('segObs', 'No existe un turno activo para registrar el seguimiento.');
            return;
        }
        if (SeguimientoDiario::where('cod_am', $this->modalCodAm)->whereDate('fecha', today())
            ->where('cod_turno', $turno->cod_turno)->exists()) {
            $this->addError('segObs', 'Ya existe un seguimiento de este residente para el turno actual.');
            return;
        }

        SeguimientoDiario::create([
            'cod_am' => $this->modalCodAm,
            'cod_turno' => $turno?->cod_turno,
            'fecha' => today()->toDateString(),
            'hora_inicio' => now()->format('H:i:s'),
            'estado_general' => $this->segEstado,
            'alimentacion' => $this->segAlimentacion,
            'movilidad' => $this->segMovilidad,
            'sueno' => $this->segSueno,
            'incidente' => $this->segIncidente,
            'requiere_medico' => $this->segRequiereMedico,
            'observacion' => filled($this->segObs) ? trim($this->segObs) : 'Seguimiento registrado desde Mis Pacientes.',
            'registrado_por' => Auth::id(),
        ]);

        $this->modalSeguimiento = false;
        $this->modalCodAm = null;
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Seguimiento guardado', 'text' => 'El registro diario fue guardado con éxito.']);
    }

    public function abrirAdministrarMed(string $codAm): void
    {
        abort_unless(auth()->user()?->can('administracion_medicacion.registrar'), 403);
        $this->getTurnoService()->autorizarAccionPaciente($codAm);
        $this->modalCodAm = $codAm;
        $this->medicacionesPaciente = MedicacionAdulto::where('cod_am', $codAm)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->get();
        $this->medCodMed = $this->medicacionesPaciente->first()?->cod_med_adulto;
        $this->medAdministrado = true;
        $this->medMotivoOmision = '';
        $this->modalMed = true;
    }

    public function abrirModalMed(string $codAm): void
    {
        $this->abrirAdministrarMed($codAm);
    }

    public function guardarMed(): void
    {
        abort_unless(auth()->user()?->can('administracion_medicacion.registrar'), 403);
        $this->getTurnoService()->autorizarAccionPaciente($this->modalCodAm);
        $this->validate([
            'medCodMed' => 'required|exists:medicacion_adulto,cod_med_adulto',
            'medAdministrado' => 'boolean',
            'medMotivoOmision' => $this->medAdministrado ? 'nullable|string|max:500' : 'required|string|min:5|max:500',
        ], [
            'medCodMed.required' => 'Seleccione un medicamento activo.',
            'medMotivoOmision.required' => 'Indique el motivo por el cual se omitió la dosis.',
            'medMotivoOmision.min' => 'El motivo de omisión debe tener al menos 5 caracteres.',
        ]);

        $med = MedicacionAdulto::where('cod_am', $this->modalCodAm)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO', 'VIGENTE'])
            ->findOrFail($this->medCodMed);
        $horaProgramada = $med->hora_programada
            ? Carbon::parse($med->hora_programada)->format('H:i')
            : now()->format('H:i');

        $guardado = DB::transaction(function () use ($med, $horaProgramada): bool {
            $duplicado = AdministracionMedicacion::where('cod_med_adulto', $med->cod_med_adulto)
                ->whereDate('fecha', today())
                ->where('hora_programada', 'like', $horaProgramada . '%')
                ->lockForUpdate()
                ->exists();
            if ($duplicado) {
                return false;
            }

            AdministracionMedicacion::create([
                'cod_med_adulto' => $med->cod_med_adulto,
                'cod_am' => $this->modalCodAm,
                'fecha' => today()->toDateString(),
                'hora_programada' => $horaProgramada,
                'hora_real' => $this->medAdministrado ? now()->format('H:i') : null,
                'administrado' => $this->medAdministrado,
                'motivo_omision' => $this->medAdministrado ? null : trim($this->medMotivoOmision),
                'registrado_por' => Auth::id(),
                'observacion' => 'Registrado desde Mis Pacientes.',
            ]);
            return true;
        });
        if (!$guardado) {
            $this->addError('medCodMed', 'Esta dosis ya fue registrada previamente hoy.');
            return;
        }

        $this->modalMed = false;
        $this->modalCodAm = null;
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Medicación registrada', 'text' => 'Toma registrada en el expediente.']);
    }

    public function abrirReportarAlerta(string $codAm): void
    {
        abort_unless(auth()->user()?->can('alertas.crear'), 403);
        $this->getTurnoService()->autorizarAccionPaciente($codAm);
        $this->modalCodAm = $codAm;
        $this->alertaTipo = 'INCIDENTE';
        $this->alertaNivel = 'ALTO';
        $this->alertaMotivo = '';
        $this->modalAlerta = true;
    }

    public function abrirModalAlerta(string $codAm): void
    {
        $this->abrirReportarAlerta($codAm);
    }

    public function guardarAlerta(): void
    {
        abort_unless(auth()->user()?->can('alertas.crear'), 403);
        $this->getTurnoService()->autorizarAccionPaciente($this->modalCodAm);
        $this->validate([
            'alertaTipo' => 'required|in:INCIDENTE,CAIDA,CONDUCTA,DESORIENTACION,DOLOR,SIGNOS,SOLICITUD_MEDICA,CLINICA,MEDICACION,CUIDADO',
            'alertaNivel' => 'required|in:BAJO,MEDIO,ALTO,CRITICO',
            'alertaMotivo' => 'required|string|min:8|max:1000',
        ], [
            'alertaMotivo.required' => 'Describa el motivo o incidente que originó la alerta.',
        ]);

        $service = $this->getTurnoService();
        $turno = $service->obtenerTurnoActivo(Auth::user());

        AlertaAdulto::create([
            'cod_am' => $this->modalCodAm,
            'cod_turno' => $turno?->cod_turno,
            'origen' => 'INCIDENTE',
            'tipo_alerta' => mb_strtoupper(trim($this->alertaTipo)),
            'nivel' => $this->alertaNivel,
            'motivo' => $this->alertaMotivo,
            'responsable_id' => Auth::id(),
            'estado' => 'ABIERTA',
        ]);

        $this->modalAlerta = false;
        $this->modalCodAm = null;
        $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Alerta creada', 'text' => 'La alerta fue enviada al monitor del turno y campana.']);
    }

    // ─── RENDER ─────────────────────────────────────────────────────

    public function render()
    {
        $fechaHoy = Carbon::now()->toDateString();
        $service = $this->getTurnoService();
        $user = Auth::user();
        $esSuperAdmin = $service->esSuperAdmin($user);

        // Asegurar que enfermero estándar no pueda burlar el filtro
        $enfermeroEfectivo = $esSuperAdmin ? $this->filtroEnfermero : (string) $user?->cod_usu;
        $turnoEfectivo = $this->filtroTurno ?: null;

        $pacientesQuery = $service->obtenerPacientesAsignadosQuery(
            $user,
            $turnoEfectivo,
            $enfermeroEfectivo ?: null
        )->with([
            'habitacion',
            'cama',
            'asignacionesTurno' => function ($q) {
                $q->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                  ->with(['turno', 'enfermero']);
            },
            'planCuidadoActivo',
        ])->withCount([
            'alertas as alertas_activas_count' => function ($q) {
                $q->whereIn('estado', ['ABIERTA', 'EN_ATENCION']);
            },
            'alertas as alertas_criticas_count' => function ($q) {
                $q->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->whereIn('nivel', ['CRITICO', 'ALTO']);
            },
            'tareasActuales as tareas_pendientes_count' => function ($q) use ($fechaHoy) {
                $q->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])
                  ->whereDate('fecha_programada', $fechaHoy);
            },
            'tareasActuales as tareas_vencidas_count' => function ($q) use ($fechaHoy) {
                $q->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])
                  ->whereDate('fecha_programada', '<', $fechaHoy);
            },
            'administracionesMedicacion as medicacion_hoy_count' => function ($q) use ($fechaHoy) {
                $q->whereDate('fecha', $fechaHoy)->where('administrado', true);
            },
            'seguimientosDiarios as seguimientos_hoy_count' => function ($q) use ($fechaHoy) {
                $q->whereDate('fecha', $fechaHoy);
            },
            'signosVitales as signos_hoy_count' => function ($q) use ($fechaHoy) {
                $q->whereDate('fecha', $fechaHoy);
            },
        ]);

        if (trim($this->search) !== '') {
            $b = trim($this->search);
            $pacientesQuery->where(function ($q) use ($b) {
                $q->whereLike('nombres', "%{$b}%")
                  ->orWhereLike('ap_paterno', "%{$b}%")
                  ->orWhereLike('ap_materno', "%{$b}%")
                  ->orWhereLike('ci', "%{$b}%")
                  ->orWhereLike('cod_am', "%{$b}%")
                  ->orWhereHas('habitacion', function ($hq) use ($b) {
                      $hq->whereLike('numero', "%{$b}%")
                         ->orWhereLike('codigo', "%{$b}%");
                  })
                  ->orWhereHas('cama', function ($cq) use ($b) {
                      $cq->whereLike('numero', "%{$b}%")
                         ->orWhereLike('codigo', "%{$b}%");
                  });
            });
        }

        // Filtro por estado clínico: TODOS / ESTABLE / VIGILANCIA / REQUIERE_ATENCION
        if ($this->filtroEstado === 'REQUIERE_ATENCION') {
            $pacientesQuery->where(function ($q) use ($fechaHoy) {
                $q->whereHas('alertas', function ($aq) {
                    $aq->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
                       ->whereIn('nivel', ['CRITICO', 'ALTO']);
                })->orWhereHas('seguimientosDiarios', function ($sq) use ($fechaHoy) {
                    $sq->whereDate('fecha', $fechaHoy)
                       ->where(function ($sq2) {
                           $sq2->where('incidente', true)->orWhere('requiere_medico', true);
                       });
                });
            });
        } elseif ($this->filtroEstado === 'VIGILANCIA') {
            $pacientesQuery->whereDoesntHave('alertas', function ($aq) {
                $aq->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
                   ->whereIn('nivel', ['CRITICO', 'ALTO']);
            })->whereDoesntHave('seguimientosDiarios', function ($sq) use ($fechaHoy) {
                $sq->whereDate('fecha', $fechaHoy)
                   ->where(function ($sq2) {
                       $sq2->where('incidente', true)->orWhere('requiere_medico', true);
                   });
            })->where(function ($q) use ($fechaHoy) {
                $q->whereHas('alertas', fn ($aq) => $aq->whereIn('estado', ['ABIERTA', 'EN_ATENCION']))
                  ->orWhereHas('tareasActuales', fn ($tq) => $tq->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])->whereDate('fecha_programada', '<', $fechaHoy))
                  ->orWhereDoesntHave('signosVitales', fn ($vq) => $vq->whereDate('fecha', $fechaHoy))
                  ->orWhereDoesntHave('seguimientosDiarios', fn ($sq) => $sq->whereDate('fecha', $fechaHoy));
            });
        } elseif ($this->filtroEstado === 'ESTABLE') {
            $pacientesQuery->whereDoesntHave('alertas', fn ($aq) => $aq->whereIn('estado', ['ABIERTA', 'EN_ATENCION']))
                ->whereDoesntHave('seguimientosDiarios', function ($sq) use ($fechaHoy) {
                    $sq->whereDate('fecha', $fechaHoy)
                       ->where(function ($sq2) {
                           $sq2->where('incidente', true)->orWhere('requiere_medico', true);
                       });
                })
                ->whereDoesntHave('tareasActuales', fn ($tq) => $tq->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])->whereDate('fecha_programada', '<', $fechaHoy))
                ->whereHas('signosVitales', fn ($vq) => $vq->whereDate('fecha', $fechaHoy))
                ->whereHas('seguimientosDiarios', fn ($sq) => $sq->whereDate('fecha', $fechaHoy));
        } elseif ($this->filtroEstado !== 'TODOS') {
            $pacientesQuery->where('estado', $this->filtroEstado);
        }

        // Filtros rápidos adicionales
        if ($this->filtroRapido === 'CON_TAREAS') {
            $pacientesQuery->whereHas('tareasActuales', fn ($q) => $q->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])->whereDate('fecha_programada', '<=', $fechaHoy));
        } elseif ($this->filtroRapido === 'CON_ALERTAS') {
            $pacientesQuery->whereHas('alertas', fn ($q) => $q->whereIn('estado', ['ABIERTA', 'EN_ATENCION']));
        } elseif ($this->filtroRapido === 'CON_SIGNOS_PENDIENTES') {
            $pacientesQuery->whereDoesntHave('signosVitales', fn ($vq) => $vq->whereDate('fecha', $fechaHoy));
        } elseif ($this->filtroRapido === 'SIN_SEGUIMIENTO') {
            $pacientesQuery->whereDoesntHave('seguimientosDiarios', fn ($sq) => $sq->whereDate('fecha', $fechaHoy));
        }

        // Obtener pacientes paginados
        $pacientes = $pacientesQuery->orderBy('nombres')->paginate(12);

        // Pre-cargar datos detallados de los pacientes paginados (signos recientes, medicación pendiente)
        $codAms = $pacientes->pluck('cod_am')->filter()->values();

        $ultimosSignos = collect();
        $medsActivas = collect();
        $adminMedsHoy = collect();
        $seguimientosHoy = collect();

        if ($codAms->isNotEmpty()) {
            $ultimosSignos = SignosVitalesAdulto::whereIn('cod_am', $codAms)
                ->orderByDesc('fecha')
                ->orderByDesc('hora')
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('cod_am')
                ->map(fn ($g) => $g->first());

            $medsActivas = MedicacionAdulto::whereIn('cod_am', $codAms)
                ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
                ->get()
                ->groupBy('cod_am');

            $adminMedsHoy = AdministracionMedicacion::whereIn('cod_am', $codAms)
                ->whereDate('fecha', $fechaHoy)
                ->where('administrado', true)
                ->get()
                ->groupBy('cod_am');

            $seguimientosHoy = SeguimientoDiario::whereIn('cod_am', $codAms)
                ->whereDate('fecha', $fechaHoy)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('cod_am')
                ->map(fn ($g) => $g->first());
        }

        // Anexar estado clínico y métricas de soporte
        foreach ($pacientes as $p) {
            $cod = $p->cod_am;
            $p->ultimo_signo = $ultimosSignos->get($cod);
            $seg = $seguimientosHoy->get($cod);
            $p->ultimo_seguimiento = $seg;

            $meds = $medsActivas->get($cod, collect());
            $admins = $adminMedsHoy->get($cod, collect());
            $adminIds = $admins->pluck('cod_med_adulto')->unique()->toArray();
            $p->meds_activas_total = $meds->count();
            $p->meds_pendientes_count = $meds->whereNotIn('cod_med_adulto', $adminIds)->count();

            // Código de estado clínico: verde = estable, amarillo = vigilancia, rojo = requiere atención
            if ($p->alertas_criticas_count > 0 || ($seg && ($seg->incidente || $seg->requiere_medico))) {
                $p->codigo_estado = 'REQUIERE_ATENCION';
                $p->estado_color = 'red';
                $p->estado_label = 'Requiere atención';
            } elseif (
                $p->alertas_activas_count > 0 ||
                $p->tareas_vencidas_count > 0 ||
                $p->meds_pendientes_count > 0 ||
                $p->signos_hoy_count === 0 ||
                $p->seguimientos_hoy_count === 0 ||
                in_array(strtoupper($p->planCuidadoActivo?->nivel_cuidado ?? ''), ['SEVERO', 'TOTAL', 'ALTO', 'DEPENDIENTE'])
            ) {
                $p->codigo_estado = 'VIGILANCIA';
                $p->estado_color = 'amber';
                $p->estado_label = 'Vigilancia';
            } else {
                $p->codigo_estado = 'ESTABLE';
                $p->estado_color = 'emerald';
                $p->estado_label = 'Estable';
            }
        }

        // Estadísticas globales de turno para el selector de filtros
        $baseParaStats = (clone $service->obtenerPacientesAsignadosQuery(
            $user,
            $turnoEfectivo,
            $enfermeroEfectivo ?: null
        ))->withCount([
            'alertas as alertas_activas_count' => fn ($q) => $q->whereIn('estado', ['ABIERTA', 'EN_ATENCION']),
            'alertas as alertas_criticas_count' => fn ($q) => $q->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->whereIn('nivel', ['CRITICO', 'ALTO']),
            'tareasActuales as tareas_pendientes_count' => function ($q) use ($fechaHoy) {
                $q->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])->whereDate('fecha_programada', $fechaHoy);
            },
            'tareasActuales as tareas_vencidas_count' => function ($q) use ($fechaHoy) {
                $q->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])->whereDate('fecha_programada', '<', $fechaHoy);
            },
            'seguimientosDiarios as seguimientos_hoy_count' => function ($q) use ($fechaHoy) {
                $q->whereDate('fecha', $fechaHoy);
            },
            'signosVitales as signos_hoy_count' => function ($q) use ($fechaHoy) {
                $q->whereDate('fecha', $fechaHoy);
            },
        ])->get();

        $stats = [
            'total' => $baseParaStats->count(),
            'requiere_atencion' => 0,
            'vigilancia' => 0,
            'estable' => 0,
        ];

        foreach ($baseParaStats as $bp) {
            if ($bp->alertas_criticas_count > 0) {
                $stats['requiere_atencion']++;
            } elseif (
                $bp->alertas_activas_count > 0 ||
                $bp->tareas_vencidas_count > 0 ||
                $bp->signos_hoy_count === 0 ||
                $bp->seguimientos_hoy_count === 0
            ) {
                $stats['vigilancia']++;
            } else {
                $stats['estable']++;
            }
        }

        return view('livewire.cuidados.mis-pacientes', [
            'pacientes' => $pacientes,
            'stats' => $stats,
            'turnos' => TurnoEnfermeria::activos()->get(),
            'enfermeros' => User::role('ENFERMEROS')->where('estado', 'ACTIVO')->orderBy('ap_paterno')->get(['cod_usu', 'nombres', 'ap_paterno']),
            'esSuperAdmin' => $esSuperAdmin,
        ])->layout('layouts.sistema');
    }
}
