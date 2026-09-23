<?php

namespace App\Livewire\Cuidados;

use App\Models\AdministracionMedicacion;
use App\Models\Residente;
use App\Models\Alerta;
use App\Models\Prescripcion;
use App\Models\PaseTurno;
use App\Models\Preadmision;
use App\Models\Atencion;
use App\Models\SignoVital;
use App\Models\EjecucionCuidado;
use App\Models\TurnoEnfermeria;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use App\Services\Clinica\SignosVitalesService;
use App\Services\Alertas\AlertasService;
use App\Services\Medicacion\RegistrarAdministracionMedicacionService;
use App\Services\Medicacion\AgendaMedicacionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        $this->filtroEnfermeroId = Auth::user()?->cod_usuario ?? '';
        $this->filtroFecha = Carbon::now()->toDateString();
        $this->loadTurnoActual();
    }

        public function asegurarModoOperativo(?string $codResidente = null, ?string $permiso = null): void
    {
        // 1. Usuario autenticado y cuenta activa
        $user = Auth::user();
        abort_unless($user, 403, 'Acción no permitida: Usuario no autenticado.');
        abort_unless(strtoupper(trim((string)$user->estado)) === 'ACTIVO', 403, 'Acción no permitida: Cuenta de usuario no activa.');

        // 2. Personal institucional vinculado
        $personal = $user->personal;
        abort_unless($personal, 403, 'Acción no permitida: Personal no vinculado al usuario.');

        // 3. Jornada activa actual en tiempo real
        // Si la jornada terminó mientras la pantalla estaba abierta, resolverJornadaActual retornará null
        $miTurnoService = app(\App\Services\Enfermeria\MiTurnoService::class);
        $ahora = \Carbon\Carbon::now();
        $jornadaActual = $miTurnoService->resolverJornadaActual($personal, $ahora);
        abort_unless($jornadaActual, 403, 'Acción no permitida: Usuario fuera de turno en modo consulta o su jornada ha finalizado.');

        // 4. Permiso si se especifica
        if ($permiso) {
            abort_unless($user->can($permiso), 403, "Acción no permitida: Carece del permiso [{$permiso}].");
        }

        // 1.1 Competencia clinica: Las mutaciones clinicas en Mi Turno requieren rol ENFERMEROS
        // (SUPERADMINISTRADOR tiene lectura global, pero NO puede registrar mutaciones clinicas solo por su rol)
        abort_unless($user->hasRole('ENFERMEROS'), 403, 'Acción clínica no permitida: Rol ENFERMEROS requerido.');

        // 5. Residente asignado en la jornada activa (SIN bypass de Superadministrador para mutaciones clinicas)
        if ($codResidente) {
            $esAsignado = \App\Models\AsignacionResidenteJornada::query()
                ->where('cod_jornada', $jornadaActual->cod_jornada)
                ->where('cod_personal', $personal->cod_personal)
                ->where('cod_residente', $codResidente)
                ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'ASIGNADO'])
                ->exists();
            abort_unless($esAsignado, 403, 'Acción no permitida: El residente no está asignado a su turno activo.');
        }
    }

    public function refrescarTurno(): void
    {
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
        $this->asegurarModoOperativo();
        $tarea = EjecucionCuidado::findOrFail($codTarea);
        $this->asegurarModoOperativo($tarea->cod_residente, 'tareas.registrar_resultado');
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($tarea->cod_residente, 'tareas.registrar_resultado', Auth::user());

        DB::transaction(function () use ($tarea) {
            $bloqueada = EjecucionCuidado::lockForUpdate()->findOrFail($tarea->cod_ejecucion);
            abort_unless($bloqueada->puedeCompletarse(), 409, 'La tarea ya no está pendiente de ejecución.');
            $bloqueada->update([
                'estado' => 'REALIZADA',
                'resultado' => 'Completada y confirmada desde el panel del turno.',
                'fecha_hora_ejecucion' => now(),
            ]);
        });

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Tarea completada',
            'text' => "La tarea fue marcada como realizada.",
        ]);
    }

    public function abrirOmitirTarea(string $codTarea)
    {
        $this->asegurarModoOperativo();
        $tarea = EjecucionCuidado::findOrFail($codTarea);
        $this->asegurarModoOperativo($tarea->cod_residente, 'tareas.omitir');
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($tarea->cod_residente, 'tareas.omitir', Auth::user());
        abort_unless($tarea->puedeCompletarse(), 409, 'La tarea ya no está pendiente de ejecución.');
        $this->tareaOmitirId = $codTarea;
        $this->motivoOmisionTarea = '';
        $this->modalOmitirTarea = true;
    }

    public function confirmarOmisionTarea()
    {
        $this->asegurarModoOperativo();
        $tarea = EjecucionCuidado::findOrFail($this->tareaOmitirId);
        $this->asegurarModoOperativo($tarea->cod_residente, 'tareas.omitir');
        $this->validate([
            'motivoOmisionTarea' => 'required|string|min:5|max:500',
        ], [
            'motivoOmisionTarea.required' => 'Debe registrar el motivo de la omisión.',
        ]);

        $tarea = EjecucionCuidado::findOrFail($this->tareaOmitirId);
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($tarea->cod_residente, 'tareas.omitir', Auth::user());
        abort_unless($tarea->puedeCompletarse(), 409, 'La tarea ya no está pendiente de ejecución.');
        $tarea->update([
            'estado' => 'OMITIDA',
            'motivo_omision' => trim($this->motivoOmisionTarea),
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
        $this->asegurarModoOperativo($codAm, 'administracion_medicacion.registrar');
        abort_unless(\Illuminate\Support\Facades\Gate::forUser(Auth::user())->allows('create', \App\Models\AdministracionMedicacion::class), 403);
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
        $this->asegurarModoOperativo($codAm, 'administracion_medicacion.registrar');
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($codAm, Auth::user());
        abort_unless(Prescripcion::where('cod_prescripcion', $codMedAdulto)->where('cod_residente', $codAm)
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
        $this->asegurarModoOperativo($this->medOmitirCodAm, 'administracion_medicacion.registrar');
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
        $this->asegurarModoOperativo();
        $alerta = Alerta::findOrFail($codAlerta);
        $this->asegurarModoOperativo($alerta->cod_residente, 'alertas.atender');
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($alerta->cod_residente, Auth::user());
        abort_unless($alerta->estado === 'ABIERTA', 409, 'La alerta ya fue atendida o cerrada.');
        $this->alertaAccionId = $codAlerta;
        $this->accionTomadaAlerta = '';
        $this->modalAtenderAlerta = true;
    }

    public function confirmarAtencionAlerta()
    {
        $this->asegurarModoOperativo();
        $alerta = Alerta::findOrFail($this->alertaAccionId);
        $this->asegurarModoOperativo($alerta->cod_residente, 'alertas.atender');
        app(AlertasService::class)->registrarIntervencion($alerta, $this->accionTomadaAlerta, Auth::user());

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
        $this->asegurarModoOperativo();
        $alerta = Alerta::findOrFail($codAlerta);
        $this->asegurarModoOperativo($alerta->cod_residente, 'alertas.cerrar');
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($alerta->cod_residente, Auth::user());
        abort_unless($alerta->puedeCerrarse(), 409, 'La alerta ya está cerrada.');
        $this->alertaAccionId = $codAlerta;
        $this->observacionCierreAlerta = '';
        $this->modalCerrarAlerta = true;
    }

    public function confirmarCierreAlerta()
    {
        $this->asegurarModoOperativo();
        $alerta = Alerta::findOrFail($this->alertaAccionId);
        $this->asegurarModoOperativo($alerta->cod_residente, 'alertas.cerrar');
        app(AlertasService::class)->cerrar($alerta, $this->observacionCierreAlerta, Auth::user());

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
        $this->asegurarModoOperativo($codAm, 'signos_vitales.crear');
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
        $this->asegurarModoOperativo($this->signoCodAm, 'signos_vitales.crear');
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
        $this->asegurarModoOperativo($codAm, 'seguimiento.crear');
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
        $this->asegurarModoOperativo($this->segCodAm, 'seguimiento.crear');
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
        $personal = Auth::user()?->personal;
        abort_unless($personal, 422, 'El usuario no está vinculado a personal clínico.');
        $asignacionArea = \App\Models\AsignacionPersonal::query()
            ->where('cod_personal', $personal->cod_personal)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->latest('fecha_asignacion')
            ->first();
        abort_unless($asignacionArea, 422, 'El personal no tiene un área institucional activa.');

        if (Atencion::where('cod_residente', $this->segCodAm)
            ->whereDate('fecha_hora', $this->filtroFecha)
            ->where('cod_personal', $personal->cod_personal)
            ->where('tipo_atencion', 'SEGUIMIENTO_ENFERMERIA')->exists()) {
            $this->addError('segObservacion', 'Ya existe un seguimiento de este residente para el turno y la fecha seleccionados.');
            return;
        }

        Atencion::create([
            'cod_residente' => $this->segCodAm,
            'cod_area' => $asignacionArea->cod_area,
            'cod_personal' => $personal->cod_personal,
            'fecha_hora' => now(),
            'tipo_atencion' => 'SEGUIMIENTO_ENFERMERIA',
            'motivo' => "Estado: {$this->segEstadoGeneral}; alimentación: {$this->segAlimentacion}; movilidad: {$this->segMovilidad}; sueño: {$this->segSueno}",
            'estado' => 'FINALIZADA',
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
        $miTurnoService = app(\App\Services\Enfermeria\MiTurnoService::class);
        $dashboard = $miTurnoService->obtenerDatosDashboard(Auth::user(), $this->filtroFecha);

        return view('livewire.cuidados.dashboard-turno', [
            'dashboard' => $dashboard,
            'stats' => [
                'pacientes' => count($dashboard['residentes']),
                'alertas_activas' => ($dashboard['kpis']['por_atender']['numero'] ?? 0) + ($dashboard['kpis']['en_atencion']['numero'] ?? 0),
                'alertas_criticas' => $dashboard['kpis']['criticas_altas']['numero'] ?? 0,
                'medicacion_pendiente' => $dashboard['estado_tareas']['pendientes'] ?? 0,
                'tareas_pendientes' => $dashboard['estado_tareas']['pendientes'] ?? 0,
                'tareas_vencidas' => $dashboard['estado_tareas']['retrasadas'] ?? 0,
                'signos_pendientes' => 0,
                'seguimientos_faltantes' => 0,
                'valoraciones_pendientes' => 0,
                'pase_estado' => 'NO INICIADO',
                'cuidados_registrados' => $dashboard['estado_tareas']['realizadas'] ?? 0,
                'incidentes_abiertos' => 0,
                'lesiones_activas' => 0,
                'dispositivos_activos' => 0,
            ],
            'alertas' => $dashboard['alertas'] ?? [],
            'medicacionPendiente' => collect(),
            'tareasPendientes' => collect(),
            'controlesSignos' => collect(),
            'seguimientosFaltantes' => collect(),
            'pacientesAsignados' => collect($dashboard['residentes'] ?? []),
            'valoracionesPendientes' => collect(),
            'paseTurnoHoy' => null,
            'esSuperAdmin' => Auth::user()?->hasRole('SUPERADMINISTRADOR') ?? false,
            'cumplimientoTurno' => [
                'total_acciones' => $dashboard['estado_tareas']['total'] ?? 0,
                'completadas' => $dashboard['estado_tareas']['realizadas'] ?? 0,
                'porcentaje' => $dashboard['estado_tareas']['porcentaje'] ?? 100,
                'tareas_completadas' => $dashboard['estado_tareas']['realizadas'] ?? 0,
                'tareas_pendientes' => $dashboard['estado_tareas']['pendientes'] ?? 0,
                'seguimientos_completados' => 0,
                'seguimientos_pendientes' => 0,
            ],
            'distribucionPacientes' => ['estable' => count($dashboard['residentes'] ?? []), 'vigilancia' => 0, 'atencion' => 0],
            'pacientesPrioritarios' => collect(),
            'proximasAcciones' => collect(),
        ])->layout('layouts.enfermeria');
    }
}