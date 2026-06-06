<?php

namespace App\Livewire\Admin\Enfermeria;

use Livewire\Component;
use App\Models\TurnoEnfermeria;
use App\Models\AsignacionTurnoAdulto;
use App\Models\TareaPlanCuidado;
use App\Models\AlertaAdulto;
use App\Models\PaseTurno;
use App\Models\AdministracionMedicacion;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardTurno extends Component
{
    public $turnoActual;
    public $pacientesAsignadosIds = [];
    public $filtroEnfermeroId;
    public $filtroFecha;

    public function mount()
    {
        $this->filtroEnfermeroId = auth()->id();
        $this->filtroFecha = Carbon::now()->toDateString();
        $this->loadTurnoActual();
    }

    public function loadTurnoActual()
    {
        $horaActual = date('H:i:s');
        
        // Intentar encontrar el turno actual por hora
        $this->turnoActual = TurnoEnfermeria::whereTime('hora_inicio', '<=', $horaActual)
            ->whereTime('hora_fin', '>=', $horaActual)
            ->first();
            
        // Fallback al primer turno si no hay coincidencia (ej. turnos nocturnos que cruzan la medianoche)
        if (!$this->turnoActual) {
             $this->turnoActual = TurnoEnfermeria::first();
        }

        if ($this->turnoActual) {
            $this->pacientesAsignadosIds = AsignacionTurnoAdulto::where('cod_usu_enfermero', $this->filtroEnfermeroId)
                ->where('cod_turno', $this->turnoActual->cod_turno)
                ->where('estado', 'ACTIVO')
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
                ->where('cod_turno', $this->turnoActual->cod_turno)
                ->get();
                
            $stats['medicacion_pendiente'] = $todasMed->where('estado', 'PENDIENTE')->count();
            
            $charts['medicacion']['administrada'] = $todasMed->where('administrado', true)->count();
            $charts['medicacion']['omitida'] = $todasMed->where('administrado', false)->where('estado', '!=', 'PENDIENTE')->count();
            $charts['medicacion']['pendiente'] = $stats['medicacion_pendiente'];

            // Alertas
            $todasAlertas = AlertaAdulto::whereIn('cod_am', $this->pacientesAsignadosIds)
                ->whereDate('created_at', $this->filtroFecha)
                ->get();
                
            $stats['alertas_activas'] = $todasAlertas->whereNotIn('estado', ['RESUELTA', 'CERRADA', 'FALSA_ALARMA'])->count();
            
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

        $valoracionesPendientes = \App\Models\AdultoMayor::whereHas('asignacionesTurno', function($q) {
            $q->where('cod_usu_enfermero', $this->filtroEnfermeroId)
              ->where('motivo_asignacion', 'VALORACION INICIAL')
              ->where('estado', 'ACTIVA');
        })->whereHas('estado', function($q) {
            $q->whereIn('estado', ['VALORACION_INICIAL', 'PENDIENTE_VALORACION_INICIAL']);
        })->with(['estado', 'asignacionTurnoActiva', 'documentos'])->get();

        return view('livewire.admin.enfermeria.dashboard-turno', [
            'stats' => $stats,
            'charts' => $charts,
            'tareasTabla' => $tareasTabla,
            'valoracionesPendientes' => $valoracionesPendientes,
        ])->layout('layouts.sistema');
    }

    public function iniciarValoracion($cod_am)
    {
        $adulto = \App\Models\AdultoMayor::with('documentos')->find($cod_am);
        
        if (!$adulto) return;

        // Validar documentos obligatorios básicos (ejemplo: CI debe existir)
        $tieneCI = $adulto->documentos->whereIn('tipo_documento', ['CI', 'CI_ADULTO'])->count() > 0;
        
        if (!$tieneCI && !auth()->user()->hasRole('SUPERADMINISTRADOR')) {
            $this->dispatch('notificar', ['tipo' => 'error', 'mensaje' => 'Faltan documentos obligatorios (CI) para iniciar la valoración.']);
            return;
        }

        activity('Enfermeria')->causedBy(auth()->user())->performedOn($adulto)->log("Inició valoración inicial del paciente {$adulto->cod_am}");
        
        $this->dispatch('abrirValoracionInicial', $cod_am);
    }
}
