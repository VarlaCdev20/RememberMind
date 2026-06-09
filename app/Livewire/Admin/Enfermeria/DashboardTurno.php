<?php

namespace App\Livewire\Admin\Enfermeria;

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

        $this->horariosPersonal = auth()->user()?->horariosSalud()
            ->where('estado', 'ACTIVO')
            ->orderByRaw("CASE WHEN dia_semana = 'LUNES' THEN 1 WHEN dia_semana = 'MARTES' THEN 2 WHEN dia_semana = 'MIERCOLES' THEN 3 WHEN dia_semana = 'JUEVES' THEN 4 WHEN dia_semana = 'VIERNES' THEN 5 WHEN dia_semana = 'SABADO' THEN 6 WHEN dia_semana = 'DOMINGO' THEN 7 ELSE 8 END")
            ->orderBy('hora_inicio')
            ->get() ?? [];

        $this->calendarioHorarios = $this->construirCalendarioHorarios($this->horariosPersonal);
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

        $valoracionesPendientes = Preadmision::query()
            ->with(['adultoGenerado', 'documentos', 'enfermero'])
            ->where('enfermero_asignado', $this->filtroEnfermeroId)
            ->where('estado', 'APROBADA')
            ->whereNotNull('cod_am_generado')
            ->whereHas('adultoGenerado', function ($q) {
                $q->whereNull('cod_habitacion')
                  ->whereNull('cod_cama')
                  ->whereHas('estado', function ($estadoQuery) {
                      $estadoQuery->whereIn('estado', ['PENDIENTE_VALORACION_ENFERMERIA', 'PREADMISION']);
                  });
            })
            ->get();

        $valoracionesRealizadasHoy = ValoracionEnfermeriaAdmision::query()
            ->with(['adultoMayor', 'registradoPor'])
            ->where('registrado_por', $this->filtroEnfermeroId)
            ->whereDate('fecha_valoracion', $this->filtroFecha)
            ->where('estado', 'COMPLETADA')
            ->orderByDesc('fecha_valoracion')
            ->orderByDesc('hora_valoracion')
            ->get();

        $stats['valoraciones_pendientes'] = $valoracionesPendientes->count();
        $stats['valoraciones_realizadas_hoy'] = $valoracionesRealizadasHoy->count();

        return view('livewire.admin.enfermeria.dashboard-turno', [
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

    public function iniciarValoracion($cod_am)
    {
        $adulto = \App\Models\AdultoMayor::with(['documentos', 'preadmisionOrigen'])->find($cod_am);
        
        if (!$adulto) return;

        if (! $adulto->preadmisionOrigen || $adulto->preadmisionOrigen->enfermero_asignado !== $this->filtroEnfermeroId) {
            $this->dispatch('notificar', ['tipo' => 'error', 'mensaje' => 'Este caso no está asignado a tu usuario para valoración inicial.']);
            return;
        }

        // Validar documentos obligatorios básicos (ejemplo: CI debe existir)
        $tieneCI = $adulto->documentos->whereIn('tipo_documento', ['CI', 'CI_ADULTO'])->count() > 0;
        
        if (!$tieneCI && !auth()->user()->hasRole('SUPERADMINISTRADOR')) {
            $this->dispatch('notificar', ['tipo' => 'error', 'mensaje' => 'Faltan documentos obligatorios (CI) para iniciar la valoración.']);
            return;
        }

        activity('Enfermeria')->causedBy(auth()->user())->performedOn($adulto)->log("Inició valoración inicial del paciente {$adulto->cod_am}");
        
        $this->dispatch('abrirValoracionInicial', $cod_am);
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
