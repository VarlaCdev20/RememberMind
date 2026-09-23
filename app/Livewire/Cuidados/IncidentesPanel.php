<?php

namespace App\Livewire\Cuidados;

use App\Models\Alerta;
use App\Models\Area;
use App\Models\Derivacion;
use App\Models\EventoAlerta;
use App\Models\Incidente;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Residente;
use App\Models\ResidenteContacto;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
        'OTRO'
    ];

    public array $gravedadesDisponibles = [
        'BAJA' => 'Baja (Leve, sin lesión)',
        'MEDIA' => 'Media (Moderada, contusión leve)',
        'ALTA' => 'Alta (Urgente, lesión visible/dolor)',
        'CRITICA' => 'Crítica (Emergencia médica inmediata)'
    ];

    protected $listeners = [
        'refrescarIncidentes' => '$refresh'
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
        $this->mostrarMasFiltros = !$this->mostrarMasFiltros;
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtro_tipo = '';
        $this->filtro_gravedad = '';
        $this->filtro_estado = '';
        $this->fecha_desde = '';
        $this->fecha_hasta = '';
        $this->resetPage();
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
        $this->cod_residente = $codResidente;
        $this->residenteSeleccionado = Residente::with([
            'cama.habitacion',
            'ocupacionActiva'
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
        if (!$user) {
            session()->flash('error', 'Sesión expirada o no autenticada.');
            return;
        }

        // Obtener el personal autenticado
        $codUsuario = $user->cod_usuario ?? $user->cod_usu;
        $personal = $user->personal ?: Personal::where('cod_usuario', $codUsuario)->first();
        if (!$personal) {
            $personal = Personal::where('estado', 'ACTIVO')->first();
        }
        $codPersonal = $personal ? $personal->cod_personal : null;

        if (!$codPersonal) {
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
        $fechaHoraIncidente = Carbon::parse($this->fecha_incidente . ' ' . $this->hora_incidente);
        if ($fechaHoraIncidente->isFuture()) {
            $this->addError('hora_incidente', 'La fecha y hora del incidente no pueden ser futuras.');
            return;
        }

        // Obtener jornada activa si existe
        $jornadaActiva = Jornada::where('estado', 'ACTIVO')
            ->whereDate('fecha', Carbon::today())
            ->first();
        $codJornada = $jornadaActiva ? $jornadaActiva->cod_jornada : null;

        // Normalización backend
        $tipoNormalizado = Str::upper(preg_replace('/\s+/', ' ', trim($tipoFinal)));
        $gravedadNormalizada = Str::upper(trim($this->gravedad));

        DB::beginTransaction();
        try {
            $incidente = new Incidente();
            $incidente->cod_residente = $this->cod_residente;
            $incidente->cod_personal = $codPersonal;
            $incidente->cod_jornada = $codJornada;
            $incidente->tipo_incidente = Str::substr($tipoNormalizado, 0, 60);
            $incidente->gravedad = $gravedadNormalizada;
            $incidente->lugar = $this->lugar ? Str::substr(trim($this->lugar), 0, 120) : null;
            $incidente->fecha_hora = $fechaHoraIncidente;
            $incidente->descripcion = trim($this->descripcion);
            $incidente->medida_inmediata = $this->medida_inmediata ? trim($this->medida_inmediata) : null;
            $incidente->requiere_medico = (bool)$this->requiere_medico;
            $incidente->requiere_derivacion = (bool)$this->requiere_derivacion;
            $incidente->estado = 'ABIERTO';
            $incidente->observacion = $this->observacion ? trim($this->observacion) : null;
            $incidente->save();

            DB::commit();

            // Log de actividad
            activity()
                ->performedOn($incidente)
                ->causedBy($user)
                ->withProperties([
                    'residente' => $this->cod_residente,
                    'tipo' => $tipoNormalizado,
                    'gravedad' => $gravedadNormalizada,
                    'requiere_medico' => $this->requiere_medico,
                    'requiere_derivacion' => $this->requiere_derivacion
                ])
                ->log('Registro de incidente de enfermería');

            $this->ultimoCodIncidente = $incidente->cod_incidente;
            $this->ultimoRequiereMedico = (bool)$this->requiere_medico;
            $this->ultimoRequiereDerivacion = (bool)$this->requiere_derivacion;
            $this->incidenteSeleccionadoId = $incidente->cod_incidente;

            $this->modalRegistrar = false;
            $this->modalExito = true;

            session()->flash('mensaje', 'Incidencia registrada con éxito: ' . $incidente->cod_incidente);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al registrar incidente: ' . $e->getMessage(), ['exception' => $e]);
            $this->addError('error_general', 'Ocurrió un error al registrar el incidente: ' . $e->getMessage());
        }
    }

    // ==========================================
    // MODAL VER INCIDENTE Y CONTACTOS
    // ==========================================
    public function verIncidente(string $codIncidente): void
    {
        $this->cerrarMenus();
        $this->incidenteSeleccionadoId = $codIncidente;
        $this->incidenteDetalle = Incidente::with([
            'residente.cama.habitacion',
            'personal.usuario',
            'jornada'
        ])->find($codIncidente);

        if (!$this->incidenteDetalle) {
            session()->flash('error', 'El incidente no existe o ha sido retirado.');
            return;
        }

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
                'nombre' => $c ? trim($c->nombres . ' ' . $c->apellido_paterno . ' ' . ($c->apellido_materno ?? '')) : 'Sin nombre',
                'parentesco' => $rel->parentesco ?? 'Contacto',
                'telefono' => $c?->telefono ?? $c?->celular ?? 'Sin teléfono',
                'es_emergencia' => (bool)$rel->contacto_emergencia,
                'es_principal' => (bool)$rel->responsable_principal,
            ];
        })->toArray();
    }

    // ==========================================
    // CAMBIO DE ESTADO TRAZABLE
    // ==========================================
    public function abrirModalEstado(string $codIncidente): void
    {
        $this->cerrarMenus();
        $this->incidenteSeleccionadoId = $codIncidente;
        $incidente = Incidente::find($codIncidente);
        if (!$incidente) return;

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
            'nota_estado' => 'required|string|min:5|max:500'
        ], [
            'nuevo_estado.required' => 'Seleccione el nuevo estado.',
            'nota_estado.required' => 'Especifique la justificación clínica del cambio de estado.',
            'nota_estado.min' => 'La nota debe tener al menos 5 caracteres.'
        ]);

        $incidente = Incidente::find($this->incidenteSeleccionadoId);
        if (!$incidente) return;

        $estadoAnterior = $incidente->estado;
        $incidente->estado = $this->nuevo_estado;
        
        $fechaActual = Carbon::now()->format('d/m/Y H:i');
        $usuarioNombre = Auth::user()->name ?? 'Personal';
        $anexo = "\n[{$fechaActual} - {$usuarioNombre}]: Estado cambiado de {$estadoAnterior} a {$this->nuevo_estado}. Justificación: " . trim($this->nota_estado);
        
        $incidente->observacion = ($incidente->observacion ?? '') . $anexo;
        $incidente->save();

        activity()
            ->performedOn($incidente)
            ->causedBy(Auth::user())
            ->withProperties([
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $this->nuevo_estado,
                'nota' => $this->nota_estado
            ])
            ->log('Actualización de estado de incidente');

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
        if (!$id) return;

        $incidente = Incidente::with(['residente', 'personal'])->find($id);
        if (!$incidente) return;

        // Comprobar si ya tiene una alerta activa vinculada
        $alertaExistente = Alerta::where('modulo', 'INCIDENTES')
            ->where('cod_registro', $id)
            ->whereIn('estado', ['ACTIVA', 'PENDIENTE'])
            ->first();

        if ($alertaExistente) {
            session()->flash('info', 'Ya existe una solicitud médica/alerta activa para este incidente (' . $alertaExistente->cod_alerta . ').');
            return;
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $codUsuario = $user->cod_usuario ?? $user->cod_usu;
            $personal = $user->personal ?: Personal::where('cod_usuario', $codUsuario)->first();
            $codPersonal = $personal ? $personal->cod_personal : $incidente->cod_personal;

            // Prioridad según gravedad
            $prioridad = in_array($incidente->gravedad, ['ALTA', 'CRITICA']) ? 'ALTA' : 'MEDIA';
            if ($incidente->gravedad === 'CRITICA') {
                $prioridad = 'CRITICA';
            }

            $alerta = new Alerta();
            $alerta->cod_alerta = 'ALE_' . strtoupper(Str::random(10));
            $alerta->cod_residente = $incidente->cod_residente;
            $alerta->cod_personal_responsable = $codPersonal;
            $alerta->tipo = 'CLINICA';
            $alerta->prioridad = $prioridad;
            $alerta->modulo = 'INCIDENTES';
            $alerta->cod_registro = $incidente->cod_incidente;
            $alerta->titulo = "Valoración médica urgente: {$incidente->tipo_incidente}";
            $alerta->descripcion = "Incidente: {$incidente->tipo_incidente} (Gravedad: {$incidente->gravedad}). Detalle: {$incidente->descripcion}";
            $alerta->fecha_hora = Carbon::now();
            $alerta->fecha_hora_limite = Carbon::now()->addHours(2);
            $alerta->generacion = 'MANUAL';
            $alerta->estado = 'ACTIVA';
            $alerta->save();

            // Evento de alerta para trazabilidad
            $evento = new EventoAlerta();
            $evento->cod_evento_alerta = 'EAL_' . strtoupper(Str::random(10));
            $evento->cod_alerta = $alerta->cod_alerta;
            $evento->cod_usuario = $codUsuario;
            $evento->tipo_evento = 'CREACION';
            $evento->estado_anterior = null;
            $evento->estado_nuevo = 'ACTIVA';
            $evento->fecha_hora = Carbon::now();
            $evento->descripcion = "Alerta médica solicitada por enfermería a partir de la incidencia {$incidente->cod_incidente}.";
            $evento->save();

            // Marcar incidente como requiere_medico = true y en seguimiento si estaba abierto
            $incidente->requiere_medico = true;
            if ($incidente->estado === 'ABIERTO') {
                $incidente->estado = 'EN_SEGUIMIENTO';
            }
            $incidente->save();

            DB::commit();

            activity()
                ->performedOn($alerta)
                ->causedBy($user)
                ->withProperties(['incidente' => $incidente->cod_incidente])
                ->log('Generación de alerta médica para incidente');

            session()->flash('mensaje', 'Solicitud de valoración médica enviada exitosamente (Alerta: ' . $alerta->cod_alerta . ').');

            if ($this->modalVer) {
                $this->alertaVinculada = $alerta;
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al generar alerta de incidente: ' . $e->getMessage());
            session()->flash('error', 'No se pudo generar la solicitud médica: ' . $e->getMessage());
        }
    }

    // ==========================================
    // MODAL Y CREACIÓN DE DERIVACIÓN
    // ==========================================
    public function abrirModalDerivacion(?string $codIncidente = null): void
    {
        $this->cerrarMenus();
        $id = $codIncidente ?: $this->incidenteSeleccionadoId;
        if (!$id) return;

        $incidente = Incidente::find($id);
        if (!$incidente) return;

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
            'derivacion_motivo' => 'required|string|min:10|max:500'
        ], [
            'derivacion_area_receptora.required' => 'Seleccione el área receptora de la derivación.',
            'derivacion_motivo.required' => 'Indique el motivo clínico de la derivación.'
        ]);

        $incidente = Incidente::find($this->incidenteSeleccionadoId);
        if (!$incidente) return;

        $user = Auth::user();
        $codUsuario = $user->cod_usuario ?? $user->cod_usu;
        $personal = $user->personal ?: Personal::where('cod_usuario', $codUsuario)->first();
        $codPersonal = $personal ? $personal->cod_personal : $incidente->cod_personal;

        // Área de enfermería como solicitante
        $areaEnfermeria = Area::where('nombre', 'LIKE', '%ENFERMER%')
            ->orWhere('nombre', 'LIKE', '%SALUD%')
            ->first();
        $codAreaSolicitante = $areaEnfermeria ? $areaEnfermeria->cod_area : $this->derivacion_area_receptora;

        DB::beginTransaction();
        try {
            $derivacion = new Derivacion();
            $derivacion->cod_derivacion = 'DER_' . strtoupper(Str::random(10));
            $derivacion->cod_residente = $incidente->cod_residente;
            $derivacion->cod_area_solicitante = $codAreaSolicitante;
            $derivacion->cod_area_receptora = $this->derivacion_area_receptora;
            $derivacion->cod_personal_solicitante = $codPersonal;
            $derivacion->cod_personal_receptor = null;
            $derivacion->cod_atencion = null;
            $derivacion->motivo = trim($this->derivacion_motivo);
            $derivacion->prioridad = $this->derivacion_prioridad;
            $derivacion->fecha_hora = Carbon::now();
            $derivacion->respuesta = null;
            $derivacion->estado = 'SOLICITADA';
            $derivacion->save();

            // Actualizar flag en incidente y anexo trazable
            $incidente->requiere_derivacion = true;
            $fechaActual = Carbon::now()->format('d/m/Y H:i');
            $incidente->observacion = ($incidente->observacion ?? '') . "\n[{$fechaActual}]: Derivación creada ({$derivacion->cod_derivacion}) hacia área receptora.";
            $incidente->save();

            DB::commit();

            activity()
                ->performedOn($derivacion)
                ->causedBy($user)
                ->withProperties(['incidente' => $incidente->cod_incidente])
                ->log('Creación de derivación desde incidente de enfermería');

            $this->modalDerivacion = false;
            session()->flash('mensaje', 'Derivación ' . $derivacion->cod_derivacion . ' creada con éxito.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear derivación: ' . $e->getMessage());
            $this->addError('error_derivacion', 'No se pudo crear la derivación: ' . $e->getMessage());
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
        // 1. Query para Residentes disponibles en el selector
        $residentesQuery = Residente::query()
            ->with(['cama.habitacion', 'ocupacionActiva'])
            ->where('estado', 'ACTIVO');

        if (!empty($this->searchResidente)) {
            $s = '%' . trim($this->searchResidente) . '%';
            $residentesQuery->where(function ($q) use ($s) {
                $q->where('nombres', 'LIKE', $s)
                  ->orWhere('apellido_paterno', 'LIKE', $s)
                  ->orWhere('apellido_materno', 'LIKE', $s)
                  ->orWhere('numero_documento', 'LIKE', $s);
            });
        }
        $residentesDisponibles = $residentesQuery->orderBy('apellido_paterno')->limit(30)->get();

        // 2. Query de Incidentes
        $query = Incidente::query()
            ->with([
                'residente.cama.habitacion',
                'personal.usuario',
                'alertaAsociada'
            ]);

        // Filtro texto: residente, tipo, descripción
        if (!empty($this->search)) {
            $term = '%' . trim($this->search) . '%';
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

        // Filtro tipo
        if (!empty($this->filtro_tipo)) {
            $query->where('tipo_incidente', $this->filtro_tipo);
        }

        // Filtro gravedad
        if (!empty($this->filtro_gravedad)) {
            $query->where('gravedad', $this->filtro_gravedad);
        }

        // Filtro estado
        if (!empty($this->filtro_estado)) {
            $query->where('estado', $this->filtro_estado);
        }

        // Filtro rango de fechas
        if (!empty($this->fecha_desde)) {
            $query->whereDate('fecha_hora', '>=', $this->fecha_desde);
        }
        if (!empty($this->fecha_hasta)) {
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

        $incidentes = $query->paginate(15);

        // Áreas disponibles para derivación
        $areasDisponibles = Area::where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();

        return view('livewire.cuidados.incidentes-panel', [
            'incidentes' => $incidentes,
            'residentesDisponibles' => $residentesDisponibles,
            'areasDisponibles' => $areasDisponibles
        ])->layout(request()->routeIs('admin.enfermeria.*') ? 'layouts.enfermeria' : 'layouts.sistema');
    }
}
