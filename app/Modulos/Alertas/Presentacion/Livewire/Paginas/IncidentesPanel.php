<?php

namespace App\Modulos\Alertas\Presentacion\Livewire\Paginas;

use App\Models\Alerta;
use App\Models\Area;
use App\Models\Incidente;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use App\Modulos\Alertas\Aplicacion\Acciones\GestionarIncidentesPanel;
use App\Modulos\Cuidados\Aplicacion\Consultas\TurnoEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class IncidentesPanel extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Tabs
    public string $tabActiva = 'listado'; // 'listado' | 'historial'

    // Filtros
    public string $search = '';

    public string $filtro_tipo = '';

    public string $filtro_gravedad = '';

    public string $filtro_estado = '';

    public string $fecha_desde = '';

    public string $fecha_hasta = '';

    public bool $mostrarMasFiltros = false;

    // Modales de control
    public bool $modalRegistrar = false;

    public bool $modalVer = false;

    public bool $modalEstado = false;

    public bool $modalDerivacion = false;

    public bool $modalExito = false;

    // Residente seleccionado para registro
    public ?string $cod_residente = null;

    public ?Residente $residenteSeleccionado = null;

    public string $searchResidente = '';

    // Formulario de Registro
    public string $tipo_incidente = '';

    public string $tipo_incidente_otro = '';

    public string $gravedad = 'BAJA'; // BAJA, MEDIA, ALTA, CRITICA

    public string $lugar = '';

    public string $fecha_incidente = '';

    public string $hora_incidente = '';

    public string $descripcion = '';

    public string $medida_inmediata = '';

    public bool $requiere_medico = false;

    public bool $requiere_derivacion = false;

    public string $observacion = '';

    // Incidente en visualización / acción
    public ?string $incidenteSeleccionadoId = null;

    public ?Incidente $incidenteDetalle = null;

    public ?Alerta $alertaVinculada = null;

    public array $contactosEmergencia = [];

    // Formulario de cambio de estado
    public string $nuevo_estado = '';

    public string $nota_estado = '';

    // Formulario de derivación asociada
    public string $derivacion_area_receptora = '';

    public string $derivacion_prioridad = 'ALTA';

    public string $derivacion_motivo = '';

    // Datos del último registro exitoso
    public ?string $ultimoCodIncidente = null;

    public bool $ultimoRequiereMedico = false;

    public bool $ultimoRequiereDerivacion = false;

    // Menú contextual abierto
    public ?string $menuAbiertoId = null;

    // Tipos de incidente estándar
    public array $tiposFrecuentes = [
        'CAÍDA',
        'RECHAZO DE MEDICACIÓN',
        'ALTERACIÓN CONDUCTUAL',
        'SÍNTOMA CLÍNICO',
        'PROBLEMA CON DISPOSITIVO',
        'OTRO',
    ];

    public array $gravedadesDisponibles = [
        'BAJA' => 'Baja (Leve, sin lesión)',
        'MEDIA' => 'Media (Moderada, contusión leve)',
        'ALTA' => 'Alta (Urgente, lesión visible/dolor)',
        'CRITICA' => 'Crítica (Emergencia médica inmediata)',
    ];

    protected $listeners = [
        'refrescarIncidentes' => '$refresh',
    ];

    public function mount()
    {
        $this->inicializarFechaHora();
    }

    public function inicializarFechaHora(): void
    {
        $ahora = Carbon::now();
        $this->fecha_incidente = $ahora->format('Y-m-d');
        $this->hora_incidente = $ahora->format('H:i');
    }

    public function setTab(string $tab): void
    {
        $this->tabActiva = in_array($tab, ['listado', 'historial']) ? $tab : 'listado';
        $this->filtro_estado = '';
        $this->cerrarMenus();
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroGravedad(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatedFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatedFechaHasta(): void
    {
        $this->resetPage();
    }

    public function toggleMasFiltros(): void
    {
        $this->mostrarMasFiltros = ! $this->mostrarMasFiltros;
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtro_tipo = '';
        $this->filtro_gravedad = '';
        $this->filtro_estado = '';
        $this->fecha_desde = '';
        $this->fecha_hasta = '';
        $this->resetValidation(['fecha_desde', 'fecha_hasta']);
        $this->resetPage();
    }

    public function limpiarFiltro(string $campo): void
    {
        if (! in_array($campo, ['search', 'filtro_tipo', 'filtro_gravedad', 'filtro_estado', 'fecha_desde', 'fecha_hasta'], true)) {
            return;
        }
        $this->{$campo} = '';
        $this->resetPage();
    }

    private function consultaIncidentes(): Builder
    {
        $usuario = Auth::user();
        abort_unless($usuario && $usuario->estado === 'ACTIVO', 403);

        return Incidente::query()->when($usuario->hasRole('ENFERMEROS'), fn (Builder $query) => $query
            ->whereIn('cod_residente', app(TurnoEnfermeriaService::class)->obtenerPacientesAsignadosIds($usuario)));
    }

    public function toggleMenuContextual(string $id): void
    {
        $this->menuAbiertoId = ($this->menuAbiertoId === $id) ? null : $id;
    }

    public function cerrarMenus(): void
    {
        $this->menuAbiertoId = null;
    }

    // ==========================================
    // SELECCIÓN Y BÚSQUEDA DE RESIDENTE
    // ==========================================
    public function seleccionarResidente(string $codResidente): void
    {
        if (Auth::user()?->hasRole('ENFERMEROS')) {
            app(TurnoEnfermeriaService::class)->autorizarAccionPaciente($codResidente, Auth::user());
        }
        $this->cod_residente = $codResidente;
        $this->residenteSeleccionado = Residente::with([
            'cama.habitacion',
            'ocupacionActiva',
        ])->find($codResidente);
        $this->searchResidente = '';
    }

    public function quitarResidente(): void
    {
        $this->cod_residente = null;
        $this->residenteSeleccionado = null;
    }

    // ==========================================
    // MODAL REGISTRO
    // ==========================================
    public function abrirModalRegistro(?string $codResidente = null): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->inicializarFechaHora();

        $this->tipo_incidente = '';
        $this->tipo_incidente_otro = '';
        $this->gravedad = 'BAJA';
        $this->lugar = '';
        $this->descripcion = '';
        $this->medida_inmediata = '';
        $this->requiere_medico = false;
        $this->requiere_derivacion = false;
        $this->observacion = '';

        if ($codResidente) {
            $this->seleccionarResidente($codResidente);
        } else {
            $this->cod_residente = null;
            $this->residenteSeleccionado = null;
        }

        $this->modalRegistrar = true;
    }

    public function cerrarModalRegistro(): void
    {
        $this->modalRegistrar = false;
    }

    public function registrarIncidente(): void
    {
        $user = Auth::user();
        if (! $user) {
            session()->flash('error', 'Sesión expirada o no autenticada.');

            return;
        }

        $personal = Personal::query()
            ->where('cod_usuario', $user->cod_usuario)
            ->where('estado', 'ACTIVO')
            ->first();
        $codPersonal = $personal?->cod_personal;

        if (! $codPersonal) {
            $this->addError('error_general', 'No se encontró un perfil de personal activo asociado.');

            return;
        }

        // Validar tipo_incidente
        $tipoFinal = $this->tipo_incidente;
        if ($this->tipo_incidente === 'OTRO') {
            $tipoFinal = trim($this->tipo_incidente_otro);
        }

        // Validación frontend / backend
        $this->validate([
            'cod_residente' => 'required|exists:residentes,cod_residente',
            'tipo_incidente' => 'required|string',
            'tipo_incidente_otro' => $this->tipo_incidente === 'OTRO' ? 'required|string|min:3|max:60' : 'nullable',
            'gravedad' => 'required|in:BAJA,MEDIA,ALTA,CRITICA',
            'lugar' => 'nullable|string|max:120',
            'fecha_incidente' => 'required|date|before_or_equal:today',
            'hora_incidente' => 'required',
            'descripcion' => 'required|string|min:10',
            'medida_inmediata' => 'nullable|string',
            'requiere_medico' => 'boolean',
            'requiere_derivacion' => 'boolean',
            'observacion' => 'nullable|string',
        ], [
            'cod_residente.required' => 'Debe seleccionar un residente.',
            'tipo_incidente.required' => 'Seleccione el tipo de incidente.',
            'tipo_incidente_otro.required' => 'Especifique el tipo de incidente.',
            'tipo_incidente_otro.max' => 'El tipo especificado no debe superar 60 caracteres.',
            'fecha_incidente.before_or_equal' => 'La fecha no puede ser futura.',
            'descripcion.required' => 'La descripción objetiva del evento es obligatoria.',
            'descripcion.min' => 'Describa el evento con al menos 10 caracteres.',
        ]);

        // Validar que la fecha y hora no sean en el futuro
        $fechaHoraIncidente = Carbon::parse($this->fecha_incidente.' '.$this->hora_incidente);
        if ($fechaHoraIncidente->isFuture()) {
            $this->addError('hora_incidente', 'La fecha y hora del incidente no pueden ser futuras.');

            return;
        }

        $turnoActivo = app(TurnoEnfermeriaService::class)
            ->autorizarMutacionPaciente($this->cod_residente, 'incidentes.crear', $user);
        $jornadaActiva = Jornada::where('cod_turno', $turnoActivo->cod_turno)
            ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO'])
            ->whereDate('fecha_jornada', Carbon::today())
            ->firstOrFail();
        $codJornada = $jornadaActiva->cod_jornada;

        try {
            $incidente = app(GestionarIncidentesPanel::class)->registrarIncidente($user, ['tipo_incidente' => $this->tipo_incidente, 'tipo_incidente_otro' => $this->tipo_incidente_otro, 'fecha_incidente' => $this->fecha_incidente, 'hora_incidente' => $this->hora_incidente, 'cod_residente' => $this->cod_residente, 'gravedad' => $this->gravedad, 'lugar' => $this->lugar, 'descripcion' => $this->descripcion, 'medida_inmediata' => $this->medida_inmediata, 'requiere_medico' => $this->requiere_medico, 'requiere_derivacion' => $this->requiere_derivacion, 'observacion' => $this->observacion]);

            $this->ultimoCodIncidente = $incidente->cod_incidente;
            $this->ultimoRequiereMedico = (bool) $this->requiere_medico;
            $this->ultimoRequiereDerivacion = (bool) $this->requiere_derivacion;
            $this->incidenteSeleccionadoId = $incidente->cod_incidente;

            $this->modalRegistrar = false;
            $this->modalExito = true;

            session()->flash('mensaje', 'Incidencia registrada con éxito.');
        } catch (\Throwable $e) {
            Log::error('Error al registrar incidente: '.$e->getMessage(), ['exception' => $e]);
            $this->addError('error_general', 'No se pudo registrar el incidente. Conservamos los datos; inténtelo de nuevo.');
        }
    }

    // ==========================================
    // MODAL VER INCIDENTE Y CONTACTOS
    // ==========================================
    public function verIncidente(string $codIncidente): void
    {
        $this->cerrarMenus();
        $incidente = $this->consultaIncidentes()->with([
            'residente.cama.habitacion',
            'personal.usuario',
            'jornada',
        ])->findOrFail($codIncidente);
        $this->incidenteSeleccionadoId = $codIncidente;
        $this->incidenteDetalle = $incidente;

        // Buscar alerta asociada
        $this->alertaVinculada = Alerta::where('modulo', 'INCIDENTES')
            ->where('cod_registro', $codIncidente)
            ->whereIn('estado', ['ACTIVA', 'PENDIENTE', 'EN_PROCESO'])
            ->latest('fecha_hora')
            ->first();

        // Contactos de emergencia del residente
        $this->cargarContactosEmergencia($this->incidenteDetalle->cod_residente);

        $this->modalVer = true;
    }

    public function cerrarModalVer(): void
    {
        $this->modalVer = false;
        $this->incidenteDetalle = null;
        $this->alertaVinculada = null;
        $this->contactosEmergencia = [];
    }

    protected function cargarContactosEmergencia(string $codResidente): void
    {
        $contactosRel = ResidenteContacto::with('contacto')
            ->where('cod_residente', $codResidente)
            ->where(function ($q) {
                $q->where('contacto_emergencia', true)
                    ->orWhere('responsable_principal', true);
            })
            ->where('estado', 'ACTIVO')
            ->get();

        $this->contactosEmergencia = $contactosRel->map(function ($rel) {
            $c = $rel->contacto;

            return [
                'nombre' => $c ? trim($c->nombres.' '.$c->apellido_paterno.' '.($c->apellido_materno ?? '')) : 'Sin nombre',
                'parentesco' => $rel->parentesco ?? 'Contacto',
                'telefono' => $c?->telefono ?? $c?->celular ?? 'Sin teléfono',
                'es_emergencia' => (bool) $rel->contacto_emergencia,
                'es_principal' => (bool) $rel->responsable_principal,
            ];
        })->toArray();
    }

    // ==========================================
    // CAMBIO DE ESTADO TRAZABLE
    // ==========================================
    public function abrirModalEstado(string $codIncidente): void
    {
        $this->cerrarMenus();
        $incidente = $this->consultaIncidentes()->findOrFail($codIncidente);
        $this->autorizarMutacionIncidente($incidente);
        $this->incidenteSeleccionadoId = $codIncidente;

        $this->nuevo_estado = $incidente->estado;
        $this->nota_estado = '';
        $this->modalEstado = true;
    }

    public function cerrarModalEstado(): void
    {
        $this->modalEstado = false;
        $this->incidenteSeleccionadoId = null;
    }

    public function actualizarEstado(): void
    {
        $this->validate([
            'nuevo_estado' => 'required|in:ABIERTO,EN_SEGUIMIENTO,CERRADO,ANULADO',
            'nota_estado' => 'required|string|min:5|max:500',
        ], [
            'nuevo_estado.required' => 'Seleccione el nuevo estado.',
            'nota_estado.required' => 'Especifique la justificación clínica del cambio de estado.',
            'nota_estado.min' => 'La nota debe tener al menos 5 caracteres.',
        ]);

        if (! app(GestionarIncidentesPanel::class)->actualizarEstado(auth()->user(), ['incidenteSeleccionadoId' => $this->incidenteSeleccionadoId, 'nuevo_estado' => $this->nuevo_estado, 'nota_estado' => $this->nota_estado])) {
            return;
        }

        $this->modalEstado = false;
        session()->flash('mensaje', "Estado actualizado a {$this->nuevo_estado} correctamente.");
    }

    // ==========================================
    // SOLICITAR VALORACIÓN MÉDICA (ALERTA)
    // ==========================================
    public function solicitarValoracionMedica(?string $codIncidente = null): void
    {
        $this->cerrarMenus();
        $id = $codIncidente ?: $this->incidenteSeleccionadoId;
        if (! $id) {
            return;
        }

        $incidente = Incidente::with(['residente', 'personal'])->find($id);
        if (! $incidente) {
            return;
        }
        $this->autorizarMutacionIncidente($incidente);

        // Comprobar si ya tiene una alerta activa vinculada
        $alertaExistente = Alerta::where('modulo', 'INCIDENTES')
            ->where('cod_registro', $id)
            ->whereIn('estado', ['ACTIVA', 'PENDIENTE'])
            ->first();

        if ($alertaExistente) {
            session()->flash('info', 'Ya existe una solicitud médica o alerta activa para este incidente.');

            return;
        }

        try {
            $alerta = app(GestionarIncidentesPanel::class)->solicitarValoracionMedica(Auth::user(), $incidente);

            session()->flash('mensaje', 'Solicitud de valoración médica enviada exitosamente.');

            if ($this->modalVer) {
                $this->alertaVinculada = $alerta;
            }
        } catch (\Throwable $e) {
            Log::error('Error al generar alerta de incidente: '.$e->getMessage());
            session()->flash('error', 'No se pudo generar la solicitud médica. Inténtelo de nuevo.');
        }
    }

    // ==========================================
    // MODAL Y CREACIÓN DE DERIVACIÓN
    // ==========================================
    public function abrirModalDerivacion(?string $codIncidente = null): void
    {
        $this->cerrarMenus();
        $id = $codIncidente ?: $this->incidenteSeleccionadoId;
        if (! $id) {
            return;
        }

        $incidente = $this->consultaIncidentes()->findOrFail($id);
        $this->autorizarMutacionIncidente($incidente);

        $this->incidenteSeleccionadoId = $id;
        $this->derivacion_prioridad = in_array($incidente->gravedad, ['ALTA', 'CRITICA']) ? 'ALTA' : 'MEDIA';
        $this->derivacion_motivo = "Derivación originada por incidente [{$incidente->tipo_incidente}]: {$incidente->descripcion}";
        $this->derivacion_area_receptora = '';

        $this->modalDerivacion = true;
    }

    public function cerrarModalDerivacion(): void
    {
        $this->modalDerivacion = false;
    }

    public function crearDerivacion(): void
    {
        $this->validate([
            'derivacion_area_receptora' => 'required|exists:areas,cod_area',
            'derivacion_prioridad' => 'required|in:BAJA,MEDIA,ALTA,URGENTE',
            'derivacion_motivo' => 'required|string|min:10|max:500',
        ], [
            'derivacion_area_receptora.required' => 'Seleccione el área receptora de la derivación.',
            'derivacion_motivo.required' => 'Indique el motivo clínico de la derivación.',
        ]);

        $incidente = Incidente::find($this->incidenteSeleccionadoId);
        if (! $incidente) {
            return;
        }
        $this->autorizarMutacionIncidente($incidente);

        $user = Auth::user();
        $codUsuario = $user->cod_usuario;
        $personal = Personal::query()
            ->where('cod_usuario', $codUsuario)
            ->where('estado', 'ACTIVO')
            ->first();
        if (! $personal) {
            $this->addError('derivacion_area_receptora', 'El usuario autenticado no tiene un perfil de personal activo.');

            return;
        }
        $codPersonal = $personal->cod_personal;

        $codAreaSolicitante = $personal->asignaciones()
            ->whereIn('estado', ['ACTIVA', 'ACTIVO'])
            ->whereHas('jornada', fn ($query) => $query->whereDate('fecha_jornada', today()))
            ->latest('fecha_asignacion')
            ->value('cod_area');
        if (! $codAreaSolicitante) {
            $this->addError('derivacion_area_receptora', 'El personal no tiene un área institucional activa en la jornada actual.');

            return;
        }

        try {
            $derivacion = app(GestionarIncidentesPanel::class)->crearDerivacion($user, ['derivacion_area_receptora' => $this->derivacion_area_receptora, 'derivacion_motivo' => $this->derivacion_motivo, 'derivacion_prioridad' => $this->derivacion_prioridad], $incidente);

            $this->modalDerivacion = false;
            session()->flash('mensaje', 'Derivación creada con éxito.');
        } catch (\Throwable $e) {
            Log::error('Error al crear derivación: '.$e->getMessage());
            $this->addError('error_derivacion', 'No se pudo crear la derivación. Conservamos los datos; inténtelo de nuevo.');
        }
    }

    public function cerrarModalExito(): void
    {
        $this->modalExito = false;
    }

    // ==========================================
    // RENDER & QUERY
    // ==========================================
    public function render()
    {
        $turnos = app(TurnoEnfermeriaService::class);
        $usuario = Auth::user();
        $restringirPorAsignacion = (bool) $usuario?->hasRole('ENFERMEROS');
        $residentesAsignados = $restringirPorAsignacion
            ? $turnos->obtenerPacientesAsignadosIds($usuario)
            : [];

        // 1. Query para Residentes disponibles en el selector
        $residentesQuery = Residente::query()
            ->with(['cama.habitacion', 'ocupacionActiva'])
            ->whereIn('estado', ['ACTIVO', 'ADMITIDO'])
            ->when($restringirPorAsignacion, fn ($residentes) => $residentes->whereIn('cod_residente', $residentesAsignados));

        if (! empty($this->searchResidente)) {
            $s = '%'.trim($this->searchResidente).'%';
            $residentesQuery->where(function ($q) use ($s) {
                $q->where('nombres', 'LIKE', $s)
                    ->orWhere('apellido_paterno', 'LIKE', $s)
                    ->orWhere('apellido_materno', 'LIKE', $s)
                    ->orWhere('numero_documento', 'LIKE', $s);
            });
        }
        $residentesDisponibles = $residentesQuery->orderBy('apellido_paterno')->limit(30)->get();

        // 2. Query de Incidentes
        $query = $this->consultaIncidentes()
            ->with([
                'residente.cama.habitacion',
                'personal.usuario',
                'alertaAsociada',
            ]);

        // Totales y tipos pertenecen exclusivamente al mismo alcance autorizado.
        $totalesEstado = (clone $query)->reorder()->selectRaw('estado, COUNT(*) as cantidad')->groupBy('estado')->pluck('cantidad', 'estado');
        $conteos = [
            'listado' => (int) ($totalesEstado['ABIERTO'] ?? 0) + (int) ($totalesEstado['EN_SEGUIMIENTO'] ?? 0),
            'historial' => (int) ($totalesEstado['CERRADO'] ?? 0) + (int) ($totalesEstado['ANULADO'] ?? 0),
        ];
        $tiposFiltro = collect($this->tiposFrecuentes)->reject(fn ($tipo) => $tipo === 'OTRO')
            ->merge((clone $query)->reorder()->distinct()->pluck('tipo_incidente'))->unique()->sort()->values();
        $estadosFiltro = $this->tabActiva === 'historial'
            ? ['CERRADO' => 'Cerrado', 'ANULADO' => 'Anulado']
            : ['ABIERTO' => 'Abierto', 'EN_SEGUIMIENTO' => 'En seguimiento'];
        $query->whereIn('estado', array_keys($estadosFiltro));

        // Filtro texto: residente, tipo, descripción
        if (trim($this->search) !== '') {
            foreach (preg_split('/\s+/u', trim($this->search), -1, PREG_SPLIT_NO_EMPTY) as $palabra) {
                $term = '%'.$palabra.'%';
                $query->where(function ($q) use ($term) {
                    $q->where('tipo_incidente', 'LIKE', $term)
                        ->orWhere('descripcion', 'LIKE', $term)
                        ->orWhere('lugar', 'LIKE', $term)
                        ->orWhereHas('residente', function ($r) use ($term) {
                            $r->where('nombres', 'LIKE', $term)
                                ->orWhere('apellido_paterno', 'LIKE', $term)
                                ->orWhere('apellido_materno', 'LIKE', $term);
                        });
                });
            }
        }

        // Filtro tipo
        if (! empty($this->filtro_tipo)) {
            $query->where('tipo_incidente', $this->filtro_tipo);
        }

        // Filtro gravedad
        if (! empty($this->filtro_gravedad)) {
            $query->where('gravedad', $this->filtro_gravedad);
        }

        // Filtro estado
        if (! empty($this->filtro_estado)) {
            $query->where('estado', $this->filtro_estado);
        }

        // Filtro rango de fechas
        $fechas = Validator::make([
            'fecha_desde' => $this->fecha_desde,
            'fecha_hasta' => $this->fecha_hasta,
        ], [
            'fecha_desde' => 'nullable|date_format:Y-m-d',
            'fecha_hasta' => 'nullable|date_format:Y-m-d'.($this->fecha_desde !== '' ? '|after_or_equal:fecha_desde' : ''),
        ], [
            'fecha_desde.date_format' => 'Seleccione una fecha inicial válida.',
            'fecha_hasta.date_format' => 'Seleccione una fecha final válida.',
            'fecha_hasta.after_or_equal' => 'Hasta debe ser igual o posterior a Desde.',
        ]);
        $this->resetValidation(['fecha_desde', 'fecha_hasta']);
        if ($fechas->fails()) {
            foreach ($fechas->errors()->messages() as $campo => $mensajes) {
                $this->addError($campo, $mensajes[0]);
            }
            $query->whereRaw('1 = 0');
        }
        if (! $fechas->fails() && $this->fecha_desde !== '') {
            $query->whereDate('fecha_hora', '>=', $this->fecha_desde);
        }
        if (! $fechas->fails() && $this->fecha_hasta !== '') {
            $query->whereDate('fecha_hora', '<=', $this->fecha_hasta);
        }

        // Ordenamiento según la especificación:
        // 1. incidentes graves/urgentes activos;
        // 2. gravedad alta;
        // 3. en seguimiento;
        // 4. abiertos;
        // 5. cerrados;
        // y dentro del mismo nivel, más reciente primero.
        if ($this->tabActiva === 'listado') {
            $query->orderByRaw("
                CASE 
                    WHEN gravedad IN ('CRITICA', 'ALTA') AND estado IN ('ABIERTO', 'EN_SEGUIMIENTO') THEN 1
                    WHEN gravedad = 'ALTA' THEN 2
                    WHEN estado = 'EN_SEGUIMIENTO' THEN 3
                    WHEN estado = 'ABIERTO' THEN 4
                    WHEN estado = 'CERRADO' THEN 5
                    ELSE 6
                END ASC
            ")->orderBy('fecha_hora', 'desc');
        } else {
            // Historial: cronológico puro, más reciente primero
            $query->orderBy('fecha_hora', 'desc');
        }

        $incidentes = $query->orderBy('cod_incidente', 'desc')->paginate(15);

        // Áreas disponibles para derivación
        $areasDisponibles = Area::where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();

        return view('livewire.cuidados.incidentes-panel', [
            'incidentes' => $incidentes,
            'residentesDisponibles' => $residentesDisponibles,
            'areasDisponibles' => $areasDisponibles,
            'conteos' => $conteos,
            'tiposFiltro' => $tiposFiltro,
            'estadosFiltro' => $estadosFiltro,
            'puedeRegistrar' => (bool) $usuario?->can('incidentes.crear') && (! $restringirPorAsignacion || $turnos->obtenerTurnoActivo($usuario) !== null),
        ])->layout(request()->routeIs('admin.enfermeria.*') ? 'layouts.enfermeria' : 'layouts.sistema');
    }

    private function autorizarMutacionIncidente(Incidente $incidente): void
    {
        if (Auth::user()?->hasRole('ENFERMEROS')) {
            app(TurnoEnfermeriaService::class)
                ->autorizarMutacionPaciente($incidente->cod_residente, 'incidentes.crear', Auth::user());
        }
    }
}
