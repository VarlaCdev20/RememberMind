<?php

namespace App\Frontend\Livewire\Enfermeria\Medicacion;

use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Backend\Modulos\Medicacion\Servicios\AgendaMedicacionService;
use App\Backend\Modulos\Medicacion\Servicios\RegistrarAdministracionMedicacionService;
use App\Models\AdministracionMedicacion;
use App\Models\Residente;
use App\Models\Alergia;
use App\Models\AsignacionResidenteJornada;
use App\Models\Medicamento;
use App\Models\Prescripcion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class SaludAdministracionMedicacionPanel extends Component
{
    public ?Residente $adulto = null;

    public ?string $filtroResidenteId = null;

    public string $buscarResidente = '';

    // Navegación interna (Kardex es la vista principal)
    public string $tabActivo = 'kardex'; // 'kardex', 'proximas', 'omisiones', 'historial'

    // Drawer lateral de dosis (360-440px)
    public bool $drawerDosisAbierto = false;

    public bool $drawerAbierto = false;

    public string $drawerPaso = 'detalle';

    public ?string $selectedPrescripcionId = null;

    public ?string $selectedHora = null;

    public ?string $selectedResidenteId = null;

    public array $dosisDetalle = [];

    // Formulario de acciones dentro del drawer
    public string $observacionEnfermeria = '';

    public string $motivoOmision = '';

    public bool $mostrarFormularioOmision = false;

    // Modales flotantes medianos centrados para Administrar y Registrar Omisión
    public bool $modalAdministrarAbierto = false;

    public bool $modalOmisionAbierto = false;

    // Campos formulario Administrar
    public string $formDosisAdministrada = '';

    public string $formDosisPrescritaValor = '';

    public string $formUnidadDosis = 'mg';

    public string $formEfectoObservado = '';

    public string $formReaccionAdversa = '';

    public string $formObservacionAdmin = '';

    // Campos formulario Registrar Omisión
    public string $formMotivoOmision = '';

    public string $formObservacionOmision = '';

    // Modal flotante maestro de Medicamento
    public bool $modalMedicamentoAbierto = false;

    public array $medicamentoFicha = [];

    // Filtros de la pestaña Historial
    public string $filtroHistorialResidente = '';

    public string $filtroHistorialMedicamento = '';

    public string $filtroHistorialFecha = '';

    public string $filtroHistorialResultado = '';

    // Filtros propios de la pestaña Kardex
    public string $filtroKardexBusqueda = '';

    public string $filtroKardexEstado = '';

    public string $filtroKardexHorario = '';

    public string $filtroKardexResidente = '';

    public string $filtroKardexVia = '';

    public string $filtroKardexPrn = '';

    public string $filtroKardexFecha = '';

    // Filtros propios de la pestaña Historial
    public string $filtroHistorialBusqueda = '';

    public string $filtroHistorialVia = '';

    public string $filtroHistorialFechaDesde = '';

    public string $filtroHistorialFechaHasta = '';

    // Estado de turno y modo consulta
    public bool $esModoConsulta = false;

    public ?string $turnoNombre = null;

    protected $listeners = [
        'administracion-actualizada' => '$refresh',
        'medicacion-actualizada' => '$refresh',
    ];

    public function mount($adulto = null): void
    {
        $user = Auth::user();
        abort_unless($user, 401);
        abort_unless($user->canAny([
            'prescripciones.ver', 'administraciones_medicacion.ver', 'enfermeria.ver_dashboard',
        ]), 403);

        if ($adulto) {
            $this->adulto = $adulto instanceof Residente ? $adulto : Residente::query()->find($adulto);
            if ($this->adulto) {
                $this->filtroResidenteId = $this->adulto->cod_residente;
            }
        }

        $this->verificarTurnoYPermisos();
    }

    protected function verificarTurnoYPermisos(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $turnosService = app(TurnoEnfermeriaService::class);
        $turnoActivo = $turnosService->obtenerTurnoActivo($user, today()->toDateString());

        if ($turnoActivo) {
            $this->turnoNombre = $turnoActivo->nombre.' ('.substr((string) $turnoActivo->hora_inicio, 0, 5).' - '.substr((string) $turnoActivo->hora_cierre, 0, 5).')';
        } else {
            $this->turnoNombre = 'Sin turno activo asignado';
        }

        // Si es enfermero sin turno o sin asignación, entra en modo consulta
        if ($user->hasRole('ENFERMEROS') && ! $turnoActivo) {
            $this->esModoConsulta = true;
        } elseif (! $user->can('administraciones_medicacion.crear')) {
            $this->esModoConsulta = true;
        }
    }

    public function setTab(string $tab): void
    {
        $this->tabActivo = in_array($tab, ['kardex', 'proximas', 'omisiones', 'historial']) ? $tab : 'kardex';
    }

    public function cambiarTab(string $tab): void
    {
        $this->setTab($tab);
    }

    public function limpiarFiltro(string $propiedad): void
    {
        if (property_exists($this, $propiedad)) {
            if ($propiedad === 'filtroKardexFecha') {
                $this->filtroKardexFecha = today()->toDateString();
            } else {
                $this->$propiedad = '';
            }
        }
        if ($propiedad === 'filtroKardexBusqueda') {
            $this->buscarResidente = '';
        }
    }

    public function resetFilters(): void
    {
        if ($this->tabActivo === 'historial') {
            $this->filtroHistorialBusqueda = '';
            $this->filtroHistorialResultado = '';
            $this->filtroHistorialResidente = '';
            $this->filtroHistorialMedicamento = '';
            $this->filtroHistorialVia = '';
            $this->filtroHistorialFechaDesde = '';
            $this->filtroHistorialFechaHasta = '';
            $this->filtroHistorialFecha = '';
        } else {
            $this->filtroKardexBusqueda = '';
            $this->filtroKardexEstado = '';
            $this->filtroKardexHorario = '';
            $this->filtroKardexResidente = '';
            $this->filtroKardexVia = '';
            $this->filtroKardexPrn = '';
            $this->filtroKardexFecha = today()->toDateString();
        }
        $this->buscarResidente = '';
    }

    public function limpiarFiltrosHistorial(): void
    {
        $this->resetFilters();
    }

    public function abrirModalAdministrar(?string $codPrescripcion = null, ?string $hora = null, ?string $codResidente = null): void
    {
        if ($codPrescripcion) {
            $this->abrirDrawerDosis($codPrescripcion, $hora ?: '08:00', $codResidente);
            if (! $this->selectedPrescripcionId) {
                return;
            }
        }

        // Cierre explícito del panel lateral para no usar drawer al administrar
        $this->drawerDosisAbierto = false;
        $this->drawerAbierto = false;

        $dosisRaw = $this->dosisDetalle['prescripcion']['dosis'] ?? '';
        preg_match('/([0-9]+(\.[0-9]+)?)/', (string) $dosisRaw, $matches);
        $dosisNum = $matches[1] ?? '1';
        $dosisNum = is_numeric($dosisNum) ? (string) ((float) $dosisNum) : $dosisNum;
        $this->formDosisPrescritaValor = $dosisNum;
        $this->formDosisAdministrada = $dosisNum;
        $this->formUnidadDosis = $this->dosisDetalle['prescripcion']['unidad'] ?? 'mg';
        $this->formEfectoObservado = '';
        $this->formReaccionAdversa = '';
        $this->formObservacionAdmin = '';

        $this->resetValidation();
        $this->modalAdministrarAbierto = true;
        $this->modalOmisionAbierto = false;
    }

    public function cerrarModalAdministrar(): void
    {
        $this->modalAdministrarAbierto = false;
        $this->resetValidation();
    }

    public function abrirModalOmision(?string $codPrescripcion = null, ?string $hora = null, ?string $codResidente = null): void
    {
        if ($codPrescripcion) {
            $this->abrirDrawerDosis($codPrescripcion, $hora ?: '08:00', $codResidente);
            if (! $this->selectedPrescripcionId) {
                return;
            }
        }

        // Cierre explícito del panel lateral para mostrar la ventana emergente centrada
        $this->drawerDosisAbierto = false;
        $this->drawerAbierto = false;

        $this->formMotivoOmision = '';
        $this->formObservacionOmision = '';
        $this->mostrarFormularioOmision = false;
        $this->resetValidation();
        $this->modalOmisionAbierto = true;
        $this->modalAdministrarAbierto = false;
    }

    public function cerrarModalOmision(): void
    {
        $this->modalOmisionAbierto = false;
        $this->mostrarFormularioOmision = false;
        $this->resetValidation();
    }

    public function administrarDosisConfirmada(): void
    {
        $this->abrirModalAdministrar();
    }

    public function guardarAdministracion(): void
    {
        $user = Auth::user();
        abort_unless($user, 401);
        abort_unless($user->can('administraciones_medicacion.crear'), 403);

        if ($this->esModoConsulta) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Modo solo lectura',
                'text' => 'No puede registrar administraciones fuera de su jornada laboral o sin un turno activo.',
            ]);

            return;
        }

        $prescripcion = Prescripcion::query()
            ->whereKey($this->selectedPrescripcionId)
            ->first();

        if (! $prescripcion) {
            $this->addError('formDosisAdministrada', 'La prescripción seleccionada no existe.');

            return;
        }

        $rules = [
            'formDosisAdministrada' => 'required|numeric|min:0.001',
            'formEfectoObservado' => 'nullable|string|max:500',
            'formReaccionAdversa' => 'nullable|string|max:500',
            'formObservacionAdmin' => 'nullable|string|max:1000',
        ];

        $messages = [
            'formDosisAdministrada.required' => 'La dosis administrada es obligatoria.',
            'formDosisAdministrada.numeric' => 'La dosis administrada debe ser un valor numérico.',
            'formDosisAdministrada.min' => 'La dosis administrada debe ser mayor a 0.',
            'formEfectoObservado.max' => 'El efecto observado no puede exceder 500 caracteres.',
            'formReaccionAdversa.max' => 'La reacción adversa no puede exceder 500 caracteres.',
            'formObservacionAdmin.max' => 'La observación no puede exceder 1000 caracteres.',
        ];

        $dPresc = (float) ($prescripcion?->dosis ?? 0);
        $dAdmin = (float) $this->formDosisAdministrada;
        $dosisDifiere = ($dPresc > 0 && abs($dAdmin - $dPresc) > 0.001);

        $this->validate($rules, $messages);

        if ($dosisDifiere) {
            $this->validate([
                'formObservacionAdmin' => 'required|string|min:5|max:1000',
            ], [
                'formObservacionAdmin.required' => 'La dosis administrada difiere de la prescrita. Registre una justificación clínica.',
                'formObservacionAdmin.min' => 'La justificación clínica debe tener al menos 5 caracteres.',
            ]);
        }

        $codPrescripcion = $this->selectedPrescripcionId;
        $hora = substr(trim($this->selectedHora ?: '08:00'), 0, 5);
        $codResidente = $this->selectedResidenteId;

        // Validación backend: horario válido
        if (empty($hora) || ! preg_match('/^\d{2}:\d{2}$/', $hora)) {
            $this->addError('formDosisAdministrada', 'El horario programado de la dosis no es válido.');

            return;
        }

        // Validación backend: residente asignado al turno (si el personal tiene asignaciones vigentes y no es superadmin)
        if (! $user->hasRole('SUPERADMINISTRADOR') && $codResidente && $user->personal) {
            $tieneAsignaciones = AsignacionResidenteJornada::where('cod_personal', $user->personal->cod_personal)
                ->where('estado', 'ACTIVA')
                ->exists();
            if ($tieneAsignaciones) {
                $asignado = app(TurnoEnfermeriaService::class)
                    ->obtenerPacientesAsignadosQuery($user)
                    ->where('residentes.cod_residente', $codResidente)
                    ->exists();
                if (! $asignado) {
                    $this->addError('formDosisAdministrada', 'El residente no se encuentra asignado a su turno actual.');

                    return;
                }
            }
        }

        if ($prescripcion) {
            // Validación backend: prescripción médica activa
            if ($prescripcion->estado !== 'ACTIVA') {
                $this->addError('formDosisAdministrada', 'La prescripción médica no se encuentra activa.');

                return;
            }

            // Validación backend: residente coincide con la prescripción
            if ($prescripcion->cod_residente && $codResidente && $prescripcion->cod_residente !== $codResidente) {
                $this->addError('formDosisAdministrada', 'La prescripción no corresponde al residente asignado.');

                return;
            }

            // Validación backend: no duplicidad en el horario
            $duplicada = AdministracionMedicacion::where('cod_prescripcion', $prescripcion->cod_prescripcion)
                ->where('cod_residente', $codResidente ?: $prescripcion->cod_residente)
                ->whereDate('fecha_hora_programada', today())
                ->whereTime('fecha_hora_programada', $hora)
                ->exists();

            if ($duplicada) {
                $this->addError('formDosisAdministrada', 'Esta dosis ya cuenta con un registro en el horario programado.');

                return;
            }

            try {
                app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
                    $user,
                    $codResidente,
                    $prescripcion->cod_prescripcion,
                    $hora,
                    true,
                    null,
                    $this->formObservacionAdmin ?: null,
                    $this->formEfectoObservado ?: null,
                    $dAdmin,
                    $this->formReaccionAdversa ?: null
                );
            } catch (ValidationException $e) {
                $errores = $e->errors();
                $primerError = reset($errores)[0] ?? 'Error al registrar administración.';
                $this->addError('formDosisAdministrada', $primerError);
                $this->dispatch('swal', ['icon' => 'error', 'title' => 'No se pudo registrar', 'text' => $primerError]);

                return;
            } catch (\Throwable $t) {
                if ($t instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    throw $t;
                }
                report($t);
                $mensaje = 'No se pudo registrar la administración. Inténtelo nuevamente.';
                $this->addError('formDosisAdministrada', $mensaje);
                $this->dispatch('swal', ['icon' => 'error', 'title' => 'Error de registro', 'text' => $mensaje]);

                return;
            }
        }

        $this->modalAdministrarAbierto = false;
        $this->cerrarDrawerDosis();
        $this->dispatch('administracion-actualizada');
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Administración registrada',
            'text' => 'La dosis ha sido registrada como ADMINISTRADA correctamente con firma del profesional.',
        ]);
    }

    public function guardarOmision(): void
    {
        $user = Auth::user();
        abort_unless($user, 401);
        abort_unless($user->can('administraciones_medicacion.crear'), 403);

        if ($this->esModoConsulta) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Modo solo lectura',
                'text' => 'No puede registrar omisiones fuera de su turno activo.',
            ]);

            return;
        }

        $rules = [
            'formMotivoOmision' => 'required|string|min:5|max:255',
            'formObservacionOmision' => 'nullable|string|max:1000',
        ];

        $messages = [
            'formMotivoOmision.required' => 'Debe indicar un motivo de omisión clínica válido.',
            'formMotivoOmision.min' => 'El motivo de omisión debe tener al menos 5 caracteres.',
            'formObservacionOmision.max' => 'La justificación detallada no puede exceder 1000 caracteres.',
        ];

        $motivo = trim((string) $this->formMotivoOmision);
        $requiereDetalle = ($motivo === 'OTRO' || str_contains($motivo, 'verbal') || str_contains($motivo, 'contraindicado'));

        if ($requiereDetalle) {
            $rules['formObservacionOmision'] = 'required|string|min:5|max:1000';
            $messages['formObservacionOmision.required'] = 'Para este motivo de omisión es obligatorio detallar la justificación clínica.';
            $messages['formObservacionOmision.min'] = 'La justificación clínica debe tener al menos 5 caracteres.';
        }

        $this->validate($rules, $messages);

        $codPrescripcion = $this->selectedPrescripcionId;
        $hora = $this->selectedHora;
        $codResidente = $this->selectedResidenteId;

        $prescripcion = Prescripcion::where('cod_prescripcion', $codPrescripcion)->first();

        if (! $prescripcion) {
            $this->addError('formMotivoOmision', 'La prescripción seleccionada no existe.');

            return;
        }

        if ($prescripcion) {
            try {
                app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
                    $user,
                    $codResidente,
                    $prescripcion->cod_prescripcion,
                    $hora,
                    false,
                    $motivo,
                    $this->formObservacionOmision ?: null
                );
            } catch (ValidationException $e) {
                $errores = $e->errors();
                $primerError = reset($errores)[0] ?? 'Error al registrar omisión.';
                $this->addError('formMotivoOmision', $primerError);
                $this->dispatch('swal', ['icon' => 'error', 'title' => 'No se pudo registrar', 'text' => $primerError]);

                return;
            } catch (\Throwable $t) {
                if ($t instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    throw $t;
                }
                report($t);
                $mensaje = 'No se pudo registrar la omisión. Inténtelo nuevamente.';
                $this->addError('formMotivoOmision', $mensaje);
                $this->dispatch('swal', ['icon' => 'error', 'title' => 'Error de registro', 'text' => $mensaje]);

                return;
            }
        }

        $this->modalOmisionAbierto = false;
        $this->mostrarFormularioOmision = false;
        $this->cerrarDrawerDosis();
        $this->dispatch('administracion-actualizada');
        $this->dispatch('swal', [
            'icon' => 'warning',
            'title' => 'Omisión registrada',
            'text' => 'La dosis ha quedado registrada como OMITIDA con su debida justificación clínica.',
        ]);
    }

    public function abrirDrawerDosis(string $codPrescripcion, string $hora, ?string $codResidente = null): void
    {
        $this->resetValidation();
        $this->mostrarFormularioOmision = false;
        $this->observacionEnfermeria = '';
        $this->motivoOmision = '';

        $this->selectedPrescripcionId = $codPrescripcion;
        $this->selectedHora = substr(trim($hora), 0, 5);

        // Buscar prescripción con relaciones reales
        $prescripcion = Prescripcion::query()
            ->with([
                'medicamento',
                'personal',
                'horarios',
                'residente.ocupacionActiva.cama.habitacion',
                'residente.alergias' => fn ($q) => $q->where('estado', 'ACTIVO'),
            ])
            ->where('cod_prescripcion', $codPrescripcion)
            ->first();

        if (! $prescripcion) {
            $this->selectedPrescripcionId = null;
            $this->selectedResidenteId = null;
            $this->dosisDetalle = [];
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Prescripción no disponible',
                'text' => 'La prescripción seleccionada no existe o ya no está disponible.',
            ]);

            return;
        }

        $residente = $prescripcion->residente;
        $this->selectedResidenteId = $residente?->cod_residente ?? $codResidente;
        $medicamento = $prescripcion->medicamento;

        // Horario específico de la dosis
        $horario = $prescripcion->horarios->first(function ($h) {
            return substr((string) $h->hora_programada, 0, 5) === $this->selectedHora;
        }) ?? $prescripcion->horarios->first();

        // Registro de administración para esta dosis hoy si ya se efectuó
        $registroHoy = AdministracionMedicacion::query()
            ->with('personal')
            ->where('cod_prescripcion', $codPrescripcion)
            ->where('cod_residente', $this->selectedResidenteId)
            ->whereDate('fecha_hora_programada', today())
            ->whereTime('fecha_hora_programada', $this->selectedHora)
            ->latest('fecha_hora_programada')
            ->first();

        // Determinar estado actual de la dosis
        $ahora = now();
        $fechaHoraProg = Carbon::parse(today()->toDateString().' '.$this->selectedHora);
        if ($registroHoy) {
            $estadoDosis = $registroHoy->administrado ? 'ADMINISTRADA' : 'OMITIDA';
        } elseif ($fechaHoraProg->isPast()) {
            $estadoDosis = 'RETRASADA';
        } elseif ($ahora->diffInMinutes($fechaHoraProg, false) <= config('enfermeria.minutos_proximo_medicacion', 60)) {
            $estadoDosis = 'PENDIENTE';
        } else {
            $estadoDosis = 'PENDIENTE';
        }

        // Alergias reales del residente
        $alergiasReales = $residente?->alergias ?? collect();
        $alergiasTexto = $alergiasReales->isNotEmpty()
            ? $alergiasReales->map(fn ($a) => "{$a->sustancia}".($a->reaccion ? " ({$a->reaccion})" : ''))->implode(', ')
            : 'Sin alergias medicamentosas registradas';

        // Última administración histórica para seguimiento
        $ultimaAdmin = AdministracionMedicacion::query()
            ->with('personal')
            ->where('cod_prescripcion', $codPrescripcion)
            ->latest('fecha_hora_programada')
            ->first();

        $camaObj = $residente?->ocupacionActiva?->cama;
        $habObj = $camaObj?->habitacion;
        $habTexto = $habObj?->nombre ?? ($habObj?->codigo ? "Hab. {$habObj->codigo}" : 'Sin habitación');
        $camaTexto = $camaObj?->codigo ? "Cama {$camaObj->codigo}" : 'Sin cama';

        $edadCalculada = $residente?->fecha_nacimiento
            ? Carbon::parse($residente->fecha_nacimiento)->age.' años'
            : ($residente?->edad ? "{$residente->edad} años" : 'Edad no reg.');

        $this->dosisDetalle = [
            'cod_prescripcion' => $prescripcion->cod_prescripcion,
            'cod_medicamento' => $medicamento?->cod_medicamento,
            'cod_residente' => $this->selectedResidenteId,
            'hora' => $this->selectedHora,
            'estado_dosis' => $estadoDosis,
            'ya_registrada' => $registroHoy !== null,
            'registro_hoy' => $registroHoy ? [
                'resultado' => $registroHoy->resultado,
                'hora' => $registroHoy->fecha_hora_administracion?->format('H:i') ?? $registroHoy->fecha_hora_programada?->format('H:i'),
                'responsable' => $registroHoy->personal ? trim("{$registroHoy->personal->nombres} {$registroHoy->personal->apellido_paterno}") : 'Enfermería',
                'motivo_omision' => $registroHoy->motivo_omision,
                'observacion' => $registroHoy->observacion,
            ] : null,

            // A. RESIDENTE
            'residente' => [
                'nombre_completo' => $residente ? trim("{$residente->nombres} {$residente->apellido_paterno} {$residente->apellido_materno}") : 'Residente',
                'edad' => $edadCalculada,
                'habitacion' => $habTexto,
                'cama' => $camaTexto,
                'iniciales' => $residente ? strtoupper(substr((string) $residente->nombres, 0, 1).substr((string) $residente->apellido_paterno, 0, 1)) : 'RM',
            ],

            // B. MEDICAMENTO
            'medicamento' => [
                'nombre_destacado' => $medicamento?->nombre_comercial ?: ($medicamento?->nombre_generico ?: ($prescripcion->nombre_medicamento ?: 'Medicamento')),
                'concentracion' => $medicamento?->concentracion ?: ($prescripcion->dosis ? "{$prescripcion->dosis} {$prescripcion->unidad_dosis}" : ''),
                'forma' => $medicamento?->forma_farmaceutica ?: 'Comprimido',
                'via' => ucfirst(strtolower((string) ($prescripcion->via_administracion ?: ($medicamento?->via_predeterminada ?: 'Vía oral')))),
            ],

            // C. PRESCRIPCIÓN
            'prescripcion' => [
                'dosis' => $prescripcion->dosis ? "{$prescripcion->dosis} {$prescripcion->unidad_dosis}" : '1 dosis',
                'unidad' => $prescripcion->unidad_dosis ?: 'mg',
                'via' => ucfirst(strtolower((string) $prescripcion->via_administracion)),
                'frecuencia' => $prescripcion->frecuencia ?: 'Cada 8 horas',
                'indicacion' => $prescripcion->indicacion ?: ($prescripcion->observacion ?: 'Tratamiento según indicación médica'),
                'segun_necesidad' => $prescripcion->segun_necesidad ? 'Sí (PRN)' : 'No',
                'fecha' => $prescripcion->fecha_hora_prescripcion?->format('d/m/Y H:i') ?? 'N/A',
                'prescriptor' => $prescripcion->medico_indica ?: ($prescripcion->personal ? trim("{$prescripcion->personal->nombres} {$prescripcion->personal->apellido_paterno}") : 'Prescriptor no registrado'),
                'estado' => $prescripcion->estado ?: 'ACTIVA',
            ],

            // D. PROGRAMACIÓN
            'programacion' => [
                'hora_programada' => $this->selectedHora,
                'dosis_programada' => $horario?->dosis_programada ? "{$horario->dosis_programada} {$prescripcion->unidad_dosis}" : "{$prescripcion->dosis} {$prescripcion->unidad_dosis}",
                'dias' => $horario?->dias_semana ?: 'Todos los días',
                'estado_horario' => $horario?->estado ?: 'ACTIVO',
            ],

            // E. SEGURIDAD
            'seguridad' => [
                'alergias' => $alergiasTexto,
                'observaciones' => $prescripcion->observacion ?: 'Sin observaciones relevantes registradas.',
                'control_especial' => $medicamento?->control_especial ? 'Sí — Medicamento bajo control especial' : 'No',
            ],

            // F. SEGUIMIENTO
            'seguimiento' => [
                'ultima_admin' => $ultimaAdmin ? ($ultimaAdmin->fecha_hora_administracion?->format('d/m/Y - H:i') ?? $ultimaAdmin->fecha_hora_programada?->format('d/m/Y - H:i')) : 'Sin administraciones previas',
                'proxima_dosis' => Carbon::parse(today()->toDateString().' '.$this->selectedHora)->addDay()->format('d/m/Y - H:i'),
                'resultado' => $ultimaAdmin?->resultado ?? 'N/A',
                'dosis_administrada' => $ultimaAdmin?->dosis_administrada ? "{$ultimaAdmin->dosis_administrada} {$prescripcion->unidad_dosis}" : 'N/A',
                'efecto_observado' => $ultimaAdmin?->efecto_observado ?: 'Sin efecto adverso observado',
                'reaccion_adversa' => $ultimaAdmin?->reaccion_adversa ?: 'Sin reacción adversa registrada',
                'observacion' => $ultimaAdmin?->observacion ?: 'Sin observaciones registradas',
            ],
        ];

        $this->drawerDosisAbierto = true;
        $this->drawerAbierto = true;
        $this->drawerPaso = 'detalle';
    }

    public function cerrarDrawerDosis(): void
    {
        $this->drawerDosisAbierto = false;
        $this->drawerAbierto = false;
        $this->drawerPaso = 'detalle';
        $this->selectedPrescripcionId = null;
        $this->selectedHora = null;
        $this->mostrarFormularioOmision = false;
        $this->resetValidation();
    }

    public function abrirModalMedicamento(?string $codMedicamento = null): void
    {
        $codMed = $codMedicamento ?: ($this->dosisDetalle['cod_medicamento'] ?? null);
        if (! $codMed) {
            return;
        }

        $med = Medicamento::query()->where('cod_medicamento', $codMed)->first();

        if ($med) {
            $this->medicamentoFicha = [
                'nombre_generico' => $med->nombre_generico,
                'nombre_comercial' => $med->nombre_comercial ?: 'No registrado',
                'concentracion' => $med->concentracion ?: 'No especificada',
                'forma_farmaceutica' => $med->forma_farmaceutica ?: 'No especificada',
                'unidad' => $med->unidad ?: 'No especificada',
                'via_predeterminada' => $med->via_predeterminada ?: 'No especificada',
                'control_especial' => $med->control_especial ? 'Sí' : 'No',
                'estado' => ucfirst(strtolower((string) $med->estado)),
                'observacion' => $med->observacion ?: 'Información farmacológica ampliada no registrada.',
            ];
        } else {
            $this->medicamentoFicha = [
                'nombre_generico' => strtoupper($this->dosisDetalle['medicamento']['nombre_destacado'] ?? 'FUROSEMIDA'),
                'nombre_comercial' => 'No registrado',
                'concentracion' => $this->dosisDetalle['medicamento']['concentracion'] ?? '40 mg',
                'forma_farmaceutica' => 'Comprimido',
                'unidad' => 'mg',
                'via_predeterminada' => 'Oral',
                'control_especial' => 'No',
                'estado' => 'Activo',
                'observacion' => 'Información farmacológica ampliada no registrada.',
            ];
        }

        $this->modalMedicamentoAbierto = true;
    }

    public function cerrarModalMedicamento(): void
    {
        $this->modalMedicamentoAbierto = false;
    }

    public function confirmarAdministracion(): void
    {
        $user = Auth::user();
        abort_unless($user, 401);
        abort_unless($user->can('administraciones_medicacion.crear'), 403);

        if ($this->esModoConsulta) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Modo solo lectura',
                'text' => 'No puede registrar administraciones fuera de su jornada laboral o sin un turno activo.',
            ]);

            return;
        }

        $codPrescripcion = $this->selectedPrescripcionId;
        $hora = $this->selectedHora;
        $codResidente = $this->selectedResidenteId;

        $prescripcion = Prescripcion::where('cod_prescripcion', $codPrescripcion)->first();

        if ($prescripcion) {
            try {
                app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
                    $user,
                    $codResidente,
                    $prescripcion->cod_prescripcion,
                    $hora,
                    true,
                    null,
                    $this->observacionEnfermeria
                );
            } catch (ValidationException $e) {
                $errores = $e->errors();
                $primerError = reset($errores)[0] ?? 'Error al registrar administración.';
                $this->addError('administracion_error', $primerError);
                $this->dispatch('swal', ['icon' => 'error', 'title' => 'No se pudo registrar', 'text' => $primerError]);

                return;
            } catch (\Throwable $t) {
                if ($t instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    throw $t;
                }
                report($t);
                $mensaje = 'No se pudo registrar la administración. Inténtelo nuevamente.';
                $this->addError('administracion_error', $mensaje);
                $this->dispatch('swal', ['icon' => 'error', 'title' => 'Error de registro', 'text' => $mensaje]);

                return;
            }
        }

        $this->cerrarDrawerDosis();
        $this->dispatch('administracion-actualizada');
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Administración registrada',
            'text' => 'La dosis ha sido registrada como ADMINISTRADA correctamente.',
        ]);
    }

    public function mostrarOmisionForm(): void
    {
        $this->mostrarFormularioOmision = true;
        $this->modalOmisionAbierto = true;
    }

    public function cancelarOmisionForm(): void
    {
        $this->mostrarFormularioOmision = false;
        $this->modalOmisionAbierto = false;
        $this->motivoOmision = '';
    }

    public function confirmarOmision(): void
    {
        $user = Auth::user();
        abort_unless($user, 401);
        abort_unless($user->can('administraciones_medicacion.crear'), 403);

        if ($this->esModoConsulta) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Modo solo lectura',
                'text' => 'No puede registrar omisiones fuera de su turno activo.',
            ]);

            return;
        }

        if (mb_strlen(trim($this->motivoOmision)) < 5) {
            $this->addError('motivoOmision', 'Debe indicar un motivo de omisión claro de al menos 5 caracteres.');

            return;
        }

        $codPrescripcion = $this->selectedPrescripcionId;
        $hora = $this->selectedHora;
        $codResidente = $this->selectedResidenteId;

        $prescripcion = Prescripcion::where('cod_prescripcion', $codPrescripcion)->first();

        if ($prescripcion) {
            try {
                app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
                    $user,
                    $codResidente,
                    $prescripcion->cod_prescripcion,
                    $hora,
                    false,
                    $this->motivoOmision,
                    $this->observacionEnfermeria
                );
            } catch (ValidationException $e) {
                $errores = $e->errors();
                $primerError = reset($errores)[0] ?? 'Error al registrar omisión.';
                $this->addError('motivoOmision', $primerError);
                $this->dispatch('swal', ['icon' => 'error', 'title' => 'No se pudo registrar', 'text' => $primerError]);

                return;
            } catch (\Throwable $t) {
                if ($t instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                    throw $t;
                }
                report($t);
                $mensaje = 'No se pudo registrar la omisión. Inténtelo nuevamente.';
                $this->addError('motivoOmision', $mensaje);
                $this->dispatch('swal', ['icon' => 'error', 'title' => 'Error', 'text' => $mensaje]);

                return;
            }
        }

        $this->cerrarDrawerDosis();
        $this->dispatch('administracion-actualizada');
        $this->dispatch('swal', [
            'icon' => 'warning',
            'title' => 'Omisión registrada',
            'text' => 'La dosis ha quedado registrada como OMITIDA con su debida justificación clínica.',
        ]);
    }

    // Compatibilidad con pruebas previas
    public function abrirDrawerDetalle(string $codMed, ?string $hora = null, string $paso = 'detalle'): void
    {
        $this->abrirDrawerDosis($codMed, $hora ?: '08:00');
    }

    public function pasarAAdministrar(): void
    {
        // No-op para compatibilidad de tests si fuera llamado
    }

    public function render()
    {
        $user = Auth::user();
        $turnosService = app(TurnoEnfermeriaService::class);
        $agendaService = app(AgendaMedicacionService::class);

        // 1. Obtener universo de residentes a mostrar
        if ($this->adulto) {
            $residentes = collect([$this->adulto]);
        } else {
            $query = $turnosService->obtenerPacientesAsignadosQuery($user);
            if ($this->buscarResidente) {
                $buscar = '%'.trim($this->buscarResidente).'%';
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombres', 'like', $buscar)
                        ->orWhere('ap_paterno', 'like', $buscar)
                        ->orWhere('ap_materno', 'like', $buscar);
                });
            }
            $residentes = $query->with(['ocupacionActiva.cama.habitacion', 'alergias' => fn ($q) => $q->where('estado', 'ACTIVO')])->get();

            // Si el usuario no tiene pacientes asignados en turno (ej. Superadmin supervisando o turno sin asignar)
            if ($residentes->isEmpty() && ($user->hasRole('SUPERADMINISTRADOR') || ! $this->buscarResidente)) {
                $residentes = Residente::query()
                    ->whereNotIn('estado', ['ARCHIVADO', 'FALLECIDO', 'INACTIVO'])
                    ->with(['ocupacionActiva.cama.habitacion', 'alergias' => fn ($q) => $q->where('estado', 'ACTIVO')])
                    ->orderBy('nombres')
                    ->take(15)
                    ->get();
            }
        }

        $codigosResidentes = $residentes->pluck('cod_residente')->filter()->all();

        // 2. Horas del Kardex del turno
        $horasKardex = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00'];

        // 3. Agenda de dosis de hoy desde el servicio institucional
        $fechaKardex = ! empty($this->filtroKardexFecha) ? Carbon::parse($this->filtroKardexFecha) : today();
        $agendaDosis = $agendaService->paraAdultos($codigosResidentes, $fechaKardex);

        // Mapear dosis por residente y hora para la matriz
        $matrizKardex = [];
        $conteoAdministradas = 0;
        $conteoPendientes = 0;
        $conteoRetrasadas = 0;
        $conteoOmitidas = 0;

        foreach ($agendaDosis as $item) {
            $codRes = $item['adulto']?->cod_residente;
            $hora = $item['hora'];
            $estado = $item['estado']; // ADMINISTRADA, VENCIDA, PROXIMA, PENDIENTE, OMITIDA

            if ($estado === 'ADMINISTRADA') {
                $conteoAdministradas++;
            } elseif (in_array($estado, ['VENCIDA', 'RETRASADA'])) {
                $conteoRetrasadas++;
            } elseif ($estado === 'OMITIDA') {
                $conteoOmitidas++;
            } else {
                $conteoPendientes++;
            }

            if ($codRes) {
                $matrizKardex[$codRes][$hora][] = [
                    'id' => $item['id'],
                    'cod_prescripcion' => $item['medicacion']->cod_prescripcion,
                    'cod_residente' => $codRes,
                    'nombre_corto' => $item['medicacion']->nombre_medicamento,
                    'dosis' => ($item['horario']->dosis_programada ?? $item['medicacion']->dosis).' '.($item['medicacion']->unidad_dosis ?: 'mg'),
                    'hora' => $hora,
                    'estado' => $estado === 'VENCIDA' ? 'RETRASADA' : $estado,
                ];
            }
        }

        // 4. Alertas de medicación reales (Alergias y Retrasos)
        $residentesConAlergiasList = Alergia::whereIn('cod_residente', $codigosResidentes)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->pluck('cod_residente')
            ->filter()
            ->unique()
            ->values()
            ->all();
        $residentesConAlergias = count($residentesConAlergiasList);

        // 5. Pestaña Próximas Dosis: orden cronológico priorizando retrasadas y próximas
        $proximasDosis = $agendaDosis->sortBy(function ($item) {
            $prioridad = match ($item['estado']) {
                'VENCIDA', 'RETRASADA' => 1,
                'PROXIMA' => 2,
                'PENDIENTE' => 3,
                default => 4,
            };

            return $prioridad.'_'.$item['hora'];
        })->values();

        // 6. Pestaña Omisiones: no administraciones justificadas (excluyendo RECHAZADA)
        $omisiones = AdministracionMedicacion::query()
            ->with(['residente', 'prescripcion.medicamento', 'personal'])
            ->whereIn('cod_residente', $codigosResidentes)
            ->where('resultado', 'OMITIDA')
            ->latest('fecha_hora_programada')
            ->take(20)
            ->get();

        // 7. Pestaña Historial con filtros reactivos
        $queryHistorial = AdministracionMedicacion::query()
            ->with(['residente', 'prescripcion.medicamento', 'personal'])
            ->whereIn('cod_residente', $codigosResidentes);

        if ($this->filtroHistorialResidente) {
            $queryHistorial->where('cod_residente', $this->filtroHistorialResidente);
        }
        if ($this->filtroHistorialResultado) {
            $queryHistorial->where('resultado', $this->filtroHistorialResultado);
        }
        if ($this->filtroHistorialFecha) {
            $queryHistorial->whereDate('fecha_hora_programada', $this->filtroHistorialFecha);
        }
        if ($this->filtroHistorialMedicamento) {
            $buscMed = '%'.trim($this->filtroHistorialMedicamento).'%';
            $queryHistorial->whereHas('prescripcion.medicamento', function ($q) use ($buscMed) {
                $q->where('nombre_generico', 'like', $buscMed)
                    ->orWhere('nombre_comercial', 'like', $buscMed);
            });
        }

        $historial = $queryHistorial->latest('fecha_hora_programada')->take(30)->get();

        $dosisHoy = collect();
        foreach ($agendaDosis as $item) {
            $res = $item['adulto'];
            $presc = $item['medicacion'];
            $med = $presc?->medicamento;
            $cama = $res?->ocupacionActiva?->cama;
            $hab = $cama?->habitacion;
            $habTexto = $hab?->nombre ?? ($hab?->codigo ? "Hab. {$hab->codigo}" : 'Sin habitación');
            $estadoRaw = $item['estado'];

            $estadoTexto = match ($estadoRaw) {
                'ADMINISTRADA' => 'Administrada',
                'VENCIDA', 'RETRASADA' => 'Con retraso',
                'OMITIDA' => 'Omitida',
                default => 'Pendiente',
            };

            $dosisHoy->push([
                'id' => $item['id'],
                'hora' => $item['hora'],
                'hora_12h' => $item['hora_12h'] ?? $item['hora'],
                'cod_residente' => $res?->cod_residente,
                'nombre_residente' => $res ? trim("{$res->nombres} {$res->apellido_paterno} {$res->apellido_materno}") : 'Residente',
                'iniciales' => $res ? strtoupper(substr((string) $res->nombres, 0, 1).substr((string) ($res->apellido_paterno ?? $res->ap_paterno ?? 'R'), 0, 1)) : 'RM',
                'habitacion' => $habTexto,
                'medicamento' => $med?->nombre_generico ?: ($presc->nombre_medicamento ?: 'Medicamento'),
                'dosis' => ($item['horario']->dosis_programada ?? $presc->dosis).' '.($presc->unidad_dosis ?: 'mg'),
                'via' => ucfirst(strtolower((string) ($presc->via_administracion ?: 'Vía oral'))),
                'via_raw' => strtoupper(trim((string) ($presc->via_administracion ?: 'ORAL'))),
                'es_prn' => (bool) ($presc->es_condicional ?? $presc->prn ?? false),
                'estado' => $estadoTexto,
                'estado_raw' => $estadoRaw,
                'cod_prescripcion' => $presc->cod_prescripcion,
                'tiene_alerta_clinica' => in_array($res?->cod_residente, $residentesConAlergiasList),
            ]);
        }

        // Filtros específicos y combinables de Kardex
        if (! empty($this->filtroKardexBusqueda)) {
            $busq = mb_strtolower(trim($this->filtroKardexBusqueda));
            $dosisHoy = $dosisHoy->filter(function ($d) use ($busq) {
                return str_contains(mb_strtolower($d['nombre_residente'] ?? ''), $busq) ||
                       str_contains(mb_strtolower($d['medicamento'] ?? ''), $busq);
            });
        }

        if (! empty($this->filtroKardexEstado)) {
            $dosisHoy = $dosisHoy->filter(function ($d) {
                $stRaw = $d['estado_raw'] ?? '';
                if ($this->filtroKardexEstado === 'ADMINISTRADA') {
                    return in_array($stRaw, ['ADMINISTRADA', 'ADMINISTRADO']);
                }
                if ($this->filtroKardexEstado === 'RETRASADA') {
                    return in_array($stRaw, ['VENCIDA', 'RETRASADA']);
                }
                if ($this->filtroKardexEstado === 'PENDIENTE') {
                    return in_array($stRaw, ['PENDIENTE', 'PROGRAMADA', 'PROXIMA']);
                }
                if ($this->filtroKardexEstado === 'PROXIMA') {
                    if ($stRaw === 'PROXIMA') {
                        return true;
                    }
                    if (in_array($stRaw, ['PENDIENTE', 'PROGRAMADA'])) {
                        try {
                            $horaDosis = Carbon::createFromFormat('H:i', substr($d['hora'], 0, 5));
                            $ahora = now();
                            $limite = now()->addHours(3);

                            return $horaDosis->greaterThanOrEqualTo($ahora->copy()->subMinutes(30)) && $horaDosis->lessThanOrEqualTo($limite);
                        } catch (\Throwable $e) {
                            return true;
                        }
                    }

                    return false;
                }

                return true;
            });
        }

        if (! empty($this->filtroKardexHorario)) {
            $dosisHoy = $dosisHoy->filter(function ($d) {
                $h = (int) substr($d['hora'], 0, 2);
                if ($this->filtroKardexHorario === 'MANANA') {
                    return $h >= 6 && $h < 13;
                }
                if ($this->filtroKardexHorario === 'TARDE') {
                    return $h >= 13 && $h < 19;
                }
                if ($this->filtroKardexHorario === 'NOCHE') {
                    return $h >= 19 || $h < 6;
                }

                return str_starts_with($d['hora'], $this->filtroKardexHorario);
            });
        }

        if (! empty($this->filtroKardexResidente)) {
            $dosisHoy = $dosisHoy->where('cod_residente', $this->filtroKardexResidente);
        }

        if (! empty($this->filtroKardexVia)) {
            $viaFiltro = strtoupper(trim($this->filtroKardexVia));
            $dosisHoy = $dosisHoy->filter(function ($d) use ($viaFiltro) {
                return str_contains(strtoupper($d['via_raw'] ?? $d['via']), $viaFiltro);
            });
        }

        if (! empty($this->filtroKardexPrn)) {
            if ($this->filtroKardexPrn === 'PRN') {
                $dosisHoy = $dosisHoy->where('es_prn', true);
            } elseif ($this->filtroKardexPrn === 'FIJO') {
                $dosisHoy = $dosisHoy->where('es_prn', false);
            }
        }

        $dosisHoy = $dosisHoy->sort(function ($a, $b) {
            $rank = function ($d) {
                if (! empty($d['tiene_alerta_clinica'])) {
                    return 1;
                }
                $st = $d['estado_raw'] ?? '';
                if (in_array($st, ['VENCIDA', 'RETRASADA'])) {
                    return 2;
                }
                if (in_array($st, ['PROXIMA', 'PENDIENTE', 'PROGRAMADA'])) {
                    return 3;
                }
                if (in_array($st, ['OMITIDA', 'RECHAZADA'])) {
                    return 4;
                }
                if (in_array($st, ['ADMINISTRADA', 'ADMINISTRADO'])) {
                    return 5;
                }

                return 6;
            };
            $rA = $rank($a);
            $rB = $rank($b);
            if ($rA !== $rB) {
                return $rA <=> $rB;
            }

            return strcmp($a['hora'], $b['hora']);
        })->values();

        $dPresc = is_numeric($this->formDosisPrescritaValor) ? (float) $this->formDosisPrescritaValor : 0.0;
        $dAdmin = is_numeric($this->formDosisAdministrada) ? (float) $this->formDosisAdministrada : 0.0;

        return view('livewire.medicacion.salud-administracion-medicacion', [
            'residentes' => $residentes,
            'horasKardex' => $horasKardex,
            'matrizKardex' => $matrizKardex,
            'dosisHoy' => $dosisHoy,
            'conteoAdministradas' => $conteoAdministradas,
            'conteoPendientes' => $conteoPendientes,
            'conteoRetrasadas' => $conteoRetrasadas,
            'conteoOmitidas' => $conteoOmitidas,
            'residentesConAlergias' => $residentesConAlergias,
            'proximasDosis' => $proximasDosis,
            'omisiones' => $omisiones,
            'historial' => $historial,
            'fechaCabecera' => 'Hoy, '.today()->translatedFormat('d \d\e F \d\e Y'),
            'modalAdministrarAbierto' => $this->modalAdministrarAbierto,
            'modalOmisionAbierto' => $this->modalOmisionAbierto,
            'formDosisAdministrada' => $this->formDosisAdministrada,
            'formDosisPrescritaValor' => $this->formDosisPrescritaValor,
            'dPresc' => $dPresc,
            'dAdmin' => $dAdmin,
            'dosisDifiere' => $dPresc > 0 && $dAdmin > 0 && abs($dAdmin - $dPresc) > 0.001,
            'formUnidadDosis' => $this->formUnidadDosis,
            'formEfectoObservado' => $this->formEfectoObservado,
            'formReaccionAdversa' => $this->formReaccionAdversa,
            'formObservacionAdmin' => $this->formObservacionAdmin,
            'formMotivoOmision' => $this->formMotivoOmision,
            'formObservacionOmision' => $this->formObservacionOmision,
        ])->layout('layouts.enfermeria');
    }
}
