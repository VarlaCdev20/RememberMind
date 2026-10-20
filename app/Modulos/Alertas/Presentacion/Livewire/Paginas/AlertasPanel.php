<?php

namespace App\Modulos\Alertas\Presentacion\Livewire\Paginas;

use App\Models\Alerta;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Acciones\AlertasService;
use App\Modulos\Alertas\Aplicacion\Acciones\DeteccionAlertasService;
use App\Modulos\Alertas\Aplicacion\Consultas\BandejaAlertas;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarPanelResidenteAlerta;
use App\Modulos\Alertas\Aplicacion\Consultas\ExperienciaAlertas;
use App\Modulos\Alertas\Aplicacion\Consultas\OrdenAlertas;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AlertasPanel extends Component
{
    use WithPagination;

    #[Url(as: 'adulto')]
    public string $filtroAdulto = '';

    public int $perPage = 15;

    public string $search = '';

    #[Url]
    public string $filtroEstado = 'ACTIVAS';

    public string $filtroResponsabilidad = '';

    public string $fechaDesde = '';

    public string $fechaHasta = '';

    public string $filtroNivel = '';

    public string $filtroOrigen = '';

    public bool $confirmarDescarte = false;

    public bool $modalCrear = false;

    public bool $modalAtender = false;

    public bool $modalCerrar = false;

    public bool $modalDetalle = false;

    public bool $modalResultadoCierre = false;

    #[Locked]
    public array $resultadoCierre = [];

    public bool $drawerGrafico = false;

    public bool $drawerUbicacion = false;

    public ?string $adultoDrawerId = null;

    public ?Residente $adultoDrawer = null;

    public $signosDrawer = [];

    #[Locked]
    public ?string $alertaId = null;

    public string $codResidente = '';

    public string $codTurno = '';

    public string $origen = 'MANUAL';

    public string $tipoAlerta = '';

    public string $nivel = 'MEDIO';

    public string $motivo = '';

    public string $accionTomada = '';

    public string $observacionCierre = '';

    public string $accion = '';

    public string $responsableId = '';

    public array $conteos = [];

    public array $chartData = [];

    public function mount(): void
    {
        $this->comprobarPermiso('ver');
        // Si solo hay registros anteriores, la entrada debe mostrarlos sin reclasificarlos.
        if (! request()->has('filtroEstado') && $this->filtroEstado === 'ACTIVAS') {
            $base = app(BandejaAlertas::class)->consulta(auth()->user())
                ->when($this->filtroAdulto, fn ($q) => $q->where('cod_residente', $this->filtroAdulto));
            if (! (clone $base)->abiertas()->exists() && (clone $base)->exists()) {
                $this->filtroEstado = '';
            }
        }
        $alerta = request()->query('alerta');
        if (is_string($alerta) && $alerta !== '') {
            $this->verDetalle($alerta);
        }
    }

    private function comprobarPermiso(string $accion): void
    {
        $user = auth()->user();
        abort_unless($user, 401);
        abort_unless($user->fresh()?->estado === 'ACTIVO' && ! $user->hasRole('FAMILIAR'), 403);
        $permiso = match ($accion) {
            'ver' => 'alertas.ver',
            'asignar' => 'alertas.asignar',
            'reconocer' => 'alertas.reconocer',
            'atender' => 'alertas.seguimiento',
            'cerrar' => 'alertas.cerrar',
            default => 'alertas.gestionar',
        };
        abort_unless($user->canAny([$permiso, 'alertas.gestionar']), 403);
    }

    public function updated($campo): void
    {
        if (in_array($campo, ['fechaDesde', 'fechaHasta'], true)) {
            $this->validate([
                'fechaDesde' => 'nullable|date_format:Y-m-d',
                'fechaHasta' => 'nullable|date_format:Y-m-d'.($this->fechaDesde !== '' ? '|after_or_equal:fechaDesde' : ''),
            ], ['fechaHasta.after_or_equal' => 'La fecha hasta debe ser igual o posterior a la fecha desde.']);
        }
        if (in_array($campo, ['search', 'filtroEstado', 'filtroNivel', 'filtroOrigen', 'filtroAdulto', 'perPage', 'filtroResponsabilidad', 'fechaDesde', 'fechaHasta'])) {
            $this->resetPage();
        }
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtroEstado = '';
        $this->filtroNivel = '';
        $this->filtroOrigen = '';
        $this->filtroAdulto = '';
        $this->filtroResponsabilidad = $this->fechaDesde = $this->fechaHasta = '';
        $this->resetValidation(['fechaDesde', 'fechaHasta']);
        $this->resetPage();
    }

    public function limpiarFiltro(string $campo): void
    {
        if ($campo === 'filtroEstado') {
            $this->filtroEstado = 'ACTIVAS';
            $this->resetPage();
        } elseif (in_array($campo, ['search', 'filtroNivel', 'filtroOrigen', 'filtroAdulto'])) {
            $this->$campo = '';
            $this->resetPage();
        }
    }

    public function setFiltroRapido(string $campo, string $valor): void
    {
        if (in_array($campo, ['filtroEstado', 'filtroNivel', 'filtroOrigen', 'filtroResponsabilidad'], true)) {
            if ($this->{$campo} === $valor) {
                $this->{$campo} = '';
            } else {
                $this->{$campo} = $valor;
            }
            $this->resetPage();
        }
    }

    public function verPrioritarias(): void
    {
        $this->filtroNivel = 'PRIORITARIAS';
        $this->filtroEstado = '';
        $this->resetPage();
    }

    public function detectarAlertas(): void
    {
        $this->comprobarPermiso('crear');
        $cantidad = app(DeteccionAlertasService::class)->detectar();
        if ($cantidad > 0) {
            session()->flash('mensaje', "Detección completada: {$cantidad} ".($cantidad === 1 ? 'nueva alerta clínica detectada.' : 'nuevas alertas clínicas detectadas.'));
        } else {
            session()->flash('mensaje', 'Detección completada: No se detectaron nuevas alertas.');
        }
    }

    public function abrirCrear(): void
    {
        $this->comprobarPermiso('crear');
        $this->cerrarModales();
        $this->reset('codResidente', 'codTurno', 'tipoAlerta', 'motivo', 'accionTomada');
        $this->origen = 'MANUAL';
        $this->nivel = 'MEDIO';
        $this->modalCrear = true;
    }

    public function guardarAlerta(): void
    {
        $this->comprobarPermiso('crear');

        $this->validate([
            'codResidente' => 'required|string|exists:residentes,cod_residente',
            'origen' => 'required|in:SIGNOS,MEDICACION,SEGUIMIENTO,PLAN,INCIDENTE,SOLICITUD_MEDICA,MANUAL,FICHA,VALORACION',
            'tipoAlerta' => 'required|string|min:3|max:60',
            'nivel' => 'required|in:BAJO,MEDIO,ALTO,CRITICO',
            'motivo' => 'required|string|min:10|max:10000',
        ], [
            'codResidente.required' => 'Debe seleccionar un residente disponible en su ámbito.',
            'codResidente.exists' => 'El residente seleccionado no es válido.',
            'origen.required' => 'Debe seleccionar el origen registrado de la alerta.',
            'tipoAlerta.required' => 'El tipo de alerta es obligatorio.',
            'tipoAlerta.min' => 'El tipo de alerta debe tener al menos 3 caracteres.',
            'tipoAlerta.max' => 'El tipo de alerta no puede superar los 60 caracteres.',
            'nivel.required' => 'Debe seleccionar la prioridad de la alerta.',
            'motivo.required' => 'Describe la situación que requiere seguimiento.',
            'motivo.min' => 'La descripción debe contener al menos 10 caracteres.',
        ]);

        $alerta = app(AlertasService::class)->crearDesdePanel($this->codResidente, [
            'origen' => $this->origen, 'tipo_alerta' => $this->tipoAlerta,
            'nivel' => $this->nivel, 'motivo' => $this->motivo,
        ], auth()->user());

        $this->dispatch('alerta-creada', alertaId: $alerta->cod_alerta);
        $this->cerrarModales();
        session()->flash('mensaje', 'Alerta registrada correctamente.');
    }

    public function verDetalle(string $id): void
    {
        $this->comprobarPermiso('ver');
        $alerta = $this->obtenerAlertaAutorizada($id);
        $this->cerrarModales();
        $this->alertaId = $id;
        $this->responsableId = $alerta->responsable?->cod_usuario ?? '';
        $this->accion = '';
        $this->modalDetalle = true;
    }

    public function asignarResponsable(): void
    {
        $this->comprobarPermiso('asignar');
        $this->validate(['responsableId' => 'required|exists:usuarios,cod_usuario'], [
            'responsableId.required' => 'Debe seleccionar un profesional responsable.',
            'responsableId.exists' => 'El profesional seleccionado no es válido.',
        ]);
        app(AlertasService::class)->asignarResponsable(
            Alerta::findOrFail($this->alertaId), User::findOrFail($this->responsableId), auth()->user(), coordinacion: true);
        $this->dispatch('alerta-actualizada');
        session()->flash('mensaje', 'Responsable asignado.');
    }

    public function guardarAccion(): void
    {
        $this->comprobarPermiso('atender');
        $this->validate(['accion' => 'required|string|min:5|max:10000'], [
            'accion.required' => 'Describe la actuación o el seguimiento de coordinación realizado.',
            'accion.min' => 'La nota de seguimiento debe contener al menos 5 caracteres.',
            'accion.max' => 'La nota de seguimiento no puede superar los 10.000 caracteres.',
        ]);
        app(AlertasService::class)->registrarDesdePanel(
            Alerta::findOrFail($this->alertaId), $this->accion, auth()->user());
        $this->dispatch('evento-alerta-registrado');
        $this->accion = '';
        session()->flash('mensaje', 'Registro de la alerta añadido.');
    }

    public function atenderAlerta(string $id): void
    {
        $this->comprobarPermiso('atender');
        $alerta = $this->obtenerAlertaAutorizada($id);
        app(AlertasService::class)->autorizarAtencion($alerta, auth()->user());
        $this->cerrarModales();
        $this->alertaId = $id;
        $this->accionTomada = '';
        $this->modalAtender = true;
    }

    public function guardarAtencion(): void
    {
        $this->comprobarPermiso('atender');

        $this->validate([
            'accionTomada' => 'required|string|min:5|max:10000',
        ], [
            'accionTomada.required' => 'Debe describir la intervención clínica asistencial realizada.',
            'accionTomada.min' => 'La nota de intervención debe contener al menos 5 caracteres descriptivos.',
            'accionTomada.max' => 'La nota de intervención no puede superar los 10.000 caracteres.',
        ]);

        app(AlertasService::class)->registrarDesdePanel(
            Alerta::findOrFail($this->alertaId), $this->accionTomada, auth()->user(), atencion: true);

        $this->dispatch('alerta-atendida', alertaId: $this->alertaId);
        $this->cerrarModales();
        session()->flash('mensaje', 'Intervención clínica registrada exitosamente.');
    }

    public function cerrarAlerta(string $id): void
    {
        $this->comprobarPermiso('cerrar');
        $alerta = $this->obtenerAlertaAutorizada($id);
        app(AlertasService::class)->autorizarCoordinacion($alerta->cod_residente, 'alertas.cerrar', auth()->user());
        abort_unless($alerta->puedeCerrarse(), 409, 'Esta alerta ya no admite cierre.');
        $this->cerrarModales();
        $this->alertaId = $id;
        $this->observacionCierre = '';
        $this->modalCerrar = true;
    }

    public function confirmarCierre(): void
    {
        $this->comprobarPermiso('cerrar');

        $this->validate([
            'observacionCierre' => 'required|string|min:5|max:10000',
        ], [
            'observacionCierre.required' => 'Debe ingresar la justificación del cierre.',
            'observacionCierre.min' => 'La justificación debe contener al menos 5 caracteres explicativos.',
            'observacionCierre.max' => 'La justificación no puede superar los 10.000 caracteres.',
        ]);

        app(AlertasService::class)->cerrar(
            Alerta::findOrFail($this->alertaId), $this->observacionCierre, auth()->user(), coordinacion: true);

        $alertaCerrada = Alerta::with('residente')->findOrFail($this->alertaId);
        $this->dispatch('alerta-cerrada', alertaId: $this->alertaId);
        $this->cerrarModales();
        $this->resultadoCierre = [
            'residente' => $alertaCerrada->residente?->nombre_completo,
            'cod_residente' => $alertaCerrada->cod_residente,
            'fecha_hora' => now()->format('d/m/Y H:i'),
        ];
        $this->modalResultadoCierre = true;
    }

    public function cerrarResultadoCierre(): void
    {
        $this->modalResultadoCierre = false;
        $this->resultadoCierre = [];
    }

    private function obtenerAlertaAutorizada(string $id): Alerta
    {
        $alerta = Alerta::findOrFail($id);
        $this->autorizarLecturaResidente($alerta->cod_residente);

        return $alerta;
    }

    private function autorizarLecturaResidente(string $codResidente): void
    {
        $usuario = auth()->user();
        $turnos = app(TurnoEnfermeriaService::class);
        if ($usuario?->hasRole('ENFERMEROS')) {
            $turnos->autorizarConsultaResidente($codResidente, $usuario);

            return;
        }
        if (! $usuario?->hasAnyRole(['SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR'])) {
            abort_unless($turnos->obtenerPacientesAsignadosQuery($usuario)
                ->whereKey($codResidente)->exists(), 403);
        }
    }

    public function verGraficos(string $codResidente): void
    {
        $this->comprobarPermiso('ver');
        $this->autorizarLecturaResidente($codResidente);
        $this->adultoDrawerId = $codResidente;
        $this->adultoDrawer = app(ConsultarPanelResidenteAlerta::class)->consultar(auth()->user(), $codResidente, 'graficos');

        $this->signosDrawer = $this->adultoDrawer?->signosVitales ?? collect();
        $this->drawerGrafico = true;
        $this->drawerUbicacion = false;
    }

    public function verUbicacion(string $codResidente): void
    {
        $this->comprobarPermiso('ver');
        $this->autorizarLecturaResidente($codResidente);
        $this->adultoDrawerId = $codResidente;
        $this->adultoDrawer = app(ConsultarPanelResidenteAlerta::class)->consultar(auth()->user(), $codResidente, 'ubicacion');

        $this->drawerUbicacion = true;
        $this->drawerGrafico = false;
    }

    public function cerrarDrawer(): void
    {
        $this->drawerGrafico = false;
        $this->drawerUbicacion = false;
        $this->adultoDrawerId = null;
        $this->adultoDrawer = null;
        $this->signosDrawer = [];
    }

    public function solicitarCerrarModales(): void
    {
        $hayCaptura = ($this->modalCrear && (trim($this->tipoAlerta) !== '' || trim($this->motivo) !== '' || $this->codResidente !== '' || $this->origen !== 'MANUAL' || $this->nivel !== 'MEDIO'))
            || ($this->modalAtender && trim($this->accionTomada) !== '')
            || ($this->modalCerrar && trim($this->observacionCierre) !== '')
            || ($this->modalDetalle && (trim($this->accion) !== '' || $this->responsableId !== (Alerta::find($this->alertaId)?->responsable?->cod_usuario ?? '')));
        if ($hayCaptura) {
            $this->confirmarDescarte = true;

            return;
        }
        $this->cerrarModales();
    }

    public function continuarCaptura(): void
    {
        $this->confirmarDescarte = false;
    }

    public function descartarCaptura(): void
    {
        $this->cerrarModales();
        $this->reset('codResidente', 'tipoAlerta', 'motivo', 'origen', 'nivel');
    }

    public function cerrarModales(): void
    {
        $this->confirmarDescarte = false;
        $this->modalCrear = false;
        $this->modalAtender = false;
        $this->modalCerrar = false;
        $this->modalDetalle = false;
        $this->modalResultadoCierre = false;
        $this->resultadoCierre = [];
        $this->alertaId = null;
        $this->accionTomada = '';
        $this->observacionCierre = '';
        $this->accion = '';
        $this->responsableId = '';
        $this->resetValidation();
    }

    public function reconocer(string $id): void
    {
        $this->comprobarPermiso('reconocer');
        $alerta = $this->obtenerAlertaAutorizada($id);
        app(AlertasService::class)->cambiarEstado($alerta, 'RECONOCIDA', 'Reconocimiento del aviso; no registra atención clínica.', auth()->user());
        $this->dispatch('alerta-actualizada');
        session()->flash('mensaje', 'Alerta reconocida. Permanece visible hasta su atención y cierre.');
    }

    public function render()
    {
        $this->comprobarPermiso('ver');
        if (($this->drawerGrafico || $this->drawerUbicacion) && $this->adultoDrawerId) {
            $this->adultoDrawer = app(ConsultarPanelResidenteAlerta::class)->consultar(auth()->user(), $this->adultoDrawerId, $this->drawerGrafico ? 'graficos' : 'ubicacion');
            $this->signosDrawer = $this->drawerGrafico ? $this->adultoDrawer->signosVitales : [];
        }
        $usuario = auth()->user();
        $bandeja = app(BandejaAlertas::class);
        $turnoService = app(TurnoEnfermeriaService::class);
        $base = $bandeja->consulta($usuario)
            ->when($this->filtroAdulto, fn ($q) => $q->where('cod_residente', $this->filtroAdulto));
        $statsEstado = (clone $base)->select('estado')->selectRaw('COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado')->map(fn ($n) => (int) $n)->all();
        $estadosHistoricos = array_diff_key($statsEstado, array_flip([...Alerta::ESTADOS_ACTIVOS, 'ATENDIDA', ...Alerta::ESTADOS_TERMINALES]));
        $statsNivel = (clone $base)->abiertas()->select('prioridad')->selectRaw('COUNT(*) as total')->groupBy('prioridad')->pluck('total', 'prioridad')->all();
        $origenesDisponibles = (clone $base)->whereNotNull('modulo')->where('modulo', '!=', '')->distinct()->orderBy('modulo')->pluck('modulo')->all();
        $conteos = [
            'total' => array_sum($statsEstado),
            'criticas' => (int) (($statsNivel['CRITICO'] ?? 0) + ($statsNivel['CRITICA'] ?? 0)),
            'altas' => (int) (($statsNivel['ALTO'] ?? 0) + ($statsNivel['ALTA'] ?? 0)),
            'abiertas' => (int) ($statsEstado['ABIERTA'] ?? 0),
            'pendientes' => array_sum(array_intersect_key($statsEstado, array_flip(Alerta::ESTADOS_ACTIVOS))),
            'en_atencion' => (int) ($statsEstado['EN_ATENCION'] ?? 0),
            'atendidas' => (int) ($statsEstado['ATENDIDA'] ?? 0),
            'cerradas' => (int) ($statsEstado['CERRADA'] ?? 0),
            'sin_responsable' => (clone $base)->abiertas()->whereNull('cod_personal_responsable')->count(),
            'vencidas' => (clone $base)->abiertas()->whereNotNull('fecha_hora_limite')->where('fecha_hora_limite', '<', now())->count(),
        ];
        $this->conteos = $conteos;
        $query = (clone $base)
            ->when($this->search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->whereHas('residente', fn ($r) => $r->whereLike('nombres', '%'.$this->search.'%')->orWhereLike('apellido_paterno', '%'.$this->search.'%')->orWhereLike('apellido_materno', '%'.$this->search.'%'))
                ->orWhereLike('cod_residente', '%'.$this->search.'%')->orWhereLike('cod_alerta', '%'.$this->search.'%')
                ->orWhereLike('tipo', '%'.$this->search.'%')->orWhereLike('titulo', '%'.$this->search.'%')->orWhereLike('descripcion', '%'.$this->search.'%')))
            ->when($this->filtroEstado === 'ACTIVAS', fn ($q) => $q->abiertas())
            ->when($this->filtroEstado !== '' && $this->filtroEstado !== 'ACTIVAS', fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroNivel === 'PRIORITARIAS', fn ($q) => $q->abiertas()->whereIn('prioridad', Alerta::PRIORIDADES_ALTAS))
            ->when($this->filtroNivel !== '' && $this->filtroNivel !== 'PRIORITARIAS', fn ($q) => $q->whereIn('prioridad', match ($this->filtroNivel) {
                'CRITICO' => ['CRITICO', 'CRITICA'], 'ALTO' => ['ALTO', 'ALTA'], 'MEDIO' => ['MEDIO', 'MEDIA'], 'BAJO' => ['BAJO', 'BAJA'], default => [$this->filtroNivel],
            }))
            ->when($this->filtroOrigen !== '', fn ($q) => $q->where('modulo', $this->filtroOrigen))
            ->when($this->filtroResponsabilidad === 'SIN_RESPONSABLE', fn ($q) => $q->whereNull('cod_personal_responsable'))
            ->when($this->filtroResponsabilidad === 'PROPIAS', fn ($q) => $q->where('cod_personal_responsable', $usuario->personal?->cod_personal ?? '__SIN_PERSONAL__'))
            ->when(validator(['fecha' => $this->fechaDesde], ['fecha' => 'required|date_format:Y-m-d'])->passes(), fn ($q) => $q->whereDate('fecha_hora', '>=', $this->fechaDesde))
            ->when(validator(['fecha' => $this->fechaHasta], ['fecha' => 'required|date_format:Y-m-d'])->passes(), fn ($q) => $q->whereDate('fecha_hora', '<=', $this->fechaHasta));
        $agrupar = fn (string $campo) => (clone $query)->select($campo)->selectRaw('COUNT(*) as total')
            ->groupBy($campo)->pluck('total', $campo)->map(fn ($n) => (int) $n)->all();
        $chartData = $this->chartData = ['estados' => $agrupar('estado'), 'origenes' => $agrupar('modulo'), 'niveles' => $agrupar('prioridad')];
        $sinResponsableConsulta = (clone $query)->whereNull('cod_personal_responsable')->count();
        $alertas = OrdenAlertas::aplicar($query)
            ->with(['residente.cama.habitacion', 'responsable'])->paginate(min(100, max(5, $this->perPage)));
        $prioritarias = OrdenAlertas::aplicar((clone $base)->abiertas()->whereIn('prioridad', Alerta::PRIORIDADES_ALTAS))
            ->with('residente')->limit(3)->get();
        $detalle = $this->alertaId ? (clone $base)->with(['residente.cama.habitacion', 'responsable.usuario', 'eventos.usuario'])->findOrFail($this->alertaId) : null;
        $signoOrigen = $detalle?->modulo === 'SIGNOS' && $usuario->can('signos_vitales.ver') && filled($detalle->cod_registro)
            ? SignoVital::query()->where('cod_residente', $detalle->cod_residente)->find($detalle->cod_registro) : null;
        $evolucionSignos = $signoOrigen ? SignoVital::query()->where('cod_residente', $detalle->cod_residente)
            ->whereIn('estado', ['VIGENTE', 'ACTIVO'])->where('fecha_hora', '>=', $signoOrigen->fecha_hora)
            ->orderBy('fecha_hora')->limit(6)->get() : collect();
        $preview = app(RolePreviewService::class)->isActive($usuario);
        $puedeIntervenir = ! $preview && $usuario->hasRole('ENFERMEROS') && $usuario->canAny(['alertas.gestionar', 'alertas.seguimiento'])
            && $usuario->personal?->estado === 'ACTIVO' && $turnoService->obtenerTurnoActivo($usuario) !== null
            && (! $detalle || $turnoService->esPacienteAsignado($detalle->cod_residente, $usuario));
        $contextoOperativo = ! $preview && ($usuario->hasRole('ENFERMEROS') ? $puedeIntervenir : $usuario->hasAnyRole(['ADMINISTRADOR', 'GERENTE', 'SUPERADMINISTRADOR']));
        $puedeReconocer = $contextoOperativo && $usuario->canAny(['alertas.reconocer', 'alertas.gestionar']);
        $puedeAsignar = $contextoOperativo && $usuario->canAny(['alertas.asignar', 'alertas.gestionar']);
        $puedeCoordinar = ! $preview && $usuario->hasAnyRole(['ADMINISTRADOR', 'SUPERADMINISTRADOR']) && $usuario->canAny(['alertas.gestionar', 'alertas.seguimiento']);
        $puedeCerrar = ! $preview && $usuario->canAny(['alertas.gestionar', 'alertas.cerrar'])
            && ($puedeIntervenir || $puedeCoordinar);
        $puedeRegistrarNuevaMedicion = $detalle?->estado === 'EN_ATENCION' && $puedeIntervenir && $usuario->can('signos_vitales.crear')
            && $turnoService->esPacienteAsignado($detalle->cod_residente, $usuario);
        $perfil = app(ExperienciaAlertas::class)->paraUsuario($usuario);
        $adultos = $this->modalCrear ? $turnoService->obtenerPacientesAsignadosQuery($usuario)->orderBy('apellido_paterno')->get() : collect();
        $personalConAcceso = $this->modalDetalle && $detalle && $puedeAsignar ? $turnoService->personalConAccesoConsultaResidente($detalle->cod_residente) : [];
        $usuarios = $this->modalDetalle && $puedeAsignar
            ? User::query()->where('estado', 'ACTIVO')->whereHas('personal', fn ($q) => $q->where('estado', 'ACTIVO'))
                ->role(['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'])
                ->with(['personal', 'roles.permissions', 'permissions'])->get()->filter(fn ($u) => $u->can('alertas.ver')
                    && ($turnoService->tieneLecturaClinicaGlobal($u) || in_array($u->personal->cod_personal, $personalConAcceso, true))) : collect();

        return view('livewire.alertas.alertas-panel', compact('alertas', 'prioritarias', 'conteos', 'chartData', 'detalle', 'signoOrigen', 'evolucionSignos',
            'puedeRegistrarNuevaMedicion', 'puedeIntervenir', 'puedeCoordinar', 'puedeCerrar', 'puedeReconocer', 'puedeAsignar', 'origenesDisponibles', 'sinResponsableConsulta', 'perfil', 'adultos', 'usuarios', 'preview', 'estadosHistoricos'))
            ->layout(request()->routeIs('admin.enfermeria.*') ? 'layouts.enfermeria' : 'layouts.sistema');
    }
}
