<?php

namespace App\Modulos\Cuidados\Presentacion\Livewire\Paginas;

use App\Models\AdministracionMedicacion;
use App\Models\Alerta;
use App\Models\AsignacionResidenteJornada;
use App\Models\EjecucionCuidado;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Models\Turno;
use App\Modulos\Alertas\Aplicacion\Acciones\AlertasService;
use App\Modulos\Alertas\Aplicacion\Consultas\TendenciaIncidentesService;
use App\Modulos\Clinica\Aplicacion\Acciones\AccesoClinicoTemporalService;
use App\Modulos\Clinica\Aplicacion\Acciones\SignosVitalesService;
use App\Modulos\Cuidados\Aplicacion\Acciones\GestionarRegistrosDashboardTurno;
use App\Modulos\Cuidados\Aplicacion\Consultas\DashboardComplementosService;
use App\Modulos\Cuidados\Aplicacion\Consultas\MiTurnoService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Medicacion\Aplicacion\Acciones\RegistrarAdministracionMedicacionService;
use App\Modulos\Medicacion\Aplicacion\Consultas\AgendaMedicacionService;
use App\Modulos\Reportes\Presentacion\DashboardPhotoRotation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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

    public string $welcomeImage = '';

    public string $welcomeSecondaryImage = '';

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

    public ?string $medOmitirCodResidente = null;

    public ?string $medOmitirHora = null;

    public string $motivoOmisionMed = '';

    public bool $modalAtenderAlerta = false;

    public ?string $alertaAccionId = null;

    public string $accionTomadaAlerta = '';

    public bool $modalCerrarAlerta = false;

    public string $observacionCierreAlerta = '';

    // Modal rápido de signos vitales
    public bool $modalSignos = false;

    public ?string $signoCodResidente = null;

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

    public ?string $segCodResidente = null;

    public string $segEstadoGeneral = '';

    public string $segAlimentacion = '';

    public string $segMovilidad = '';

    public string $segSueno = '';

    public bool $segIncidente = false;

    public bool $segRequiereMedico = false;

    public string $segObservacion = '';

    public function mount()
    {
        [$this->welcomeImage, $this->welcomeSecondaryImage] = app(DashboardPhotoRotation::class)->pair('enfermeria');

        $this->filtroEnfermeroId = Auth::user()?->cod_usuario ?? '';
        $this->filtroFecha = Carbon::now()->toDateString();
        $this->loadTurnoActual();
    }

    public function asegurarModoOperativo(?string $codResidente = null, ?string $permiso = null): void
    {
        // 1. Usuario autenticado y cuenta activa
        $user = Auth::user();
        abort_unless($user, 403, 'Acción no permitida: Usuario no autenticado.');
        abort_unless(strtoupper(trim((string) $user->estado)) === 'ACTIVO', 403, 'Acción no permitida: Cuenta de usuario no activa.');

        // 2. Personal institucional vinculado
        $personal = $user->personal;
        abort_unless($personal, 403, 'Acción no permitida: Personal no vinculado al usuario.');

        $accesoTemporal = app(AccesoClinicoTemporalService::class);
        if ($accesoTemporal->sustituyeRol($user)) {
            if ($permiso) {
                abort_unless($user->can($permiso), 403);
            }
            if ($codResidente && $permiso) {
                app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($codResidente, $permiso, $user);
            }

            return;
        }

        // 3. Jornada activa actual en tiempo real
        // Si la jornada terminó mientras la pantalla estaba abierta, resolverJornadaActual retornará null
        $miTurnoService = app(MiTurnoService::class);
        $ahora = Carbon::now();
        $jornadaActual = $miTurnoService->resolverJornadaActual($personal, $ahora);
        abort_unless($jornadaActual, 403, 'Acción no permitida: Usuario fuera de turno en modo consulta o su jornada ha finalizado.');

        // 4. Permiso si se especifica
        if ($permiso) {
            abort_unless($user->can($permiso), 403, "Acción no permitida: Carece del permiso [{$permiso}].");
        }

        abort_unless(app(AccesoClinicoTemporalService::class)
            ->tieneRol($user, ['ENFERMEROS']), 403, 'Acción clínica no permitida: Rol ENFERMEROS requerido.');

        // 5. Residente asignado en la jornada activa
        if ($codResidente) {
            $esAsignado = AsignacionResidenteJornada::query()
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
        $this->filtroFecha = Carbon::now()->toDateString();
        $this->loadTurnoActual();
    }

    private function autorizarLectura(): void
    {
        $usuario = Auth::user();
        abort_unless($usuario?->fresh()?->estado === 'ACTIVO', 403, 'La cuenta de usuario no está activa.');
        Gate::forUser($usuario)->authorize('enfermeria.ver_dashboard');
    }

    public function loadTurnoActual()
    {
        $this->autorizarLectura();
        $service = app(TurnoEnfermeriaService::class);
        $this->turnosActivos = Turno::activos()->orderBy('orden')->get();
        $this->turnoActual = $service->obtenerTurnoActivo(Auth::user(), $this->filtroFecha);

        $this->pacientesAsignadosIds = $service->esSuperAdmin(Auth::user())
            ? $service->obtenerPacientesAsignadosIds(Auth::user())
            : $service->obtenerPacientesAsignadosIds(Auth::user(), $this->turnoActual?->cod_turno);
    }

    // ─── ACCIONES DE TAREAS ──────────────────────────────────────────

    public function completarTarea(string $codTarea)
    {
        app(GestionarRegistrosDashboardTurno::class)->completarTarea(auth()->user(), [

        ], $codTarea);

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Tarea completada',
            'text' => 'La tarea fue marcada como realizada.',
        ]);
    }

    public function abrirOmitirTarea(string $codTarea)
    {
        $this->asegurarModoOperativo();
        $tarea = EjecucionCuidado::findOrFail($codTarea);
        $this->asegurarModoOperativo($tarea->cod_residente, 'ejecuciones_cuidado.gestionar');
        app(TurnoEnfermeriaService::class)->autorizarMutacionPaciente($tarea->cod_residente, 'ejecuciones_cuidado.gestionar', Auth::user());
        abort_unless($tarea->puedeCompletarse(), 409, 'La tarea ya no está pendiente de ejecución.');
        $this->tareaOmitirId = $codTarea;
        $this->motivoOmisionTarea = '';
        $this->modalOmitirTarea = true;
    }

    public function confirmarOmisionTarea()
    {
        $this->resetValidation();
        $tarea = app(GestionarRegistrosDashboardTurno::class)->confirmarOmisionTarea(auth()->user(), [
            'tareaOmitirId' => $this->tareaOmitirId,
            'motivoOmisionTarea' => $this->motivoOmisionTarea,
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

    public function administrarMed(string $codPrescripcion, string $codResidente, ?string $horaProgramada = null)
    {
        $this->asegurarModoOperativo($codResidente, 'administraciones_medicacion.crear');
        abort_unless(Gate::forUser(Auth::user())->allows('create', AdministracionMedicacion::class), 403);
        if (! $horaProgramada) {
            $ocurrencia = app(AgendaMedicacionService::class)->paraAdulto($codResidente)
                ->first(fn (array $item) => $item['medicacion']->cod_prescripcion === $codPrescripcion);
            $horaProgramada = $ocurrencia['hora'] ?? '';
        }
        app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
            Auth::user(), $codResidente, $codPrescripcion, $horaProgramada, true,
            null, 'Administrada desde la agenda del turno.'
        );

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Medicación administrada',
            'text' => 'Se registró la administración de la dosis correctamente.',
        ]);
    }

    public function abrirOmitirMed(string $codPrescripcion, string $codResidente, ?string $horaProgramada = null)
    {
        $this->asegurarModoOperativo($codResidente, 'administraciones_medicacion.crear');
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($codResidente, Auth::user());
        abort_unless(Prescripcion::where('cod_prescripcion', $codPrescripcion)->where('cod_residente', $codResidente)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'VIGENTE'])->exists(), 404);
        $this->medOmitirId = $codPrescripcion;
        $this->medOmitirCodResidente = $codResidente;
        $ocurrencia = app(AgendaMedicacionService::class)->paraAdulto($codResidente)
            ->first(fn (array $item) => $item['medicacion']->cod_prescripcion === $codPrescripcion && $item['registro'] === null);
        $this->medOmitirHora = $horaProgramada ?: ($ocurrencia['hora'] ?? null);
        $this->motivoOmisionMed = '';
        $this->modalOmitirMed = true;
    }

    public function confirmarOmisionMed()
    {
        $this->asegurarModoOperativo($this->medOmitirCodResidente, 'administraciones_medicacion.crear');
        $this->validate([
            'motivoOmisionMed' => 'required|string|min:5|max:500',
        ], [
            'motivoOmisionMed.required' => 'Debe indicar el motivo por el cual no se administró el medicamento.',
        ]);

        app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
            Auth::user(), $this->medOmitirCodResidente, $this->medOmitirId,
            (string) $this->medOmitirHora, false, $this->motivoOmisionMed
        );

        $this->modalOmitirMed = false;
        $this->medOmitirId = null;
        $this->medOmitirCodResidente = null;
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
        $this->asegurarModoOperativo($alerta->cod_residente, 'alertas.gestionar');
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($alerta->cod_residente, Auth::user());
        abort_unless(in_array($alerta->estado, Alerta::ESTADOS_ACTIVOS, true), 409, 'El estado actual de la alerta no admite iniciar atención.');
        $this->alertaAccionId = $codAlerta;
        $this->accionTomadaAlerta = '';
        $this->modalAtenderAlerta = true;
    }

    public function confirmarAtencionAlerta()
    {
        $this->asegurarModoOperativo();
        $alerta = Alerta::findOrFail($this->alertaAccionId);
        $this->asegurarModoOperativo($alerta->cod_residente, 'alertas.gestionar');
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
        $this->asegurarModoOperativo($alerta->cod_residente, 'alertas.gestionar');
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
        $this->asegurarModoOperativo($alerta->cod_residente, 'alertas.gestionar');
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

    public function abrirRegistrarSignos(string $codResidente)
    {
        $this->asegurarModoOperativo($codResidente, 'signos_vitales.crear');
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($codResidente, Auth::user());
        $this->signoCodResidente = $codResidente;
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
        $this->asegurarModoOperativo($this->signoCodResidente, 'signos_vitales.crear');
        app(SignosVitalesService::class)->registrar($this->signoCodResidente, [
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
        $this->signoCodResidente = null;

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Signos vitales registrados',
            'text' => 'Control de signos guardado y analizado por el monitor clínico.',
        ]);
    }

    // ─── ACCIÓN RÁPIDA DE SEGUIMIENTO DIARIO ────────────────────────

    public function abrirRegistrarSeguimiento(string $codResidente)
    {
        $this->asegurarModoOperativo($codResidente, 'atenciones.crear');
        app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($codResidente, Auth::user());
        $this->segCodResidente = $codResidente;
        $this->segEstadoGeneral = '';
        $this->segAlimentacion = '';
        $this->segMovilidad = '';
        $this->segSueno = '';
        $this->segIncidente = false;
        $this->segRequiereMedico = false;
        $this->segObservacion = '';
        $this->modalSeguimiento = true;
    }

    public function guardarSeguimiento()
    {
        $this->resetValidation();
        app(GestionarRegistrosDashboardTurno::class)->guardarSeguimiento(auth()->user(), [
            'segCodResidente' => $this->segCodResidente,
            'segIncidente' => $this->segIncidente,
            'segRequiereMedico' => $this->segRequiereMedico,
            'segObservacion' => $this->segObservacion,
            'turnoActual' => $this->turnoActual,
            'filtroFecha' => $this->filtroFecha,
            'segEstadoGeneral' => $this->segEstadoGeneral,
            'segAlimentacion' => $this->segAlimentacion,
            'segMovilidad' => $this->segMovilidad,
            'segSueno' => $this->segSueno,
        ]);

        $this->modalSeguimiento = false;
        $this->segCodResidente = null;

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
        $this->autorizarLectura();
        $miTurnoService = app(MiTurnoService::class);
        $dashboard = $miTurnoService->obtenerDatosDashboard(Auth::user(), $this->filtroFecha);
        $incidentesTendencia = ($dashboard['estado'] ?? null) === 'SIN_JORNADA_ACTIVA'
            ? null
            : app(TendenciaIncidentesService::class)->resumir(
                array_column($dashboard['residentes'] ?? [], 'cod_residente')
            );
        $residentesDashboard = collect($dashboard['residentes'] ?? []);
        $distribucionPacientes = [
            'sin_alertas' => $residentesDashboard->where('estado_seguimiento', 'SIN_ALERTAS')->count(),
            'vigilancia' => $residentesDashboard->where('estado_seguimiento', 'VIGILANCIA')->count(),
            'atencion' => $residentesDashboard->filter(
                fn ($residente) => in_array($residente['estado_seguimiento'] ?? null, ['CRITICO', 'CRÍTICO', 'ATENCION'], true)
            )->count(),
        ];

        return view('livewire.cuidados.dashboard-turno', [
            'dashboard' => $dashboard,
            'complementos' => app(DashboardComplementosService::class)->resumir($dashboard, Auth::user()),
            'incidentesTendencia' => $incidentesTendencia,
            'stats' => [
                'pacientes' => count($dashboard['residentes']),
                'alertas_activas' => ($dashboard['kpis']['por_atender']['numero'] ?? 0) + ($dashboard['kpis']['en_atencion']['numero'] ?? 0),
            ],
            'distribucionPacientes' => $distribucionPacientes,
        ])->layout('layouts.enfermeria');
    }
}
