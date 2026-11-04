<?php

namespace App\Modulos\Institucion\Aplicacion\Consultas;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class ConsultarCoberturaEquipo
{
    public function consultar(User $usuario, array $entrada): array
    {
        abort_unless($usuario->fresh()?->estado === 'ACTIVO' && ! $usuario->hasRole('FAMILIAR')
            && $usuario->can('jornadas.ver') && $usuario->can('asignaciones_personal.ver'), 403);
        $filtros = Validator::make($entrada, ['fecha' => ['nullable', 'date_format:Y-m-d'],
            'area' => ['nullable', 'string', 'exists:areas,cod_area'], 'turno' => ['nullable', 'string', 'exists:turnos,cod_turno']])->validate();
        $fecha = $filtros['fecha'] ?? today()->toDateString();
        $filas = DB::table('asignaciones_personal as ap')->join('personal as p', 'p.cod_personal', '=', 'ap.cod_personal')
            ->join('jornadas as j', 'j.cod_jornada', '=', 'ap.cod_jornada')
            ->join('turnos as t', 't.cod_turno', '=', 'j.cod_turno')->join('areas as a', 'a.cod_area', '=', 'ap.cod_area')
            ->whereDate('j.fecha_jornada', $fecha)->whereIn('ap.estado', ['ACTIVO', 'ACTIVA'])
            ->when($filtros['area'] ?? null, fn ($q, $valor) => $q->where('a.cod_area', $valor))
            ->when($filtros['turno'] ?? null, fn ($q, $valor) => $q->where('t.cod_turno', $valor))
            ->orderBy('a.nombre')->orderBy('t.hora_inicio')->orderBy('p.apellido_paterno')->orderBy('ap.cod_asignacion_personal')
            ->get(['a.nombre as area', 't.nombre as turno', 't.hora_inicio', 't.hora_cierre',
                'p.nombres', 'p.apellido_paterno', 'p.apellido_materno', 'p.profesion', 'p.estado as estado_personal',
                'ap.funcion', 'j.estado as estado_jornada'])->map(function ($fila) use ($fecha): array {
                    $inicio = Carbon::parse($fecha.' '.$fila->hora_inicio);
                    $fin = Carbon::parse($fecha.' '.$fila->hora_cierre);
                    if ($fin->lte($inicio)) {
                        $fin->addDay();
                    }

                    return ['area' => $fila->area, 'turno' => $fila->turno,
                        'persona' => trim($fila->nombres.' '.$fila->apellido_paterno.' '.$fila->apellido_materno),
                        'funcion' => $fila->funcion ?: $fila->profesion,
                        'inicio' => $inicio->format('d/m H:i'), 'fin' => $fin->format('d/m H:i'),
                        'situacion' => $fila->estado_personal === 'ACTIVO' ? 'Asignación registrada' : 'Revisar estado del trabajador',
                        'estado_jornada' => str_replace('_', ' ', $fila->estado_jornada)];
                })->all();

        return compact('filas', 'fecha', 'filtros') + ['areas' => DB::table('areas')->orderBy('nombre')->get(['cod_area', 'nombre']),
            'turnos' => DB::table('turnos')->orderBy('hora_inicio')->get(['cod_turno', 'nombre'])];
    }
}
