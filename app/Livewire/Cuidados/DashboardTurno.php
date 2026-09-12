<?php

namespace App\Livewire\Cuidados;

use App\Models\AccionAlerta;
use App\Models\AdministracionMedicacion;
use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\MedicacionAdulto;
use App\Models\DispositivoResidente;
use App\Models\IncidenteResidente;
use App\Models\LesionResidente;
use App\Models\PaseTurno;
use App\Models\Preadmision;
use App\Models\RegistroCuidado;
use App\Models\SeguimientoDiario;
use App\Models\SignosVitalesAdulto;
use App\Models\TareaPlanCuidado;
use App\Models\TurnoEnfermeria;
use App\Models\ValoracionEnfermeriaAdmision;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use App\Services\Clinica\SignosVitalesService;
use App\Services\Alertas\AlertasService;
use App\Services\Medicacion\RegistrarAdministracionMedicacionService;
use App\Services\Medicacion\AgendaMedicacionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardTurno extends Component
{
    protected $listeners = [
        'refreshDashboard' => '$refresh',
        'valoracionCompletada' => '$refresh',
        'signos-actualizados' => '$refresh',
        'alerta-atendida' => '$refresh',
        'alerta-cerrada' => '$refresh',
    ];

    public $turnoActual;
    public $turnosActivos = [];
    public $pacientesAsignadosIds = [];
    public $filtroEnfermeroId;
    public $filtroFecha;

    // Modales de acciones operativas rápidas
    public bool $modalOmitirTarea = false;
    public ?string $tareaOmitirId = null;
    public string $motivoOmisionTarea = '';

    public bool $modalOmitirMed = false;
    public ?string $medOmitirId = null;
    public ?string $medOmitirCodAm = null;
    public ?string $medOmitirHora = null;
    public string $motivoOmisionMed = '';

    public bool $modalAtenderAlerta = false;
    public ?string $alertaAccionId = null;
    public string $accionTomadaAlerta = '';

    public bool $modalCerrarAlerta = false;
    public string $observacionCierreAlerta = '';

    // Modal rápido de signos vitales
    public bool $modalSignos = false;
    public ?string $signoCodAm = null;
    public string $signoPresion = '';
    public string $signoFC = '';
    public string $signoFR = '';
    public string $signoTemp = '';
    public string $signoSat = '';
    public string $signoGlucosa = '';
    public string $signoDolor = '';
    public string $signoObservacion = '';

    // Modal rápido de seguimiento
    public bool $modalSeguimiento = false;
    public ?string $segCodAm = null;
    public string $segEstadoGeneral = 'ESTABLE';
    public string $segAlimentacion = 'COMPLETA';
    public string $segMovilidad = 'INDEPENDIENTE';
    public string $segSueno = 'NORMAL';
    public bool $segIncidente = false;
    public bool $segRequiereMedico = false;
    public string $segObservacion = '';

    public function mount()
    {
        $this->filtroEnfermeroId = Auth::user()?->cod_usu ?? '';
        $this->filtroFecha = Carbon::now()->toDateString();
        $this->loadTurnoActual();
    }

    public function loadTurnoActual()
    {
        $service = app(TurnoEnfermeriaService::class);
        $this->turnosActivos = TurnoEnfermeria::activos()->orderBy('orden')->get();
        $this->turnoActual = $service->obtenerTurnoActivo(Auth::user(), $this->filtroFecha);

        $this->pacientesAsignadosIds = $service->esSuperAdmin(Auth::user())
            ? $service->obtenerPacientesAsignadosIds(Auth::user())
            : $service->obtenerPacientesAsignadosIds(Auth::user(), $this->turnoActual?->cod_turno);
    }

    // ─── ACCIONES DE TAREAS ──────────────────────────────────────────

    public function completarTarea(string $codTarea)
    {
        abort_unless(auth()->user()?->can('tareas.registrar_resultado'), 403);
        $tarea = TareaPlanCuidado::findOrFail($codTarea);
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($tarea->cod_am, 'tareas.registrar_resultado', Auth::user());

        DB::transaction(function () use ($tarea) {
            $bloqueada = TareaPlanCuidado::lockForUpdate()->findOrFail($tarea->cod_tarea);
            abort_unless($bloqueada->puedeCompletarse(), 409, 'La tarea ya no está pendiente de ejecución.');
            $bloqueada->update([
                'estado' => 'REALIZADA',
                'resultado' => 'Completada y confirmada desde el panel del turno.',
                'fecha_realizada' => now(),
                'registrado_por' => Auth::id(),
            ]);
        });

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Tarea completada',
            'text' => "La tarea '{$tarea->titulo}' fue marcada como realizada.",
        ]);
    }

    public function abrirOmitirTarea(string $codTarea)
    {
        abort_unless(auth()->user()?->can('tareas.omitir'), 403);
        $tarea = TareaPlanCuidado::findOrFail($codTarea);
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($tarea->cod_am, 'tareas.omitir', Auth::user());
        abort_unless($tarea->puedeCompletarse(), 409, 'La tarea ya no está pendiente de ejecución.');
        $this->tareaOmitirId = $codTarea;
        $this->motivoOmisionTarea = '';
        $this->modalOmitirTarea = true;
    }

    public function confirmarOmisionTarea()
    {
        abort_unless(auth()->user()?->can('tareas.omitir'), 403);
        $this->validate([
            'motivoOmisionTarea' => 'required|string|min:5|max:500',
        ], [
            'motivoOmisionTarea.required' => 'Debe registrar el motivo de la omisión.',
        ]);

        $tarea = TareaPlanCuidado::findOrFail($this->tareaOmitirId);
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($tarea->cod_am, 'tareas.omitir', Auth::user());
        abort_unless($tarea->puedeCompletarse(), 409, 'La tarea ya no está pendiente de ejecución.');
        $tarea->update([
            'estado' => 'OMITIDA',
            'motivo_omision' => trim($this->motivoOmisionTarea),
            'registrado_por' => Auth::id(),
        ]);

        $this->modalOmitirTarea = false;
        $this->tareaOmitirId = null;

        $this->dispatch('swal', [
            'icon' => 'warning',
            'title' => 'Tarea omitida',
            'text' => "Se registró la omisión de la tarea '{$tarea->titulo}'.",
        ]);
    }

    // ─── ACCIONES DE MEDICACIÓN ──────────────────────────────────────

    public function administrarMed(string $codMedAdulto, string $codAm, ?string $horaProgramada = null)
    {
        abort_unless(auth()->user()?->can('administracion_medicacion.registrar'), 403);
        if (! $horaProgramada) {
            $ocurrencia = app(AgendaMedicacionService::class)->paraAdulto($codAm)
                ->first(fn (array $item) => $item['medicacion']->cod_med_adulto === $codMedAdulto);
            $horaProgramada = $ocurrencia['hora'] ?? '';
        }
        app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
            Auth::user(), $codAm, $codMedAdulto, $horaProgramada, true,
            null, 'Administrada desde la agenda del turno.'
        );

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Medicación administrada',
            'text' => 'Se registró la administración de la dosis correctamente.',
        ]);
    }

    public function abrirOmitirMed(string $codMedAdulto, string $codAm, ?string $horaProgramada = null)
    {
        abort_unless(auth()->user()?->can('administracion_medicacion.registrar'), 403);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($codAm, Auth::user());
        abort_unless(MedicacionAdulto::where('cod_med_adulto', $codMedAdulto)->where('cod_am', $codAm)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])->exists(), 404);
        $this->medOmitirId = $codMedAdulto;
        $this->medOmitirCodAm = $codAm;
        $ocurrencia = app(AgendaMedicacionService::class)->paraAdulto($codAm)
            ->first(fn (array $item) => $item['medicacion']->cod_med_adulto === $codMedAdulto && $item['registro'] === null);
        $this->medOmitirHora = $horaProgramada ?: ($ocurrencia['hora'] ?? null);
        $this->motivoOmisionMed = '';
        $this->modalOmitirMed = true;
    }

    public function confirmarOmisionMed()
    {
        abort_unless(auth()->user()?->can('administracion_medicacion.registrar'), 403);
        $this->validate([
            'motivoOmisionMed' => 'required|string|min:5|max:500',
        ], [
            'motivoOmisionMed.required' => 'Debe indicar el motivo por el cual no se administró el medicamento.',
        ]);

        app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
            Auth::user(), $this->medOmitirCodAm, $this->medOmitirId,
            (string) $this->medOmitirHora, false, $this->motivoOmisionMed
        );

        $this->modalOmitirMed = false;
        $this->medOmitirId = null;
        $this->medOmitirCodAm = null;
        $this->medOmitirHora = null;

        $this->dispatch('swal', [
            'icon' => 'warning',
            'title' => 'Omisión registrada',
            'text' => 'Se registró la omisión de la medicación y se generó la alerta correspondiente.',
        ]);
    }

    // ─── ACCIONES DE ALERTAS ─────────────────────────────────────────

    public function abrirAtenderAlerta(string $codAlerta)
    {
        abort_unless(auth()->user()?->can('alertas.atender'), 403);
        $alerta = AlertaAdulto::findOrFail($codAlerta);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($alerta->cod_am, Auth::user());
        abort_unless($alerta->estado === 'ABIERTA', 409, 'La alerta ya fue atendida o cerrada.');
        $this->alertaAccionId = $codAlerta;
        $this->accionTomadaAlerta = '';
        $this->modalAtenderAlerta = true;
    }

    public function confirmarAtencionAlerta()
    {
        app(AlertasService::class)->registrarIntervencion(AlertaAdulto::findOrFail($this->alertaAccionId), $this->accionTomadaAlerta, Auth::user());

        $this->modalAtenderAlerta = false;
        $this->alertaAccionId = null;

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Alerta en atención',
            'text' => 'La alerta pasó a estado EN_ATENCIÓN con su respectiva acción registrada.',
        ]);
    }

    public function abrirCerrarAlerta(string $codAlerta)
    {
        abort_unless(auth()->user()?->can('alertas.cerrar'), 403);
        $alerta = AlertaAdulto::findOrFail($codAlerta);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($alerta->cod_am, Auth::user());
        abort_unless($alerta->puedeCerrarse(), 409, 'La alerta ya está cerrada.');
        $this->alertaAccionId = $codAlerta;
        $this->observacionCierreAlerta = '';
        $this->modalCerrarAlerta = true;
    }

    public function confirmarCierreAlerta()
    {
        app(AlertasService::class)->cerrar(AlertaAdulto::findOrFail($this->alertaAccionId), $this->observacionCierreAlerta, Auth::user());

        $this->modalCerrarAlerta = false;
        $this->alertaAccionId = null;

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Alerta cerrada',
            'text' => 'La alerta ha sido cerrada conservando el historial clínico.',
        ]);
    }

    // ─── ACCIÓN RÁPIDA DE SIGNOS VITALES ────────────────────────────

    public function abrirRegistrarSignos(string $codAm)
    {
        abort_unless(auth()->user()?->can('signos_vitales.crear'), 403);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($codAm, Auth::user());
        $this->signoCodAm = $codAm;
        $this->signoPresion = '';
        $this->signoFC = '';
        $this->signoFR = '';
        $this->signoTemp = '';
        $this->signoSat = '';
        $this->signoGlucosa = '';
        $this->signoDolor = '';
        $this->signoObservacion = '';
        $this->modalSignos = true;
    }

    public function guardarSignos()
    {
        app(SignosVitalesService::class)->registrar($this->signoCodAm, [
            'presion_arterial' => $this->signoPresion,
            'frecuencia_cardiaca' => $this->signoFC,
            'frecuencia_respiratoria' => $this->signoFR,
            'temperatura' => $this->signoTemp,
            'saturacion' => $this->signoSat,
            'glucosa' => $this->signoGlucosa,
            'dolor' => $this->signoDolor,
            'observacion' => $this->signoObservacion,
        ], Auth::user());

        $this->modalSignos = false;
        $this->signoCodAm = null;

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Signos vitales registrados',
            'text' => 'Control de signos guardado y analizado por el monitor clínico.',
        ]);
    }

    // ─── ACCIÓN RÁPIDA DE SEGUIMIENTO DIARIO ────────────────────────

    public function abrirRegistrarSeguimiento(string $codAm)
    {
        abort_unless(auth()->user()?->can('seguimiento.crear'), 403);
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($codAm, Auth::user());
        $this->segCodAm = $codAm;
        $this->segEstadoGeneral = 'ESTABLE';
        $this->segAlimentacion = 'COMPLETA';
        $this->segMovilidad = 'INDEPENDIENTE';
        $this->segSueno = 'NORMAL';
        $this->segIncidente = false;
        $this->segRequiereMedico = false;
        $this->segObservacion = '';
        $this->modalSeguimiento = true;
    }

    public function guardarSeguimiento()
    {
        abort_unless(auth()->user()?->can('seguimiento.crear'), 403);
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($this->segCodAm, 'seguimiento.crear', Auth::user());
        $this->validate([
            'segEstadoGeneral' => 'required|in:ESTABLE,VIGILANCIA,DELICADO,CRITICO',
            'segAlimentacion' => 'required|in:COMPLETA,PARCIAL,RECHAZADA,AYUNO',
            'segMovilidad' => 'required|in:INDEPENDIENTE,ASISTIDA,SILLA_RUEDAS,ENCAMADO',
            'segSueno' => 'required|in:NORMAL,INTERRUMPIDO,INSOMNIO,SOMNOLENCIA',
            'segObservacion' => 'nullable|string|max:1000',
        ]);

        if (($this->segIncidente || $this->segRequiereMedico) && mb_strlen(trim($this->segObservacion)) < 15) {
            $this->addError('segObservacion', 'Describa la situación clínica y las medidas iniciales con al menos 15 caracteres.');
            return;
        }
        if (!$this->turnoActual) {
            $this->addError('segObservacion', 'No existe un turno activo para registrar el seguimiento.');
            return;
        }
        if (SeguimientoDiario::where('cod_am', $this->segCodAm)->whereDate('fecha', $this->filtroFecha)
            ->where('cod_turno', $this->turnoActual->cod_turno)->exists()) {
            $this->addError('segObservacion', 'Ya existe un seguimiento de este residente para el turno y la fecha seleccionados.');
            return;
        }

        SeguimientoDiario::create([
            'cod_am' => $this->segCodAm,
            'cod_turno' => $this->turnoActual?->cod_turno,
            'registrado_por' => Auth::id(),
            'fecha' => $this->filtroFecha,
            'hora_inicio' => now()->format('H:i:s'),
            'estado_general' => $this->segEstadoGeneral,
            'alimentacion' => $this->segAlimentacion,
            'movilidad' => $this->segMovilidad,
            'sueno' => $this->segSueno,
            'incidente' => $this->segIncidente,
            'requiere_medico' => $this->segRequiereMedico,
            'observacion' => $this->segObservacion ?: 'Seguimiento registrado desde cola de turno.',
        ]);

        $this->modalSeguimiento = false;
        $this->segCodAm = null;

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Seguimiento registrado',
            'text' => 'El seguimiento de este turno ha sido registrado correctamente.',
        ]);
    }

    public function iniciarValoracion(string $codPre)
    {
        return redirect()->route('admin.admision.valoracion-enfermeria');
    }

    public function render()
    {
        $service = app(TurnoEnfermeriaService::class);
        $esSuperAdmin = $service->esSuperAdmin(Auth::user());
        $turnoAlcance = $esSuperAdmin ? null : $this->turnoActual?->cod_turno;

        // 1. Alcance: global para supervisión; turno asignado para Enfermería.
        $pacientesQuery = $service->obtenerPacientesAsignadosQuery(
            Auth::user(),
            $turnoAlcance
        )->with(['habitacion', 'cama', 'planCuidadoActivo']);

        $pacientesAsignados = $pacientesQuery->get();
        $codAms = $pacientesAsignados->pluck('cod_am')->toArray();

        // 2. Alertas activas de los residentes asignados
        $alertas = AlertaAdulto::with(['adultoMayor.habitacion', 'responsable'])
            ->whereIn('cod_am', $codAms)
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
            ->orderByRaw("CASE nivel WHEN 'CRITICO' THEN 1 WHEN 'ALTO' THEN 2 WHEN 'MEDIO' THEN 3 ELSE 4 END")
            ->orderByDesc('created_at')
            ->get();

        // 3. Medicación próxima o pendiente
        $medicacionesActivas = MedicacionAdulto::with('adultoMayor')
            ->whereIn('cod_am', $codAms)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->get();

        $administracionesHoy = AdministracionMedicacion::whereIn('cod_am', $codAms)
            ->whereDate('fecha', $this->filtroFecha)
            ->get();

        // Determinar qué dosis están pendientes hoy
        $medicacionCola = $medicacionesActivas->map(function ($med) use ($administracionesHoy) {
            $tomaHoy = $administracionesHoy->firstWhere('cod_med_adulto', $med->cod_med_adulto);
            return [
                'medicacion' => $med,
                'paciente' => $med->adultoMayor,
                'administrado' => $tomaHoy ? (bool) $tomaHoy->administrado : null,
                'hora_programada' => $med->hora_programada ? Carbon::parse($med->hora_programada)->format('H:i') : '08:00',
                'hora_real' => $tomaHoy?->hora_real ? Carbon::parse($tomaHoy->hora_real)->format('H:i') : null,
                'motivo_omision' => $tomaHoy?->motivo_omision,
                'toma_id' => $tomaHoy?->cod_admin_med,
            ];
        })->sortBy('hora_programada');

        $medicacionPendiente = $medicacionCola->filter(fn ($item) => $item['administrado'] === null);

        // 4. Tareas pendientes y vencidas
        $tareasQuery = TareaPlanCuidado::with(['adultoMayor.habitacion', 'plan'])
            ->whereIn('cod_am', $codAms)
            ->whereIn('estado', ['PENDIENTE', 'EN_PROCESO']);

        if ($turnoAlcance) {
            $tareasQuery->where(function ($q) use ($turnoAlcance) {
                $q->where('cod_turno', $turnoAlcance)
                  ->orWhereNull('cod_turno');
            });
        }

        $tareasPendientes = $tareasQuery
            ->whereDate('fecha_programada', '<=', $this->filtroFecha)
            ->orderByRaw("CASE WHEN fecha_programada < '{$this->filtroFecha}' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE prioridad WHEN 'ALTA' THEN 1 WHEN 'URGENTE' THEN 1 WHEN 'NORMAL' THEN 2 ELSE 3 END")
            ->orderBy('hora_programada')
            ->get();

        // 5. Controles / Signos pendientes
        $signosHoy = SignosVitalesAdulto::whereIn('cod_am', $codAms)
            ->whereDate('fecha', $this->filtroFecha)
            ->get()
            ->groupBy('cod_am');

        $controlesSignos = $pacientesAsignados->map(function ($paciente) use ($signosHoy) {
            $registros = $signosHoy->get($paciente->cod_am);
            $ultimo = $registros ? $registros->sortByDesc('hora')->first() : null;
            return [
                'paciente' => $paciente,
                'tiene_control_hoy' => !empty($registros),
                'ultimo' => $ultimo,
            ];
        });

        // 6. Seguimientos faltantes en el turno
        $seguimientosHoy = SeguimientoDiario::whereIn('cod_am', $codAms)
            ->whereDate('fecha', $this->filtroFecha)
            ->when($turnoAlcance, fn ($q) => $q->where('cod_turno', $turnoAlcance))
            ->pluck('cod_am')
            ->toArray();

        $seguimientosFaltantes = $pacientesAsignados->filter(function ($paciente) use ($seguimientosHoy) {
            return !in_array($paciente->cod_am, $seguimientosHoy);
        });

        // 8. Valoraciones iniciales (Preadmisiones asignadas para valoración)
        $valoracionesPendientes = Preadmision::query()
            ->with(['documentos', 'enfermero'])
            ->when(!$esSuperAdmin, fn ($q) => $q->where('enfermero_asignado', Auth::id()))
            ->whereIn('estado', ['PREADMISION_ASIGNADA', 'EN_VALORACION_ENFERMERIA'])
            ->whereNull('cod_am_generado')
            ->orderByRaw("CASE prioridad WHEN 'CRITICA' THEN 1 WHEN 'ALTA' THEN 2 WHEN 'MEDIA' THEN 3 ELSE 4 END")
            ->orderByDesc('created_at')
            ->get();

        // 9. Pase de turno
        $paseTurnoHoy = null;
        if ($this->turnoActual) {
            $paseTurnoHoy = PaseTurno::with(['enfermeroSaliente', 'enfermeroEntrante', 'turnoEntrante'])
                ->where('turno_saliente_id', $this->turnoActual->cod_turno)
                ->whereDate('fecha', $this->filtroFecha)
                ->latest()
                ->first();
        }

        $registrosCuidadosHoy = RegistroCuidado::whereIn('cod_am', $codAms)
            ->whereDate('fecha_hora_evento', $this->filtroFecha)
            ->count();
        $incidentesAbiertos = IncidenteResidente::whereIn('cod_am', $codAms)
            ->whereIn('estado', ['ABIERTO', 'EN_SEGUIMIENTO'])
            ->count();
        $lesionesActivas = LesionResidente::whereIn('cod_am', $codAms)
            ->where('estado', 'ACTIVA')
            ->count();
        $dispositivosActivos = DispositivoResidente::whereIn('cod_am', $codAms)
            ->where('estado', 'ACTIVO')
            ->count();

        // Estadísticas consolidadas reales
        $stats = [
            'pacientes' => count($codAms),
            'alertas_activas' => $alertas->count(),
            'alertas_criticas' => $alertas->where('nivel', 'CRITICO')->count(),
            'medicacion_pendiente' => $medicacionPendiente->count(),
            'tareas_pendientes' => $tareasPendientes->count(),
            'tareas_vencidas' => $tareasPendientes->filter(fn ($t) => $t->fecha_programada->toDateString() < $this->filtroFecha)->count(),
            'signos_pendientes' => $controlesSignos->where('tiene_control_hoy', false)->count(),
            'seguimientos_faltantes' => $seguimientosFaltantes->count(),
            'valoraciones_pendientes' => $valoracionesPendientes->count(),
            'pase_estado' => $paseTurnoHoy ? $paseTurnoHoy->estado : 'NO INICIADO',
            'cuidados_registrados' => $registrosCuidadosHoy,
            'incidentes_abiertos' => $incidentesAbiertos,
            'lesiones_activas' => $lesionesActivas,
            'dispositivos_activos' => $dispositivosActivos,
        ];

        // 10. Datos para Gráfica 1: Cumplimiento del turno (% tareas y seguimientos con datos 100% reales)
        $tareasCompletadasHoy = TareaPlanCuidado::whereIn('cod_am', $codAms)
            ->whereDate('fecha_realizada', $this->filtroFecha)
            ->where('estado', 'REALIZADA')
            ->count();

        $totalTareasHoy = $tareasPendientes->count() + $tareasCompletadasHoy;
        $totalSeguimientosHoy = count($codAms);
        $seguimientosCompletadosHoy = max(0, $totalSeguimientosHoy - $seguimientosFaltantes->count());

        $totalAccionesTurno = $totalTareasHoy + $totalSeguimientosHoy;
        $accionesCompletadasTurno = $tareasCompletadasHoy + $seguimientosCompletadosHoy;
        $porcentajeCumplimiento = $totalAccionesTurno > 0
            ? (int) round(($accionesCompletadasTurno / $totalAccionesTurno) * 100)
            : 100;

        $cumplimientoTurno = [
            'total_acciones' => $totalAccionesTurno,
            'completadas' => $accionesCompletadasTurno,
            'porcentaje' => $porcentajeCumplimiento,
            'tareas_completadas' => $tareasCompletadasHoy,
            'tareas_pendientes' => $tareasPendientes->count(),
            'seguimientos_completados' => $seguimientosCompletadosHoy,
            'seguimientos_pendientes' => $seguimientosFaltantes->count(),
        ];

        // 11. Datos para Gráfica 2: Distribución real de pacientes (estable / vigilancia / atención)
        $distribucionPacientes = [
            'estable' => 0,
            'vigilancia' => 0,
            'atencion' => 0,
        ];

        $evaluacionPacientes = $pacientesAsignados->map(function ($paciente) use ($alertas, $medicacionPendiente, $tareasPendientes, $controlesSignos, $seguimientosFaltantes, &$distribucionPacientes) {
            $alertasPaciente = $alertas->where('cod_am', $paciente->cod_am);
            $tieneCritica = $alertasPaciente->contains('nivel', 'CRITICO');
            $tieneAlta = $alertasPaciente->contains('nivel', 'ALTO');
            $tieneMedia = $alertasPaciente->contains('nivel', 'MEDIO');
            $tieneBaja = $alertasPaciente->contains('nivel', 'BAJO');

            $medPend = $medicacionPendiente->first(fn ($m) => ($m['paciente']->cod_am ?? null) === $paciente->cod_am);
            $tareaPend = $tareasPendientes->firstWhere('cod_am', $paciente->cod_am);
            $controlSignos = $controlesSignos->first(fn ($c) => ($c['paciente']->cod_am ?? null) === $paciente->cod_am);
            $faltaSigno = $controlSignos ? !$controlSignos['tiene_control_hoy'] : false;
            $faltaSeguimiento = $seguimientosFaltantes->contains('cod_am', $paciente->cod_am);

            if ($tieneCritica || $tieneAlta) {
                $estado = 'ATENCION';
                $distribucionPacientes['atencion']++;
                $score = 100 + ($tieneCritica ? 50 : 20);
            } elseif ($tieneMedia || $tieneBaja || $faltaSigno || $tareaPend || $medPend) {
                $estado = 'VIGILANCIA';
                $distribucionPacientes['vigilancia']++;
                $score = 50 + ($faltaSigno ? 10 : 0) + ($medPend ? 15 : 0) + ($tareaPend ? 10 : 0);
            } else {
                $estado = 'ESTABLE';
                $distribucionPacientes['estable']++;
                $score = 10;
            }

            if ($medPend) {
                $proximaAccion = [
                    'tipo' => 'MEDICACION',
                    'icono' => 'ph-pill',
                    'texto' => 'Medicación: ' . $medPend['medicacion']->nombre_medicamento . ' (' . $medPend['hora_programada'] . ')',
                ];
            } elseif ($tareaPend) {
                $proximaAccion = [
                    'tipo' => 'TAREA',
                    'icono' => 'ph-check-square',
                    'texto' => 'Tarea: ' . $tareaPend->titulo . ($tareaPend->hora_programada ? ' (' . Carbon::parse($tareaPend->hora_programada)->format('H:i') . ')' : ''),
                ];
            } elseif ($faltaSigno) {
                $proximaAccion = [
                    'tipo' => 'SIGNOS',
                    'icono' => 'ph-heartbeat',
                    'texto' => 'Control de signos vitales pendiente',
                ];
            } elseif ($faltaSeguimiento) {
                $proximaAccion = [
                    'tipo' => 'SEGUIMIENTO',
                    'icono' => 'ph-notebook',
                    'texto' => 'Seguimiento de guardia pendiente',
                ];
            } else {
                $proximaAccion = [
                    'tipo' => 'COMPLETO',
                    'icono' => 'ph-check-circle',
                    'texto' => 'Al día en este turno',
                ];
            }

            return [
                'paciente' => $paciente,
                'estado' => $estado,
                'score' => $score,
                'alertas_count' => $alertasPaciente->count(),
                'alerta_max' => $tieneCritica ? 'CRITICO' : ($tieneAlta ? 'ALTO' : ($tieneMedia ? 'MEDIO' : ($tieneBaja ? 'BAJO' : null))),
                'proxima_accion' => $proximaAccion,
            ];
        });

        $pacientesPrioritarios = $evaluacionPacientes->sortByDesc('score')->take(5)->values();

        if (count($codAms) === 0) {
            $distribucionPacientes = ['estable' => 0, 'vigilancia' => 0, 'atencion' => 0];
        }

        // 12. Próximas acciones combinadas (máximo 6 ordenadas por hora)
        $proximasAcciones = collect();
        foreach ($medicacionPendiente->take(5) as $item) {
            $proximasAcciones->push([
                'tipo' => 'MEDICACION',
                'hora' => $item['hora_programada'],
                'paciente' => $item['paciente'],
                'titulo' => $item['medicacion']->nombre_medicamento,
                'detalle' => $item['medicacion']->dosis . ' (' . $item['medicacion']->via_administracion . ')',
                'item_med' => $item,
            ]);
        }
        foreach ($tareasPendientes->take(5) as $t) {
            $proximasAcciones->push([
                'tipo' => 'TAREA',
                'hora' => $t->hora_programada ? Carbon::parse($t->hora_programada)->format('H:i') : 'Turno',
                'paciente' => $t->adultoMayor,
                'titulo' => $t->titulo,
                'detalle' => $t->area ?? 'Cuidado general',
                'item_tarea' => $t,
            ]);
        }
        $proximasAcciones = $proximasAcciones->sortBy('hora')->values()->take(6);

        return view('livewire.cuidados.dashboard-turno', [
            'stats' => $stats,
            'alertas' => $alertas,
            'medicacionPendiente' => $medicacionPendiente,
            'tareasPendientes' => $tareasPendientes,
            'controlesSignos' => $controlesSignos,
            'seguimientosFaltantes' => $seguimientosFaltantes,
            'pacientesAsignados' => $pacientesAsignados,
            'valoracionesPendientes' => $valoracionesPendientes,
            'paseTurnoHoy' => $paseTurnoHoy,
            'esSuperAdmin' => $esSuperAdmin,
            'cumplimientoTurno' => $cumplimientoTurno,
            'distribucionPacientes' => $distribucionPacientes,
            'pacientesPrioritarios' => $pacientesPrioritarios,
            'proximasAcciones' => $proximasAcciones,
        ])->layout('layouts.sistema');
    }
}
