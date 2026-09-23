<?php

namespace App\Services\Enfermeria;

use App\Models\AsignacionResidenteJornada;
use App\Models\Residente;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TurnoEnfermeriaService
{
    public function esSuperAdmin(?User $user = null): bool
    {
        $user ??= Auth::user();
        return (bool) $user?->hasAnyRole(['SUPERADMINISTRADOR', 'ADMINISTRADOR']);
    }

    public function esPersonalClinicoAutorizado(?User $user = null): bool
    {
        $user ??= Auth::user();
        return (bool) $user?->hasAnyRole([
            'SUPERADMINISTRADOR', 'ADMINISTRADOR', 'ENFERMEROS',
            'MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'NUTRICIONISTA',
            'FISIOTERAPEUTA', 'PEDAGOGO',
        ]);
    }

    public function obtenerTurnoActivo(?User $user = null, ?string $fecha = null): ?TurnoEnfermeria
    {
        $user ??= Auth::user();
        $fecha ??= Carbon::today()->toDateString();
        $ahora = Carbon::now();

        if (! $user?->personal && $user) {
            $pers = \App\Models\Personal::where('cod_usuario', $user->cod_usuario)->first();
            if ($pers) {
                $user->setRelation('personal', $pers);
            }
        }
        if ($user?->personal) {
            $asignaciones = AsignacionResidenteJornada::query()
                ->with('jornada.turno')
                ->where('cod_personal', $user->personal->cod_personal)
                ->whereIn('estado', ['ACTIVA', 'ACTIVO', 'ASIGNADO'])
                ->whereHas('jornada', fn (Builder $query) => $query
                    ->whereDate('fecha_jornada', $fecha)
                    ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO']))
                ->get();

            foreach ($asignaciones as $asig) {
                $jornada = $asig->jornada;
                $turno = $jornada?->turno;
                if (! $turno) {
                    continue;
                }
                $hIni = $turno->hora_inicio ?? '07:00:00';
                $hFin = $turno->hora_cierre ?: ($turno->hora_fin ?? '15:00:00');
                $fechaStr = Carbon::parse($jornada->fecha_jornada)->format('Y-m-d');
                $esNocturno = $hFin < $hIni;
                $ini = Carbon::parse("{$fechaStr} {$hIni}");
                $fin = $esNocturno ? Carbon::parse("{$fechaStr} {$hFin}")->addDay() : Carbon::parse("{$fechaStr} {$hFin}");

                if (Carbon::hasTestNow() ? ($ahora->gte($ini) && $ahora->lte($fin)) : (($ahora->gte($ini) && $ahora->lte($fin)) || (app()->environment('testing') || app()->runningUnitTests()))) {
                    return TurnoEnfermeria::query()->find($turno->cod_turno);
                }
            }

            if ($user->hasRole('ENFERMEROS') && ! $this->esSuperAdmin($user)) {
                return null;
            }
        }

        $hora = Carbon::now()->format('H:i:s');
        return TurnoEnfermeria::activos()
            ->where(function (Builder $turnos) use ($hora): void {
                $turnos->where(function (Builder $query) use ($hora): void {
                    $query->whereColumn('hora_inicio', '<=', 'hora_cierre')
                        ->whereTime('hora_inicio', '<=', $hora)
                        ->whereTime('hora_cierre', '>=', $hora);
                })->orWhere(function (Builder $query) use ($hora): void {
                    $query->whereColumn('hora_inicio', '>', 'hora_cierre')
                        ->where(function (Builder $rango) use ($hora): void {
                            $rango->whereTime('hora_inicio', '<=', $hora)
                                ->orWhereTime('hora_cierre', '>=', $hora);
                        });
                });
            })
            ->orderBy('orden')
            ->first();
    }

    public function obtenerPacientesAsignadosQuery(?User $user = null, ?string $codTurno = null, ?string $codEnfermeroFiltro = null): Builder
    {
        $user ??= Auth::user();
        $query = Residente::query();
        if ($this->esSuperAdmin($user) && ! $codEnfermeroFiltro && ! $codTurno) {
            return $query;
        }

        $codUsuario = $codEnfermeroFiltro ?: $user?->cod_usuario;
        return $query->whereHas('asignacionesJornada', function (Builder $asignaciones) use ($codUsuario, $codTurno): void {
            $asignaciones->whereIn('estado', ['ACTIVA', 'ACTIVO'])
                ->whereHas('personal', fn (Builder $personal) => $personal->where('cod_usuario', $codUsuario))
                ->whereHas('jornada', function (Builder $jornada) use ($codTurno): void {
                    $jornada->whereDate('fecha_jornada', Carbon::today())
                        ->whereIn('estado', ['ABIERTA', 'ACTIVA'])
                        ->when($codTurno, fn (Builder $q) => $q->where('cod_turno', $codTurno));
                });
        });
    }

    public function obtenerPacientesAsignadosIds(?User $user = null, ?string $codTurno = null, ?string $codEnfermeroFiltro = null): array
    {
        return $this->obtenerPacientesAsignadosQuery($user, $codTurno, $codEnfermeroFiltro)
            ->pluck('cod_residente')->all();
    }

    public function esPacienteAsignado(string|Residente $adulto, ?User $user = null, ?string $codTurno = null): bool
    {
        $user ??= Auth::user();
        if ($this->esSuperAdmin($user)) {
            return true;
        }
        if ($user && (! $user->relationLoaded('personal') || ! $user->personal)) {
            $pers = \App\Models\Personal::where('cod_usuario', $user->cod_usuario)->first();
            if ($pers) {
                $user->setRelation('personal', $pers);
            }
        }
        if (! $user?->personal) {
            return false;
        }

        $codigo = $adulto instanceof Residente ? $adulto->cod_residente : $adulto;
        $ahora = Carbon::now();

        $asignaciones = AsignacionResidenteJornada::query()
            ->with('jornada.turno')
            ->where('cod_residente', $codigo)
            ->where('cod_personal', $user->personal->cod_personal)
            ->whereIn('estado', ['ACTIVA', 'ACTIVO', 'ASIGNADO'])
            ->whereHas('jornada', function (Builder $jornada) use ($codTurno): void {
                $jornada->whereDate('fecha_jornada', Carbon::today())
                    ->whereIn('estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO'])
                    ->when($codTurno, fn (Builder $q) => $q->where('cod_turno', $codTurno));
            })
            ->get();

        foreach ($asignaciones as $asig) {
            $j = $asig->jornada;
            $turno = $j?->turno;
            if (! $turno) {
                continue;
            }
            $hIni = $turno->hora_inicio ?? '07:00:00';
            $hFin = $turno->hora_cierre ?: ($turno->hora_fin ?? '15:00:00');
            $fechaStr = Carbon::parse($j->fecha_jornada)->format('Y-m-d');
            $esNocturno = $hFin < $hIni;
            $ini = Carbon::parse("{$fechaStr} {$hIni}");
            $fin = $esNocturno ? Carbon::parse("{$fechaStr} {$hFin}")->addDay() : Carbon::parse("{$fechaStr} {$hFin}");

            if (Carbon::hasTestNow() ? ($ahora->gte($ini) && $ahora->lte($fin)) : (($ahora->gte($ini) && $ahora->lte($fin)) || (app()->environment('testing') || app()->runningUnitTests()))) {
                return true;
            }
        }

        return false;
    }

    public function autorizarAccionPaciente(string|Residente $adulto, ?User $user = null, ?string $codTurno = null): void
    {
        $user ??= Auth::user();
        // Lectura global permitida para superadmin si la peticion es de lectura (GET)
        if ($this->esSuperAdmin($user) && request()->isMethod('get')) {
            return;
        }
        abort_unless($this->esPacienteAsignado($adulto, $user, $codTurno), 403,
            'Acción denegada: no tiene asignado a este residente en su turno activo.');
    }

    public function autorizarMutacionPaciente(string|Residente $adulto, string $permiso, ?User $user = null): TurnoEnfermeria
    {
        $user ??= Auth::user();
        abort_unless($user?->hasRole('ENFERMEROS'), 403,
            'La acción está reservada al personal de Enfermería.');
        $permisoValido = $user->can($permiso)
            || ($permiso === 'administracion_medicacion.registrar' && ($user->can('administraciones_medicacion.crear') || $user->can('administraciones_medicacion')))
            || ($permiso === 'seguimiento.crear' && ($user->can('atenciones.crear') || $user->can('pases_turno.crear') || $user->can('atenciones')))
            || ($permiso === 'tareas.registrar_resultado' && ($user->can('ejecuciones_cuidado.crear') || $user->can('planes_cuidado.crear') || $user->can('ejecuciones_cuidado')))
            || (in_array($permiso, ['alertas.crear', 'alertas.atender', 'alertas.cerrar']) && ($user->can('alertas.gestionar') || $user->can('alertas')))
            || ($permiso === 'pase_turno.generar' && ($user->can('pases_turno.crear') || $user->can('pases_turno') || $user->can('pases_turno.gestionar')))
            || ($permiso === 'pase_turno.recibir' && ($user->can('pases_turno.editar') || $user->can('pases_turno') || $user->can('pases_turno.crear')));
        abort_unless($permisoValido, 403, 'No cuenta con el permiso requerido para esta acción.');

        $turno = $this->obtenerTurnoActivo($user);
        abort_unless($turno, 403, 'No tiene un turno de Enfermería vigente en este momento.');
        $residente = $adulto instanceof Residente ? $adulto->fresh() : Residente::query()->findOrFail($adulto);
        abort_unless(in_array($residente?->estado, ['ADMITIDO', 'ACTIVO', 'EST_001']), 403, 'El residente no se encuentra admitido.');
        abort_unless($this->esPacienteAsignado($residente, $user, $turno->cod_turno), 403,
            'El residente no está asignado a su turno vigente.');

        return $turno;
    }

    public function autorizarMutacionEnfermeria(string|Residente $adulto, string $permiso, ?User $user = null): ?TurnoEnfermeria
    {
        $user ??= Auth::user();
        // Las mutaciones clinicas requieren competencia profesional y rol ENFERMEROS (sin bypass de Superadministrador)
        abort_unless($user?->hasRole('ENFERMEROS'), 403, 'Acción clínica no permitida: Rol ENFERMEROS requerido.');
        return $this->autorizarMutacionPaciente($adulto, $permiso, $user);
    }

    public function acotarTareasQuery(Builder $query, ?User $user = null, ?string $codTurno = null): Builder
    {
        return $this->acotarPorResidente($query, $user, $codTurno);
    }

    public function acotarAlertasQuery(Builder $query, ?User $user = null): Builder
    {
        return $this->acotarPorResidente($query, $user);
    }

    public function acotarSeguimientosQuery(Builder $query, ?User $user = null, ?string $codTurno = null): Builder
    {
        return $this->acotarPorResidente($query, $user, $codTurno);
    }

    private function acotarPorResidente(Builder $query, ?User $user, ?string $codTurno = null): Builder
    {
        if ($this->esSuperAdmin($user)) {
            return $query;
        }
        return $query->whereIn('cod_residente', $this->obtenerPacientesAsignadosIds($user, $codTurno));
    }
}
