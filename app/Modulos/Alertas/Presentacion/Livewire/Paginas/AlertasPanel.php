<?php

namespace App\Modulos\Alertas\Presentacion\Livewire\Paginas;

use App\Models\Alerta;
use App\Models\Atencion;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\SignoVital;
use App\Models\User;
use App\Modulos\Alertas\Aplicacion\Acciones\AlertasService;
use App\Modulos\Alertas\Aplicacion\Acciones\DeteccionAlertasService;
use App\Modulos\Alertas\Aplicacion\Acciones\RegistrarActuacionEspecialidadDesdeAlerta;
use App\Modulos\Alertas\Aplicacion\Consultas\BandejaAlertas;
use App\Modulos\Alertas\Aplicacion\Consultas\CamposRegistroEspecialidad;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarContextoEspecialidadAlerta;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarOrigenOperativoAlerta;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarPanelResidenteAlerta;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarResumenEquipoAlertas;
use App\Modulos\Alertas\Aplicacion\Consultas\ConsultarTrazaAlerta;
use App\Modulos\Alertas\Aplicacion\Consultas\ExperienciaAlertas;
use App\Modulos\Alertas\Aplicacion\Consultas\OrdenAlertas;
use App\Modulos\Alertas\Aplicacion\Consultas\VinculosProfesionalesAlertas;
use App\Modulos\Clinica\Aplicacion\Consultas\ContextoClinicoService;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use LogicException;

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

    #[Url]
    public string $filtroEspecialidad = '';

    public bool $confirmarDescarte = false;

    public bool $modalCrear = false;

    public bool $modalAtender = false;

    public bool $modalCerrar = false;

    public bool $modalDetalle = false;

    public bool $modalEspecialidad = false;

    public array $capturaEspecialidad = [];

    public string $atencionEspecialidad = '';

    public string $tipoAtencionEspecialidad = '';

    public string $motivoAtencionEspecialidad = '';

    #[Locked]
    public ?string $solicitudEspecialidad = null;

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

    public string $buscarResidente = '';

    public string $buscarResponsable = '';

    public int $paginaResidentes = 1;

    #[Locked]
    public ?string $trazaId = null;

    public bool $mostrarAnalisis = false;

    #[Locked]
    public ?string $solicitudAlta = null;

    public array $conteos = [];

    public array $chartData = [];

    public function mount(): void
    {
        $this->comprobarPermiso('ver');
        $rol = app(ExperienciaAlertas::class)->paraUsuario(auth()->user())['rol'];
        if (! request()->has('filtroEspecialidad') && isset(VinculosProfesionalesAlertas::ESPECIALIDADES[$rol])) {
            $this->filtroEspecialidad = $rol;
        }
        // Si solo hay registros anteriores, la entrada debe mostrarlos sin reclasificarlos.
        if (! request()->has('filtroEstado') && $this->filtroEstado === 'ACTIVAS') {
            $base = app(BandejaAlertas::class)->consulta(auth()->user())
                ->when($this->filtroAdulto, fn ($q) => $q->where('cod_residente', $this->filtroAdulto));
            app(VinculosProfesionalesAlertas::class)->aplicar($base, auth()->user(), $this->filtroEspecialidad);
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
        if ($campo === 'buscarResidente') {
            $this->paginaResidentes = 1;
        }
        if (in_array($campo, ['fechaDesde', 'fechaHasta'], true)) {
            $this->validate([
                'fechaDesde' => 'nullable|date_format:Y-m-d',
                'fechaHasta' => 'nullable|date_format:Y-m-d'.($this->fechaDesde !== '' ? '|after_or_equal:fechaDesde' : ''),
            ], ['fechaHasta.after_or_equal' => 'La fecha hasta debe ser igual o posterior a la fecha desde.']);
        }
        if (in_array($campo, ['search', 'filtroEstado', 'filtroNivel', 'filtroOrigen', 'filtroAdulto', 'perPage', 'filtroResponsabilidad', 'fechaDesde', 'fechaHasta', 'filtroEspecialidad'])) {
            $this->trazaId = null;
            $this->resetPage();
        }
    }

    public function limpiarFiltros(): void
    {
        $this->trazaId = null;
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
        if (in_array($campo, ['filtroEstado', 'filtroNivel', 'filtroOrigen', 'filtroResponsabilidad', 'filtroEspecialidad'], true)) {
            $this->{$campo} = $valor;
            $this->trazaId = null;
            $this->resetPage();
        }
    }

    public function filtrarResumen(string $resumen): void
    {
        abort_unless(in_array($resumen, ['urgentes', 'por_atender', 'en_atencion', 'cerradas', 'anteriores', 'todas', 'sin_responsable', 'fuera_plazo'], true), 422);
        $residente = $this->filtroAdulto;
        $this->limpiarFiltros();
        $this->filtroAdulto = $residente;
        $this->filtroEstado = match ($resumen) {
            'por_atender' => 'POR_ATENDER', 'en_atencion' => 'EN_ATENCION', 'cerradas' => 'CERRADA', 'anteriores' => 'ANTERIORES', 'sin_responsable' => 'ACTIVAS', 'fuera_plazo' => 'CON_LIMITE_SUPERADO', default => '',
        };
        $this->filtroResponsabilidad = $resumen === 'sin_responsable' ? 'SIN_RESPONSABLE' : '';
        $this->filtroNivel = $resumen === 'urgentes' ? 'PRIORITARIAS' : '';
    }

    public function seleccionarTraza(string $id): void
    {
        $this->comprobarPermiso('ver');
        app(BandejaAlertas::class)->consulta(auth()->user())->findOrFail($id);
        $this->trazaId = $id;
        $this->dispatch('alertas-caso-seleccionado');
    }

    public function filtrarEquipo(string $responsable = ''): void
    {
        $this->comprobarPermiso('ver');
        abort_unless($responsable === '' || $responsable === 'SIN_RESPONSABLE'
            || (str_starts_with($responsable, 'PERSONAL:') && strlen($responsable) <= 80), 422);
        $residente = $this->filtroAdulto;
        $this->limpiarFiltros();
        $this->filtroAdulto = $residente;
        $this->filtroEstado = 'ACTIVAS';
        $this->filtroResponsabilidad = $responsable;
    }

    public function paginaResidentes(int $paso): void
    {
        $this->comprobarPermiso('crear');
        $this->paginaResidentes = max(1, $this->paginaResidentes + ($paso > 0 ? 1 : -1));
    }

    public function verPrioritarias(): void
    {
        $this->filtroEspecialidad = '';
        $this->search = $this->filtroOrigen = $this->filtroResponsabilidad = $this->fechaDesde = $this->fechaHasta = '';
        $this->resetValidation(['fechaDesde', 'fechaHasta']);
        $this->trazaId = null;
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
        $preview = app(RolePreviewService::class)->isActive(auth()->user());
        abort_if($preview && ! in_array(app(ExperienciaAlertas::class)->paraUsuario(auth()->user())['foco'], ['turno', 'coordinacion', 'auditoria'], true), 403);
        $this->cerrarModales();
        $this->reset('codResidente', 'codTurno', 'tipoAlerta', 'motivo', 'accionTomada', 'buscarResidente', 'paginaResidentes');
        $this->origen = 'MANUAL';
        $this->nivel = 'MEDIO';
        $this->solicitudAlta = $preview ? null : (string) Str::uuid();
        $this->modalCrear = true;
    }

    public function abrirRegistroEspecialidad(string $id): void
    {
        $this->comprobarPermiso('ver');
        $definicion = app(CamposRegistroEspecialidad::class)->paraRol(app(ExperienciaAlertas::class)->paraUsuario(auth()->user())['rol']);
        abort_unless($definicion, 403);
        $this->obtenerAlertaAutorizada($id);
        if ($this->protegerCapturaDetalle()) {
            return;
        }
        $this->cerrarModales();
        $this->alertaId = $id;
        $this->capturaEspecialidad = array_fill_keys(array_keys($definicion['campos']), null);
        $this->solicitudEspecialidad = (string) Str::uuid();
        $this->modalEspecialidad = true;
    }

    public function guardarRegistroEspecialidad(): void
    {
        $this->comprobarPermiso('ver');
        abort_unless($this->modalEspecialidad && $this->alertaId && $this->solicitudEspecialidad, 409);
        try {
            app(RegistrarActuacionEspecialidadDesdeAlerta::class)->ejecutar(auth()->user(), $this->alertaId, $this->capturaEspecialidad, [
                'atencion' => $this->atencionEspecialidad, 'tipoAtencion' => $this->tipoAtencionEspecialidad,
                'motivoAtencion' => $this->motivoAtencionEspecialidad,
            ], $this->solicitudEspecialidad);
        } catch (ValidationException $excepcion) {
            $errores = [];
            foreach ($excepcion->errors() as $campo => $mensajes) {
                $destino = match ($campo) {
                    'atencionEspecialidad', 'capturaEspecialidad' => $campo,
                    'tipo_atencion' => 'tipoAtencionEspecialidad', 'motivo' => 'motivoAtencionEspecialidad',
                    default => 'capturaEspecialidad.'.$campo,
                };
                $errores[$destino] = $mensajes;
            }
            throw ValidationException::withMessages($errores);
        }
        $this->cerrarModales();
        session()->flash('mensaje', 'Registro profesional guardado en el expediente y enlazado al historial de la alerta. Se conserva el estado de la alerta.');
    }

    public function guardarAlerta(): void
    {
        $this->comprobarPermiso('crear');
        abort_if(app(RolePreviewService::class)->isActive(auth()->user()), 403);

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

        // Una petición repetida después de confirmar no vuelve a crear el aviso.
        abort_unless($this->modalCrear && $this->solicitudAlta, 409, 'Abre el formulario para registrar una nueva alerta.');
        $alerta = app(AlertasService::class)->crearDesdePanel($this->codResidente, [
            'origen' => $this->origen, 'tipo_alerta' => $this->tipoAlerta,
            'nivel' => $this->nivel, 'motivo' => $this->motivo,
        ], auth()->user(), $this->solicitudAlta);

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
        $this->buscarResponsable = '';
        $this->trazaId = $id;
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
        if ($this->protegerCapturaDetalle()) {
            return;
        }
        $this->cerrarModales();
        $this->alertaId = $id;
        $this->accionTomada = '';
        $this->modalAtender = true;
    }

    public function guardarAtencion(): void
    {
        $this->comprobarPermiso('atender');
        abort_if(app(RolePreviewService::class)->isActive(auth()->user()), 403);

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
        if ($this->protegerCapturaDetalle()) {
            return;
        }
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
        if ($this->protegerCapturaDetalle()) {
            return;
        }
        $this->adultoDrawerId = $codResidente;
        $this->adultoDrawer = app(ConsultarPanelResidenteAlerta::class)->consultar(auth()->user(), $codResidente, 'graficos');

        $this->modalDetalle = false;

        $this->signosDrawer = $this->adultoDrawer?->signosVitales ?? collect();
        $this->drawerGrafico = true;
        $this->drawerUbicacion = false;
    }

    public function verUbicacion(string $codResidente): void
    {
        $this->comprobarPermiso('ver');
        $this->autorizarLecturaResidente($codResidente);
        if ($this->protegerCapturaDetalle()) {
            return;
        }
        $this->adultoDrawerId = $codResidente;
        $this->adultoDrawer = app(ConsultarPanelResidenteAlerta::class)->consultar(auth()->user(), $codResidente, 'ubicacion');

        $this->modalDetalle = false;

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
            || ($this->modalEspecialidad && (collect($this->capturaEspecialidad)->contains(fn ($v) => $v !== null && $v !== '') || $this->atencionEspecialidad !== '' || $this->tipoAtencionEspecialidad !== '' || $this->motivoAtencionEspecialidad !== ''))
            || ($this->modalAtender && trim($this->accionTomada) !== '')
            || ($this->modalCerrar && trim($this->observacionCierre) !== '')
            || ($this->modalDetalle && (trim($this->accion) !== '' || $this->responsableId !== (Alerta::find($this->alertaId)?->responsable?->cod_usuario ?? '')));
        if ($hayCaptura) {
            $this->confirmarDescarte = true;

            return;
        }
        $this->cerrarModales();
    }

    private function protegerCapturaDetalle(): bool
    {
        if ($this->modalDetalle && (trim($this->accion) !== '' || $this->responsableId !== (Alerta::find($this->alertaId)?->responsable?->cod_usuario ?? ''))) {
            $this->confirmarDescarte = true;

            return true;
        }

        return false;
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
        $this->cerrarDrawer();
        $this->confirmarDescarte = false;
        $this->modalCrear = false;
        $this->modalAtender = false;
        $this->modalCerrar = false;
        $this->modalDetalle = false;
        $this->modalEspecialidad = false;
        $this->capturaEspecialidad = [];
        $this->atencionEspecialidad = $this->tipoAtencionEspecialidad = $this->motivoAtencionEspecialidad = '';
        $this->solicitudEspecialidad = null;
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
        $ambito = $bandeja->consulta($usuario)
            ->when($this->filtroAdulto, fn ($q) => $q->where('cod_residente', $this->filtroAdulto));
        $vinculador = app(VinculosProfesionalesAlertas::class);
        $base = $vinculador->aplicar(clone $ambito, $usuario, $this->filtroEspecialidad);
        $especialidadesDisponibles = VinculosProfesionalesAlertas::ESPECIALIDADES;
        $statsEstado = (clone $base)->select('estado')->selectRaw('COUNT(*) as total')->groupBy('estado')->pluck('total', 'estado')->map(fn ($n) => (int) $n)->all();
        $estadosHistoricos = array_diff_key($statsEstado, array_flip([...Alerta::ESTADOS_ACTIVOS, 'ATENDIDA', ...Alerta::ESTADOS_TERMINALES]));
        $statsNivel = (clone $base)->abiertas()->select('prioridad')->selectRaw('COUNT(*) as total')->groupBy('prioridad')->pluck('total', 'prioridad')->all();
        $origenesDisponibles = (clone $base)->whereNotNull('modulo')->where('modulo', '!=', '')->distinct()->orderBy('modulo')->pluck('modulo')->all();
        $conteos = [
            'total' => array_sum($statsEstado),
            'anteriores' => array_sum($estadosHistoricos),
            'criticas' => (int) (($statsNivel['CRITICO'] ?? 0) + ($statsNivel['CRITICA'] ?? 0)),
            'altas' => (int) (($statsNivel['ALTO'] ?? 0) + ($statsNivel['ALTA'] ?? 0)),
            'abiertas' => (int) ($statsEstado['ABIERTA'] ?? 0),
            'por_atender' => array_sum(array_intersect_key($statsEstado, array_flip(array_diff(Alerta::ESTADOS_ACTIVOS, ['EN_ATENCION'])))),
            'pendientes' => array_sum(array_intersect_key($statsEstado, array_flip(Alerta::ESTADOS_ACTIVOS))),
            'en_atencion' => (int) ($statsEstado['EN_ATENCION'] ?? 0),
            'atendidas' => (int) ($statsEstado['ATENDIDA'] ?? 0),
            'cerradas' => (int) ($statsEstado['CERRADA'] ?? 0),
            'sin_responsable' => (clone $base)->abiertas()->whereNull('cod_personal_responsable')->count(),
            'vencidas' => (clone $base)->abiertas()->whereNotNull('fecha_hora_limite')->where('fecha_hora_limite', '<', now())->count(),
        ];
        $this->conteos = $conteos;
        $query = (clone $base)
            ->when(trim($this->search) !== '', function ($query) {
                foreach (preg_split('/\s+/u', trim(mb_substr($this->search, 0, 250))) as $palabra) {
                    $patron = '%'.$palabra.'%';
                    $query->where(fn ($q) => $q
                        ->whereHas('residente', fn ($r) => $r->whereLike('nombres', $patron)->orWhereLike('apellido_paterno', $patron)->orWhereLike('apellido_materno', $patron))
                        ->orWhereLike('cod_residente', $patron)->orWhereLike('cod_alerta', $patron)
                        ->orWhereHas('residente.cama', fn ($c) => $c->whereLike('codigo', $patron)->orWhereHas('habitacion', fn ($h) => $h->whereLike('codigo', $patron)))
                        ->orWhereLike('tipo', $patron)->orWhereLike('titulo', $patron)->orWhereLike('descripcion', $patron));
                }
            })
            ->when($this->filtroEstado === 'POR_ATENDER', fn ($q) => $q->whereIn('estado', array_diff(Alerta::ESTADOS_ACTIVOS, ['EN_ATENCION'])))
            ->when($this->filtroEstado === 'ANTERIORES', fn ($q) => $q->whereNotIn('estado', [...Alerta::ESTADOS_ACTIVOS, 'ATENDIDA', ...Alerta::ESTADOS_TERMINALES]))
            ->when($this->filtroEstado === 'ACTIVAS', fn ($q) => $q->abiertas())
            ->when($this->filtroEstado === 'CON_LIMITE_SUPERADO', fn ($q) => $q->abiertas()->whereNotNull('fecha_hora_limite')->where('fecha_hora_limite', '<', now()))
            ->when(! in_array($this->filtroEstado, ['', 'ACTIVAS', 'POR_ATENDER', 'ANTERIORES', 'CON_LIMITE_SUPERADO'], true), fn ($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroNivel === 'PRIORITARIAS', fn ($q) => $q->abiertas()->whereIn('prioridad', Alerta::PRIORIDADES_ALTAS))
            ->when($this->filtroNivel !== '' && $this->filtroNivel !== 'PRIORITARIAS', fn ($q) => $q->whereIn('prioridad', match ($this->filtroNivel) {
                'CRITICO' => ['CRITICO', 'CRITICA'], 'ALTO' => ['ALTO', 'ALTA'], 'MEDIO' => ['MEDIO', 'MEDIA'], 'BAJO' => ['BAJO', 'BAJA'], default => [$this->filtroNivel],
            }))
            ->when($this->filtroOrigen !== '', fn ($q) => $q->where('modulo', $this->filtroOrigen))
            ->when($this->filtroResponsabilidad === 'SIN_RESPONSABLE', fn ($q) => $q->whereNull('cod_personal_responsable'))
            ->when($this->filtroResponsabilidad === 'PROPIAS', fn ($q) => $q->where('cod_personal_responsable', $usuario->personal?->cod_personal ?? '__SIN_PERSONAL__'))
            ->when(str_starts_with($this->filtroResponsabilidad, 'PERSONAL:'), fn ($q) => $q->where('cod_personal_responsable', substr($this->filtroResponsabilidad, 9)))
            ->when(validator(['fecha' => $this->fechaDesde], ['fecha' => 'required|date_format:Y-m-d'])->passes(), fn ($q) => $q->whereDate('fecha_hora', '>=', $this->fechaDesde))
            ->when(validator(['fecha' => $this->fechaHasta], ['fecha' => 'required|date_format:Y-m-d'])->passes(), fn ($q) => $q->whereDate('fecha_hora', '<=', $this->fechaHasta));
        $agrupar = fn (string $campo) => (clone $query)->select($campo)->selectRaw('COUNT(*) as total')
            ->groupBy($campo)->pluck('total', $campo)->map(fn ($n) => (int) $n)->all();
        $chartData = $this->chartData = $this->mostrarAnalisis
            ? ['estados' => $agrupar('estado'), 'origenes' => $agrupar('modulo'), 'niveles' => $agrupar('prioridad')]
            : ['estados' => [], 'origenes' => [], 'niveles' => []];
        $sinResponsableConsulta = (clone $query)->whereNull('cod_personal_responsable')->count();
        $consultaSeleccion = clone $query;
        $alertas = OrdenAlertas::aplicar($query)
            ->with(['residente.cama.habitacion', 'responsable'])->paginate(min(100, max(5, $this->perPage)));
        $vinculosAlertas = $vinculador->paraAlertas($usuario, $alertas->getCollection());
        $prioritarias = OrdenAlertas::aplicar((clone $ambito)->abiertas()->whereIn('prioridad', Alerta::PRIORIDADES_ALTAS))
            ->with('residente')->limit(3)->get();
        $detalle = $this->alertaId ? (clone $ambito)->with(['residente.cama.habitacion', 'responsable.usuario', 'eventos.usuario'])->findOrFail($this->alertaId) : null;
        $signoOrigen = $detalle && in_array(mb_strtoupper(trim((string) $detalle->modulo)), ['SIGNOS', 'SIGNOS_VITALES'], true) && $usuario->can('signos_vitales.ver') && filled($detalle->cod_registro)
            ? SignoVital::query()->where('cod_residente', $detalle->cod_residente)->find($detalle->cod_registro) : null;
        $evolucionSignos = $signoOrigen ? SignoVital::query()->where('cod_residente', $detalle->cod_residente)
            ->whereIn('estado', ['VIGENTE', 'ACTIVO'])->where('fecha_hora', '>=', $signoOrigen->fecha_hora)
            ->orderBy('fecha_hora')->limit(6)->get() : collect();
        $origenDetalle = $this->modalDetalle && $detalle ? app(ConsultarOrigenOperativoAlerta::class)->consultar($usuario, $detalle->getKey()) : ['disponible' => false, 'nodos' => []];
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
        $definicionEspecialidad = app(CamposRegistroEspecialidad::class)->paraRol($perfil['rol']);
        $areaEspecialidad = null;
        $errorContextoEspecialidad = '';
        $atencionesEspecialidad = collect();
        if ($this->modalEspecialidad && $detalle && $definicionEspecialidad) {
            if (! $preview) {
                try {
                    $personalEspecialidad = app(ContextoClinicoService::class)->personalActivo($usuario);
                    $areaEspecialidad = app(ContextoClinicoService::class)->areaAtencion($personalEspecialidad);
                } catch (LogicException $excepcion) {
                    $errorContextoEspecialidad = $excepcion->getMessage();
                }
            }
            if ($definicionEspecialidad['requiereAtencion'] && $usuario->can('atenciones.ver')) {
                $atencionesEspecialidad = Atencion::where('cod_residente', $detalle->cod_residente)
                    ->where('cod_personal', $usuario->personal?->getKey())->orderByDesc('fecha_hora')->limit(8)->get();
            }
        }
        $puedeGuardarEspecialidad = ! $preview && $definicionEspecialidad && $areaEspecialidad
            && $usuario->hasRole($perfil['rol']) && $usuario->can($definicionEspecialidad['permiso'])
            && $detalle && in_array($detalle->residente?->estado, ['ACTIVO', 'ADMITIDO', 'EST_001'], true);
        $residentesQuery = $this->modalCrear ? $turnoService->obtenerPacientesAsignadosQuery($usuario)
            ->when($this->buscarResidente !== '', fn ($q) => $q->where(fn ($r) => $r->whereLike('nombres', '%'.$this->buscarResidente.'%')
                ->orWhereLike('apellido_paterno', '%'.$this->buscarResidente.'%')->orWhereLike('apellido_materno', '%'.$this->buscarResidente.'%')
                ->orWhereLike('cod_residente', '%'.$this->buscarResidente.'%'))) : null;
        $totalResidentes = $residentesQuery ? (clone $residentesQuery)->count() : 0;
        $adultos = $residentesQuery ? $residentesQuery->with('cama.habitacion')->orderBy('apellido_paterno')->orderBy('cod_residente')
            ->offset((max(1, $this->paginaResidentes) - 1) * 8)->limit(8)->get() : collect();
        $residenteSeleccionado = $this->modalCrear && $this->codResidente !== ''
            ? $turnoService->obtenerPacientesAsignadosQuery($usuario)->with('cama.habitacion')->find($this->codResidente) : null;
        $personalConAcceso = $this->modalDetalle && $detalle && $puedeAsignar ? $turnoService->personalConAccesoConsultaResidente($detalle->cod_residente) : [];
        $usuarios = $this->modalDetalle && $puedeAsignar
            ? User::query()->where('estado', 'ACTIVO')->whereHas('personal', fn ($q) => $q->where('estado', 'ACTIVO'))
                ->role(['ENFERMEROS', 'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'])
                ->where(fn ($q) => $q->whereHas('personal', fn ($p) => $p->whereIn('cod_personal', $personalConAcceso))
                    ->orWhereHas('roles', fn ($r) => $r->whereIn('name', ['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'])))
                ->when($this->buscarResponsable !== '', fn ($q) => $q->whereHas('personal', fn ($r) => $r->whereLike('nombres', '%'.$this->buscarResponsable.'%')->orWhereLike('apellido_paterno', '%'.$this->buscarResponsable.'%')->orWhereLike('apellido_materno', '%'.$this->buscarResponsable.'%')))
                ->with(['personal', 'roles.permissions', 'permissions'])
                ->orderBy(Personal::select('nombres')->whereColumn('personal.cod_usuario', 'usuarios.cod_usuario')->limit(1))
                ->orderBy('cod_usuario')->limit(24)->get()->filter(fn ($u) => $u->can('alertas.ver')
                    && ($turnoService->tieneLecturaClinicaGlobal($u) || in_array($u->personal->cod_personal, $personalConAcceso, true))) : collect();

        $codigoTraza = $this->trazaId && (clone $consultaSeleccion)->whereKey($this->trazaId)->exists() ? $this->trazaId : $alertas->first()?->getKey();
        $vinculoSeleccion = [];
        if ($codigoTraza) {
            $vinculoSeleccion = $vinculosAlertas[$codigoTraza]
                ?? $vinculador->paraAlertas($usuario, (clone $ambito)->whereKey($codigoTraza)->get())[$codigoTraza]
                ?? [];
        }
        $contextoEspecialidad = $codigoTraza && in_array($perfil['rol'], array_column($vinculoSeleccion, 'rol'), true)
            ? app(ConsultarContextoEspecialidadAlerta::class)->consultar($usuario, $codigoTraza) : null;
        $traza = $codigoTraza ? app(ConsultarTrazaAlerta::class)->consultar($usuario, $codigoTraza, $contextoEspecialidad) : null;
        $resumenEquipo = app(ConsultarResumenEquipoAlertas::class)->consultar($usuario, $this->filtroAdulto);

        return view('livewire.alertas.alertas-panel', compact('alertas', 'prioritarias', 'conteos', 'chartData', 'detalle', 'signoOrigen', 'evolucionSignos',
            'vinculosAlertas', 'especialidadesDisponibles',
            'definicionEspecialidad', 'areaEspecialidad', 'errorContextoEspecialidad', 'atencionesEspecialidad', 'puedeGuardarEspecialidad', 'contextoEspecialidad', 'resumenEquipo', 'origenDetalle',
            'traza', 'totalResidentes', 'residenteSeleccionado', 'puedeRegistrarNuevaMedicion', 'puedeIntervenir', 'puedeCoordinar', 'puedeCerrar', 'puedeReconocer', 'puedeAsignar', 'origenesDisponibles', 'sinResponsableConsulta', 'perfil', 'adultos', 'usuarios', 'preview', 'estadosHistoricos'))
            ->layout($perfil['foco'] === 'turno' ? 'layouts.enfermeria' : 'layouts.sistema');
    }
}
