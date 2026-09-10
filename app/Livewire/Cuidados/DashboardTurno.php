<?php

namespace App\Livewire\Cuidados;

use Livewire\Component;
use App\Models\ValoracionEnfermeriaAdmision;
use App\Models\Preadmision;
use App\Models\TurnoEnfermeria;
use App\Models\AsignacionTurnoAdulto;
use App\Models\TareaPlanCuidado;
use App\Models\SeguimientoDiario;
use App\Models\AlertaAdulto;
use App\Models\PaseTurno;
use App\Models\AdministracionMedicacion;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class DashboardTurno extends Component
{
    protected $listeners = [
        'refreshDashboard' => '$refresh',
        'valoracionCompletada' => '$refresh'
    ];

    public $turnoActual;
    public $horarioAsignadoHoy;
    public $horarioSiguienteAsignado;
    public $turnosActivos = [];
    public $horariosPersonal = [];
    public $calendarioHorarios = [];
    public $pacientesAsignadosIds = [];
    public $filtroEnfermeroId;
    public $filtroFecha;

    public function mount()
    {
        $this->filtroEnfermeroId = auth()->user()?->cod_usu ?? '';
        $this->filtroFecha = Carbon::now()->toDateString();
        $this->loadTurnoActual();
    }

    public function loadTurnoActual()
    {
        $turnosOrdenados = TurnoEnfermeria::activos()->orderBy('orden')->get();

        $this->turnosActivos = $turnosOrdenados->values();
        $this->turnoActual = null;

        $service = app(\App\Services\Identidad\GeneradorPlanillaEnfermeriaService::class);
        $this->calendarioHorarios = [];

        for ($i = 0; $i < 14; $i++) {
            $fecha = Carbon::today()->addDays($i);
            $fechaStr = $fecha->toDateString();
            
            $asignacion = $service->obtenerTurnoEnFecha($this->filtroEnfermeroId, $fechaStr);
            $turnosDia = [];

            if ($asignacion && $asignacion['turno_codigo'] !== 'DESCANSO') {
                $turnosDia[] = [
                    'turno' => $asignacion['turno_nombre'],
                    'hora_inicio' => substr($asignacion['hora_inicio'], 0, 5),
                    'hora_fin' => substr($asignacion['hora_fin'], 0, 5),
                ];
            }

            $this->calendarioHorarios[] = [
                'fecha' => $fechaStr,
                'fecha_texto' => $fecha->translatedFormat('d \d\e F'),
                'dia_semana' => $this->normalizarDiaSemana($fecha->locale('es')->translatedFormat('l')),
                'es_hoy' => $i === 0,
                'turnos' => $turnosDia,
            ];
        }

        $calendarioCollection = collect($this->calendarioHorarios);
        $this->horarioAsignadoHoy = $calendarioCollection->first(fn ($item) => $item['es_hoy'] && !empty($item['turnos']));
        $this->horarioSiguienteAsignado = $calendarioCollection
            ->first(fn ($item) => !empty($item['turnos']) && !$item['es_hoy'] && Carbon::parse($item['fecha'])->greaterThan(today()))
            ?? $calendarioCollection->first(fn ($item) => !empty($item['turnos']) && !$item['es_hoy']);

        $turnoAsignadoNombre = data_get($this->horarioAsignadoHoy, 'turnos.0.turno');

        if ($turnoAsignadoNombre) {
            $this->turnoActual = $turnosOrdenados->first(function ($turno) use ($turnoAsignadoNombre) {
                return Str::lower(trim($turno->nombre)) === Str::lower(trim($turnoAsignadoNombre));
            });
        }

        if ($this->turnoActual) {
            $this->pacientesAsignadosIds = AsignacionTurnoAdulto::where('cod_usu_enfermero', $this->filtroEnfermeroId)
                ->where('cod_turno', $this->turnoActual->cod_turno)
                ->where('estado', 'ACTIVA')
                ->pluck('cod_am')
                ->toArray();
        }
    }

    public function exportarReporte()
    {
        $this->dispatch('notificar', ['tipo' => 'success', 'mensaje' => 'Generando reporte preliminar del turno...']);
        // Aquí iría la generación del PDF con laravel-pdf o dompdf.
    }

    public function render()
    {
        $stats = [
            'pacientes' => count($this->pacientesAsignadosIds),
            'tareas_pendientes' => 0,
            'medicacion_pendiente' => 0,
            'signos_pendientes' => 0, // Placeholder si no usamos la tabla directamente
            'alertas_activas' => 0,
            'seguimientos_hoy' => 0,
            'pase_pendiente' => 'NO INICIADO',
        ];

        $charts = [
            'tareas' => ['pendientes' => 0, 'realizadas' => 0, 'omitidas' => 0],
            'alertas' => ['leve' => 0, 'moderada' => 0, 'critica' => 0],
            'supervision' => ['Baja' => 0, 'Media' => 0, 'Alta' => 0],
            'medicacion' => ['administrada' => 0, 'omitida' => 0, 'pendiente' => 0],
            'actividad' => [], // horas -> count
        ];

        $tareasTabla = [];

        if ($this->turnoActual && count($this->pacientesAsignadosIds) > 0) {
            // Tareas
            $todasTareas = TareaPlanCuidado::whereIn('cod_am', $this->pacientesAsignadosIds)
                ->where('cod_turno', $this->turnoActual->cod_turno)
                ->whereDate('fecha_programada', $this->filtroFecha)
                ->get();
                
            $stats['tareas_pendientes'] = $todasTareas->where('estado', 'PENDIENTE')->count();
            $stats['signos_pendientes'] = $todasTareas->where('estado', 'PENDIENTE')
                ->filter(function($t) {
                    $titulo = strtolower($t->titulo);
                    return str_contains($titulo, 'signo') || str_contains($titulo, 'presión') || str_contains($titulo, 'temperatura');
                })->count();
            
            $charts['tareas']['pendientes'] = $todasTareas->where('estado', 'PENDIENTE')->count();
            $charts['tareas']['realizadas'] = $todasTareas->where('estado', 'REALIZADA')->count();
            $charts['tareas']['omitidas'] = $todasTareas->whereIn('estado', ['OMITIDA', 'CANCELADA'])->count();
            
            // Medicación
            $todasMed = AdministracionMedicacion::whereIn('cod_am', $this->pacientesAsignadosIds)
                ->whereDate('fecha', $this->filtroFecha)
                ->get();
                
            $stats['medicacion_pendiente'] = $todasMed->where('administrado', false)->whereNull('motivo_omision')->count();
            
            $charts['medicacion']['administrada'] = $todasMed->where('administrado', true)->count();
            $charts['medicacion']['omitida'] = $todasMed->where('administrado', false)->whereNotNull('motivo_omision')->count();
            $charts['medicacion']['pendiente'] = $stats['medicacion_pendiente'];

            // Alertas
            $todasAlertas = AlertaAdulto::whereIn('cod_am', $this->pacientesAsignadosIds)
                ->whereDate('created_at', $this->filtroFecha)
                ->get();
                
            $stats['alertas_activas'] = $todasAlertas->whereNotIn('estado', ['RESUELTA', 'CERRADA', 'FALSA_ALARMA'])->count();
            $stats['seguimientos_hoy'] = SeguimientoDiario::whereIn('cod_am', $this->pacientesAsignadosIds)
                ->whereDate('fecha', $this->filtroFecha)
                ->count();
            
            $charts['alertas']['leve'] = $todasAlertas->where('nivel', 'LEVE')->count();
            $charts['alertas']['moderada'] = $todasAlertas->where('nivel', 'MODERADA')->count();
            $charts['alertas']['critica'] = $todasAlertas->where('nivel', 'CRITICA')->count();

            // Pases de turno
            $pases = PaseTurno::where('enfermero_saliente_id', $this->filtroEnfermeroId)
                ->where('turno_saliente_id', $this->turnoActual->cod_turno)
                ->whereDate('fecha', $this->filtroFecha)
                ->first();
                
            $stats['pase_pendiente'] = $pases ? $pases->estado : 'NO INICIADO';

            // Supervisión
            $supervisiones = AsignacionTurnoAdulto::whereIn('cod_am', $this->pacientesAsignadosIds)
                ->where('cod_turno', $this->turnoActual->cod_turno)
                ->get();
                
            foreach($supervisiones as $sup) {
                $nivel = ucfirst(strtolower($sup->nivel_supervision ?? 'Baja'));
                if (isset($charts['supervision'][$nivel])) {
                    $charts['supervision'][$nivel]++;
                } else {
                    $charts['supervision']['Baja']++;
                }
            }

            // Llenar datos de actividad por hora (mock)
            for ($i = 0; $i < 8; $i++) {
                $horaStr = Carbon::parse($this->turnoActual->hora_inicio)->addHours($i)->format('H:00');
                $charts['actividad'][$horaStr] = rand(0, 5);
            }

            // Tabla Principal
            $tareasTabla = TareaPlanCuidado::with(['adultoMayor'])
                ->whereIn('cod_am', $this->pacientesAsignadosIds)
                ->where('cod_turno', $this->turnoActual->cod_turno)
                ->whereDate('fecha_programada', $this->filtroFecha)
                ->orderBy('hora_programada', 'asc')
                ->take(10)
                ->get();
        }

        // Preadmisiones asignadas al enfermero que aún no han sido admitidas.
        // En este punto del flujo la persona es solo una preadmisión (no adulto_mayor).
        $valoracionesPendientes = Preadmision::query()
            ->with(['documentos', 'enfermero'])
            ->where('enfermero_asignado', $this->filtroEnfermeroId)
            ->whereIn('estado', ['PREADMISION_ASIGNADA', 'EN_VALORACION_ENFERMERIA'])
            ->whereNull('cod_am_generado')
            ->orderByRaw("CASE prioridad WHEN 'CRITICA' THEN 1 WHEN 'ALTA' THEN 2 WHEN 'MEDIA' THEN 3 ELSE 4 END")
            ->orderByDesc('created_at')
            ->get();

        $valoracionesRealizadasHoy = ValoracionEnfermeriaAdmision::query()
            ->with(['adultoMayor', 'preadmision', 'registradoPor'])
            ->where('registrado_por', $this->filtroEnfermeroId)
            ->whereDate('fecha_valoracion', $this->filtroFecha)
            ->where('estado', 'COMPLETADA')
            ->orderByDesc('fecha_valoracion')
            ->orderByDesc('hora_valoracion')
            ->get();

        $stats['valoraciones_pendientes'] = $valoracionesPendientes->count();
        $stats['valoraciones_realizadas_hoy'] = $valoracionesRealizadasHoy->count();

        return view('livewire.cuidados.dashboard-turno', [
            'stats' => $stats,
            'charts' => $charts,
            'tareasTabla' => $tareasTabla,
            'valoracionesPendientes' => $valoracionesPendientes,
            'valoracionesRealizadasHoy' => $valoracionesRealizadasHoy,
            'turnosActivos' => $this->turnosActivos,
            'horariosPersonal' => $this->horariosPersonal,
            'horarioAsignadoHoy' => $this->horarioAsignadoHoy,
            'horarioSiguienteAsignado' => $this->horarioSiguienteAsignado,
            'calendarioHorarios' => $this->calendarioHorarios,
        ])->layout('layouts.sistema');
    }

    public function iniciarValoracion($cod_pre)
    {
        $preadmision = Preadmision::with(['documentos'])->find($cod_pre);

        if (! $preadmision) return;

        if ($preadmision->enfermero_asignado !== $this->filtroEnfermeroId) {
            $this->dispatch('notificar', ['tipo' => 'error', 'mensaje' => 'Esta preadmisión no está asignada a tu usuario.']);
            return;
        }

        // 2. Validar que corresponde al turno/día vigente en la planilla rotativa
        $service = app(\App\Services\Identidad\GeneradorPlanillaEnfermeriaService::class);
        $fechaHoy = now()->toDateString();
        $asignacionPlaza = $service->obtenerTurnoEnFecha($this->filtroEnfermeroId, $fechaHoy);

        if (!$asignacionPlaza || $asignacionPlaza['turno_codigo'] === 'DESCANSO') {
            $this->dispatch('notificar', [
                'tipo' => 'error',
                'mensaje' => "No tiene una asignación de turno vigente para iniciar esta valoración"
            ]);
            return;
        }

        // Nota: Permitimos iniciar la valoración a pesar de estar fuera del rango exacto de horas 
        // del turno, siempre que el enfermero tenga una plaza vigente hoy. Esto previene bloqueos por horas extra o registros tardíos.

        // 3. Validar no solapamiento con otras valoraciones iniciales que estén activamente EN PROCESO
        $solapamiento = Preadmision::where('enfermero_asignado', $this->filtroEnfermeroId)
            ->where('cod_pre', '!=', $cod_pre)
            ->where('estado', 'EN_VALORACION_ENFERMERIA')
            ->exists();

        if ($solapamiento) {
            $this->dispatch('notificar', [
                'tipo' => 'error',
                'mensaje' => 'Tienes otra valoración de preadmisión activa en proceso (solapamiento).'
            ]);
            return;
        }

        // Verificar documentos mínimos (cédula registrada en el modelo o archivo subido)
        $tieneCI = !empty($preadmision->ci) || ($preadmision->documentos
            ->whereIn('tipo_documento', ['CI', 'CI_ADULTO', 'IDENTIFICACION'])
            ->count() > 0);

        if (! $tieneCI && ! auth()->user()->hasRole('SUPERADMINISTRADOR')) {
            $this->dispatch('notificar', ['tipo' => 'error', 'mensaje' => 'Faltan documentos obligatorios (CI) para iniciar la valoración.']);
            return;
        }

        // Avanzar estado PREADMISION_ASIGNADA → EN_VALORACION_ENFERMERIA al iniciar
        if ($preadmision->estado === 'PREADMISION_ASIGNADA') {
            $preadmision->update(['estado' => 'EN_VALORACION_ENFERMERIA']);
        }

        activity('Enfermeria')
            ->causedBy(auth()->user())
            ->performedOn($preadmision)
            ->log("Inició valoración de enfermería para preadmisión {$preadmision->cod_pre}");

        $this->dispatch('notificar', [
            'tipo'    => 'success',
            'mensaje' => "Valoración iniciada para {$preadmision->nombres} {$preadmision->ap_paterno} ({$preadmision->cod_pre}).",
        ]);

        $this->dispatch('abrirValoracionInicial', $preadmision->cod_pre);
    }

    private function construirCalendarioHorarios($horarios): array
    {
        $dias = [
            1 => 'LUNES',
            2 => 'MARTES',
            3 => 'MIERCOLES',
            4 => 'JUEVES',
            5 => 'VIERNES',
            6 => 'SABADO',
            7 => 'DOMINGO',
        ];

        $calendario = [];

        for ($i = 0; $i < 14; $i++) {
            $fecha = Carbon::today()->addDays($i);
            $diaSemana = $dias[$fecha->dayOfWeekIso] ?? '';
            $turnosDia = $horarios->filter(function ($horario) use ($diaSemana) {
                return $this->normalizarDiaSemana($horario->dia_semana) === $this->normalizarDiaSemana($diaSemana);
            })->values();

            $calendario[] = [
                'fecha' => $fecha->toDateString(),
                'fecha_texto' => $fecha->translatedFormat('d \d\e F'),
                'dia_semana' => $diaSemana,
                'es_hoy' => $i === 0,
                'turnos' => $turnosDia->map(function ($horario) {
                    return [
                        'turno' => $horario->turno,
                        'hora_inicio' => substr($horario->hora_inicio, 0, 5),
                        'hora_fin' => substr($horario->hora_fin, 0, 5),
                    ];
                })->all(),
            ];
        }

        return $calendario;
    }

    private function normalizarDiaSemana(?string $dia): string
    {
        $dia = Str::upper(trim((string) $dia));
        $dia = strtr($dia, [
            'Á' => 'A', 'À' => 'A', 'Ä' => 'A',
            'É' => 'E', 'È' => 'E', 'Ë' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Ï' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ö' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Ü' => 'U',
            'Ñ' => 'N',
        ]);

        return preg_replace('/\s+/', '', $dia);
    }
}
