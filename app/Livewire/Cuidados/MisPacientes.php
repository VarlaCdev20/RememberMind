<?php

namespace App\Livewire\Cuidados;

use App\Models\AdministracionMedicacion;
use App\Models\Residente;
use App\Models\Alerta;
use App\Models\Prescripcion;
use App\Models\Atencion;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Models\PaseTurno;
use App\Models\SignoVital;
use App\Services\Enfermeria\TurnoEnfermeriaService;
use App\Services\Medicacion\AgendaMedicacionService;
use App\Services\Medicacion\RegistrarAdministracionMedicacionService;
use App\Services\Clinica\SignosVitalesService;
use App\Services\Alertas\AlertasService;
use App\Services\Enfermeria\CuidadosEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
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

    #[Url(as: 'residente')]
    public ?string $residente = null;
    public bool $mostrarPanelDetalle = false;
    public ?array $detalleResidente = null;
    public bool $esModoConsulta = false;
    public bool $esResidenteAsignado = false;


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

    public bool $modalCuidado = false;
    public string $cuidadoTipo = 'HIGIENE', $cuidadoSubtipo = 'GENERAL', $cuidadoObs = '';

    public bool $modalDolor = false;
    public int $dolorIntensidad = 5;
    public string $dolorDetalle = '';

    public bool $modalProcedimiento = false;
    public string $procTipo = 'CURACION', $procDetalle = '';

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

        $turnoActual = $service->obtenerTurnoActivo($user);
        $this->esModoConsulta = ($turnoActual === null) && !$service->esSuperAdmin($user);

        if ($service->esSuperAdmin($user)) {
            $this->filtroEnfermero = '';
            $this->filtroTurno = '';
        } else {
            $this->filtroEnfermero = (string) $user?->cod_usuario;
            if ($turnoActual) {
                $this->filtroTurno = (string) $turnoActual->cod_turno;
            }
        }

        if (!empty($this->residente)) {
            $this->seleccionarResidente($this->residente);
        }
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFiltroRapido(): void { $this->resetPage(); }
    public function updatingFiltroTurno(): void { $this->resetPage(); }
    public function updatingFiltroEnfermero(): void { $this->resetPage(); }
    public function updatingFiltroEstado(): void { $this->resetPage(); }
    public function updatingVistaModo(): void { $this->resetPage(); }

    
    public function seleccionarResidente(?string $codResidente): void
    {
        if (empty($codResidente)) {
            $this->cerrarPanelDetalle();
            return;
        }

        $user = Auth::user();
        abort_unless($user && $user->estado === 'ACTIVO', 403, 'Sesión no activa o usuario inactivo.');
        abort_unless(
            $user->can('pacientes.ver') || $user->can('residentes.ver') || $user->can('pacientes') || $user->can('cuidados.ver') || $user->hasRole(['ENFERMEROS', 'SUPERADMINISTRADOR']),
            403,
            'No tiene permisos para consultar residentes.'
        );

        $service = $this->getTurnoService();
        $turnoActual = $service->obtenerTurnoActivo($user);
        $esSuperAdmin = $service->esSuperAdmin($user);
        $this->esModoConsulta = ($turnoActual === null) && !$esSuperAdmin;

        // Buscar residente por cod_residente
        $adulto = Residente::query()
            ->where('cod_residente', $codResidente)
            ->with([
                'cama.habitacion',
                'planCuidadoActivo.intervenciones',
                'alertas' => fn ($q) => $q->whereIn('estado', ['ABIERTA', 'EN_ATENCION', 'ASIGNADA', 'RECONOCIDA', 'PENDIENTE'])->orderByDesc('fecha_hora'),
            ])
            ->first();

        if (!$adulto) {
            $this->cerrarPanelDetalle();
            abort(403, 'El residente solicitado no existe o no está disponible.');
        }

        $codRes = $adulto->cod_residente;
        $codAm = $codRes;

        // Validar alcance / relación operativa
        $esPermitido = false;
        $esAsignadoAlUsuario = false;

        if ($esSuperAdmin) {
            $esPermitido = true;
            $esAsignadoAlUsuario = false;
        } elseif ($turnoActual) {
            // EN_TURNO: Verificar que el residente esté asignado al usuario en el turno activo
            $esAsignadoAlUsuario = $service->esPacienteAsignado($codAm, $user, $turnoActual->cod_turno)
                                || $service->esPacienteAsignado($codRes, $user, $turnoActual->cod_turno);
            $esPermitido = $esAsignadoAlUsuario;
        } else {
            // FUERA_DE_TURNO: Verificar si el residente está cubierto por alguna jornada de enfermería activa del sistema
            $miTurnoService = app(\App\Services\Enfermeria\MiTurnoService::class);
            $jornadasActivas = $miTurnoService->resolverJornadasActivasSistema(Carbon::now());
            if ($jornadasActivas->isNotEmpty()) {
                $codJornadasActivas = $jornadasActivas->pluck('cod_jornada')->all();
                $esPermitido = \App\Models\AsignacionResidenteJornada::query()
                    ->whereIn('cod_jornada', $codJornadasActivas)
                    ->where(function ($q) use ($codAm, $codRes) {
                        $q->where('cod_residente', $codAm)
                          ->orWhere('cod_residente', $codRes);
                    })
                    ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'ASIGNADO'])
                    ->exists();
            }
            $esAsignadoAlUsuario = false;
        }

        if (!$esPermitido) {
            $this->cerrarPanelDetalle();
            abort(403, 'No tiene autorización para acceder al residente indicado o no se encuentra asignado a su turno.');
        }

        $this->residente = $codAm;
        $this->esResidenteAsignado = $esAsignadoAlUsuario;
        $this->detalleResidente = $this->construirDetalleResidente($adulto, $turnoActual);
        $this->mostrarPanelDetalle = true;
    }

    public function cerrarPanelDetalle(): void
    {
        $this->mostrarPanelDetalle = false;
        $this->detalleResidente = null;
        $this->residente = null;
    }

        protected function construirDetalleResidente(Residente $adulto, ?TurnoEnfermeria $turnoActual): array
    {
        $codRes = $adulto->cod_residente;
        $codAm = $codRes;

        // 1. IDENTIFICACIÓN
        $nombreCompleto = trim($adulto->nombres . ' ' . $adulto->apellido_paterno . ' ' . ($adulto->apellido_materno ?? ''));
        if (empty($nombreCompleto)) {
            $nombreCompleto = $adulto->nombre_completo ?? 'Residente';
        }
        $edadTexto = $adulto->edad_texto ?? ($adulto->fecha_nacimiento ? Carbon::parse($adulto->fecha_nacimiento)->age . ' años' : '79 años');
        $documento = $adulto->ci ?? ($adulto->numero_documento ?? 'Sin documento');

        $cama = $adulto->cama;
        $hab = $cama?->habitacion;
        $numHab = $hab ? ($hab->numero ?? $hab->codigo) : '';
        $habitacionTexto = $numHab ? (str_starts_with(strtolower($numHab), 'hab') ? $numHab : "Hab. {$numHab}") : 'Sin habitación';
        $numCama = $cama ? ($cama->numero ?? $cama->codigo) : '';
        $camaTexto = $numCama ? (str_starts_with(strtolower($numCama), 'cama') ? $numCama : "Cama {$numCama}") : 'Sin cama';
        $ubicacionFormateada = $hab ? "{$habitacionTexto} · {$camaTexto}" : ($adulto->ubicacion_formateada ?: 'Ubicación no asignada');

        // Estado de seguimiento
        $alertasActivas = Alerta::query()
            ->where(function ($q) use ($codAm, $codRes) {
                $q->where('cod_residente', $codAm)->orWhere('cod_residente', $codRes);
            })
            ->whereIn('estado', ['ABIERTA', 'EN_ATENCION', 'ASIGNADA', 'RECONOCIDA', 'PENDIENTE'])
            ->orderByDesc('fecha_hora')
            ->get();

        $alertasCriticasCount = $alertasActivas->whereIn('prioridad', ['CRITICO', 'ALTO', 'CRITICA'])->count();

        $estadoSeguimiento = 'ESTABLE';
        $estadoColor = 'emerald';
        $estadoHumano = 'Estable';

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
        $miTurnoService = app(\App\Services\Enfermeria\MiTurnoService::class);
        $jornadasActivas = $miTurnoService->resolverJornadasActivasSistema(Carbon::now());
        $codJornadasActivas = $jornadasActivas->pluck('cod_jornada')->all();

        $asigQuery = \App\Models\AsignacionResidenteJornada::query()
            ->where(function ($q) use ($codAm, $codRes) {
                $q->where('cod_residente', $codAm)->orWhere('cod_residente', $codRes);
            })
            ->whereIn('estado', ['ACTIVO', 'ACTIVA', 'ASIGNADO'])
            ->with(['personal', 'jornada.turno']);

        if (!empty($codJornadasActivas)) {
            $asigQuery->whereIn('cod_jornada', $codJornadasActivas);
        }

        $asigResidente = $asigQuery->orderByDesc('fecha_hora')->first();

        if ($asigResidente && $asigResidente->personal) {
            $nomEnf = trim($asigResidente->personal->nombres . ' ' . $asigResidente->personal->apellido_paterno);
            if (!empty($nomEnf)) {
                $responsableTexto = "A cargo de: Enf. {$nomEnf}";
            }
        } elseif ($turnoActual) {
            // Fallback: si no hay asignación específica al residente, usar el usuario autenticado
            $userAuth = Auth::user();
            $persAuth = $userAuth?->personal;
            $nomAuth = $persAuth ? trim($persAuth->nombres . ' ' . $persAuth->apellido_paterno) : '';
            if (!empty($nomAuth)) {
                $responsableTexto = "A cargo de: Enf. {$nomAuth}";
            } else {
                $responsableTexto = 'Enfermero/a de guardia';
            }
        }

        if ($asigResidente && $asigResidente->jornada?->turno) {
            $turnoNombre = $asigResidente->jornada->turno->nombre;
        }

        // 2. ÚLTIMOS SIGNOS VITALES (resumen del último registro real)
        $ultimoSignoModel = SignoVital::query()
            ->where(function ($q) use ($codAm, $codRes) {
                $q->where('cod_residente', $codAm)->orWhere('cod_residente', $codRes);
            })
            ->orderByDesc('fecha_hora')
            ->first();

        $ultimosSignos = null;
        if ($ultimoSignoModel) {
            $fHora = Carbon::parse($ultimoSignoModel->fecha_hora);
            $fechaHoraTexto = $fHora->isToday() ? ('Hoy ' . $fHora->format('H:i')) : $fHora->format('d/m/Y H:i');
            $paLimpia = '—';
            if ($ultimoSignoModel->presion_sistolica && $ultimoSignoModel->presion_diastolica) {
                $paLimpia = (int)$ultimoSignoModel->presion_sistolica . '/' . (int)$ultimoSignoModel->presion_diastolica;
            } elseif ($ultimoSignoModel->presion_arterial) {
                $paLimpia = $ultimoSignoModel->presion_arterial;
            }
            $ultimosSignos = [
                'pa' => $paLimpia,
                'fc' => $ultimoSignoModel->frecuencia_cardiaca ? ((int)$ultimoSignoModel->frecuencia_cardiaca . ' lpm') : '—',
                'fr' => $ultimoSignoModel->frecuencia_respiratoria ? ((int)$ultimoSignoModel->frecuencia_respiratoria . ' rpm') : '—',
                'temp' => $ultimoSignoModel->temperatura ? (round((float)$ultimoSignoModel->temperatura, 1) . ' °C') : '—',
                'sat' => $ultimoSignoModel->saturacion ? ((int)$ultimoSignoModel->saturacion . '%') : ($ultimoSignoModel->saturacion_oxigeno ? ((int)$ultimoSignoModel->saturacion_oxigeno . '%') : '—'),
                'glucosa' => $ultimoSignoModel->glucemia ? (int)$ultimoSignoModel->glucemia : ($ultimoSignoModel->glucosa ?: '—'),
                'fecha_hora' => $fechaHoraTexto,
                'observacion' => $ultimoSignoModel->observacion,
            ];
        }

        // 3. PRÓXIMA MEDICACIÓN (bloque muy visible)
        $agendaMeds = app(AgendaMedicacionService::class)->paraAdulto($codAm);
        $medsPendientes = $agendaMeds->filter(fn ($item) => empty($item['registro']));
        $proximaMed = null;

        if ($medsPendientes->isNotEmpty()) {
            $medPendiente = $medsPendientes->sortBy('hora')->first();
            $horaMed = $medPendiente['hora'];
            $tiempoRestante = '';
            try {
                $horaCarbon = Carbon::parse(today()->toDateString() . ' ' . $horaMed);
                $diffMin = (int) now()->diffInMinutes($horaCarbon, false);
                if ($diffMin > 0 && $diffMin <= 60) {
                    $tiempoRestante = "En {$diffMin} min";
                } elseif ($diffMin > 60) {
                    $tiempoRestante = "En " . round($diffMin / 60, 1) . " hrs";
                } elseif ($diffMin <= 0 && $diffMin >= -60) {
                    $tiempoRestante = "Hace " . abs($diffMin) . " min";
                } else {
                    $tiempoRestante = "Atrasada";
                }
            } catch (\Throwable $e) {
                $tiempoRestante = '';
            }

            $presc = $medPendiente['medicacion'];
            $via = $presc->via_administracion ?: 'VO';
            if (stripos($via, 'oral') !== false) {
                $via = 'VO';
            }

            $nombreMed = $presc->medicamento?->nombre_generico ?: ($presc->nombre_medicamento ?: 'Medicamento');
            $dosisMed = $presc->dosis ? ((float)$presc->dosis == (int)$presc->dosis ? (int)$presc->dosis : (float)$presc->dosis) . ' ' . ($presc->unidad_dosis ?: 'mg') : '50 mg';
            $proximaMed = [
                'nombre' => $nombreMed,
                'dosis' => $dosisMed,
                'hora' => $horaMed,
                'via' => $via,
                'tiempo_restante' => $tiempoRestante,
            ];
        } else {
            // Verificar prescripciones activas
            $prescActiva = Prescripcion::where('cod_residente', $codAm)->whereIn('estado', ['ACTIVA', 'ACTIVO'])->where('segun_necesidad', false)->first();
            if ($prescActiva) {
                $via = $prescActiva->via_administracion ?: 'VO';
                if (stripos($via, 'oral') !== false) $via = 'VO';
                $proximaMed = [
                    'nombre' => $prescActiva->nombre_medicamento,
                    'dosis' => $prescActiva->dosis ?: 'Según indicación',
                    'hora' => 'Según horario',
                    'via' => $via,
                    'tiempo_restante' => 'Prescripción activa',
                ];
            }
        }

        // 4. PRÓXIMA ATENCIÓN
        $proximaAtencion = Atencion::query()
            ->where(function ($q) use ($codAm, $codRes) {
                $q->where('cod_residente', $codAm)->orWhere('cod_residente', $codRes);
            })
            ->where('fecha_hora', '>=', now())
            ->orderBy('fecha_hora')
            ->first();

        $proximaAtencionTexto = 'Control de signos';
        $proximaAtencionHora = '10:00 · Hoy';

        if ($proximaAtencion) {
            $proximaAtencionTexto = $proximaAtencion->motivo ?: ($proximaAtencion->tipo_atencion ?: 'Atención programada');
            $proximaAtencionHora = Carbon::parse($proximaAtencion->fecha_hora)->format('H:i') . ' · Hoy';
        } elseif ($proximaMed) {
            $proximaAtencionTexto = 'Administración de ' . $proximaMed['nombre'];
            $proximaAtencionHora = $proximaMed['hora'] . ' · Hoy';
        } elseif ($ultimoSignoModel && Carbon::parse($ultimoSignoModel->fecha_hora)->isToday()) {
            $proximaAtencionTexto = 'Seguimiento de guardia';
            $proximaAtencionHora = 'Continuo · Hoy';
        }

        // 5. ALERTAS ACTIVAS
        $alertasList = $alertasActivas->map(function ($al) {
            $tiempo = 'Reportada ';
            $fAl = $al->fecha_hora ?? ($al->created_at ?? null);
            if ($fAl) {
                $c = Carbon::parse($fAl);
                if ($c->isToday()) {
                    $tiempo .= 'hoy ' . $c->format('H:i');
                } elseif ($c->isYesterday()) {
                    $tiempo .= 'ayer ' . $c->format('H:i');
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
        $iniciales = strtoupper(substr($adulto->nombres ?: 'A', 0, 1) . substr($adulto->apellido_paterno ?: 'M', 0, 1));

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
            'foto' => $adulto->foto ? asset('storage/' . $adulto->foto) : '',
        ];
    }

    // ─── ACCIONES RÁPIDAS MODALES ───────────────────────────────────

    public function abrirRegistrarSignos(string $codAm): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless((auth()->user()?->can('signos_vitales.crear') || auth()->user()?->can('signos_vitales.registrar') || auth()->user()?->can('signos_vitales') || auth()->user()?->hasRole('ENFERMEROS')), 403);
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
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        app(SignosVitalesService::class)->registrar($this->modalCodAm, [
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
        $this->modalCodAm = null;
        if ($this->residente) {
            $this->seleccionarResidente($this->residente);
        }
        $this->dispatch('signos-actualizados');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Signos registrados', 'text' => 'Control de signos guardado correctamente.']);
    }

    public function abrirRegistrarSeguimiento(string $codAm): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless((auth()->user()?->can('seguimiento.crear') || auth()->user()?->can('atenciones.crear') || auth()->user()?->can('atenciones') || auth()->user()?->can('pases_turno') || auth()->user()?->hasRole('ENFERMEROS')), 403);
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
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless((auth()->user()?->can('seguimiento.crear') || auth()->user()?->can('atenciones.crear') || auth()->user()?->can('atenciones') || auth()->user()?->can('pases_turno') || auth()->user()?->hasRole('ENFERMEROS')), 403);
        $turno = $this->getTurnoService()->autorizarMutacionPaciente($this->modalCodAm, 'seguimiento.crear', Auth::user());
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
        if (Atencion::where('cod_residente', $this->modalCodAm)->whereDate('fecha_hora', today())
            ->where('tipo_atencion', 'SEGUIMIENTO_DIARIO')->exists()) {
            $this->addError('segObs', 'Ya existe un seguimiento de este residente para el turno actual.');
            return;
        }

        $personal = Auth::user()?->personal;
        $codArea = $personal?->asignaciones()->whereIn('estado', ['ACTIVA', 'ACTIVO'])->latest('fecha_asignacion')->value('cod_area');
        abort_unless($personal && $codArea, 422, 'El usuario debe tener personal y área institucional asignados.');
        Atencion::create([
            'cod_residente' => $this->modalCodAm,
            'cod_area' => $codArea,
            'cod_personal' => $personal->cod_personal,
            'tipo_atencion' => 'SEGUIMIENTO_DIARIO',
            'motivo' => $this->segEstado,
            'fecha_hora' => now(),
            'estado' => 'FINALIZADA',
            'observacion' => filled($this->segObs) ? trim($this->segObs) : 'Seguimiento registrado desde Mis Pacientes.',
        ]);

        $this->modalSeguimiento = false;
        $this->modalCodAm = null;
        if ($this->residente) {
            $this->seleccionarResidente($this->residente);
        }
        $this->dispatch('seguimiento-guardado');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Seguimiento guardado', 'text' => 'El registro diario fue guardado con éxito.']);
    }

    public function abrirAdministrarMed(string $codAm): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless((auth()->user()?->can('administracion_medicacion.registrar') || auth()->user()?->can('administraciones_medicacion.crear') || auth()->user()?->can('administraciones_medicacion')), 403);
        $this->getTurnoService()->autorizarAccionPaciente($codAm);
        $this->modalCodAm = $codAm;
        $this->medicacionesPaciente = Prescripcion::where('cod_residente', $codAm)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO', 'VIGENTE'])
            ->where('segun_necesidad', false)
            ->get();
        $primerMed = $this->medicacionesPaciente->first();
        $this->medCodMed = $primerMed?->cod_prescripcion ?? ($primerMed?->cod_med_adulto ?? '');
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
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless((auth()->user()?->can('administracion_medicacion.registrar') || auth()->user()?->can('administraciones_medicacion.crear') || auth()->user()?->can('administraciones_medicacion')), 403);
        $this->getTurnoService()->autorizarMutacionPaciente($this->modalCodAm, 'administracion_medicacion.registrar', Auth::user());
        $this->validate([
            'medCodMed' => 'required|exists:prescripciones,cod_prescripcion',
            'medAdministrado' => 'boolean',
            'medMotivoOmision' => $this->medAdministrado ? 'nullable|string|max:500' : 'required|string|min:5|max:500',
        ], [
            'medCodMed.required' => 'Seleccione un medicamento activo.',
            'medMotivoOmision.required' => 'Indique el motivo por el cual se omitió la dosis.',
            'medMotivoOmision.min' => 'El motivo de omisión debe tener al menos 5 caracteres.',
        ]);

        $ocurrencia = app(AgendaMedicacionService::class)->paraAdulto($this->modalCodAm)
            ->first(fn (array $item) => $item['medicacion']->cod_med_adulto === $this->medCodMed && $item['registro'] === null);
        if (! $ocurrencia) {
            $this->addError('medCodMed', 'No existe una dosis programada pendiente para este medicamento hoy.');
            return;
        }
        app(RegistrarAdministracionMedicacionService::class)->registrarProgramada(
            Auth::user(), $this->modalCodAm, $this->medCodMed, $ocurrencia['hora'],
            (bool) $this->medAdministrado, $this->medMotivoOmision, 'Registrado desde Mis Pacientes.'
        );

        $this->modalMed = false;
        $this->modalCodAm = null;
        if ($this->residente) {
            $this->seleccionarResidente($this->residente);
        }
        $this->dispatch('medicacion-registrada');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Medicación registrada', 'text' => 'Toma registrada en el expediente.']);
    }

    public function abrirReportarAlerta(string $codAm): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless((auth()->user()?->can('alertas.crear') || auth()->user()?->can('incidentes.crear') || auth()->user()?->can('alertas.ver') || auth()->user()?->hasRole('ENFERMEROS')), 403);
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
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        app(AlertasService::class)->crear($this->modalCodAm, [
            'origen' => 'INCIDENTE', 'tipo_alerta' => $this->alertaTipo,
            'nivel' => $this->alertaNivel, 'motivo' => $this->alertaMotivo,
        ], Auth::user());

        $this->modalAlerta = false;
        $this->modalCodAm = null;
        if ($this->residente) {
            $this->seleccionarResidente($this->residente);
        }
        $this->dispatch('alerta-creada');
        $this->dispatch('swal', ['icon' => 'warning', 'title' => 'Alerta creada', 'text' => 'La alerta fue enviada al monitor del turno y campana.']);
    }

    public function abrirRegistrarCuidado(string $codAm, string $tipo = 'HIGIENE', string $subtipo = 'GENERAL'): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless((auth()->user()?->can('seguimiento.crear') || auth()->user()?->can('atenciones.crear') || auth()->user()?->can('atenciones') || auth()->user()?->can('pases_turno') || auth()->user()?->hasRole('ENFERMEROS')), 403);
        $this->getTurnoService()->autorizarAccionPaciente($codAm);
        $this->modalCodAm = $codAm;
        $tipoUpper = strtoupper(trim($tipo));
        $this->cuidadoTipo = in_array($tipoUpper, ['HIGIENE', 'ALIMENTACION', 'MOVILIDAD', 'ELIMINACION', 'PIEL'], true) ? $tipoUpper : 'HIGIENE';
        $this->cuidadoSubtipo = $subtipo ?: 'GENERAL';
        $this->cuidadoObs = '';
        $this->modalCuidado = true;
    }

    public function guardarCuidado(): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless((auth()->user()?->can('seguimiento.crear') || auth()->user()?->can('atenciones.crear') || auth()->user()?->can('atenciones') || auth()->user()?->can('pases_turno') || auth()->user()?->hasRole('ENFERMEROS')), 403);
        $this->validate([
            'cuidadoTipo' => 'required|in:HIGIENE,ALIMENTACION,MOVILIDAD,ELIMINACION,PIEL',
            'cuidadoSubtipo' => 'required|string|max:50',
            'cuidadoObs' => 'nullable|string|max:2000',
        ]);

        app(CuidadosEnfermeriaService::class)->registrar($this->modalCodAm, [
            'tipo' => $this->cuidadoTipo === 'PIEL' ? 'HIGIENE' : $this->cuidadoTipo,
            'subtipo' => $this->cuidadoSubtipo,
            'observacion' => $this->cuidadoObs ?: 'Cuidado de enfermería registrado desde Mis Pacientes.',
        ], Auth::user());

        $this->modalCuidado = false;
        $this->modalCodAm = null;
        if ($this->residente) {
            $this->seleccionarResidente($this->residente);
        }
        $this->dispatch('cuidado-registrado');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Cuidado registrado', 'text' => 'El cuidado asistencial fue registrado correctamente.']);
    }

    public function abrirRegistrarDolor(string $codAm, int $intensidad = 5): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless((auth()->user()?->can('seguimiento.crear') || auth()->user()?->can('atenciones.crear') || auth()->user()?->can('atenciones') || auth()->user()?->can('pases_turno') || auth()->user()?->hasRole('ENFERMEROS')), 403);
        $this->getTurnoService()->autorizarAccionPaciente($codAm);
        $this->modalCodAm = $codAm;
        $this->dolorIntensidad = max(0, min(10, $intensidad));
        $this->dolorDetalle = '';
        $this->modalDolor = true;
    }

    public function guardarDolor(): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless((auth()->user()?->can('seguimiento.crear') || auth()->user()?->can('atenciones.crear') || auth()->user()?->can('atenciones') || auth()->user()?->can('pases_turno') || auth()->user()?->hasRole('ENFERMEROS')), 403);
        $this->validate([
            'dolorIntensidad' => 'required|integer|min:0|max:10',
            'dolorDetalle' => 'required|string|min:5|max:1000',
        ]);

        app(CuidadosEnfermeriaService::class)->registrarDolor(
            $this->modalCodAm,
            'VALORACION',
            $this->dolorIntensidad,
            $this->dolorDetalle,
            Auth::user()
        );

        $this->modalDolor = false;
        $this->modalCodAm = null;
        if ($this->residente) {
            $this->seleccionarResidente($this->residente);
        }
        $this->dispatch('dolor-registrado');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Dolor registrado', 'text' => 'Valoración del síntoma guardada correctamente.']);
    }

    public function abrirRegistrarProcedimiento(string $codAm, string $tipo = 'CURACION'): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless((auth()->user()?->can('seguimiento.crear') || auth()->user()?->can('atenciones.crear') || auth()->user()?->can('atenciones') || auth()->user()?->can('pases_turno') || auth()->user()?->hasRole('ENFERMEROS')), 403);
        $this->getTurnoService()->autorizarAccionPaciente($codAm);
        $this->modalCodAm = $codAm;
        $tipoUpper = strtoupper(trim($tipo));
        $this->procTipo = in_array($tipoUpper, ['CURACION', 'SONDA', 'CATETER', 'OXIGENO', 'OTRO'], true) ? $tipoUpper : 'CURACION';
        $this->procDetalle = '';
        $this->modalProcedimiento = true;
    }

    public function guardarProcedimiento(): void
    {
        abort_if($this->esModoConsulta, 403, 'Operación no permitida en modo consulta / fuera de turno.');
        abort_unless((auth()->user()?->can('seguimiento.crear') || auth()->user()?->can('atenciones.crear') || auth()->user()?->can('atenciones') || auth()->user()?->can('pases_turno') || auth()->user()?->hasRole('ENFERMEROS')), 403);
        $this->validate([
            'procTipo' => 'required|in:CURACION,SONDA,CATETER,OXIGENO,OTRO',
            'procDetalle' => 'required|string|min:5|max:2000',
        ]);

        app(CuidadosEnfermeriaService::class)->registrar($this->modalCodAm, [
            'tipo' => 'PROCEDIMIENTO',
            'subtipo' => $this->procTipo,
            'observacion' => $this->procDetalle,
        ], Auth::user());

        $this->modalProcedimiento = false;
        $this->modalCodAm = null;
        if ($this->residente) {
            $this->seleccionarResidente($this->residente);
        }
        $this->dispatch('procedimiento-registrado');
        $this->dispatch('swal', ['icon' => 'success', 'title' => 'Procedimiento registrado', 'text' => 'El procedimiento o control fue registrado correctamente.']);
    }

    // ─── RENDER ─────────────────────────────────────────────────────

    public function render()
    {
        $fechaHoy = Carbon::now()->toDateString();
        $service = $this->getTurnoService();
        $user = Auth::user();
        $esSuperAdmin = $service->esSuperAdmin($user);
        $esSupervisor = $esSuperAdmin;

        // Asegurar que enfermero estándar no pueda burlar el filtro
        $enfermeroEfectivo = $esSupervisor ? $this->filtroEnfermero : (string) $user?->cod_usuario;
        $turnoEfectivo = $this->filtroTurno ?: null;

        $pacientesQuery = $service->obtenerPacientesAsignadosQuery(
            $user,
            $turnoEfectivo,
            $enfermeroEfectivo ?: null
        )->with([
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
            $b = trim($this->search);
            $pacientesQuery->where(function ($q) use ($b) {
                $q->whereLike('nombres', "%{$b}%")
                  ->orWhereLike('apellido_paterno', "%{$b}%")
                  ->orWhereLike('apellido_materno', "%{$b}%")
                  ->orWhereLike('numero_documento', "%{$b}%")
                  ->orWhereLike('cod_residente', "%{$b}%")
                  ->orWhereHas('cama', function ($cq) use ($b) {
                      $cq->whereLike('numero', "%{$b}%")
                         ->orWhereLike('codigo', "%{$b}%")
                         ->orWhereHas('habitacion', function ($hq) use ($b) {
                             $hq->whereLike('numero', "%{$b}%")
                                ->orWhereLike('nombre', "%{$b}%")
                                ->orWhereLike('codigo', "%{$b}%");
                         });
                  });
            });
        }

        // Filtro por estado clínico: TODOS / ESTABLE / VIGILANCIA / REQUIERE_ATENCION
        if ($this->filtroEstado === 'REQUIERE_ATENCION') {
            $pacientesQuery->where(function ($q) use ($fechaHoy) {
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
        $pacientes = $pacientesQuery->orderBy('nombres')->paginate(12);

        // Pre-cargar datos detallados de los pacientes paginados (signos recientes, medicación pendiente)
        $codAms = $pacientes->pluck('cod_residente')->filter()->values();

        $ultimosSignos = collect();
        $medsActivas = collect();
        $adminMedsHoy = collect();
        $seguimientosHoy = collect();
        $proximasAtenciones = collect();
        $proximasMeds = collect();

        if ($codAms->isNotEmpty()) {
            $ultimosSignos = SignoVital::whereIn('cod_residente', $codAms)
                ->orderByDesc('fecha_hora')
                ->get()
                ->groupBy('cod_residente')
                ->map(fn ($g) => $g->first());

            $medsActivas = Prescripcion::whereIn('cod_residente', $codAms)
                ->where('estado', 'ACTIVA')
                ->get()
                ->groupBy('cod_residente');

            $adminMedsHoy = AdministracionMedicacion::whereIn('cod_residente', $codAms)
                ->whereDate('fecha_hora_programada', $fechaHoy)
                ->whereIn('resultado', ['ADMINISTRADA', 'ADMINISTRADO'])
                ->get()
                ->groupBy('cod_residente');

            $seguimientosHoy = PaseTurno::whereIn('cod_residente', $codAms)
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
                $p->codigo_estado = 'ESTABLE';
                $p->estado_color = 'emerald';
                $p->estado_label = 'Estable';
            }

            // Nivel de supervisión explícito (Regla 10)
            $rawNivel = strtoupper(trim((string)($p->planCuidadoActivo?->nivel_cuidado ?? '')));
            $p->supervision_label = match($rawNivel) {
                'BAJO', 'BAJA', 'LEVE', 'MINIMO' => 'Supervisión baja',
                'ALTO', 'ALTA', 'SEVERO', 'TOTAL', 'DEPENDIENTE' => 'Supervisión alta',
                default => 'Supervisión moderada',
            };

            // Movilidad explícita (Regla 11)
            $movRaw = strtolower(trim((string)($p->movilidad ?? ($p->planCuidadoActivo?->tipo_cuidado ?? ''))));
            if (str_contains($movRaw, 'independien')) {
                $p->movilidad_label = 'Movilidad independiente';
            } elseif (str_contains($movRaw, 'dispositiv') || str_contains($movRaw, 'baston') || str_contains($movRaw, 'andador')) {
                $p->movilidad_label = 'Usa dispositivo';
            } elseif (str_contains($movRaw, 'caida')) {
                $p->movilidad_label = 'Riesgo de caída';
            } else {
                $p->movilidad_label = 'Movilidad asistida';
            }

            // Próxima atención en la fila de lista
            $proxAten = $proximasAtenciones->get($p->cod_residente);
            $proxMed  = $proximasMeds->get($p->cod_residente);
            if ($proxAten) {
                $p->proxima_atencion_texto = $proxAten->motivo ?: ($proxAten->tipo_atencion ?: 'Atención programada');
                $p->proxima_atencion_hora  = \Carbon\Carbon::parse($proxAten->fecha_hora)->format('H:i');
            } elseif ($proxMed) {
                $p->proxima_atencion_texto = 'Admin. ' . $proxMed['nombre'];
                $p->proxima_atencion_hora  = $proxMed['hora'];
            } else {
                $p->proxima_atencion_texto = 'Control de signos';
                $p->proxima_atencion_hora  = '—';
            }
        }

        // Estadísticas globales de turno para el selector de filtros
        $baseParaStats = (clone $service->obtenerPacientesAsignadosQuery(
            $user,
            $turnoEfectivo,
            $enfermeroEfectivo ?: null
        ))->withCount([
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
            'esModoConsulta' => $this->esModoConsulta,
            'esResidenteAsignado' => $this->esResidenteAsignado,
            'detalleResidente' => $this->detalleResidente,
            'stats' => $stats,
            'turnos' => TurnoEnfermeria::activos()->get(),
            'enfermeros' => User::role('ENFERMEROS')->where('estado', 'ACTIVO')->with('personal')->get(),
            'esSuperAdmin' => $esSuperAdmin,
        ])->layout('layouts.enfermeria');
    }
}
