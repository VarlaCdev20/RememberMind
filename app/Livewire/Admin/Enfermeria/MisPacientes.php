<?php

namespace App\Livewire\Admin\Enfermeria;

use App\Models\AdultoMayor;
use App\Models\AsignacionTurnoAdulto;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class MisPacientes extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filtroEstado = 'TODOS';
    public string $filtroTurno = '';
    public string $filtroEnfermero = '';

    // Filtros rápidos
    public string $filtroRapido = 'TODOS'; 

    public function mount()
    {
        $this->filtroEnfermero = (string) auth()->id();
        
        // Determinar turno actual
        $horaActual = date('H:i:s');
        $turnoActual = TurnoEnfermeria::whereTime('hora_inicio', '<=', $horaActual)
            ->whereTime('hora_fin', '>=', $horaActual)
            ->first();
            
        if (!$turnoActual) {
             $turnoActual = TurnoEnfermeria::first();
        }

        if ($turnoActual) {
            $this->filtroTurno = (string) $turnoActual->cod_turno;
        }

        // Si es superadmin, permitir ver todos (dejamos filtro vacío si no selecciona uno)
        if (auth()->user()->hasRole('SUPERADMINISTRADOR')) {
            $this->filtroEnfermero = '';
            $this->filtroTurno = '';
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingFiltroRapido()
    {
        $this->resetPage();
    }
    
    public function updatingFiltroTurno()
    {
        $this->resetPage();
    }

    public function render()
    {
        $fechaHoy = Carbon::now()->toDateString();

        $amQuery = AdultoMayor::with([
            'habitacion', 'cama',
            'asignacionesTurno' => function($q) {
                $q->where('estado', 'ACTIVA')->with(['turno', 'enfermero']);
            },
            'planesCuidado' => function($q) {
                $q->where('estado', 'ACTIVO');
            }
        ])->withCount([
            'alertas as alertas_activas_count' => function($q) use ($fechaHoy) {
                $q->whereNotIn('estado', ['RESUELTA', 'CERRADA', 'FALSA_ALARMA'])
                  ->whereDate('created_at', $fechaHoy);
            },
            'tareasActuales as tareas_pendientes_count' => function($q) use ($fechaHoy) {
                $q->where('estado', 'PENDIENTE')->whereDate('fecha_programada', $fechaHoy);
            },
            'tareasActuales as tareas_vencidas_count' => function($q) use ($fechaHoy) {
                $q->where('estado', 'PENDIENTE')->whereDate('fecha_programada', '<', $fechaHoy);
            },
            'administracionesMedicacion as medicacion_pendiente_count' => function($q) use ($fechaHoy) {
                $q->where('estado', 'PENDIENTE')->whereDate('fecha', $fechaHoy);
            },
            'tareasActuales as signos_pendientes_count' => function($q) use ($fechaHoy) {
                $q->where('estado', 'PENDIENTE')
                  ->whereDate('fecha_programada', $fechaHoy)
                  ->where(function($sub) {
                      $sub->where('titulo', 'ilike', '%signo%')
                          ->orWhere('titulo', 'ilike', '%presi%')
                          ->orWhere('titulo', 'ilike', '%temperatura%')
                          ->orWhere('area', 'SIGNOS_VITALES');
                  });
            },
            'seguimientosDiarios as seguimientos_hoy_count' => function($q) use ($fechaHoy) {
                $q->whereDate('fecha', $fechaHoy);
            }
        ]);

        // Filtrar por asignación al enfermero y turno actual
        $amQuery->whereHas('asignacionesTurno', function($q) {
            $q->where('estado', 'ACTIVA');
            if ($this->filtroTurno !== '') {
                $q->where('cod_turno', $this->filtroTurno);
            }
            if ($this->filtroEnfermero !== '') {
                $q->where('cod_usu_enfermero', $this->filtroEnfermero);
            }
        });

        // Search
        if ($this->search) {
            $amQuery->where(function ($q) {
                $q->where('nombres', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ap_paterno', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ap_materno', 'ilike', '%' . $this->search . '%')
                  ->orWhere('ci', 'ilike', '%' . $this->search . '%');
            });
        }

        if ($this->filtroEstado !== 'TODOS') {
            $amQuery->where('estado', $this->filtroEstado);
        }

        // Filtros rápidos
        if ($this->filtroRapido === 'CON_TAREAS') {
            $amQuery->having('tareas_pendientes_count', '>', 0);
        } elseif ($this->filtroRapido === 'CON_ALERTAS') {
            $amQuery->having('alertas_activas_count', '>', 0);
        } elseif ($this->filtroRapido === 'MEDICACION_PENDIENTE') {
            $amQuery->having('medicacion_pendiente_count', '>', 0);
        } elseif ($this->filtroRapido === 'CON_SIGNOS_PENDIENTES') {
            $amQuery->having('signos_pendientes_count', '>', 0);
        } elseif ($this->filtroRapido === 'SIN_SEGUIMIENTO') {
            $amQuery->having('seguimientos_hoy_count', '=', 0);
        }

        $pacientes = $amQuery->orderBy('nombres')->paginate(12);

        // Stats
        $statsBase = AdultoMayor::whereHas('asignacionesTurno', function($q) {
            $q->where('estado', 'ACTIVA');
            if ($this->filtroTurno !== '') $q->where('cod_turno', $this->filtroTurno);
            if ($this->filtroEnfermero !== '') $q->where('cod_usu_enfermero', $this->filtroEnfermero);
        })->withCount([
            'alertas as alertas_activas_count' => function($q) use ($fechaHoy) {
                $q->whereNotIn('estado', ['RESUELTA', 'CERRADA', 'FALSA_ALARMA'])->whereDate('created_at', $fechaHoy);
            },
            'tareasActuales as tareas_vencidas_count' => function($q) use ($fechaHoy) {
                $q->where('estado', 'PENDIENTE')->whereDate('fecha_programada', '<', $fechaHoy);
            },
            'seguimientosDiarios as seguimientos_hoy_count' => function($q) use ($fechaHoy) {
                $q->whereDate('fecha', $fechaHoy);
            }
        ])->get();

        $stats = [
            'total' => $statsBase->count(),
            'con_alerta' => $statsBase->where('alertas_activas_count', '>', 0)->count(),
            'sin_seguimiento' => $statsBase->where('seguimientos_hoy_count', 0)->count(),
            'tareas_vencidas' => $statsBase->where('tareas_vencidas_count', '>', 0)->count(),
        ];

        return view('livewire.admin.enfermeria.mis-pacientes', [
            'pacientes' => $pacientes,
            'stats' => $stats,
            'turnos' => TurnoEnfermeria::activos()->get(),
            'enfermeros' => User::role('ENFERMEROS')->orderBy('ap_paterno')->get(['cod_usu','nombres','ap_paterno'])
        ])->layout('layouts.sistema');
    }
}
