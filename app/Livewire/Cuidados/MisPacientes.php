<?php

namespace App\Livewire\Cuidados;

use App\Models\AdultoMayor;
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

    /**
     * Filtros rápidos:
     * TODOS
     * CON_TAREAS
     * CON_ALERTAS
     * MEDICACION_PENDIENTE
     * CON_SIGNOS_PENDIENTES
     * SIN_SEGUIMIENTO
     */
    public string $filtroRapido = 'TODOS';

    public function mount(): void
    {
        $this->filtroEnfermero = (string) auth()->id();

        $turnoActual = $this->obtenerTurnoActual();

        if ($turnoActual) {
            $this->filtroTurno = (string) $turnoActual->cod_turno;
        }

        /*
         * Si es superadmin, puede ver todos los enfermeros/turnos.
         */
        if (auth()->user()?->hasRole('SUPERADMINISTRADOR')) {
            $this->filtroEnfermero = '';
            $this->filtroTurno = '';
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroRapido(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroTurno(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEnfermero(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $fechaHoy = Carbon::now()->toDateString();

        $pacientesQuery = AdultoMayor::query()
            ->with([
                'habitacion',
                'cama',
                'asignacionesTurno' => function ($q) {
                    $q->where('estado', 'ACTIVA')
                        ->with(['turno', 'enfermero']);
                },
                'planesCuidado' => function ($q) {
                    $q->where('estado', 'ACTIVO');
                },
            ])
            ->withCount([
                'alertas as alertas_activas_count' => function ($q) use ($fechaHoy) {
                    $q->whereNotIn('estado', ['RESUELTA', 'CERRADA', 'FALSA_ALARMA'])
                        ->whereDate('created_at', $fechaHoy);
                },

                'tareasActuales as tareas_pendientes_count' => function ($q) use ($fechaHoy) {
                    $q->where('estado', 'PENDIENTE')
                        ->whereDate('fecha_programada', $fechaHoy);
                },

                'tareasActuales as tareas_vencidas_count' => function ($q) use ($fechaHoy) {
                    $q->where('estado', 'PENDIENTE')
                        ->whereDate('fecha_programada', '<', $fechaHoy);
                },

                /*
                 * CORREGIDO:
                 * La tabla administracion_medicacion NO tiene columna estado.
                 * Sus campos reales incluyen:
                 * - fecha
                 * - hora_programada
                 * - hora_real
                 * - administrado boolean
                 * - motivo_omision
                 * - efecto_observado
                 * - observacion
                 *
                 * Entonces:
                 * administrado = false/null => medicación pendiente.
                 */
                'administracionesMedicacion as medicacion_pendiente_count' => function ($q) use ($fechaHoy) {
                    $q->whereDate('fecha', $fechaHoy)
                        ->where(function ($sub) {
                            $sub->where('administrado', false)
                                ->orWhereNull('administrado');
                        });
                },

                'tareasActuales as signos_pendientes_count' => function ($q) use ($fechaHoy) {
                    $q->where('estado', 'PENDIENTE')
                        ->whereDate('fecha_programada', $fechaHoy)
                        ->where(function ($sub) {
                            $sub->where('titulo', 'ilike', '%signo%')
                                ->orWhere('titulo', 'ilike', '%presi%')
                                ->orWhere('titulo', 'ilike', '%temperatura%')
                                ->orWhere('area', 'SIGNOS_VITALES');
                        });
                },

                'seguimientosDiarios as seguimientos_hoy_count' => function ($q) use ($fechaHoy) {
                    $q->whereDate('fecha', $fechaHoy);
                },
            ]);

        /*
         * Filtra adultos mayores asignados al enfermero y turno actual.
         * Esta pantalla es para PACIENTES ya admitidos/asignados,
         * no para preadmisiones.
         */
        $pacientesQuery->whereHas('asignacionesTurno', function ($q) {
            $q->where('estado', 'ACTIVA');

            if ($this->filtroTurno !== '') {
                $q->where('cod_turno', $this->filtroTurno);
            }

            if ($this->filtroEnfermero !== '') {
                $q->where('cod_usu_enfermero', $this->filtroEnfermero);
            }
        });

        /*
         * Búsqueda por datos del adulto mayor.
         */
        if (trim($this->search) !== '') {
            $busqueda = trim($this->search);

            $pacientesQuery->where(function ($q) use ($busqueda) {
                $q->where('nombres', 'ilike', '%' . $busqueda . '%')
                    ->orWhere('ap_paterno', 'ilike', '%' . $busqueda . '%')
                    ->orWhere('ap_materno', 'ilike', '%' . $busqueda . '%')
                    ->orWhere('ci', 'ilike', '%' . $busqueda . '%')
                    ->orWhere('cod_am', 'ilike', '%' . $busqueda . '%');
            });
        }

        /*
         * Filtro por estado del adulto mayor.
         * Se mantiene como estaba porque tu componente ya lo usaba.
         */
        if ($this->filtroEstado !== 'TODOS') {
            $pacientesQuery->where('estado', $this->filtroEstado);
        }

        /*
         * Filtros rápidos corregidos usando whereHas.
         * Evitamos depender de HAVING con alias de withCount,
         * que puede ser problemático en PostgreSQL.
         */
        $this->aplicarFiltroRapido($pacientesQuery, $fechaHoy);

        $pacientes = $pacientesQuery
            ->orderBy('nombres')
            ->paginate(12);

        /*
         * Estadísticas del turno/enfermero actual.
         */
        $statsBase = AdultoMayor::query()
            ->whereHas('asignacionesTurno', function ($q) {
                $q->where('estado', 'ACTIVA');

                if ($this->filtroTurno !== '') {
                    $q->where('cod_turno', $this->filtroTurno);
                }

                if ($this->filtroEnfermero !== '') {
                    $q->where('cod_usu_enfermero', $this->filtroEnfermero);
                }
            })
            ->withCount([
                'alertas as alertas_activas_count' => function ($q) use ($fechaHoy) {
                    $q->whereNotIn('estado', ['RESUELTA', 'CERRADA', 'FALSA_ALARMA'])
                        ->whereDate('created_at', $fechaHoy);
                },

                'tareasActuales as tareas_vencidas_count' => function ($q) use ($fechaHoy) {
                    $q->where('estado', 'PENDIENTE')
                        ->whereDate('fecha_programada', '<', $fechaHoy);
                },

                'seguimientosDiarios as seguimientos_hoy_count' => function ($q) use ($fechaHoy) {
                    $q->whereDate('fecha', $fechaHoy);
                },
            ])
            ->get();

        $stats = [
            'total' => $statsBase->count(),
            'con_alerta' => $statsBase->where('alertas_activas_count', '>', 0)->count(),
            'sin_seguimiento' => $statsBase->where('seguimientos_hoy_count', 0)->count(),
            'tareas_vencidas' => $statsBase->where('tareas_vencidas_count', '>', 0)->count(),
        ];

        return view('livewire.cuidados.mis-pacientes', [
            'pacientes' => $pacientes,
            'stats' => $stats,
            'turnos' => TurnoEnfermeria::activos()->get(),
            'enfermeros' => User::role('ENFERMEROS')
                ->where('estado', 'ACTIVO')
                ->orderBy('ap_paterno')
                ->get(['cod_usu', 'nombres', 'ap_paterno', 'ap_materno']),
        ])->layout('layouts.sistema');
    }

    /**
     * Obtiene el turno actual considerando turnos normales y turnos nocturnos.
     *
     * Turno normal:
     * 06:00 - 14:00
     *
     * Turno nocturno:
     * 22:00 - 06:00
     */
    private function obtenerTurnoActual(): ?TurnoEnfermeria
    {
        $horaActual = Carbon::now()->format('H:i:s');

        $turnoActual = TurnoEnfermeria::query()
            ->where(function ($q) use ($horaActual) {
                /*
                 * Turnos normales:
                 * hora_inicio <= hora_fin
                 * Ejemplo: 06:00 - 14:00
                 */
                $q->whereColumn('hora_inicio', '<=', 'hora_fin')
                    ->whereTime('hora_inicio', '<=', $horaActual)
                    ->whereTime('hora_fin', '>=', $horaActual);
            })
            ->orWhere(function ($q) use ($horaActual) {
                /*
                 * Turnos que cruzan medianoche:
                 * hora_inicio > hora_fin
                 * Ejemplo: 22:00 - 06:00
                 */
                $q->whereColumn('hora_inicio', '>', 'hora_fin')
                    ->where(function ($sub) use ($horaActual) {
                        $sub->whereTime('hora_inicio', '<=', $horaActual)
                            ->orWhereTime('hora_fin', '>=', $horaActual);
                    });
            })
            ->first();

        return $turnoActual ?: TurnoEnfermeria::first();
    }

    /**
     * Aplica filtros rápidos usando relaciones reales.
     * Esto evita usar HAVING sobre alias calculados por withCount,
     * que puede fallar o comportarse raro en PostgreSQL.
     */
    private function aplicarFiltroRapido($query, string $fechaHoy): void
    {
        if ($this->filtroRapido === 'CON_TAREAS') {
            $query->whereHas('tareasActuales', function ($q) use ($fechaHoy) {
                $q->where('estado', 'PENDIENTE')
                    ->whereDate('fecha_programada', $fechaHoy);
            });
        }

        if ($this->filtroRapido === 'CON_ALERTAS') {
            $query->whereHas('alertas', function ($q) use ($fechaHoy) {
                $q->whereNotIn('estado', ['RESUELTA', 'CERRADA', 'FALSA_ALARMA'])
                    ->whereDate('created_at', $fechaHoy);
            });
        }

        if ($this->filtroRapido === 'MEDICACION_PENDIENTE') {
            $query->whereHas('administracionesMedicacion', function ($q) use ($fechaHoy) {
                $q->whereDate('fecha', $fechaHoy)
                    ->where(function ($sub) {
                        $sub->where('administrado', false)
                            ->orWhereNull('administrado');
                    });
            });
        }

        if ($this->filtroRapido === 'CON_SIGNOS_PENDIENTES') {
            $query->whereHas('tareasActuales', function ($q) use ($fechaHoy) {
                $q->where('estado', 'PENDIENTE')
                    ->whereDate('fecha_programada', $fechaHoy)
                    ->where(function ($sub) {
                        $sub->where('titulo', 'ilike', '%signo%')
                            ->orWhere('titulo', 'ilike', '%presi%')
                            ->orWhere('titulo', 'ilike', '%temperatura%')
                            ->orWhere('area', 'SIGNOS_VITALES');
                    });
            });
        }

        if ($this->filtroRapido === 'SIN_SEGUIMIENTO') {
            $query->whereDoesntHave('seguimientosDiarios', function ($q) use ($fechaHoy) {
                $q->whereDate('fecha', $fechaHoy);
            });
        }
    }
}