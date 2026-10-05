<?php

namespace App\Frontend\Livewire\Enfermeria\Cuidados;

use App\Backend\Modulos\Alertas\Servicios\AlertasService;
use App\Backend\Modulos\Clinica\Servicios\SignosVitalesService;
use App\Backend\Modulos\Enfermeria\Servicios\CuidadosEnfermeriaService;
use App\Backend\Modulos\Enfermeria\Servicios\MiTurnoService;
use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Backend\Modulos\Medicacion\Servicios\AgendaMedicacionService;
use App\Backend\Modulos\Medicacion\Servicios\RegistrarAdministracionMedicacionService;
use App\Backend\Modulos\Identidad\Servicios\RolePreviewService;
use App\Models\AdministracionMedicacion;
use App\Models\Alerta;
use App\Models\IndicacionClinica;
use App\Models\ObjetivoSignoVital;
use App\Models\AsignacionResidenteJornada;
use App\Models\Atencion;
use App\Models\EjecucionCuidado;
use App\Models\PaseTurno;
use App\Models\Prescripcion;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\Turno;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\ValidationException;

class MisPacientes extends Component
{
    use WithPagination;

    #[Url(as: 'buscar')]
    public string $search = '';

    public string $filtroEstado = 'TODOS'; // 'TODOS' | 'ESTABLE' | 'VIGILANCIA' | 'REQUIERE_ATENCION'

    public string $filtroTurno = '';

    public string $filtroEnfermero = '';

    public string $filtroRapido = 'TODOS';

    public string $filtroAlertas = '';

    public string $filtroMedicacion = '';

    public string $filtroCuidados = '';

    public string $vistaModo = 'tarjetas'; // 'tabla' (Lista) | 'tarjetas' (Tarjetas)

    public string $filtroHabitacion = '';

    public string $orden = 'NOMBRE_ASC';

    #[Url(as: 'residente')]
    public ?string $residente = null;

    public bool $mostrarPanelDetalle = false;

    public bool $mostrarSelectorModal = false;

    public bool $tieneMedicacionProgramadaPendiente = false;

    public int $cantidadMedicacionProgramadaPendiente = 0;

    public ?array $detalleResidente = null;

    public string $drawerPaso = 'resident-summary';

    public ?string $registroTipo = null;

    public array $registroInicial = [];

    public bool $confirmarDescarte = false;

    #[Locked]
    public bool $descarteCriticoConfirmado = false;

    public ?string $accionDescarte = null;

    public bool $esModoConsulta = false;

    public bool $esResidenteAsignado = false;

    protected ?TurnoEnfermeriaService $turnoService = null;

    // Modales rápidos operativos
    public bool $modalSignos = false;

    public ?string $modalCodResidente = null;

    public string $signoPA = '';

    public string $signoSis = '';

    public string $signoDia = '';

    #[Locked]
    public array $signosHistorial = [];

    #[Locked]
    public array $signosBandasObjetivo = [];

    #[Locked]
    public array $signosContextoTurno = [];

    #[Locked]
    public array $signosEvaluacion = [];

    public bool $signosIntentoGuardar = false;

    #[Locked]
    public bool $signosConfirmacionPendiente = false;

    #[Locked]
    public string $signosPasoConfirmacion = 'revision';

    #[Locked]
    public array $signosResultadoRegistro = [];

    #[Locked]
    public ?string $signosConfirmacionHuella = null;

    public string $signoFC = '';

    public string $signoFR = '';

    public string $signoTemp = '';

    public string $signoSat = '';

    public string $signoGlucosa = '';

    public string $signoObs = '';

    public bool $signoConfirmarAtipico = false;

    public bool $modalSeguimiento = false;

    public string $segEstado = '';

    public string $segAlimentacion = '';

    public string $segMovilidad = '';

    public string $segSueno = '';

    public bool $segIncidente = false;

    public bool $segRequiereMedico = false;

    public string $segObs = '';

    public bool $modalMed = false;

    public ?string $medCodMed = null;

    public bool $medAdministrado = true;

    public string $medMotivoOmision = '';

    public string $medResultado = 'ADMINISTRADA';

    public string $medFechaHoraReal = '';

    public string $medDosisAdministrada = '';

    public string $medObservacion = '';

    public array $medOpcionesProgramadas = [];

    public array $medDetalleProgramado = [];

    #[Locked]
    public ?string $medOcurrenciaSeleccionada = null;

    public $medicacionesPaciente = [];

    public bool $modalAlerta = false;

    public string $alertaTipo = 'INCIDENTE';

    public string $alertaNivel = 'ALTO';

    public string $alertaMotivo = '';

    public bool $modalCuidado = false;

    public string $cuidadoTipo = 'HIGIENE';

    public string $cuidadoSubtipo = 'GENERAL';

    public string $cuidadoObs = '';

    public string $ingestaTipoComida = '';

    public string $ingestaPorcentaje = '';

    public string $ingestaCantidadMl = '';

    public string $ingestaTolerancia = '';

    public bool $ingestaDificultadDeglucion = false;

    public string $ingestaObservacion = '';

    public string $elimTipo = '';

    public string $elimCantidadUrinaria = '';

    public string $elimCaracteristicaUrinaria = '';

    public string $elimContinenciaUrinaria = '';

    public string $elimCantidadIntestinal = '';

    public string $elimCaracteristicaIntestinal = '';

    public string $elimContinenciaIntestinal = '';

    public string $elimObservacion = '';

    public string $movMarcha = '';

    public string $movTraslado = '';

    public string $movTipoApoyo = '';

    public string $movEquilibrio = '';

    public string $movFatiga = '';

    public string $movRiesgoCaida = '';

    public string $movObservacion = '';

    public bool $modalDolor = false;

    public int $dolorIntensidad = 5;

    public string $dolorDetalle = '';

    public string $dolorFechaHora = '';

    public string $dolorEva = '';

    public string $dolorUbicacion = '';

    public string $dolorDuracionValor = '';

    public string $dolorDuracionUnidad = '';

    public string $dolorDesencadenante = '';

    public string $dolorIntervencion = '';

    public bool $modalProcedimiento = false;

    public string $procTipo = 'CURACION';

    public string $procDetalle = '';

    protected function getTurnoService(): TurnoEnfermeriaService
    {
        if (! $this->turnoService) {
            $this->turnoService = app(TurnoEnfermeriaService::class);
        }

        return $this->turnoService;
    }

    public function mount(): void
    {
        $service = $this->getTurnoService();
        $user = Auth::user();

        $turnoActual = $service->obtenerTurnoActivo($user);
        $this->esModoConsulta = ($turnoActual === null) && ! $service->esSuperAdmin($user);

        if ($service->tieneLecturaClinicaGlobal($user)) {
            $this->filtroEnfermero = '';
            $this->filtroTurno = '';
        } else {
            $this->filtroEnfermero = (string) $user?->cod_usuario;
            if ($turnoActual) {
                $this->filtroTurno = (string) $turnoActual->cod_turno;
            }
        }

        $residenteSolicitado = request()->query('residente');
        if (is_string($residenteSolicitado) && $residenteSolicitado !== '') {
            $this->seleccionarResidente($residenteSolicitado);
            if (request()->query('registrar') === 'signos') {
                $this->mostrarSelectorRegistro();
                $this->abrirFormularioRegistro('signos');
            }
        } elseif (! empty($this->residente)) {
            $this->seleccionarResidente($this->residente);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroAlertas(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroMedicacion(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroCuidados(): void
    {
        $this->resetPage();
    }

    public function aplicarFiltro(string $tipo, string $valor): void
    {
        // Si el valor ya está seleccionado en ese tipo, alternar (deseleccionar)
        $valorActual = match ($tipo) {
            'alertas' => $this->filtroAlertas,
            'habitacion' => $this->filtroHabitacion,
            'medicacion' => $this->filtroMedicacion,
            'cuidados' => $this->filtroCuidados,
            'estado' => $this->filtroEstado,
            'rapido' => $this->filtroRapido,
            default => null,
        };

        if ($valorActual === $valor) {
            $this->removerFiltro($tipo);
            return;
        }

        match ($tipo) {
            'alertas' => $this->filtroAlertas = $valor,
            'habitacion' => $this->filtroHabitacion = $valor,
            'medicacion' => $this->filtroMedicacion = $valor,
            'cuidados' => $this->filtroCuidados = $valor,
            'estado' => $this->filtroEstado = $valor,
            'rapido' => $this->filtroRapido = $valor,
            default => null,
        };
        if ($tipo === 'alertas' && $valor === 'CON_ALERTAS') {
            $this->filtroRapido = 'CON_ALERTAS';
        } elseif ($tipo === 'alertas' && $valor !== 'CON_ALERTAS') {
            if ($this->filtroRapido === 'CON_ALERTAS') {
                $this->filtroRapido = 'TODOS';
            }
        }
        $this->resetPage();
    }

    public function removerFiltro(string $tipo): void
    {
        match ($tipo) {
            'alertas' => $this->filtroAlertas = '',
            'habitacion' => $this->filtroHabitacion = '',
            'medicacion' => $this->filtroMedicacion = '',
            'cuidados' => $this->filtroCuidados = '',
            'estado' => $this->filtroEstado = 'TODOS',
            'rapido' => $this->filtroRapido = 'TODOS',
            default => null,
        };
        if ($tipo === 'alertas' && $this->filtroRapido === 'CON_ALERTAS') {
            $this->filtroRapido = 'TODOS';
        }
        $this->resetPage();
    }

    public function limpiarFiltrosActivos(): void
    {
        $this->filtroAlertas = '';
        $this->filtroHabitacion = '';
        $this->filtroMedicacion = '';
        $this->filtroCuidados = '';
        $this->filtroRapido = 'TODOS';
        $this->filtroEstado = 'TODOS';
        $this->resetPage();
    }

    public function obtenerFiltrosActivos(): array
    {
        $activos = [];

        if (! empty($this->filtroAlertas)) {
            $label = match ($this->filtroAlertas) {
                'CON_ALERTAS' => 'Con alertas',
                'SIN_ALERTAS' => 'Sin alertas',
                'CON_ALERTAS_PRIORITARIAS', 'CRITICAS' => 'Con alertas prioritarias',
                default => $this->filtroAlertas,
            };
            $activos[] = [
                'tipo' => 'alertas',
                'categoria' => 'Alertas',
                'valor' => $this->filtroAlertas,
                'label' => 'Alertas: ' . $label,
                'icono' => 'ph-bell',
                'tono' => 'red',
            ];
        } elseif ($this->filtroRapido === 'CON_ALERTAS') {
            $activos[] = [
                'tipo' => 'alertas',
                'categoria' => 'Alertas',
                'valor' => 'CON_ALERTAS',
                'label' => 'Alertas: Con alertas',
                'icono' => 'ph-bell',
                'tono' => 'red',
            ];
        }

        if (! empty($this->filtroHabitacion)) {
            $activos[] = [
                'tipo' => 'habitacion',
                'categoria' => 'Habitación',
                'valor' => $this->filtroHabitacion,
                'label' => 'Habitación: ' . $this->filtroHabitacion,
                'icono' => 'ph-bed',
                'tono' => 'blue',
            ];
        }

        if (! empty($this->filtroMedicacion)) {
            $label = match ($this->filtroMedicacion) {
                'CON_MEDICACION_PENDIENTE' => 'Con medicación pendiente',
                'CON_MEDICACION_PROGRAMADA', 'CON_MEDICACION' => 'Con medicación programada',
                'SIN_MEDICACION' => 'Sin medicación activa',
                default => $this->filtroMedicacion,
            };
            $activos[] = [
                'tipo' => 'medicacion',
                'categoria' => 'Medicación',
                'valor' => $this->filtroMedicacion,
                'label' => 'Medicación: ' . $label,
                'icono' => 'ph-pill',
                'tono' => 'violet',
            ];
        }

        if (! empty($this->filtroCuidados)) {
            $label = match ($this->filtroCuidados) {
                'CON_CUIDADOS_PENDIENTES' => 'Con cuidados pendientes',
                'CON_PLAN_ACTIVO' => 'Con plan de cuidado activo',
                'SIN_PLAN' => 'Sin plan de cuidado',
                default => $this->filtroCuidados,
            };
            $activos[] = [
                'tipo' => 'cuidados',
                'categoria' => 'Cuidados',
                'valor' => $this->filtroCuidados,
                'label' => 'Cuidados: ' . $label,
                'icono' => 'ph-heart',
                'tono' => 'green',
            ];
        }

        if ($this->filtroEstado !== 'TODOS' && ! empty($this->filtroEstado)) {
            $label = match ($this->filtroEstado) {
                'REQUIERE_ATENCION' => 'Requiere atención',
                'VIGILANCIA' => 'Vigilancia',
                'ESTABLE' => 'Estable',
                default => $this->filtroEstado,
            };
            $activos[] = [
                'tipo' => 'estado',
                'categoria' => 'Estado',
                'valor' => $this->filtroEstado,
                'label' => 'Estado: ' . $label,
                'icono' => 'ph-activity',
                'tono' => 'amber',
            ];
        }

        return $activos;
    }

    public function updatingFiltroRapido(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroTurno(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEnfermero(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatingVistaModo(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroHabitacion(): void
    {
        $this->resetPage();
    }

    public function updatingOrden(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtroEstado = 'TODOS';
        $this->filtroRapido = 'TODOS';
        $this->filtroHabitacion = '';
        $this->orden = 'NOMBRE_ASC';
        $this->resetPage();
    }

    public function seleccionarResidente(?string $codResidente): void
    {
        if (empty($codResidente)) {
            $this->cerrarPanelAhora();

            return;
        }

        $user = Auth::user();
        abort_unless($user && $user->estado === 'ACTIVO', 403, 'Sesión no activa o usuario inactivo.');
        abort_unless(
            $user->can('residentes.ver') || $user->hasRole(['ENFERMEROS', 'SUPERADMINISTRADOR']),
            403,
            'No tiene permisos para consultar residentes.'
        );

        $service = $this->getTurnoService();
        $turnoActual = $service->obtenerTurnoActivo($user);
        $esSuperAdmin = $service->esSuperAdmin($user);
        $tieneLecturaClinicaGlobal = $service->tieneLecturaClinicaGlobal($user);
        $this->esModoConsulta = ($turnoActual === null) && ! $esSuperAdmin;

        // Buscar residente por cod_residente
        $adulto = Residente::query()
            ->where('cod_residente', $codResidente)
            ->with([
                'cama.habitacion',
                'planCuidadoActivo.intervenciones',
                'alertas' => fn ($q) => $q->whereIn('estado', ['ABIERTA', 'EN_ATENCION', 'ASIGNADA', 'RECONOCIDA', 'PENDIENTE'])->orderByDesc('fecha_hora'),
            ])
            ->first();

        if (! $adulto) {
            $this->cerrarPanelAhora();
            abort(403, 'El residente solicitado no existe o no está disponible.');
        }

        $codRes = $adulto->cod_residente;
        $codResidente = $codRes;

        // Validar alcance / relación operativa
        $esPermitido = false;
        $esAsignadoAlUsuario = false;

        if ($tieneLecturaClinicaGlobal) {
            $esPermitido = true;
            $esAsignadoAlUsuario = false;
        } elseif ($turnoActual) {
            // EN_TURNO: Verificar que el residente esté asignado al usuario en el turno activo
            $esAsignadoAlUsuario = $service->esPacienteAsignado($codResidente, $user, $turnoActual->cod_turno)
                                || $service->esPacienteAsignado($codRes, $user, $turnoActual->cod_turno);
            $esPermitido = $esAsignadoAlUsuario;
        } else {
            // FUERA_DE_TURNO: Verificar si el residente está cubierto por alguna jornada de enfermería activa del sistema
            $miTurnoService = app(MiTurnoService::class);
            $jornadasActivas = $miTurnoService->resolverJornadasActivasSistema(Carbon::now());
            if ($jornadasActivas->isNotEmpty()) {
                $codJornadasActivas = $jornadasActivas->pluck('cod_jornada')->all();
                $esPermitido = AsignacionResidenteJornada::query()
                    ->whereIn('cod_jornada', $codJornadasActivas)
                    ->where(function ($q) use ($codResidente, $codRes) {
                        $q->where('cod_residente', $codResidente)
                            ->orWhere('cod_residente', $codRes);
                    })
                    ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'ASIGNADO'])
                    ->exists();
            }
            $esAsignadoAlUsuario = false;
        }

        if (! $esPermitido) {
            $this->cerrarPanelAhora();
            abort(403, 'No tiene autorización para acceder al residente indicado o no se encuentra asignado a su turno.');
        }

        $this->residente = $codResidente;
        $this->esResidenteAsignado = $esAsignadoAlUsuario;
        $this->detalleResidente = $this->construirDetalleResidente($adulto, $turnoActual);
        $this->mostrarPanelDetalle = true;
        $this->mostrarSelectorModal = false;
        $this->tieneMedicacionProgramadaPendiente = false;
        $this->cantidadMedicacionProgramadaPendiente = 0;
        $this->drawerPaso = 'resident-summary';
        $this->registroTipo = null;
        $this->registroInicial = [];
        $this->signosHistorial = [];
        $this->signosBandasObjetivo = [];
        $this->signosContextoTurno = [];
        $this->signosEvaluacion = [];
        $this->signosIntentoGuardar = false;
        $this->signosConfirmacionPendiente = false;
        $this->signosPasoConfirmacion = 'revision';
        $this->signosResultadoRegistro = [];
        $this->signosConfirmacionHuella = null;
        $this->confirmarDescarte = false;
        $this->accionDescarte = null;
        $this->dispatch('resident-directory-opened');
    }

    public function cerrarPanelDetalle(): void
    {
        if ($this->drawerPaso === 'register-form' && $this->formularioModificado()) {
            $this->solicitarDescarte('cerrar');

            return;
        }

        $this->cerrarPanelAhora();
    }

    private function cerrarPanelAhora(): void
    {
        $this->mostrarPanelDetalle = false;
        $this->mostrarSelectorModal = false;
        $this->tieneMedicacionProgramadaPendiente = false;
        $this->cantidadMedicacionProgramadaPendiente = 0;
        $this->detalleResidente = null;
        $this->residente = null;
        $this->drawerPaso = 'resident-summary';
        $this->limpiarRegistroEnPanel();
    }

    public function mostrarSelectorRegistro(): void
    {
        abort_unless($this->mostrarPanelDetalle && $this->detalleResidente && $this->puedeRegistrar(), 403);
        $this->actualizarMedicacionProgramadaPendiente($this->detalleResidente['cod_residente']);
        $this->drawerPaso = 'register-selector';
        $this->mostrarPanelDetalle = false;
        $this->mostrarSelectorModal = true;
        $this->confirmarDescarte = false;
        $this->dispatch('resident-directory-selector-opened');
    }

    public function puedeRegistrar(): bool
    {
        $usuario = auth()->user();

        return ! $this->esModoConsulta
            && $this->esResidenteAsignado
            && $usuario?->estado === 'ACTIVO'
            && ! app(RolePreviewService::class)->isActive($usuario)
            && (bool) $usuario->canAny([
                'signos_vitales.crear', 'administraciones_medicacion.crear',
                'valoraciones_dolor.crear', 'registros_ingesta.crear',
                'registros_eliminacion.crear', 'registros_movilidad.crear',
                'atenciones.crear',
            ]);
    }

    public function cerrarSelectorRegistro(): void
    {
        abort_unless($this->mostrarSelectorModal && $this->detalleResidente, 403);

        if ($this->confirmarDescarte) {
            $this->cancelarDescarte();

            return;
        }

        if ($this->drawerPaso === 'register-form' && $this->formularioModificado()) {
            $this->solicitarDescarte('cerrar');

            return;
        }

        $this->cerrarFlujoRegistro();
    }

    private function cerrarFlujoRegistro(): void
    {
        $this->limpiarRegistroEnPanel();
        $this->mostrarSelectorModal = false;
        $this->mostrarPanelDetalle = true;
        $this->drawerPaso = 'resident-summary';
        $this->dispatch('resident-directory-selector-closed');
    }

    public function abrirFormularioRegistro(string $tipo): void
    {
        abort_unless($this->mostrarSelectorModal && $this->detalleResidente && $this->drawerPaso === 'register-selector', 403);
        $codResidente = $this->detalleResidente['cod_residente'];

        match ($tipo) {
            'signos' => $this->abrirRegistrarSignos($codResidente),
            'medicacion' => $this->prepararAdministracionProgramada($codResidente),
            'dolor' => $this->abrirRegistrarDolor($codResidente),
            'alimentacion' => $this->abrirRegistrarCuidado($codResidente, 'ALIMENTACION'),
            'eliminacion' => $this->abrirRegistrarCuidado($codResidente, 'ELIMINACION'),
            'movilidad' => $this->abrirRegistrarCuidado($codResidente, 'MOVILIDAD'),
            'seguimiento' => $this->abrirRegistrarSeguimiento($codResidente),
            'procedimiento' => $this->abrirRegistrarProcedimiento($codResidente),
            default => abort(422, 'Tipo de registro no disponible.'),
        };

        $this->registroTipo = $tipo;
        $this->registroInicial = $this->estadoFormularioRegistro();
        $this->drawerPaso = 'register-form';
        $this->confirmarDescarte = false;
        $this->resetValidation();
        $this->dispatch('resident-directory-step-changed');
    }

    public function volverPanelDetalle(): void
    {
        if ($this->confirmarDescarte) {
            $this->cancelarDescarte();

            return;
        }

        if ($this->drawerPaso === 'register-form') {
            if ($this->formularioModificado()) {
                $this->solicitarDescarte('volver');

                return;
            }

            $this->limpiarRegistroEnPanel();
            $this->drawerPaso = 'register-selector';
            $this->actualizarMedicacionProgramadaPendiente($this->detalleResidente['cod_residente']);
            $this->dispatch('resident-directory-selector-opened');

            return;
        }

        if ($this->drawerPaso === 'register-selector') {
            $this->cerrarSelectorRegistro();
        }
    }

    public function cancelarDescarte(): void
    {
        $this->confirmarDescarte = false;
        $this->accionDescarte = null;
        $this->descarteCriticoConfirmado = false;
    }

    public function descartarCambios(): void
    {
        abort_unless($this->confirmarDescarte, 409);
        if ($this->lecturaCriticaSinGuardar() && ! $this->descarteCriticoConfirmado) {
            $this->descarteCriticoConfirmado = true;

            return;
        }
        $accion = $this->accionDescarte;
        $this->limpiarRegistroEnPanel();

        if ($accion === 'cerrar') {
            $this->cerrarFlujoRegistro();

            return;
        }

        $this->drawerPaso = 'register-selector';
        $this->actualizarMedicacionProgramadaPendiente($this->detalleResidente['cod_residente']);
        $this->dispatch('resident-directory-selector-opened');
    }

    private function actualizarMedicacionProgramadaPendiente(string $codResidente): void
    {
        $this->cantidadMedicacionProgramadaPendiente = auth()->user()?->can('administraciones_medicacion.crear')
            ? app(AgendaMedicacionService::class)->paraAdulto($codResidente)
                ->filter(fn (array $item) => $item['registro'] === null)->count()
            : 0;
        $this->tieneMedicacionProgramadaPendiente = $this->cantidadMedicacionProgramadaPendiente > 0;
    }

    private function solicitarDescarte(string $accion): void
    {
        $this->confirmarDescarte = true;
        $this->accionDescarte = $accion;
        $this->descarteCriticoConfirmado = false;
        $this->dispatch('resident-directory-step-changed');
    }

    public function lecturaCriticaSinGuardar(): bool
    {
        return $this->registroTipo === 'signos' && $this->signosResultadoRegistro === []
            && collect($this->signosEvaluacion['resultados'] ?? [])->contains(
                fn (array $resultado) => ($resultado['severidad'] ?? null) === 'CRITICO'
            );
    }

    private function formularioModificado(): bool
    {
        return $this->registroInicial !== $this->estadoFormularioRegistro();
    }

    private function estadoFormularioRegistro(): array
    {
        $campos = match ($this->registroTipo) {
            'signos' => ['signoSis', 'signoDia', 'signoFC', 'signoFR', 'signoTemp', 'signoSat', 'signoGlucosa', 'signoObs'],
            'medicacion' => ['medCodMed', 'medAdministrado', 'medMotivoOmision', 'medResultado', 'medFechaHoraReal', 'medDosisAdministrada', 'medObservacion', 'medOcurrenciaSeleccionada'],
            'dolor' => ['dolorFechaHora', 'dolorEva', 'dolorUbicacion', 'dolorDuracionValor', 'dolorDuracionUnidad', 'dolorDesencadenante', 'dolorIntervencion'],
            'alimentacion' => ['ingestaTipoComida', 'ingestaPorcentaje', 'ingestaCantidadMl', 'ingestaTolerancia', 'ingestaDificultadDeglucion', 'ingestaObservacion'],
            'eliminacion' => ['elimTipo', 'elimCantidadUrinaria', 'elimCaracteristicaUrinaria', 'elimContinenciaUrinaria', 'elimCantidadIntestinal', 'elimCaracteristicaIntestinal', 'elimContinenciaIntestinal', 'elimObservacion'],
            'movilidad' => ['movMarcha', 'movTraslado', 'movTipoApoyo', 'movEquilibrio', 'movFatiga', 'movRiesgoCaida', 'movObservacion'],
            'seguimiento' => ['segEstado', 'segAlimentacion', 'segMovilidad', 'segSueno', 'segIncidente', 'segRequiereMedico', 'segObs'],
            'procedimiento' => ['procTipo', 'procDetalle'],
            'alerta' => ['alertaTipo', 'alertaNivel', 'alertaMotivo'],
            default => [],
        };

        return collect($campos)->mapWithKeys(fn (string $campo) => [$campo => $this->{$campo}])->all();
    }

    private function limpiarRegistroEnPanel(): void
    {
        $campos = array_keys($this->estadoFormularioRegistro());
        if ($campos !== []) {
            $this->reset(...$campos);
        }
        $this->registroTipo = null;
        $this->registroInicial = [];
        $this->signosHistorial = [];
        $this->signosBandasObjetivo = [];
        $this->signosContextoTurno = [];
        $this->signosEvaluacion = [];
        $this->signosIntentoGuardar = false;
        $this->signosConfirmacionPendiente = false;
        $this->signosPasoConfirmacion = 'revision';
        $this->signosResultadoRegistro = [];
        $this->signosConfirmacionHuella = null;
        $this->confirmarDescarte = false;
        $this->accionDescarte = null;
        $this->modalCodResidente = null;
        $this->medOpcionesProgramadas = [];
        $this->medDetalleProgramado = [];
        $this->reset(['modalSignos', 'modalSeguimiento', 'modalMed', 'modalAlerta', 'modalCuidado', 'modalDolor', 'modalProcedimiento']);
        $this->resetValidation();
    }

    protected function construirDetalleResidente(Residente $adulto, ?Turno $turnoActual): array
    {
        $codRes = $adulto->cod_residente;
        $codResidente = $codRes;
        $usuario = Auth::user();

        // 1. IDENTIFICACIÓN
        $nombreCompleto = trim($adulto->nombres.' '.$adulto->apellido_paterno.' '.($adulto->apellido_materno ?? ''));
        if (empty($nombreCompleto)) {
            $nombreCompleto = $adulto->nombre_completo ?? 'Residente';
        }
        $edadTexto = $adulto->fecha_nacimiento ? Carbon::parse($adulto->fecha_nacimiento)->age.' años' : null;
        $documento = $adulto->ci ?? ($adulto->numero_documento ?? 'Sin documento');

        $cama = $adulto->cama;
        $hab = $cama?->habitacion;
        $numHab = $hab ? ($hab->codigo ?? $hab->nombre) : '';
        $habitacionTexto = $numHab ? (str_starts_with(strtolower($numHab), 'hab') ? $numHab : "Hab. {$numHab}") : 'Sin habitación';
        $numCama = $cama?->codigo ?? '';
        $camaTexto = $numCama ? (str_starts_with(strtolower($numCama), 'cama') ? $numCama : "Cama {$numCama}") : 'Sin cama';
        $ubicacionFormateada = $hab ? "{$habitacionTexto} · {$camaTexto}" : ($adulto->ubicacion_formateada ?: 'Ubicación no asignada');

        // Estado de seguimiento
        $alertasActivas = Alerta::query()
            ->where(function ($q) use ($codResidente, $codRes) {
                $q->where('cod_residente', $codResidente)->orWhere('cod_residente', $codRes);
            })
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION', 'ASIGNADA', 'RECONOCIDA', 'PENDIENTE'])
            ->orderByDesc('fecha_hora')
            ->get();

        $alertasCriticasCount = $alertasActivas->whereIn('prioridad', ['CRITICO', 'ALTO', 'CRITICA'])->count();

        $estadoSeguimiento = 'SIN_ALERTAS';
        $estadoColor = 'slate';
        $estadoHumano = 'Sin alertas activas';

        if ($alertasCriticasCount > 0) {
            $estadoSeguimiento = 'REQUIERE_ATENCION';
            $estadoColor = 'red';
            $estadoHumano = 'Requiere atención';
        } elseif ($alertasActivas->isNotEmpty()) {
            $estadoSeguimiento = 'VIGILANCIA';
            $estadoColor = 'amber';
            $estadoHumano = 'Vigilancia';
        }

        // Responsable actual y turno — muestra nombre real del personal asignado al residente
        $responsableTexto = 'Sin enfermero asignado';
        $turnoNombre = $turnoActual ? ($turnoActual->nombre ?? 'Turno en curso') : 'Turno actual de guardia';

        // Buscar siempre en asignaciones_residente_jornada para obtener el responsable real del residente
        $miTurnoService = app(MiTurnoService::class);
        $jornadasActivas = $miTurnoService->resolverJornadasActivasSistema(Carbon::now());
        $codJornadasActivas = $jornadasActivas->pluck('cod_jornada')->all();

        $asigQuery = AsignacionResidenteJornada::query()
            ->where(function ($q) use ($codResidente, $codRes) {
                $q->where('cod_residente', $codResidente)->orWhere('cod_residente', $codRes);
            })
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'ASIGNADO'])
            ->with(['personal', 'jornada.turno']);

        if (! empty($codJornadasActivas)) {
            $asigQuery->whereIn('cod_jornada', $codJornadasActivas);
        }

        $asigResidente = $asigQuery->orderByDesc('fecha_hora')->first();

        if ($asigResidente && $asigResidente->personal) {
            $nomEnf = trim($asigResidente->personal->nombres.' '.$asigResidente->personal->apellido_paterno);
            if (! empty($nomEnf)) {
                $responsableTexto = "A cargo de: Enf. {$nomEnf}";
            }
        }

        if ($asigResidente && $asigResidente->jornada?->turno) {
            $turnoNombre = $asigResidente->jornada->turno->nombre;
        }

        // 2. ÚLTIMOS SIGNOS VITALES (resumen del último registro real)
        $ultimoSignoModel = $usuario?->can('signos_vitales.ver')
            ? SignoVital::query()
                ->where('cod_residente', $codResidente)
                ->orderByDesc('fecha_hora')
                ->first()
            : null;

        $ultimosSignos = null;
        if ($ultimoSignoModel) {
            $fHora = Carbon::parse($ultimoSignoModel->fecha_hora);
            $fechaHoraTexto = $fHora->isToday() ? ('Hoy '.$fHora->format('H:i')) : $fHora->format('d/m/Y H:i');
            $paLimpia = '—';
            if ($ultimoSignoModel->presion_sistolica && $ultimoSignoModel->presion_diastolica) {
                $paLimpia = (int) $ultimoSignoModel->presion_sistolica.'/'.(int) $ultimoSignoModel->presion_diastolica;
            } elseif ($ultimoSignoModel->presion_arterial) {
                $paLimpia = $ultimoSignoModel->presion_arterial;
            }
            $ultimosSignos = [
                'pa' => $paLimpia,
                'fc' => $ultimoSignoModel->frecuencia_cardiaca ? ((int) $ultimoSignoModel->frecuencia_cardiaca.' lpm') : '—',
                'fr' => $ultimoSignoModel->frecuencia_respiratoria ? ((int) $ultimoSignoModel->frecuencia_respiratoria.' rpm') : '—',
                'temp' => $ultimoSignoModel->temperatura ? (round((float) $ultimoSignoModel->temperatura, 1).' °C') : '—',
                'sat' => $ultimoSignoModel->saturacion ? ((int) $ultimoSignoModel->saturacion.'%') : ($ultimoSignoModel->saturacion_oxigeno ? ((int) $ultimoSignoModel->saturacion_oxigeno.'%') : '—'),
                'glucosa' => $ultimoSignoModel->glucemia ? (int) $ultimoSignoModel->glucemia : ($ultimoSignoModel->glucosa ?: '—'),
                'fecha_hora' => $fechaHoraTexto,
                'observacion' => $ultimoSignoModel->observacion,
            ];
        }

        // 3. PRÓXIMA MEDICACIÓN (bloque muy visible)
        $agendaMeds = $usuario?->can('prescripciones.ver')
            ? app(AgendaMedicacionService::class)->paraAdulto($codResidente)
            : collect();
        $medsPendientes = $agendaMeds->filter(fn ($item) => empty($item['registro']));
        $proximaMed = null;

        if ($medsPendientes->isNotEmpty()) {
            $medPendiente = $medsPendientes->sortBy('hora')->first();
            $horaMed = $medPendiente['hora'];
            $tiempoRestante = '';
            try {
                $horaCarbon = Carbon::parse(today()->toDateString().' '.$horaMed);
                $diffMin = (int) now()->diffInMinutes($horaCarbon, false);
                if ($diffMin > 0 && $diffMin <= 60) {
                    $tiempoRestante = "En {$diffMin} min";
                } elseif ($diffMin > 60) {
                    $tiempoRestante = 'En '.round($diffMin / 60, 1).' hrs';
                } elseif ($diffMin <= 0 && $diffMin >= -60) {
                    $tiempoRestante = 'Hace '.abs($diffMin).' min';
                } else {
                    $tiempoRestante = 'Atrasada';
                }
            } catch (\Throwable $e) {
                $tiempoRestante = '';
            }

            $presc = $medPendiente['medicacion'];
            $via = $presc->via_administracion ?: 'Vía no registrada';
            if (stripos($via, 'oral') !== false) {
                $via = 'VO';
            }

            $nombreMed = $presc->medicamento?->nombre_generico ?: ($presc->nombre_medicamento ?: 'Medicamento');
            $dosisMed = $presc->dosis ? ((float) $presc->dosis == (int) $presc->dosis ? (int) $presc->dosis : (float) $presc->dosis).' '.($presc->unidad_dosis ?: '') : 'Dosis no registrada';
            $proximaMed = [
                'nombre' => $nombreMed,
                'dosis' => $dosisMed,
                'hora' => $horaMed,
                'via' => $via,
                'tiempo_restante' => $tiempoRestante,
            ];
        } elseif ($usuario?->can('prescripciones.ver')) {
            // Verificar prescripciones activas
            $prescActiva = Prescripcion::where('cod_residente', $codResidente)->whereIn('estado', ['ACTIVA', 'ACTIVO'])->where('segun_necesidad', false)->first();
            if ($prescActiva) {
                $via = $prescActiva->via_administracion ?: 'Vía no registrada';
                if (stripos($via, 'oral') !== false) {
                    $via = 'VO';
                }
                $proximaMed = [
                    'nombre' => $prescActiva->nombre_medicamento,
                    'dosis' => $prescActiva->dosis ?: 'Dosis no registrada',
                    'hora' => null,
                    'via' => $via,
                    'tiempo_restante' => 'Prescripción activa',
                ];
            }
        }

        // Próximo cuidado pendiente del plan; la medicación tiene su propia card.
        $proximoCuidado = $usuario?->can('ejecuciones_cuidado.ver')
            ? $adulto->ejecucionesCuidado()->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])
                ->where('fecha_hora_programada', '>=', now())
                ->with('intervencion')->orderBy('fecha_hora_programada')->first()
            : null;
        $proximaAtencionTexto = $proximoCuidado ? ($proximoCuidado->intervencion?->nombre ?: 'Cuidado programado') : null;
        $proximaAtencionHora = $proximoCuidado?->fecha_hora_programada?->format('d/m H:i');

        // 5. ALERTAS ACTIVAS
        $alertasList = ($usuario?->can('alertas.ver') ? $alertasActivas : collect())->map(function ($al) {
            $tiempo = 'Reportada ';
            $fAl = $al->fecha_hora ?? ($al->created_at ?? null);
            if ($fAl) {
                $c = Carbon::parse($fAl);
                if ($c->isToday()) {
                    $tiempo .= 'hoy '.$c->format('H:i');
                } elseif ($c->isYesterday()) {
                    $tiempo .= 'ayer '.$c->format('H:i');
                } else {
                    $tiempo .= $c->format('d/m H:i');
                }
            } else {
                $tiempo .= 'recientemente';
            }

            return [
                'cod_alerta' => $al->cod_alerta,
                'tipo' => $al->tipo_alerta ?? $al->tipo ?? 'Alerta clínica',
                'prioridad' => $al->prioridad ?? $al->nivel ?? 'ALTO',
                'motivo' => $al->motivo ?? $al->descripcion ?? 'Alerta activa',
                'tiempo' => $tiempo,
            ];
        })->values()->all();

        // Iniciales y Foto
        $iniciales = strtoupper(substr($adulto->nombres ?: 'A', 0, 1).substr($adulto->apellido_paterno ?: 'M', 0, 1));

        $medicacion = $usuario?->can('prescripciones.ver')
            ? $adulto->prescripciones()->whereIn('estado', ['ACTIVA', 'ACTIVO', 'VIGENTE'])
                ->with(['medicamento', 'horarios' => fn ($q) => $q->whereIn('estado', ['ACTIVO', 'ACTIVA'])])
                ->limit(8)->get()
                ->map(fn ($item) => [
                    'nombre' => $item->medicamento?->nombre_generico ?: ($item->nombre_medicamento ?: 'Medicamento registrado'),
                    'dosis' => trim((string) $item->dosis.' '.(string) $item->unidad_dosis),
                    'via' => $item->via_administracion,
                    'frecuencia' => $item->frecuencia,
                    'horarios' => $item->horarios->pluck('hora_programada')->filter()->implode(', '),
                    'ultima_administracion' => $usuario?->can('administraciones_medicacion.ver')
                        ? $item->administraciones()->orderByDesc('fecha_hora_programada')->first()?->resultado
                        : null,
                ])->all()
            : [];
        $cuidados = $usuario?->can('planes_cuidado.ver')
            ? $adulto->ejecucionesCuidado()->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])
                ->with('intervencion')->orderBy('fecha_hora_programada')->limit(8)->get()
                ->map(fn ($item) => [
                    'nombre' => $item->intervencion?->nombre ?: 'Cuidado programado',
                    'fecha' => $item->fecha_hora_programada?->format('d/m/Y H:i'),
                    'estado' => $item->estado,
                ])->all()
            : [];
        $observaciones = $usuario?->can('notas_clinicas.ver')
            ? $adulto->notasClinicas()->orderByDesc('fecha_hora')->limit(5)->get()
                ->map(fn ($item) => [
                    'contenido' => $item->contenido,
                    'fecha' => $item->fecha_hora?->format('d/m/Y H:i'),
                ])->all()
            : [];
        $historial = $usuario?->can('atenciones.ver')
            ? $adulto->atenciones()->orderByDesc('fecha_hora')->limit(6)->get()
                ->map(fn ($item) => [
                    'tipo' => $item->tipo_atencion?->nombre ?: 'Atención registrada',
                    'fecha' => $item->fecha_hora?->format('d/m/Y H:i'),
                ])->all()
            : [];

        $alergiasResumen = $usuario?->can('alergias.ver')
            ? $adulto->alergias()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->orderByDesc('fecha_hora')->limit(3)->pluck('sustancia')->filter()->implode(', ')
            : null;
        $indicacionesResumen = $usuario?->can('indicaciones_clinicas.ver')
            ? IndicacionClinica::query()->where('cod_residente', $codResidente)->whereIn('estado', ['ACTIVA', 'ACTIVO', 'VIGENTE'])->orderByDesc('fecha_hora')->limit(2)->pluck('descripcion')->filter()->map(fn ($texto) => Str::limit(trim((string) $texto), 90))->implode(' · ')
            : null;

        $seguimientoReciente = collect();
        if ($ultimoSignoModel) {
            $seguimientoReciente->push([
                'fecha_hora' => Carbon::parse($ultimoSignoModel->fecha_hora),
                'tipo' => 'Control de signos vitales',
                'dato' => 'PA '.$ultimosSignos['pa'].' · FC '.$ultimosSignos['fc'].' · SpO₂ '.$ultimosSignos['sat'],
            ]);
        }
        if ($usuario?->can('atenciones.ver')) {
            $adulto->atenciones()->whereIn('tipo_atencion', ['SEGUIMIENTO_DIARIO', 'PROCEDIMIENTO', 'CUIDADO_ENFERMERIA'])
                ->orderByDesc('fecha_hora')->limit(5)->get()
                ->each(fn ($item) => $seguimientoReciente->push([
                    'fecha_hora' => Carbon::parse($item->fecha_hora),
                    'tipo' => Str::headline((string) $item->getRawOriginal('tipo_atencion')),
                    'dato' => Str::limit(trim((string) ($item->observacion ?: $item->motivo)), 110),
                ]));
        }
        if ($usuario?->can('ejecuciones_cuidado.ver')) {
            $adulto->ejecucionesCuidado()->whereNotNull('fecha_hora_ejecucion')
                ->orderByDesc('fecha_hora_ejecucion')->limit(4)->get()
                ->each(fn ($item) => $seguimientoReciente->push([
                    'fecha_hora' => Carbon::parse($item->fecha_hora_ejecucion),
                    'tipo' => 'Cuidado realizado',
                    'dato' => Str::limit(trim((string) ($item->observacion ?: $item->resultado)), 110),
                ]));
        }
        $seguimientoReciente = $seguimientoReciente->sortByDesc('fecha_hora')->take(4)
            ->map(fn ($item) => [
                'fecha' => $item['fecha_hora']->isToday() ? 'Hoy' : $item['fecha_hora']->format('d/m'),
                'hora' => $item['fecha_hora']->format('H:i'),
                'tipo' => $item['tipo'],
                'dato' => $item['dato'],
            ])->values()->all();

        return [
            'cod_residente' => $codRes,
            'cod_residente' => $codRes,
            'nombre_completo' => $nombreCompleto,
            'edad_texto' => $edadTexto,
            'documento' => $documento,
            'habitacion_texto' => $habitacionTexto,
            'cama_texto' => $camaTexto,
            'ubicacion_formateada' => $ubicacionFormateada,
            'estado_seguimiento' => $estadoSeguimiento,
            'estado_color' => $estadoColor,
            'estado_humano' => $estadoHumano,
            'responsable_texto' => $responsableTexto,
            'turno_nombre' => $turnoNombre,
            'ultimos_signos' => $ultimosSignos,
            'proxima_medicacion' => $proximaMed,
            'proxima_atencion_texto' => $proximaAtencionTexto,
            'proxima_atencion_hora' => $proximaAtencionHora,
            'alertas_count' => count($alertasList),
            'alertas' => $alertasList,
            'iniciales' => $iniciales,
            'foto' => $adulto->foto ? asset('storage/'.$adulto->foto) : '',
            'fecha_ingreso' => $adulto->admisiones()->orderBy('fecha_hora_admision')->first()?->fecha_hora_admision,
            'plan_nombre' => $usuario?->can('planes_cuidado.ver') ? $adulto->planCuidadoActivo?->nombre : null,
            'plan_prioridad' => $usuario?->can('planes_cuidado.ver') ? $adulto->planCuidadoActivo?->prioridad : null,
            'medicacion' => $medicacion,
            'cuidados' => $cuidados,
            'observaciones' => $observaciones,
            'historial' => $historial,
            'alergias_resumen' => $alergiasResumen,
            'indicaciones_resumen' => $indicacionesResumen,
            'seguimiento_reciente' => $seguimientoReciente,
        ];
    }

    // ─── ACCIONES RÁPIDAS MODALES ───────────────────────────────────

    public function abrirRegistrarSignos(string $codResidente): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless(auth()->user()?->can('signos_vitales.crear'), 403);
        $this->getTurnoService()->autorizarAccionPaciente($codResidente);
        $this->modalCodResidente = $codResidente;
        $this->reset(['signoPA', 'signoFC', 'signoFR', 'signoTemp', 'signoSat', 'signoGlucosa', 'signoObs', 'signoConfirmarAtipico']);
        $this->reset(['signoSis', 'signoDia']);
        $this->signosEvaluacion = [];
        $camposObjetivo = [
            'presion_sistolica' => 'sis', 'presion_diastolica' => 'dia',
            'frecuencia_cardiaca' => 'fc', 'frecuencia_respiratoria' => 'fr',
            'temperatura' => 'temp', 'saturacion_oxigeno' => 'sat', 'glucemia' => 'glucosa',
        ];
        $this->signosBandasObjetivo = ObjetivoSignoVital::query()->where('cod_residente', $codResidente)
            ->where('estado', 'VIGENTE')->where('vigente_desde', '<=', now())->whereNull('vigente_hasta')
            ->whereNotNull('min_objetivo')->whereNotNull('max_objetivo')
            ->get(['parametro', 'min_objetivo', 'max_objetivo'])
            ->mapWithKeys(fn (ObjetivoSignoVital $objetivo) => isset($camposObjetivo[$objetivo->parametro])
                ? [$camposObjetivo[$objetivo->parametro] => ['min' => (float) $objetivo->min_objetivo, 'max' => (float) $objetivo->max_objetivo]] : [])
            ->all();
        $this->signosIntentoGuardar = false;
        $this->signosConfirmacionPendiente = false;
        $this->signosPasoConfirmacion = 'revision';
        $this->signosResultadoRegistro = [];
        $this->signosConfirmacionHuella = null;
        $turnoSignos = $this->getTurnoService()->obtenerTurnoActivo(auth()->user());
        $finTurnoSignos = $turnoSignos?->hora_cierre ?: $turnoSignos?->hora_fin;
        $this->signosContextoTurno = [
            'nombre' => $turnoSignos?->nombre ?? 'Sin turno activo',
            'horario' => $turnoSignos?->hora_inicio && $finTurnoSignos
                ? substr((string) $turnoSignos->hora_inicio, 0, 5).'–'.substr((string) $finTurnoSignos, 0, 5)
                : null,
        ];
        $this->signosHistorial = auth()->user()?->can('signos_vitales.ver') ? SignoVital::query()->porResidente($codResidente)->vigentes()
            ->orderByDesc('fecha_hora')->limit(30)
            ->get(['fecha_hora', 'presion_sistolica', 'presion_diastolica', 'frecuencia_cardiaca', 'frecuencia_respiratoria', 'temperatura', 'saturacion_oxigeno', 'glucemia'])
            ->map(fn (SignoVital $signo) => [
                'fecha' => $signo->fecha_hora?->timezone(config('app.timezone'))->format('d/m H:i'),
                'sis' => $signo->presion_sistolica, 'dia' => $signo->presion_diastolica,
                'fc' => $signo->frecuencia_cardiaca, 'fr' => $signo->frecuencia_respiratoria,
                'temp' => $signo->temperatura, 'sat' => $signo->saturacion_oxigeno,
                'glucosa' => $signo->glucemia,
            ])->all() : [];
        $this->modalSignos = true;
    }

    public function abrirModalSignos(string $codResidente): void
    {
        $this->abrirRegistrarSignos($codResidente);
    }

    public function updated(string $propiedad): void
    {
        if (! in_array($propiedad, ['signoSis', 'signoDia', 'signoFC', 'signoFR',
            'signoTemp', 'signoSat', 'signoGlucosa'], true)
            || ! $this->modalSignos || ! $this->modalCodResidente || $this->esModoConsulta) {
            return;
        }

        $this->signoConfirmarAtipico = false;
        $this->descarteCriticoConfirmado = false;
        $this->signosConfirmacionPendiente = false;
        $this->signosPasoConfirmacion = 'revision';
        $this->signosConfirmacionHuella = null;
        $campoError = match ($propiedad) {
            'signoSis' => 'presion_sistolica',
            'signoDia' => 'presion_diastolica',
            'signoFC' => 'frecuencia_cardiaca',
            'signoFR' => 'frecuencia_respiratoria',
            'signoTemp' => 'temperatura',
            'signoSat' => 'saturacion_oxigeno',
            'signoGlucosa' => 'glucemia',
        };
        $this->resetValidation($campoError);
        $this->resetValidation('mediciones');
        $this->resetValidation('signos_confirmacion');
        try {
            $this->signosEvaluacion = app(SignosVitalesService::class)->preEvaluar([
                'presion_sistolica' => $this->signoSis,
                'presion_diastolica' => $this->signoDia,
                'frecuencia_cardiaca' => $this->signoFC,
                'frecuencia_respiratoria' => $this->signoFR,
                'temperatura' => $this->signoTemp,
                'saturacion_oxigeno' => $this->signoSat,
                'glucemia' => $this->signoGlucosa,
            ], $this->modalCodResidente, Auth::user())->toArray();
        } catch (ValidationException $exception) {
            // Durante la escritura, la validación visible de los campos muestra
            // el error técnico; no se presenta una clasificación clínica parcial.
            $this->signosEvaluacion = [];
        }
    }

    public function guardarSignos(): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_if($this->signosResultadoRegistro !== [], 409, 'Este registro ya fue confirmado.');
        abort_if($this->mostrarSelectorModal && $this->drawerPaso !== 'register-form', 403);
        if ($this->drawerPaso === 'register-form') {
            abort_unless($this->mostrarSelectorModal && $this->registroTipo === 'signos'
                && $this->detalleResidente && $this->modalCodResidente === $this->detalleResidente['cod_residente'], 403);
            $this->signosIntentoGuardar = true;
            $this->resetValidation();
            try {
                try {
                    $evaluacionPrevia = app(SignosVitalesService::class)->preEvaluar([
                        'presion_sistolica' => $this->signoSis,
                        'presion_diastolica' => $this->signoDia,
                        'frecuencia_cardiaca' => $this->signoFC,
                        'frecuencia_respiratoria' => $this->signoFR,
                        'temperatura' => $this->signoTemp,
                        'saturacion_oxigeno' => $this->signoSat,
                        'glucemia' => $this->signoGlucosa,
                    ], $this->modalCodResidente, Auth::user());
                } catch (ValidationException) {
                    // El validador de registro devuelve los mensajes específicos
                    // de cada campo y conserva los valores para corregirlos.
                    $evaluacionPrevia = null;
                }
                $requiereConfirmacion = collect($evaluacionPrevia?->resultados ?? [])->contains(
                    fn ($resultado) => $resultado->severidad?->value === 'CRITICO'
                        || $resultado->comportamientoAlerta->value === 'AUTOMATICA_AL_CONFIRMAR'
                );
                $presionAtipica = $evaluacionPrevia !== null
                    && is_numeric($this->signoSis) && is_numeric($this->signoDia)
                    && (float) $this->signoSis <= (float) $this->signoDia;
                $huella = hash('sha256', json_encode([
                    $this->modalCodResidente, $this->signoSis, $this->signoDia, $this->signoFC,
                    $this->signoFR, $this->signoTemp, $this->signoSat, $this->signoGlucosa, $this->signoObs,
                ]));
                if (($requiereConfirmacion || $presionAtipica)
                    && (! $this->signosConfirmacionPendiente || $this->signosConfirmacionHuella !== $huella)) {
                    $this->signosEvaluacion = $evaluacionPrevia->toArray();
                    $this->signosConfirmacionPendiente = true;
                    $this->signosPasoConfirmacion = 'revision';
                    $this->signosConfirmacionHuella = $huella;
                    $this->dispatch('resident-directory-step-changed');
                    return;
                }
                if ($requiereConfirmacion && $this->signosPasoConfirmacion !== 'final') {
                    $this->signosPasoConfirmacion = 'final';
                    $this->dispatch('resident-directory-step-changed');
                    return;
                }
                $registro = app(SignosVitalesService::class)->registrarConEvaluacion($this->modalCodResidente, [
                    'presion_sistolica' => $this->signoSis,
                    'presion_diastolica' => $this->signoDia,
                    'frecuencia_cardiaca' => $this->signoFC,
                    'frecuencia_respiratoria' => $this->signoFR,
                    'temperatura' => $this->signoTemp,
                    'saturacion_oxigeno' => $this->signoSat,
                    'glucemia' => $this->signoGlucosa,
                    'observacion' => $this->signoObs,
                ], Auth::user());
            } catch (ValidationException $exception) {
                foreach ($exception->errors() as $campo => $mensajes) {
                    $this->addError($campo, $mensajes[0]);
                }
                $this->dispatch('signos-validacion-fallida');
                return;
            } catch (QueryException $exception) {
                report($exception);
                $this->addError('signos_guardado', 'No se pudo guardar el registro. Conservamos los datos para que vuelvas a intentarlo.');
                return;
            }

            $this->signosResultadoRegistro = [
                'cod_signo' => $registro->signo->cod_signo,
                'cod_alerta' => $registro->alerta?->cod_alerta,
                'hay_critico' => collect($registro->evaluacion->resultados)->contains(
                    fn ($resultado) => $resultado->severidad?->value === 'CRITICO'
                ),
                'fecha_hora' => $registro->signo->fecha_hora?->timezone(config('app.timezone'))->format('d/m/Y H:i'),
                'profesional' => Auth::user()?->name,
                'mediciones' => array_values(array_filter([
                    ['nombre' => 'Presión arterial', 'valor' => $registro->signo->presion_sistolica !== null && $registro->signo->presion_diastolica !== null
                        ? $registro->signo->presion_sistolica.'/'.$registro->signo->presion_diastolica.' mmHg' : null],
                    ['nombre' => 'Pulso', 'valor' => $registro->signo->frecuencia_cardiaca !== null ? $registro->signo->frecuencia_cardiaca.' lpm' : null],
                    ['nombre' => 'Respiración', 'valor' => $registro->signo->frecuencia_respiratoria !== null ? $registro->signo->frecuencia_respiratoria.' rpm' : null],
                    ['nombre' => 'Temperatura', 'valor' => $registro->signo->temperatura !== null ? $registro->signo->temperatura.' °C' : null],
                    ['nombre' => 'Saturación', 'valor' => $registro->signo->saturacion_oxigeno !== null ? $registro->signo->saturacion_oxigeno.' %' : null],
                    ['nombre' => 'Glucemia', 'valor' => $registro->signo->glucemia !== null ? $registro->signo->glucemia.' mg/dL' : null],
                ], fn (array $medicion) => $medicion['valor'] !== null)),
                'advertencias' => collect($registro->evaluacion->resultados)
                    ->filter(fn ($resultado) => in_array($resultado->severidad?->value, ['ADVERTENCIA', 'ALTO'], true))
                    ->map(fn ($resultado) => ['variable' => str_replace('_', ' ', $resultado->variable), 'valor' => $resultado->valor, 'unidad' => $resultado->unidad])
                    ->values()->all(),
            ];
            $this->signosConfirmacionPendiente = false;
            $this->signosPasoConfirmacion = 'revision';
            $this->drawerPaso = 'register-result';
            $this->dispatch('signos-actualizados');
            $this->dispatch('resident-directory-step-changed');

            return;
        }

        app(SignosVitalesService::class)->registrar($this->modalCodResidente, [
            'presion_arterial' => $this->signoPA,
            'frecuencia_cardiaca' => $this->signoFC,
            'frecuencia_respiratoria' => $this->signoFR,
            'temperatura' => $this->signoTemp,
            'saturacion' => $this->signoSat,
            'glucosa' => $this->signoGlucosa,
            'valor_atipico_confirmado' => $this->signoConfirmarAtipico,
            'observacion' => $this->signoObs,
        ], Auth::user());

        $this->modalSignos = false;
        $this->modalCodResidente = null;
        if ($this->residente) {
            $this->seleccionarResidente($this->residente);
        }
        $this->dispatch('signos-actualizados');
        $this->dispatch('rm-toast', ['icon' => 'success', 'title' => 'Signos registrados correctamente.']);
    }

    public function cancelarConfirmacionSignos(): void
    {
        $this->signosConfirmacionPendiente = false;
        $this->signosPasoConfirmacion = 'revision';
        $this->signosConfirmacionHuella = null;
    }

    public function volverResidenteDesdeSignos(): void
    {
        abort_unless($this->mostrarSelectorModal && $this->drawerPaso === 'register-result'
            && $this->signosResultadoRegistro !== [], 409);
        $codResidente = $this->modalCodResidente;
        $this->cerrarFlujoRegistro();
        if ($codResidente) {
            $this->seleccionarResidente($codResidente);
        }
    }

    public function abrirRegistrarSeguimiento(string $codResidente): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless(auth()->user()?->can('atenciones.crear'), 403);
        $this->getTurnoService()->autorizarAccionPaciente($codResidente);
        $this->modalCodResidente = $codResidente;
        $this->segEstado = '';
        $this->segAlimentacion = '';
        $this->segMovilidad = '';
        $this->segSueno = '';
        $this->segIncidente = false;
        $this->segRequiereMedico = false;
        $this->segObs = '';
        $this->modalSeguimiento = true;
    }

    public function abrirModalSeguimiento(string $codResidente): void
    {
        $this->abrirRegistrarSeguimiento($codResidente);
    }

    public function guardarSeguimiento(): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless(auth()->user()?->can('atenciones.crear'), 403);
        $turno = $this->getTurnoService()->autorizarMutacionPaciente($this->modalCodResidente, 'atenciones.crear', Auth::user());
        $service = $this->getTurnoService();
        $turno = $service->obtenerTurnoActivo(Auth::user());
        $this->validate([
            'segEstado' => 'required|in:ESTABLE,VIGILANCIA,DELICADO,CRITICO',
            'segAlimentacion' => 'required|in:COMPLETA,PARCIAL,RECHAZADA,AYUNO',
            'segMovilidad' => 'required|in:INDEPENDIENTE,ASISTIDA,SILLA_RUEDAS,ENCAMADO',
            'segSueno' => 'required|in:NORMAL,INTERRUMPIDO,INSOMNIO,SOMNOLENCIA',
            'segObs' => 'required|string|min:10|max:1000',
        ]);
        if (($this->segIncidente || $this->segRequiereMedico) && mb_strlen(trim($this->segObs)) < 15) {
            $this->addError('segObs', 'Describa la situación clínica y las medidas iniciales con al menos 15 caracteres.');

            return;
        }
        if (! $turno) {
            $this->addError('segObs', 'No existe un turno activo para registrar el seguimiento.');

            return;
        }
        if (Atencion::where('cod_residente', $this->modalCodResidente)->whereDate('fecha_hora', today())
            ->where('tipo_atencion', 'SEGUIMIENTO_DIARIO')->exists()) {
            $this->addError('segObs', 'Ya existe un seguimiento de este residente para el turno actual.');

            return;
        }

        $personal = Auth::user()?->personal;
        $codArea = $personal?->asignaciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->latest('fecha_asignacion')->value('cod_area');
        abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional asignados.');
        Atencion::create([
            'cod_residente' => $this->modalCodResidente,
            'cod_area' => $codArea,
            'cod_personal' => $personal->cod_personal,
            'tipo_atencion' => 'SEGUIMIENTO_DIARIO',
            'motivo' => $this->segEstado,
            'fecha_hora' => now(),
            'estado' => 'FINALIZADA',
            'observacion' => trim($this->segObs),
        ]);

        $this->modalSeguimiento = false;
        $this->modalCodResidente = null;
        if ($this->residente) {
            $this->seleccionarResidente($this->residente);
        }
        $this->dispatch('seguimiento-guardado');
        $this->dispatch('rm-toast', ['icon' => 'success', 'title' => 'Seguimiento registrado correctamente.']);
    }

    public function abrirAdministrarMed(string $codResidente): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless(auth()->user()?->can('administraciones_medicacion.crear'), 403);
        $this->getTurnoService()->autorizarAccionPaciente($codResidente);
        $this->modalCodResidente = $codResidente;
        $this->medicacionesPaciente = Prescripcion::where('cod_residente', $codResidente)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO', 'VIGENTE'])
            ->where('segun_necesidad', false)
            ->get();
        $primerMed = $this->medicacionesPaciente->first();
        $this->medCodMed = $primerMed?->cod_prescripcion ?? '';
        $this->medAdministrado = true;
        $this->medMotivoOmision = '';
        $this->modalMed = true;
    }

    private function prepararAdministracionProgramada(string $codResidente): void
    {
        abort_if($this->esModoConsulta, 403, 'No tiene un turno activo para registrar medicación.');
        $this->getTurnoService()->autorizarMutacionPaciente($codResidente, 'administraciones_medicacion.crear', Auth::user());
        $this->modalCodResidente = $codResidente;
        $this->medOpcionesProgramadas = app(AgendaMedicacionService::class)->paraAdulto($codResidente)
            ->filter(fn (array $item) => $item['registro'] === null)
            ->map(fn (array $item) => [
                'cod_horario' => $item['horario']->cod_horario_prescripcion,
                'medicamento' => $item['medicacion']->medicamento?->nombre_comercial
                    ?: $item['medicacion']->medicamento?->nombre_generico,
                'hora' => $item['hora'],
                'dosis' => $item['horario']->dosis_programada ?? $item['medicacion']->dosis,
                'unidad' => $item['medicacion']->unidad_dosis,
            ])->values()->all();
        abort_if($this->medOpcionesProgramadas === [], 422, 'No hay dosis programadas pendientes para este residente.');
        $this->medOcurrenciaSeleccionada = null;
        $this->medDetalleProgramado = [];
        $this->medCodMed = null;
        $this->medResultado = 'ADMINISTRADA';
        $this->medFechaHoraReal = now()->format('Y-m-d\TH:i');
        $this->medDosisAdministrada = '';
        $this->medMotivoOmision = '';
        $this->medObservacion = '';
        $this->modalMed = false;
    }

    public function seleccionarOcurrenciaMed(string $codHorario): void
    {
        abort_unless($this->mostrarSelectorModal && $this->drawerPaso === 'register-form'
            && $this->registroTipo === 'medicacion' && $this->detalleResidente
            && $this->modalCodResidente === $this->detalleResidente['cod_residente'], 403);
        $this->getTurnoService()->autorizarMutacionPaciente($this->modalCodResidente, 'administraciones_medicacion.crear', Auth::user());
        $ocurrencia = app(AgendaMedicacionService::class)->paraAdulto($this->modalCodResidente)
            ->first(fn (array $item) => $item['registro'] === null
                && $item['horario']->cod_horario_prescripcion === $codHorario);
        abort_unless($ocurrencia, 422, 'La dosis programada no está disponible para este residente.');

        $prescripcion = $ocurrencia['medicacion'];
        $medicamento = $prescripcion->medicamento;
        $this->medOcurrenciaSeleccionada = $codHorario;
        $this->medCodMed = $prescripcion->cod_prescripcion;
        $this->medDetalleProgramado = [
            'medicamento' => $medicamento?->nombre_comercial ?: $medicamento?->nombre_generico,
            'presentacion' => trim(($medicamento?->concentracion ?? '').' · '.($medicamento?->forma_farmaceutica ?? ''), ' ·'),
            'dosis_prescrita' => $prescripcion->dosis,
            'dosis_programada' => $ocurrencia['horario']->dosis_programada,
            'unidad' => $prescripcion->unidad_dosis,
            'via' => $prescripcion->via_administracion,
            'frecuencia' => $prescripcion->frecuencia,
            'hora' => $ocurrencia['hora'],
        ];
        $this->medDosisAdministrada = (string) ($ocurrencia['horario']->dosis_programada ?? $prescripcion->dosis ?? '');
        $this->medFechaHoraReal = now()->format('Y-m-d\TH:i');
        $this->medResultado = 'ADMINISTRADA';
        $this->medMotivoOmision = '';
        $this->resetValidation();
    }

    public function updatedMedResultado(string $valor): void
    {
        if ($valor === 'ADMINISTRADA') {
            $this->medMotivoOmision = '';
            $this->medFechaHoraReal = now()->format('Y-m-d\TH:i');
        } elseif ($valor === 'OMITIDA') {
            $this->medFechaHoraReal = '';
        }
    }

    public function abrirModalMed(string $codResidente): void
    {
        $this->abrirAdministrarMed($codResidente);
    }

    public function guardarMed(): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_if($this->mostrarSelectorModal && $this->drawerPaso !== 'register-form', 403);
        if ($this->drawerPaso === 'register-form') {
            abort_unless($this->mostrarSelectorModal && $this->registroTipo === 'medicacion'
                && $this->detalleResidente && $this->modalCodResidente === $this->detalleResidente['cod_residente'], 403);
            $this->getTurnoService()->autorizarMutacionPaciente($this->modalCodResidente, 'administraciones_medicacion.crear', Auth::user());
            $this->medMotivoOmision = trim($this->medMotivoOmision);
            $this->medObservacion = trim($this->medObservacion);
            $this->validate([
                'medOcurrenciaSeleccionada' => 'required|string',
                'medResultado' => 'required|in:ADMINISTRADA,OMITIDA',
                'medFechaHoraReal' => $this->medResultado === 'ADMINISTRADA' ? 'required|date_format:Y-m-d\TH:i' : 'nullable',
                'medDosisAdministrada' => $this->medResultado === 'ADMINISTRADA'
                    ? 'required|numeric|decimal:0,3|between:0.001,9999999.999' : 'nullable',
                'medMotivoOmision' => $this->medResultado === 'OMITIDA'
                    ? 'required|string|min:5|max:500' : 'nullable',
                'medObservacion' => 'nullable|string|max:2000',
            ], [
                'medOcurrenciaSeleccionada.required' => 'Selecciona una dosis programada.',
                'medResultado.in' => 'Selecciona un estado válido.',
                'medFechaHoraReal.required' => 'Ingresa la fecha y hora de administración.',
                'medFechaHoraReal.date_format' => 'Ingresa una fecha y hora de administración válidas.',
                'medDosisAdministrada.required' => 'Ingresa la dosis administrada.',
                'medDosisAdministrada.numeric' => 'Ingresa una dosis numérica válida.',
                'medDosisAdministrada.decimal' => 'La dosis admite como máximo tres decimales.',
                'medDosisAdministrada.between' => 'La dosis debe ser mayor que cero y caber en la BDD.',
                'medMotivoOmision.required' => 'Indica el motivo de la no administración.',
                'medMotivoOmision.min' => 'El motivo debe tener al menos 5 caracteres.',
                'medObservacion.max' => 'Las observaciones no pueden superar 2000 caracteres.',
            ]);
            $ocurrencia = app(AgendaMedicacionService::class)->paraAdulto($this->modalCodResidente)
                ->first(fn (array $item) => $item['registro'] === null
                    && $item['horario']->cod_horario_prescripcion === $this->medOcurrenciaSeleccionada
                    && $item['medicacion']->cod_prescripcion === $this->medCodMed);
            if (! $ocurrencia) {
                $this->addError('medOcurrenciaSeleccionada', 'La dosis ya no está pendiente o no pertenece a este residente.');
                return;
            }
            if ($this->medResultado === 'ADMINISTRADA') {
                $momento = Carbon::createFromFormat('!Y-m-d\TH:i', $this->medFechaHoraReal, config('app.timezone'));
                if ($momento->toDateString() !== now()->toDateString() || $momento->isFuture()) {
                    $this->addError('medFechaHoraReal', 'La administración debe registrarse con una fecha y hora válida de hoy.');
                    return;
                }
            }
            app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
                Auth::user(), $this->modalCodResidente, $ocurrencia['medicacion']->cod_prescripcion,
                $ocurrencia['hora'], $this->medResultado === 'ADMINISTRADA',
                $this->medResultado === 'OMITIDA' ? $this->medMotivoOmision : null,
                $this->medObservacion, null,
                $this->medResultado === 'ADMINISTRADA' ? $this->medDosisAdministrada : null,
                null, $this->medResultado === 'ADMINISTRADA' ? $momento->format('Y-m-d H:i') : null,
                $this->medOcurrenciaSeleccionada,
            );
            $this->cerrarFlujoRegistro();
            if ($this->residente) {
                $this->seleccionarResidente($this->residente);
            }
            $this->dispatch('medicacion-registrada');
            $this->dispatch('rm-toast', ['icon' => 'success', 'title' => 'Medicación registrada en el expediente.']);
            return;
        }
        abort_unless(auth()->user()?->can('administraciones_medicacion.crear'), 403);
        $this->getTurnoService()->autorizarMutacionPaciente($this->modalCodResidente, 'administraciones_medicacion.crear', Auth::user());
        $this->validate([
            'medCodMed' => 'required|exists:prescripciones,cod_prescripcion',
            'medAdministrado' => 'boolean',
            'medMotivoOmision' => $this->medAdministrado ? 'nullable|string|max:500' : 'required|string|min:5|max:500',
        ], [
            'medCodMed.required' => 'Seleccione un medicamento activo.',
            'medMotivoOmision.required' => 'Indique el motivo por el cual se omitió la dosis.',
            'medMotivoOmision.min' => 'El motivo de omisión debe tener al menos 5 caracteres.',
        ]);

        $ocurrencia = app(AgendaMedicacionService::class)->paraAdulto($this->modalCodResidente)
            ->first(fn (array $item) => $item['medicacion']->cod_prescripcion === $this->medCodMed && $item['registro'] === null);
        if (! $ocurrencia) {
            $this->addError('medCodMed', 'No existe una dosis programada pendiente para este medicamento hoy.');

            return;
        }
        app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
            Auth::user(), $this->modalCodResidente, $this->medCodMed, $ocurrencia['hora'],
            (bool) $this->medAdministrado, $this->medMotivoOmision, 'Registrado desde Mis Pacientes.'
        );

        $this->modalMed = false;
        $this->modalCodResidente = null;
        if ($this->residente) {
            $this->seleccionarResidente($this->residente);
        }
        $this->dispatch('medicacion-registrada');
        $this->dispatch('rm-toast', ['icon' => 'success', 'title' => 'Medicación registrada en el expediente.']);
    }

    public function abrirReportarAlerta(string $codResidente): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless(auth()->user()?->can('alertas.gestionar'), 403);
        $this->getTurnoService()->autorizarAccionPaciente($codResidente);
        $this->modalCodResidente = $codResidente;
        $this->alertaTipo = 'INCIDENTE';
        $this->alertaNivel = 'ALTO';
        $this->alertaMotivo = '';
        $this->modalAlerta = true;
    }

    public function abrirModalAlerta(string $codResidente): void
    {
        $this->abrirReportarAlerta($codResidente);
    }

    public function guardarAlerta(): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        app(AlertasService::class)->crear($this->modalCodResidente, [
            'origen' => 'INCIDENTE', 'tipo_alerta' => $this->alertaTipo,
            'nivel' => $this->alertaNivel, 'motivo' => $this->alertaMotivo,
        ], Auth::user());

        $this->modalAlerta = false;
        $this->modalCodResidente = null;
        if ($this->residente) {
            $this->seleccionarResidente($this->residente);
        }
        $this->dispatch('alerta-creada');
        $this->dispatch('rm-toast', ['icon' => 'warning', 'title' => 'Alerta enviada al monitor del turno.']);
    }

    public function abrirRegistrarCuidado(string $codResidente, string $tipo = 'HIGIENE', string $subtipo = 'GENERAL'): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        $permiso = match (strtoupper(trim($tipo))) {
            'ALIMENTACION' => 'registros_ingesta.crear',
            'ELIMINACION' => 'registros_eliminacion.crear',
            'MOVILIDAD' => 'registros_movilidad.crear',
            default => 'atenciones.crear',
        };
        abort_unless(auth()->user()?->can($permiso), 403);
        $this->getTurnoService()->autorizarAccionPaciente($codResidente);
        $this->modalCodResidente = $codResidente;
        $tipoUpper = strtoupper(trim($tipo));
        $this->cuidadoTipo = in_array($tipoUpper, ['HIGIENE', 'ALIMENTACION', 'MOVILIDAD', 'ELIMINACION', 'PIEL'], true) ? $tipoUpper : 'HIGIENE';
        $this->cuidadoSubtipo = $subtipo ?: 'GENERAL';
        $this->cuidadoObs = '';
        if ($this->cuidadoTipo === 'ALIMENTACION') {
            $this->reset(['ingestaTipoComida', 'ingestaPorcentaje', 'ingestaCantidadMl', 'ingestaTolerancia', 'ingestaDificultadDeglucion', 'ingestaObservacion']);
        }
        if ($this->cuidadoTipo === 'ELIMINACION') {
            $this->reset(['elimTipo', 'elimCantidadUrinaria', 'elimCaracteristicaUrinaria', 'elimContinenciaUrinaria', 'elimCantidadIntestinal', 'elimCaracteristicaIntestinal', 'elimContinenciaIntestinal', 'elimObservacion']);
        }
        if ($this->cuidadoTipo === 'MOVILIDAD') {
            $this->reset(['movMarcha', 'movTraslado', 'movTipoApoyo', 'movEquilibrio', 'movFatiga', 'movRiesgoCaida', 'movObservacion']);
        }
        $this->modalCuidado = true;
    }

    public function updatedElimTipo(): void
    {
        $this->reset(['elimCantidadUrinaria', 'elimCaracteristicaUrinaria', 'elimContinenciaUrinaria', 'elimCantidadIntestinal', 'elimCaracteristicaIntestinal', 'elimContinenciaIntestinal']);
        $this->resetValidation();
    }

    public function guardarCuidado(): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        if ($this->drawerPaso === 'register-form' && $this->registroTipo === 'alimentacion') {
            abort_unless($this->mostrarSelectorModal && $this->detalleResidente
                && $this->modalCodResidente === $this->detalleResidente['cod_residente'], 403);
            app(CuidadosEnfermeriaService::class)->registrarAlimentacion($this->modalCodResidente, [
                'tipo_comida' => $this->ingestaTipoComida,
                'porcentaje_consumido' => $this->ingestaPorcentaje === '' ? null : $this->ingestaPorcentaje,
                'cantidad_ml' => $this->ingestaCantidadMl === '' ? null : $this->ingestaCantidadMl,
                'tolerancia' => $this->ingestaTolerancia === '' ? null : $this->ingestaTolerancia,
                'dificultad_deglucion' => $this->ingestaDificultadDeglucion,
                'observacion' => $this->ingestaObservacion,
            ], Auth::user());
            $this->cerrarFlujoRegistro();
            if ($this->residente) {
                $this->seleccionarResidente($this->residente);
            }
            $this->dispatch('cuidado-registrado');
            $this->dispatch('rm-toast', ['icon' => 'success', 'title' => 'Cuidado de alimentación guardado.']);

            return;
        }
        if ($this->drawerPaso === 'register-form' && $this->registroTipo === 'eliminacion') {
            abort_unless($this->mostrarSelectorModal && $this->detalleResidente
                && $this->modalCodResidente === $this->detalleResidente['cod_residente'], 403);
            $esUrinaria = $this->elimTipo === 'URINARIA';
            app(CuidadosEnfermeriaService::class)->registrarEliminacion($this->modalCodResidente, [
                'tipo_eliminacion' => $this->elimTipo,
                'cantidad' => $esUrinaria ? $this->elimCantidadUrinaria : $this->elimCantidadIntestinal,
                'caracteristica' => $esUrinaria ? $this->elimCaracteristicaUrinaria : $this->elimCaracteristicaIntestinal,
                'continencia' => $esUrinaria ? $this->elimContinenciaUrinaria : $this->elimContinenciaIntestinal,
                'observacion' => $this->elimObservacion,
            ], Auth::user());
            $this->cerrarFlujoRegistro();
            if ($this->residente) {
                $this->seleccionarResidente($this->residente);
            }
            $this->dispatch('cuidado-registrado');
            $this->dispatch('rm-toast', ['icon' => 'success', 'title' => 'Cuidado de eliminación guardado.']);

            return;
        }
        if ($this->drawerPaso === 'register-form' && $this->registroTipo === 'movilidad') {
            abort_unless($this->mostrarSelectorModal && $this->detalleResidente
                && $this->modalCodResidente === $this->detalleResidente['cod_residente'], 403);
            app(CuidadosEnfermeriaService::class)->registrarMovilidad($this->modalCodResidente, [
                'marcha' => $this->movMarcha,
                'traslado' => $this->movTraslado === '' ? null : $this->movTraslado,
                'tipo_apoyo' => $this->movTipoApoyo === '' ? null : $this->movTipoApoyo,
                'equilibrio' => $this->movEquilibrio === '' ? null : $this->movEquilibrio,
                'fatiga' => $this->movFatiga === '' ? null : $this->movFatiga,
                'riesgo_caida' => $this->movRiesgoCaida === '' ? null : $this->movRiesgoCaida,
                'observacion' => $this->movObservacion,
            ], Auth::user());
            $this->cerrarFlujoRegistro();
            if ($this->residente) {
                $this->seleccionarResidente($this->residente);
            }
            $this->dispatch('cuidado-registrado');
            $this->dispatch('rm-toast', ['icon' => 'success', 'title' => 'Cuidado de movilidad guardado.']);

            return;
        }
        abort_unless(auth()->user()?->can('atenciones.crear'), 403);
        $this->validate([
            'cuidadoTipo' => 'required|in:HIGIENE,ALIMENTACION,MOVILIDAD,ELIMINACION,PIEL',
            'cuidadoSubtipo' => 'required|string|max:50',
            'cuidadoObs' => 'nullable|string|max:2000',
        ]);

        app(CuidadosEnfermeriaService::class)->registrar($this->modalCodResidente, [
            'tipo' => $this->cuidadoTipo === 'PIEL' ? 'HIGIENE' : $this->cuidadoTipo,
            'subtipo' => $this->cuidadoSubtipo,
            'observacion' => $this->cuidadoObs ?: 'Cuidado de enfermería registrado desde Mis Pacientes.',
        ], Auth::user());

        $this->modalCuidado = false;
        $this->modalCodResidente = null;
        if ($this->residente) {
            $this->seleccionarResidente($this->residente);
        }
        $this->dispatch('cuidado-registrado');
        $this->dispatch('rm-toast', ['icon' => 'success', 'title' => 'Cuidado registrado correctamente.']);
    }

    public function abrirRegistrarDolor(string $codResidente, int $intensidad = 5): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless(auth()->user()?->can('valoraciones_dolor.crear'), 403);
        $this->getTurnoService()->autorizarAccionPaciente($codResidente);
        $this->modalCodResidente = $codResidente;
        $this->dolorIntensidad = max(0, min(10, $intensidad));
        $this->dolorDetalle = '';
        $this->dolorFechaHora = now()->format('Y-m-d\TH:i');
        $this->dolorEva = '';
        $this->dolorUbicacion = '';
        $this->dolorDuracionValor = '';
        $this->dolorDuracionUnidad = '';
        $this->dolorDesencadenante = '';
        $this->dolorIntervencion = '';
        $this->modalDolor = true;
    }

    public function guardarDolor(): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_if($this->mostrarSelectorModal && $this->drawerPaso !== 'register-form', 403);
        if ($this->drawerPaso === 'register-form') {
            abort_unless($this->mostrarSelectorModal && $this->registroTipo === 'dolor'
                && $this->detalleResidente && $this->modalCodResidente === $this->detalleResidente['cod_residente'], 403);
            app(CuidadosEnfermeriaService::class)->registrarValoracionDolor($this->modalCodResidente, [
                'fecha_hora' => $this->dolorFechaHora,
                'intensidad' => $this->dolorEva,
                'ubicacion' => $this->dolorUbicacion,
                'duracion_valor' => $this->dolorDuracionValor,
                'duracion_unidad' => $this->dolorDuracionUnidad,
                'desencadenante' => $this->dolorDesencadenante,
                'intervencion' => $this->dolorIntervencion,
            ], Auth::user());
            $this->cerrarFlujoRegistro();
            if ($this->residente) {
                $this->seleccionarResidente($this->residente);
            }
            $this->dispatch('dolor-registrado');
            $this->dispatch('rm-toast', ['icon' => 'success', 'title' => 'Valoración del dolor guardada.']);

            return;
        }
        abort_unless(auth()->user()?->can('atenciones.crear'), 403);
        $this->validate([
            'dolorIntensidad' => 'required|integer|min:0|max:10',
            'dolorDetalle' => 'required|string|min:5|max:1000',
        ]);

        app(CuidadosEnfermeriaService::class)->registrarDolor(
            $this->modalCodResidente,
            'VALORACION',
            $this->dolorIntensidad,
            $this->dolorDetalle,
            Auth::user()
        );

        $this->modalDolor = false;
        $this->modalCodResidente = null;
        if ($this->residente) {
            $this->seleccionarResidente($this->residente);
        }
        $this->dispatch('dolor-registrado');
        $this->dispatch('rm-toast', ['icon' => 'success', 'title' => 'Valoración del dolor guardada.']);
    }

    public function abrirRegistrarProcedimiento(string $codResidente, string $tipo = 'CURACION'): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless(auth()->user()?->can('atenciones.crear'), 403);
        $this->getTurnoService()->autorizarAccionPaciente($codResidente);
        $this->modalCodResidente = $codResidente;
        $tipoUpper = strtoupper(trim($tipo));
        $this->procTipo = in_array($tipoUpper, ['CURACION', 'SONDA', 'CATETER', 'OXIGENO', 'OTRO'], true) ? $tipoUpper : 'CURACION';
        $this->procDetalle = '';
        $this->modalProcedimiento = true;
    }

    public function guardarProcedimiento(): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless(auth()->user()?->can('atenciones.crear'), 403);
        $this->validate([
            'procTipo' => 'required|in:CURACION,SONDA,CATETER,OXIGENO,OTRO',
            'procDetalle' => 'required|string|min:5|max:2000',
        ]);

        app(CuidadosEnfermeriaService::class)->registrar($this->modalCodResidente, [
            'tipo' => 'PROCEDIMIENTO',
            'subtipo' => $this->procTipo,
            'observacion' => $this->procDetalle,
        ], Auth::user());

        $this->modalProcedimiento = false;
        $this->modalCodResidente = null;
        if ($this->residente) {
            $this->seleccionarResidente($this->residente);
        }
        $this->dispatch('procedimiento-registrado');
        $this->dispatch('rm-toast', ['icon' => 'success', 'title' => 'Procedimiento registrado correctamente.']);
    }

    // ─── RENDER ─────────────────────────────────────────────────────

    public function render()
    {
        $fechaHoy = Carbon::now()->toDateString();
        $service = $this->getTurnoService();
        $user = Auth::user();
        $esSuperAdmin = $service->esSuperAdmin($user);
        $tieneLecturaClinicaGlobal = $service->tieneLecturaClinicaGlobal($user);
        $turnoActual = $service->obtenerTurnoActivo($user);

        // Asegurar que enfermero estándar no pueda burlar el filtro
        $enfermeroEfectivo = $tieneLecturaClinicaGlobal ? $this->filtroEnfermero : (string) $user?->cod_usuario;
        $turnoEfectivo = $this->filtroTurno ?: null;

        $errorCarga = false;
        try {
        $pacientesQuery = $service->obtenerPacientesAsignadosQuery(
            $user,
            $turnoEfectivo,
            $enfermeroEfectivo ?: null
        )->with([
            'cama.habitacion',
            'ocupacionActiva.cama.habitacion',
            'asignacionesTurno' => function ($q) {
                $q->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                    ->with(['jornada.turno', 'personal.usuario']);
            },
            'planCuidadoActivo',
        ])->withCount([
            'alertas as alertas_activas_count' => function ($q) {
                $q->whereIn('estado', ['ABIERTA', 'EN_ATENCION']);
            },
            'alertas as alertas_criticas_count' => function ($q) {
                $q->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->whereIn('prioridad', ['CRITICO', 'ALTO', 'CRITICA']);
            },
            'tareasActuales as tareas_pendientes_count' => function ($q) use ($fechaHoy) {
                $q->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])
                    ->whereDate('fecha_hora_programada', $fechaHoy);
            },
            'tareasActuales as tareas_vencidas_count' => function ($q) use ($fechaHoy) {
                $q->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])
                    ->whereDate('fecha_hora_programada', '<', $fechaHoy);
            },
            'administracionesMedicacion as medicacion_hoy_count' => function ($q) use ($fechaHoy) {
                $q->whereDate('fecha_hora_programada', $fechaHoy)->whereIn('resultado', ['ADMINISTRADA', 'ADMINISTRADO']);
            },
            'seguimientosDiarios as seguimientos_hoy_count' => function ($q) use ($fechaHoy) {
                $q->whereDate('fecha_hora', $fechaHoy);
            },
            'signosVitales as signos_hoy_count' => function ($q) use ($fechaHoy) {
                $q->whereDate('fecha_hora', $fechaHoy);
            },
        ]);

        if (trim($this->search) !== '') {
            foreach (preg_split('/\s+/', trim($this->search)) as $termino) {
                $pacientesQuery->where(function ($q) use ($termino) {
                    $q->whereLike('nombres', "%{$termino}%")
                        ->orWhereLike('apellido_paterno', "%{$termino}%")
                        ->orWhereLike('apellido_materno', "%{$termino}%")
                        ->orWhereLike('numero_documento', "%{$termino}%")
                        ->orWhereLike('cod_residente', "%{$termino}%")
                        ->orWhereHas('cama', function ($cq) use ($termino) {
                            $cq->whereLike('codigo', "%{$termino}%")
                                ->orWhereHas('habitacion', function ($hq) use ($termino) {
                                    $hq->whereLike('nombre', "%{$termino}%")
                                        ->orWhereLike('codigo', "%{$termino}%");
                                });
                    });
                });
            }
        }

        // Filtro Alertas estructurado
        if ($this->filtroAlertas === 'CON_ALERTAS' || $this->filtroRapido === 'CON_ALERTAS') {
            $pacientesQuery->whereHas('alertas', fn ($q) => $q->whereIn('estado', ['ABIERTA', 'EN_ATENCION']));
        } elseif ($this->filtroAlertas === 'SIN_ALERTAS') {
            $pacientesQuery->whereDoesntHave('alertas', fn ($q) => $q->whereIn('estado', ['ABIERTA', 'EN_ATENCION']));
        } elseif ($this->filtroAlertas === 'CON_ALERTAS_PRIORITARIAS' || $this->filtroAlertas === 'CRITICAS') {
            $pacientesQuery->whereHas('alertas', fn ($q) => $q->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->whereIn('prioridad', ['CRITICO', 'ALTO', 'CRITICA']));
        }

        // Filtro Medicación estructurado
        if ($this->filtroMedicacion === 'CON_MEDICACION_PENDIENTE') {
            $pacientesQuery->whereHas('medicaciones', function ($pq) use ($fechaHoy) {
                $pq->where('estado', 'ACTIVA')
                    ->whereDoesntHave('administracionesMedicacion', function ($aq) use ($fechaHoy) {
                        $aq->whereDate('fecha_hora_programada', $fechaHoy)
                            ->whereIn('resultado', ['ADMINISTRADA', 'ADMINISTRADO']);
                    });
            });
        } elseif ($this->filtroMedicacion === 'CON_MEDICACION_PROGRAMADA' || $this->filtroMedicacion === 'CON_MEDICACION') {
            $pacientesQuery->whereHas('medicaciones', fn ($pq) => $pq->where('estado', 'ACTIVA'));
        } elseif ($this->filtroMedicacion === 'SIN_MEDICACION') {
            $pacientesQuery->whereDoesntHave('medicaciones', fn ($pq) => $pq->where('estado', 'ACTIVA'));
        }

        // Filtro Cuidados estructurado
        if ($this->filtroCuidados === 'CON_CUIDADOS_PENDIENTES') {
            $pacientesQuery->whereHas('ejecucionesCuidado', function ($eq) use ($fechaHoy) {
                $eq->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])
                    ->whereDate('fecha_hora_programada', '<=', $fechaHoy);
            });
        } elseif ($this->filtroCuidados === 'CON_PLAN_ACTIVO') {
            $pacientesQuery->whereHas('planCuidadoActivo');
        } elseif ($this->filtroCuidados === 'SIN_PLAN') {
            $pacientesQuery->whereDoesntHave('planCuidadoActivo');
        }

        if ($this->filtroHabitacion !== '') {
            $pacientesQuery->whereHas('cama.habitacion', fn ($q) => $q->where('codigo', $this->filtroHabitacion));
        }

        // Filtro por estado clínico: TODOS / ESTABLE / VIGILANCIA / REQUIERE_ATENCION
        if ($this->filtroEstado === 'REQUIERE_ATENCION') {
            $pacientesQuery->where(function ($q) {
                $q->whereHas('alertas', function ($aq) {
                    $aq->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
                        ->whereIn('prioridad', ['CRITICO', 'ALTO', 'CRITICA']);
                });
            });
        } elseif ($this->filtroEstado === 'VIGILANCIA') {
            $pacientesQuery->whereDoesntHave('alertas', function ($aq) {
                $aq->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
                    ->whereIn('prioridad', ['CRITICO', 'ALTO', 'CRITICA']);
            })->where(function ($q) use ($fechaHoy) {
                $q->whereHas('alertas', fn ($aq) => $aq->whereIn('estado', ['ABIERTA', 'EN_ATENCION']))
                    ->orWhereHas('tareasActuales', fn ($tq) => $tq->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])->whereDate('fecha_hora_programada', '<', $fechaHoy))
                    ->orWhereDoesntHave('signosVitales', fn ($vq) => $vq->whereDate('fecha_hora', $fechaHoy))
                    ->orWhereDoesntHave('seguimientosDiarios', fn ($sq) => $sq->whereDate('fecha_hora', $fechaHoy));
            });
        } elseif ($this->filtroEstado === 'ESTABLE') {
            $pacientesQuery->whereDoesntHave('alertas', fn ($aq) => $aq->whereIn('estado', ['ABIERTA', 'EN_ATENCION']))
                ->whereDoesntHave('tareasActuales', fn ($tq) => $tq->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])->whereDate('fecha_hora_programada', '<', $fechaHoy))
                ->whereHas('signosVitales', fn ($vq) => $vq->whereDate('fecha_hora', $fechaHoy))
                ->whereHas('seguimientosDiarios', fn ($sq) => $sq->whereDate('fecha_hora', $fechaHoy));
        } elseif ($this->filtroEstado !== 'TODOS') {
            $pacientesQuery->where('estado', $this->filtroEstado);
        }

        // Filtros rápidos adicionales
        if ($this->filtroRapido === 'CON_TAREAS') {
            $pacientesQuery->whereHas('tareasActuales', fn ($q) => $q->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])->whereDate('fecha_hora_programada', '<=', $fechaHoy));
        } elseif ($this->filtroRapido === 'CON_ALERTAS') {
            $pacientesQuery->whereHas('alertas', fn ($q) => $q->whereIn('estado', ['ABIERTA', 'EN_ATENCION']));
        } elseif ($this->filtroRapido === 'CON_SIGNOS_PENDIENTES') {
            $pacientesQuery->whereDoesntHave('signosVitales', fn ($vq) => $vq->whereDate('fecha_hora', $fechaHoy));
        } elseif ($this->filtroRapido === 'SIN_SEGUIMIENTO') {
            $pacientesQuery->whereDoesntHave('seguimientosDiarios', fn ($sq) => $sq->whereDate('fecha_hora', $fechaHoy));
        }

        // Obtener pacientes paginados
        if (in_array($this->orden, ['HAB_ASC', 'HAB_DESC'], true)) {
            $direccion = $this->orden === 'HAB_DESC' ? 'desc' : 'asc';
            $pacientesQuery->orderBy(
                \App\Models\Habitacion::query()
                    ->select('habitaciones.codigo')
                    ->join('camas', 'camas.cod_habitacion', '=', 'habitaciones.cod_habitacion')
                    ->join('ocupaciones_cama', 'ocupaciones_cama.cod_cama', '=', 'camas.cod_cama')
                    ->whereColumn('ocupaciones_cama.cod_residente', 'residentes.cod_residente')
                    ->whereIn('ocupaciones_cama.estado', ['ACTIVA', 'ACTIVO'])
                    ->limit(1),
                $direccion
            );
        }
        $pacientesQuery->orderBy('nombres', $this->orden === 'NOMBRE_DESC' ? 'desc' : 'asc');
        $pacientes = $pacientesQuery->paginate(12);

        // Pre-cargar datos detallados de los pacientes paginados (signos recientes, medicación pendiente)
        $codResidentes = $pacientes->pluck('cod_residente')->filter()->values();

        $ultimosSignos = collect();
        $medsActivas = collect();
        $adminMedsHoy = collect();
        $seguimientosHoy = collect();
        $proximasAtenciones = collect();
        $proximosCuidados = collect();
        $proximasMedicaciones = collect();

        if ($codResidentes->isNotEmpty()) {
            $proximasMedicaciones = app(AgendaMedicacionService::class)
                ->paraAdultos($codResidentes->all())
                ->filter(fn (array $item) => in_array($item['estado'], ['PROXIMA', 'PENDIENTE'], true))
                ->groupBy(fn (array $item) => $item['medicacion']->cod_residente)
                ->map(fn ($items) => $items->first());
            $proximasAtenciones = Atencion::query()
                ->whereIn('cod_residente', $codResidentes)
                ->where('fecha_hora', '>=', now())
                ->orderBy('fecha_hora')
                ->get()
                ->groupBy('cod_residente')
                ->map(fn ($atenciones) => $atenciones->first());
            $proximosCuidados = EjecucionCuidado::query()
                ->whereIn('cod_residente', $codResidentes)
                ->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])
                ->where('fecha_hora_programada', '>=', now())
                ->with('intervencion')
                ->orderBy('fecha_hora_programada')
                ->get()
                ->groupBy('cod_residente')
                ->map(fn ($cuidados) => $cuidados->first());
            $ultimosSignos = SignoVital::whereIn('cod_residente', $codResidentes)
                ->orderByDesc('fecha_hora')
                ->get()
                ->groupBy('cod_residente')
                ->map(fn ($g) => $g->first());

            $medsActivas = Prescripcion::whereIn('cod_residente', $codResidentes)
                ->where('estado', 'ACTIVA')
                ->get()
                ->groupBy('cod_residente');

            $adminMedsHoy = AdministracionMedicacion::whereIn('cod_residente', $codResidentes)
                ->whereDate('fecha_hora_programada', $fechaHoy)
                ->whereIn('resultado', ['ADMINISTRADA', 'ADMINISTRADO'])
                ->get()
                ->groupBy('cod_residente');

            $seguimientosHoy = PaseTurno::whereIn('cod_residente', $codResidentes)
                ->whereDate('fecha_hora', $fechaHoy)
                ->orderByDesc('fecha_hora')
                ->get()
                ->groupBy('cod_residente')
                ->map(fn ($g) => $g->first());
        }

        // Anexar estado clínico y métricas de soporte
        foreach ($pacientes as $p) {
            $cod = $p->cod_residente;
            $p->ultimo_signo = $ultimosSignos->get($cod);
            $seg = $seguimientosHoy->get($cod);
            $p->ultimo_seguimiento = $seg;

            $meds = $medsActivas->get($cod, collect());
            $admins = $adminMedsHoy->get($cod, collect());
            $adminIds = $admins->pluck('cod_prescripcion')->unique()->toArray();
            $p->meds_activas_total = $meds->count();
            $p->meds_pendientes_count = $meds->whereNotIn('cod_prescripcion', $adminIds)->count();

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
                $p->codigo_estado = 'SIN_ALERTAS';
                $p->estado_color = 'slate';
                $p->estado_label = 'Sin alertas activas';
            }

            // Nivel de supervisión explícito (Regla 10)
            $rawNivel = strtoupper(trim((string) ($p->planCuidadoActivo?->nivel_cuidado ?? '')));
            $p->supervision_label = match ($rawNivel) {
                'BAJO', 'BAJA', 'LEVE', 'MINIMO' => 'Supervisión baja',
                'ALTO', 'ALTA', 'SEVERO', 'TOTAL', 'DEPENDIENTE' => 'Supervisión alta',
                default => 'Supervisión no registrada',
            };

            // Movilidad explícita (Regla 11)
            $movRaw = strtolower(trim((string) ($p->movilidad ?? ($p->planCuidadoActivo?->tipo_cuidado ?? ''))));
            if (str_contains($movRaw, 'independien')) {
                $p->movilidad_label = 'Movilidad independiente';
            } elseif (str_contains($movRaw, 'dispositiv') || str_contains($movRaw, 'baston') || str_contains($movRaw, 'andador')) {
                $p->movilidad_label = 'Usa dispositivo';
            } elseif (str_contains($movRaw, 'caida')) {
                $p->movilidad_label = 'Riesgo de caída';
            } else {
                $p->movilidad_label = 'Movilidad no registrada';
            }

            // Próxima atención en la fila de lista
            $proxAten = $proximasAtenciones->get($p->cod_residente);
            $proxCuidado = $proximosCuidados->get($p->cod_residente);
            $proxMed = $proximasMedicaciones->get($p->cod_residente);
            $opciones = collect([
                $proxCuidado ? ['fecha' => $proxCuidado->fecha_hora_programada, 'tipo' => 'cuidado'] : null,
                $proxAten ? ['fecha' => Carbon::parse($proxAten->fecha_hora), 'tipo' => 'atencion'] : null,
                $proxMed ? ['fecha' => $proxMed['programada'], 'tipo' => 'medicacion'] : null,
            ])->filter()->sortBy('fecha')->values();
            $tipoProximo = $opciones->first()['tipo'] ?? null;
            if ($tipoProximo === 'cuidado') {
                $p->proxima_atencion_texto = $proxCuidado->intervencion?->nombre ?: 'Cuidado programado';
                $p->proxima_atencion_hora = $proxCuidado->fecha_hora_programada?->format('H:i');
            } elseif ($tipoProximo === 'atencion') {
                $p->proxima_atencion_texto = $proxAten->motivo ?: ($proxAten->tipo_atencion ?: 'Atención programada');
                $p->proxima_atencion_hora = Carbon::parse($proxAten->fecha_hora)->format('H:i');
            } elseif ($tipoProximo === 'medicacion') {
                $medicacion = $proxMed['medicacion'];
                $p->proxima_atencion_texto = 'Medicamento · '.$medicacion->nombre_medicamento
                    .($medicacion->dosis ? ' '.rtrim(rtrim((string) $medicacion->dosis, '0'), '.').' '.$medicacion->unidad_dosis : '');
                $p->proxima_atencion_hora = $proxMed['hora'];
            } else {
                $p->proxima_atencion_texto = null;
                $p->proxima_atencion_hora = null;
            }
        }

        // Estadísticas globales de turno para el selector de filtros
        $baseParaStats = (clone $service->obtenerPacientesAsignadosQuery(
            $user,
            $turnoEfectivo,
            $enfermeroEfectivo ?: null
        ))->with('cama.habitacion')->withCount([
            'alertas as alertas_activas_count' => fn ($q) => $q->whereIn('estado', ['ABIERTA', 'EN_ATENCION']),
            'alertas as alertas_criticas_count' => fn ($q) => $q->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])->whereIn('prioridad', ['CRITICO', 'ALTO', 'CRITICA']),
            'tareasActuales as tareas_pendientes_count' => function ($q) use ($fechaHoy) {
                $q->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])->whereDate('fecha_hora_programada', $fechaHoy);
            },
            'tareasActuales as tareas_vencidas_count' => function ($q) use ($fechaHoy) {
                $q->whereIn('estado', ['PENDIENTE', 'EN_PROCESO'])->whereDate('fecha_hora_programada', '<', $fechaHoy);
            },
            'seguimientosDiarios as seguimientos_hoy_count' => function ($q) use ($fechaHoy) {
                $q->whereDate('fecha_hora', $fechaHoy);
            },
            'signosVitales as signos_hoy_count' => function ($q) use ($fechaHoy) {
                $q->whereDate('fecha_hora', $fechaHoy);
            },
        ])->get();

        $stats = [
            'total' => $baseParaStats->count(),
            'con_alertas' => $baseParaStats->filter(fn ($p) => $p->alertas_activas_count > 0)->count(),
            'sin_alertas' => $baseParaStats->filter(fn ($p) => $p->alertas_activas_count === 0)->count(),
            'requiere_atencion' => 0,
            'vigilancia' => 0,
            'estable' => 0,
            'con_med_pendiente' => $baseParaStats->filter(fn ($p) => ($p->meds_pendientes_count ?? 0) > 0)->count(),
            'con_med_programada' => $baseParaStats->filter(fn ($p) => ($p->meds_activas_total ?? 0) > 0)->count(),
            'sin_med' => $baseParaStats->filter(fn ($p) => ($p->meds_activas_total ?? 0) === 0)->count(),
            'con_cuidados_pendientes' => $baseParaStats->filter(fn ($p) => ($p->tareas_pendientes_count ?? 0) > 0 || ($p->tareas_vencidas_count ?? 0) > 0)->count(),
            'con_plan_activo' => $baseParaStats->filter(fn ($p) => !empty($p->planCuidadoActivo))->count(),
            'sin_plan' => $baseParaStats->filter(fn ($p) => empty($p->planCuidadoActivo))->count(),
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

        $habitacionesConteo = $baseParaStats
            ->map(fn ($p) => $p->cama?->habitacion?->codigo)
            ->filter()
            ->groupBy(fn ($h) => $h)
            ->map(fn ($grupo, $codigo) => [
                'value' => (string) $codigo,
                'label' => (string) $codigo,
                'count' => $grupo->count(),
            ])
            ->values()
            ->sortBy('label')
            ->values();

        $habitaciones = $baseParaStats
            ->map(fn ($p) => $p->cama?->habitacion?->codigo)
            ->filter()
            ->unique()
            ->sort()
            ->values();
        } catch (QueryException $exception) {
            report($exception);
            $errorCarga = true;
            $pacientes = new LengthAwarePaginator([], 0, 12);
            $stats = ['total' => 0, 'con_alertas' => 0, 'requiere_atencion' => 0, 'vigilancia' => 0, 'estable' => 0];
            $habitaciones = collect();
            $habitacionesConteo = collect();
        }

        return view('livewire.cuidados.mis-residentes-directorio', [
            'pacientes' => $pacientes,
            'esModoConsulta' => $this->esModoConsulta,
            'esResidenteAsignado' => $this->esResidenteAsignado,
            'detalleResidente' => $this->detalleResidente,
            'stats' => $stats,
            'habitaciones' => $habitaciones,
            'habitacionesConteo' => $habitacionesConteo,
            'filtrosActivos' => $this->obtenerFiltrosActivos(),
            'errorCarga' => $errorCarga,
            'esSuperAdmin' => $esSuperAdmin,
            'turnoActual' => $turnoActual,
        ])->layout('layouts.enfermeria');
    }
}
