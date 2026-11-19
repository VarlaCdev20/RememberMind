<?php

namespace App\Modulos\Institucion\Aplicacion\Consultas;

use App\Modulos\Institucion\Infraestructura\Persistencia\HistoriaLaboral;

use App\Modulos\Cuidados\Infraestructura\Persistencia\RegistrosConsumo;
use App\Models\AsignacionPersonal;
use App\Models\EventoAsignacionPersonal;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\AutorizarGestionPersonal;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;

final class ConsultarAvisosPlanificacion
{
    public const CAMPOS = ['cod_usuario_afectado_aviso', 'estado_aviso_planificacion', 'numero_intentos_aviso',
        'fecha_hora_ultimo_intento_aviso', 'fecha_hora_disponible_aviso', 'categoria_fallo_aviso'];

    public function disponible(): bool
    {
        return (bool) config('remembermind.avisos_planificacion_habilitados', false)
            && Schema::hasColumns(HistoriaLaboral::TABLA, self::CAMPOS);
    }

    public function administra(User $actor): bool
    {
        return $actor->hasAnyRole(['ADMINISTRADOR', 'SUPERADMINISTRADOR']) && $actor->can('asignaciones_personal.ver');
    }

    public function gestiona(User $actor): bool
    {
        return $actor->estado === 'ACTIVO' && ! $actor->hasRole('FAMILIAR') && $actor->can('turnos.asignar')
            && $actor->hasAnyRole(['GERENTE', 'ADMINISTRADOR', 'SUPERADMINISTRADOR']) && ! app(RolePreviewService::class)->isActive($actor);
    }

    public function propio(User $actor): bool
    {
        return $actor->estado === 'ACTIVO' && ! $actor->hasRole('FAMILIAR')
            && $actor->personal?->estado === 'ACTIVO' && $actor->hasAnyRole(AutorizarGestionPersonal::ROLES_TRABAJADOR);
    }

    public function horariosPropios(User $actor): Paginator
    {
        $actor = $actor->fresh(['personal', 'roles']);
        abort_unless($actor && $this->propio($actor), 403);

        $ahora = CarbonImmutable::now();

        return AsignacionPersonal::query()
            ->join('jornadas as j', 'j.cod_jornada', '=', 'asignaciones_jornada.cod_jornada')
            ->join('turnos as t', 't.cod_turno', '=', 'j.cod_turno')
            ->join('areas as ar', 'ar.cod_area', '=', 'asignaciones_jornada.cod_area')
            ->where('asignaciones_jornada.cod_personal', $actor->personal->getKey())
            ->whereIn('asignaciones_jornada.estado', ['ACTIVO', 'ACTIVA'])
            ->whereIn('j.estado', ['ABIERTA', 'ACTIVA', 'EN_CURSO', 'ACTIVO'])
            ->where('t.estado', 'ACTIVO')
            ->whereIn('ar.estado', ['ACTIVO', 'ACTIVA'])
            ->where(function ($fechas) use ($ahora) {
                $fechas->whereDate('j.fecha_jornada', '>=', $ahora->toDateString())
                    ->orWhere(fn ($noche) => $noche
                        ->whereDate('j.fecha_jornada', $ahora->subDay()->toDateString())
                        ->whereColumn('t.hora_cierre', '<=', 't.hora_inicio')
                        ->whereTime('t.hora_cierre', '>', $ahora->format('H:i:s')));
            })
            ->orderBy('j.fecha_jornada')->orderBy('t.hora_inicio')->orderBy('asignaciones_jornada.cod_asignacion_personal')
            ->toBase()
            ->simplePaginate(15, ['j.fecha_jornada', 't.nombre as turno', 't.hora_inicio', 't.hora_cierre', 'ar.nombre as area'], 'paginaHorarios')
            ->through(function ($fila): array {
                $fecha = CarbonImmutable::parse($fila->fecha_jornada)->startOfDay();
                $inicio = $fecha->setTimeFromTimeString($fila->hora_inicio);
                $fin = $fecha->setTimeFromTimeString($fila->hora_cierre);
                if ($fin->lte($inicio)) {
                    $fin = $fin->addDay();
                }

                return ['turno' => $fila->turno, 'area' => $fila->area, 'inicio' => $inicio, 'fin' => $fin];
            });
    }

    public function puedeConsultar(?User $actor): bool
    {
        $actor = $actor?->fresh(['personal', 'roles']);
        return $actor && $actor->estado === 'ACTIVO' && ! $actor->hasRole('FAMILIAR')
            && ($this->administra($actor) || $this->gestiona($actor) || $this->propio($actor));
    }

    public function consulta(User $actor): Builder
    {
        $actor = $actor->fresh(['personal', 'roles']);
        abort_unless($actor && $this->puedeConsultar($actor), 403);
        abort_unless($this->disponible(), 409, 'Los avisos de planificación todavía no están habilitados.');
        $gestiona = $this->gestiona($actor);
        $administra = $this->administra($actor);
        $propio = $this->propio($actor);
        // La autorización se aplica antes de cualquier límite o paginación.
        return EventoAsignacionPersonal::query()->withoutGlobalScope('clase_acto')
            ->from(HistoriaLaboral::TABLA.' as e')->where('e.clase_acto', 'ASIGNACION')
            ->joinSub(RegistrosConsumo::consulta('asignaciones_personal'), 'a', 'a.cod_asignacion_personal', '=', 'e.cod_asignacion_personal')
            ->join('personal as p', 'p.cod_personal', '=', 'a.cod_personal')
            ->join('areas as ar', 'ar.cod_area', '=', 'a.cod_area')->join('jornadas as j', 'j.cod_jornada', '=', 'a.cod_jornada')
            ->join('turnos as t', 't.cod_turno', '=', 'j.cod_turno')
            ->whereNotNull('e.estado_aviso_planificacion')->where(function ($q) use ($actor, $gestiona, $administra, $propio) {
                $q->where(function ($disponible) use ($actor, $administra, $propio) {
                    $disponible->where('e.estado_aviso_planificacion', 'DISPONIBLE');
                    if (! $administra) {
                        $disponible->where('e.cod_usuario_afectado_aviso', $actor->getKey());
                        if ($propio) $disponible->where('a.cod_personal', $actor->personal->getKey());
                        else $disponible->whereRaw('1=0');
                    }
                });
                if ($gestiona) $q->orWhere(function ($pendiente) use ($actor) {
                    $pendiente->whereIn('e.estado_aviso_planificacion', ['PENDIENTE', 'FALLIDO']);
                    if (! $actor->hasAnyRole(['GERENTE', 'SUPERADMINISTRADOR'])) $pendiente->where('e.cod_usuario_registro', $actor->getKey());
                });
            })->select(['e.*', 'p.nombres as nombres_trabajador', 'p.apellido_paterno as apellido_trabajador',
                'ar.nombre as nombre_area', 't.nombre as nombre_turno', 't.hora_inicio', 't.hora_cierre', 'j.fecha_jornada'])
            ->orderByDesc('e.fecha_hora_registro')->orderByDesc('e.cod_evento_asignacion');
    }

    public function presentar(EventoAsignacionPersonal $evento): array
    {
        $estado = $evento->estado_aviso_planificacion;
        return ['clave' => $evento->getKey(), 'titulo' => match ($evento->tipo_evento) {
            'ALTA' => 'Asignación registrada', 'FINALIZACION' => 'Asignación finalizada', 'CANCELACION' => 'Asignación cancelada',
            'RECTIFICACION' => 'Asignación rectificada', default => 'Cambio de asignación',
        }, 'persona' => trim($evento->nombres_trabajador.' '.$evento->apellido_trabajador),
            'contexto' => \Carbon\Carbon::parse($evento->fecha_jornada)->format('d/m/Y').' · '.$evento->nombre_turno.' · '.$evento->nombre_area,
            'horario' => substr($evento->hora_inicio, 0, 5).'–'.substr($evento->hora_cierre, 0, 5),
            'efecto' => $evento->fecha_hora_efecto->format('d/m/Y H:i'), 'registro' => $evento->fecha_hora_registro->format('d/m/Y H:i'),
            'situacion_asignacion' => match ($evento->estado_nuevo) {'ACTIVO', 'ACTIVA' => 'Activa', 'INACTIVO', 'INACTIVA' => 'Inactiva', default => 'Situación registrada'},
            'estado' => $estado, 'estado_texto' => match ($estado) {
                'DISPONIBLE' => 'Disponible en la bandeja', 'FALLIDO' => 'Cambio guardado; no se pudo preparar el aviso', default => 'Cambio guardado; aviso pendiente de preparación',
            }, 'intentos' => $evento->numero_intentos_aviso];
    }
}
