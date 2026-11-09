<?php

namespace App\Modulos\Institucion\Aplicacion\Consultas;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class ConsultarCoberturaEquipo
{
    private const ESTADOS_JORNADA = [
        'planificada' => ['PLANIFICADA'],
        'abierta' => ['ABIERTA', 'ACTIVA', 'ACTIVO', 'EN_CURSO'],
        'cerrada' => ['CERRADA', 'CERRADO'],
        'cancelada' => ['CANCELADA', 'CANCELADO'],
    ];

    public function consultar(User $usuario, array $entrada, bool $incluirFechasArea = false): array
    {
        $usuario = $usuario->fresh();
        abort_unless($usuario?->estado === 'ACTIVO' && ! $usuario->hasRole('FAMILIAR')
            && $usuario->can('jornadas.ver') && $usuario->can('asignaciones_personal.ver'), 403);
        $filtros = Validator::make($entrada, [
            'fecha' => ['nullable', 'date_format:Y-m-d'], 'modo' => ['nullable', 'in:fecha,vigente'],
            'area' => ['nullable', 'string', 'exists:areas,cod_area'], 'turno' => ['nullable', 'string', 'exists:turnos,cod_turno'],
            'estado' => ['nullable', Rule::in(array_keys(self::ESTADOS_JORNADA))],
        ], [
            'fecha.date_format' => 'Elige una fecha de jornada válida.', 'area.*' => 'Elige un área disponible.',
            'turno.*' => 'Elige un turno disponible.', 'modo.in' => 'Elige una modalidad de consulta.',
            'estado.in' => 'Elige un estado de jornada disponible.',
        ])->validate();
        $ahora = now();
        $vigente = ($filtros['modo'] ?? null) === 'vigente';
        $fecha = $vigente ? $ahora->toDateString() : ($filtros['fecha'] ?? $ahora->toDateString());
        $anterior = $ahora->copy()->subDay()->toDateString();
        $estados = $vigente ? self::ESTADOS_JORNADA['abierta'] : (self::ESTADOS_JORNADA[$filtros['estado'] ?? ''] ?? []);
        $filas = DB::table('asignaciones_personal as ap')->join('personal as p', 'p.cod_personal', '=', 'ap.cod_personal')
            ->join('jornadas as j', 'j.cod_jornada', '=', 'ap.cod_jornada')
            ->join('turnos as t', 't.cod_turno', '=', 'j.cod_turno')->join('areas as a', 'a.cod_area', '=', 'ap.cod_area')
            ->where(fn ($q) => $q->whereDate('j.fecha_jornada', $fecha)
                ->when($vigente, fn ($q) => $q->orWhereDate('j.fecha_jornada', $anterior)))
            ->whereIn('ap.estado', ['ACTIVO', 'ACTIVA'])
            ->when($estados, fn ($q) => $q->whereIn('j.estado', $estados))
            ->when($filtros['area'] ?? null, fn ($q, $valor) => $q->where('a.cod_area', $valor))
            ->when($filtros['turno'] ?? null, fn ($q, $valor) => $q->where('t.cod_turno', $valor))
            ->orderBy('a.nombre')->orderBy('j.fecha_jornada')->orderBy('t.hora_inicio')->orderBy('p.apellido_paterno')->orderBy('ap.cod_asignacion_personal')
            ->get(['ap.cod_asignacion_personal', 'ap.cod_area', 'p.cod_personal', 't.cod_turno', 'a.nombre as area', 't.nombre as turno', 't.hora_inicio', 't.hora_cierre', 'j.fecha_jornada',
                'p.nombres', 'p.apellido_paterno', 'p.apellido_materno', 'p.profesion', 'p.estado as estado_personal',
                'ap.funcion', 'ap.tipo_asignacion', 'j.estado as estado_jornada'])->map(function ($fila) use ($vigente, $ahora): ?array {
                    $fechaJornada = Carbon::parse($fila->fecha_jornada)->toDateString();
                    $inicio = Carbon::parse($fechaJornada.' '.$fila->hora_inicio);
                    $fin = Carbon::parse($fechaJornada.' '.$fila->hora_cierre);
                    if ($fin->lte($inicio)) {
                        $fin->addDay();
                    }
                    if ($vigente && ($ahora->lt($inicio) || $ahora->gte($fin))) {
                        return null;
                    }

                    return ['cod_asignacion' => $fila->cod_asignacion_personal, 'cod_personal' => $fila->cod_personal, 'cod_turno' => $fila->cod_turno,
                        'cod_area' => $fila->cod_area, 'fecha_jornada' => $fechaJornada, 'fecha_jornada_texto' => $inicio->format('d/m/Y'),
                        'area' => $fila->area, 'turno' => $fila->turno,
                        'persona' => trim($fila->nombres.' '.$fila->apellido_paterno.' '.$fila->apellido_materno),
                        'funcion' => $fila->funcion ?: $fila->profesion,
                        'profesion' => $fila->profesion, 'funcion_asignada' => $fila->funcion,
                        'tipo_asignacion' => str_replace('_', ' ', (string) $fila->tipo_asignacion),
                        'estado_personal' => $fila->estado_personal,
                        'inicio' => $inicio->format('d/m H:i'), 'fin' => $fin->format('d/m H:i'),
                        'situacion' => $fila->estado_personal === 'ACTIVO' ? 'Asignación registrada' : 'Revisar estado del trabajador',
                        'estado_jornada' => str_replace('_', ' ', $fila->estado_jornada)];
                })->filter()->values();

        $resumen = ['personas' => $filas->pluck('cod_personal')->unique()->count(),
            'turnos' => $filas->pluck('cod_turno')->unique()->count(), 'asignaciones' => $filas->count()];
        $fechasArea = ['anterior' => null, 'siguiente' => null];
        if ($incluirFechasArea && ! empty($filtros['area'])) {
            $jornadasArea = DB::table('asignaciones_personal as ap')->join('jornadas as j', 'j.cod_jornada', '=', 'ap.cod_jornada')
                ->where('ap.cod_area', $filtros['area'])->whereIn('ap.estado', ['ACTIVA', 'ACTIVO'])
                ->when($filtros['turno'] ?? null, fn ($q, $turno) => $q->where('j.cod_turno', $turno));
            $anterior = (clone $jornadasArea)->whereDate('j.fecha_jornada', '<', $fecha)->max('j.fecha_jornada');
            $siguiente = (clone $jornadasArea)->whereDate('j.fecha_jornada', '>', $fecha)->min('j.fecha_jornada');
            $fechasArea = ['anterior' => $anterior ? Carbon::parse($anterior)->toDateString() : null,
                'siguiente' => $siguiente ? Carbon::parse($siguiente)->toDateString() : null];
        }

        $filas = $filas->all();
        $estadosJornada = array_keys(self::ESTADOS_JORNADA);

        return compact('filas', 'fecha', 'filtros', 'resumen', 'fechasArea', 'vigente', 'ahora', 'estadosJornada') + ['areas' => DB::table('areas')->orderBy('nombre')->get(['cod_area', 'nombre']),
            'turnos' => DB::table('turnos')->orderBy('hora_inicio')->get(['cod_turno', 'nombre'])];
    }
}
