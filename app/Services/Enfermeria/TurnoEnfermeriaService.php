<?php

namespace App\Services\Enfermeria;

use App\Models\AdultoMayor;
use App\Models\AsignacionTurnoAdulto;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Identidad\GeneradorPlanillaEnfermeriaService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TurnoEnfermeriaService
{
    /**
     * Determina si el usuario tiene rol de Superadministrador o Administrador (visión institucional completa).
     */
    public function esSuperAdmin(?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return false;
        }
        return $user->hasRole('SUPERADMINISTRADOR') || $user->hasRole('ADMINISTRADOR');
    }

    /**
     * Obtiene el turno activo del enfermero en una fecha dada.
     * Prioriza la planilla institucional y como fallback la franja horaria actual.
     */
    public function obtenerTurnoActivo(?User $user = null, ?string $fecha = null): ?TurnoEnfermeria
    {
        $user = $user ?? Auth::user();
        $fechaStr = $fecha ?: Carbon::today()->toDateString();
        $turnosOrdenados = TurnoEnfermeria::activos()->orderBy('orden')->get();

        if ($turnosOrdenados->isEmpty()) {
            return null;
        }

        // 1. Intentar obtener de la planilla de asignaciones si existe el servicio y el usuario
        if ($user) {
            try {
                $service = app(GeneradorPlanillaEnfermeriaService::class);
                $asignacion = $service->obtenerTurnoEnFecha($user->cod_usu, $fechaStr);
                if ($asignacion && !empty($asignacion['turno_nombre']) && $asignacion['turno_codigo'] !== 'DESCANSO') {
                    $nombreAsignado = Str::lower(trim($asignacion['turno_nombre']));
                    $turnoPlanilla = $turnosOrdenados->first(fn ($t) => Str::lower(trim($t->nombre)) === $nombreAsignado);
                    if ($turnoPlanilla) {
                        return $turnoPlanilla;
                    }
                }
            } catch (\Throwable $e) {
                // Fallback al cálculo horario
            }
        }

        // 2. Cálculo por hora actual
        $horaActual = Carbon::now()->format('H:i:s');
        $turnoPorHora = TurnoEnfermeria::activos()
            ->where(function ($q) use ($horaActual) {
                $q->whereColumn('hora_inicio', '<=', 'hora_fin')
                    ->whereTime('hora_inicio', '<=', $horaActual)
                    ->whereTime('hora_fin', '>=', $horaActual);
            })
            ->orWhere(function ($q) use ($horaActual) {
                $q->whereColumn('hora_inicio', '>', 'hora_fin')
                    ->where(function ($sub) use ($horaActual) {
                        $sub->whereTime('hora_inicio', '<=', $horaActual)
                            ->orWhereTime('hora_fin', '>=', $horaActual);
                    });
            })
            ->orderBy('orden')
            ->first();

        return $turnoPorHora ?: $turnosOrdenados->first();
    }

    /**
     * Consulta base de adultos mayores según las asignaciones activas en AsignacionTurnoAdulto.
     * Si es Superadmin/Admin, permite ver todos (o filtrar opcionalmente por turno/enfermero).
     * Si es enfermero normal, restringe estrictamente a los residentes asignados en AsignacionTurnoAdulto.
     */
    public function obtenerPacientesAsignadosQuery(?User $user = null, ?string $codTurno = null, ?string $codEnfermeroFiltro = null): Builder
    {
        $user = $user ?? Auth::user();
        $esSuperAdmin = $this->esSuperAdmin($user);

        $query = AdultoMayor::query();

        if ($esSuperAdmin) {
            if ($codEnfermeroFiltro) {
                $query->whereHas('asignacionesTurno', function ($q) use ($codEnfermeroFiltro, $codTurno) {
                    $q->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                        ->where('cod_usu_enfermero', $codEnfermeroFiltro);
                    if ($codTurno) {
                        $q->where('cod_turno', $codTurno);
                    }
                });
            } elseif ($codTurno) {
                $query->whereHas('asignacionesTurno', function ($q) use ($codTurno) {
                    $q->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                        ->where('cod_turno', $codTurno);
                });
            }
            return $query;
        }

        // Enfermero normal: restringido a AsignacionTurnoAdulto activa
        $codEnf = $user?->cod_usu;
        $query->whereHas('asignacionesTurno', function ($q) use ($codEnf, $codTurno) {
            $q->whereIn('estado', ['ACTIVO', 'ACTIVA'])
                ->where('cod_usu_enfermero', $codEnf)
                ->where(function ($dateQ) {
                    $dateQ->whereNull('fecha_fin')
                          ->orWhereDate('fecha_fin', '>=', Carbon::today());
                });
            if ($codTurno) {
                $q->where(function ($sub) use ($codTurno) {
                    $sub->where('cod_turno', $codTurno)
                        ->orWhereNull('cod_turno');
                });
            }
        });

        return $query;
    }

    /**
     * Array de cod_am de pacientes asignados al enfermero / turno.
     */
    public function obtenerPacientesAsignadosIds(?User $user = null, ?string $codTurno = null, ?string $codEnfermeroFiltro = null): array
    {
        return $this->obtenerPacientesAsignadosQuery($user, $codTurno, $codEnfermeroFiltro)
            ->pluck('cod_am')
            ->toArray();
    }

    /**
     * Verifica si un adulto mayor está asignado al enfermero en AsignacionTurnoAdulto.
     */
    public function esPacienteAsignado(string|AdultoMayor $adulto, ?User $user = null, ?string $codTurno = null): bool
    {
        $user = $user ?? Auth::user();
        if ($this->esSuperAdmin($user)) {
            return true;
        }

        if (!$user) {
            return false;
        }

        $codAm = $adulto instanceof AdultoMayor ? $adulto->cod_am : $adulto;

        return AsignacionTurnoAdulto::where('cod_am', $codAm)
            ->where('cod_usu_enfermero', $user->cod_usu)
            ->whereIn('estado', ['ACTIVO', 'ACTIVA'])
            ->where(function ($dateQ) {
                $dateQ->whereNull('fecha_fin')
                      ->orWhereDate('fecha_fin', '>=', Carbon::today());
            })
            ->when($codTurno, function ($q) use ($codTurno) {
                $q->where(function ($sub) use ($codTurno) {
                    $sub->where('cod_turno', $codTurno)
                        ->orWhereNull('cod_turno');
                });
            })
            ->exists();
    }

    /**
     * Valida de manera estricta y lanza 403 si el enfermero no tiene asignado al residente.
     */
    public function autorizarAccionPaciente(string|AdultoMayor $adulto, ?User $user = null, ?string $codTurno = null): void
    {
        if (!$this->esPacienteAsignado($adulto, $user, $codTurno)) {
            abort(403, 'Acción denegada: no tiene asignado a este residente en su turno activo.');
        }
    }

    /**
     * Acota una consulta de Tareas al universo asignado del enfermero.
     */
    public function acotarTareasQuery(Builder $query, ?User $user = null, ?string $codTurno = null): Builder
    {
        if ($this->esSuperAdmin($user)) {
            if ($codTurno) {
                $query->where('cod_turno', $codTurno);
            }
            return $query;
        }

        $pacientesIds = $this->obtenerPacientesAsignadosIds($user, $codTurno);
        return $query->whereIn('cod_am', $pacientesIds);
    }

    /**
     * Acota una consulta de Alertas al universo asignado del enfermero.
     */
    public function acotarAlertasQuery(Builder $query, ?User $user = null): Builder
    {
        if ($this->esSuperAdmin($user)) {
            return $query;
        }

        $pacientesIds = $this->obtenerPacientesAsignadosIds($user);
        return $query->whereIn('cod_am', $pacientesIds);
    }

    /**
     * Acota una consulta de Seguimiento Diario al universo asignado del enfermero.
     */
    public function acotarSeguimientosQuery(Builder $query, ?User $user = null, ?string $codTurno = null): Builder
    {
        if ($this->esSuperAdmin($user)) {
            if ($codTurno) {
                $query->where('cod_turno', $codTurno);
            }
            return $query;
        }

        $pacientesIds = $this->obtenerPacientesAsignadosIds($user, $codTurno);
        return $query->whereIn('cod_am', $pacientesIds);
    }
}
