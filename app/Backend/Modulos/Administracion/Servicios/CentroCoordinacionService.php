<?php

namespace App\Backend\Modulos\Administracion\Servicios;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class CentroCoordinacionService
{
    public function resumen(string $periodo = '7d'): array
    {
        $periodos = ['7d' => 7, '4w' => 28, '3m' => 90, 'year' => 365];
        $dias = $periodos[$periodo] ?? 7;
        $hoy = CarbonImmutable::today(config('app.timezone'));
        $inicio = $hoy->subDays($dias - 1);

        $camas = DB::table('camas as c')->join('habitaciones as h', 'h.cod_habitacion', '=', 'c.cod_habitacion')
            ->whereIn('c.estado', ['ACTIVA', 'ACTIVO', 'DISPONIBLE'])
            ->whereIn('h.estado', ['ACTIVA', 'ACTIVO', 'DISPONIBLE'])->pluck('c.cod_cama');
        $ocupaciones = $camas->isEmpty() ? collect() : DB::table('ocupaciones_cama')
            ->whereIn('cod_cama', $camas->all())
            ->where('fecha_hora_asignacion', '<', $hoy->addDay())
            ->where(function ($query) use ($inicio) {
                $query->whereNull('fecha_hora_liberacion')->orWhere('fecha_hora_liberacion', '>=', $inicio);
            })->where('estado', '!=', 'ANULADA')->get(['cod_cama', 'fecha_hora_asignacion', 'fecha_hora_liberacion', 'estado']);
        $ocupadas = $ocupaciones->filter(fn ($fila) => $fila->estado === 'ACTIVA'
            && $fila->fecha_hora_liberacion === null
            && CarbonImmutable::parse($fila->fecha_hora_asignacion)->lessThanOrEqualTo(now()))
            ->pluck('cod_cama')->unique()->count();
        $admisionesPorDia = DB::table('admisiones')
            ->where('fecha_hora_admision', '>=', $inicio)
            ->where('fecha_hora_admision', '<', $hoy->addDay())
            ->get(['fecha_hora_admision'])
            ->countBy(fn ($fila) => CarbonImmutable::parse($fila->fecha_hora_admision)->toDateString());

        $historia = [];
        for ($n = 0; $n < $dias; $n++) {
            $dia = $inicio->addDays($n);
            $finDia = $dia->addDay();
            $historia[] = [
                'fecha' => $dia->toDateString(),
                'etiqueta' => $dia->format('d/m'),
                'ocupadas' => $ocupaciones->filter(fn ($fila) =>
                    CarbonImmutable::parse($fila->fecha_hora_asignacion)->lessThan($finDia)
                    && ($fila->fecha_hora_liberacion === null || CarbonImmutable::parse($fila->fecha_hora_liberacion)->greaterThanOrEqualTo($finDia))
                )->pluck('cod_cama')->unique()->count(),
                'admisiones' => (int) ($admisionesPorDia[$dia->toDateString()] ?? 0),
            ];
        }
        if ($dias > 7) {
            $tamanoGrupo = $dias === 28 ? 7 : ($dias === 90 ? 7 : 30);
            $historia = collect($historia)->chunk($tamanoGrupo)->map(fn ($grupo) => [
                'fecha' => $grupo->last()['fecha'],
                'etiqueta' => $grupo->first()['etiqueta'].'–'.$grupo->last()['etiqueta'],
                'ocupadas' => $grupo->last()['ocupadas'],
                'admisiones' => $grupo->sum('admisiones'),
            ])->values()->all();
        }

        $alertas = DB::table('alertas')->whereNotIn('estado', ['CERRADA', 'ANULADA']);
        $alertasPorPrioridad = (clone $alertas)->select('prioridad')->get()->countBy(fn ($fila) => strtoupper((string) $fila->prioridad));
        $jornada = DB::table('jornadas')->whereDate('fecha_jornada', $hoy)->whereIn('estado', ['ABIERTA', 'ACTIVA'])
            ->orderBy('cod_jornada')->first();
        $cobertura = $jornada ? DB::table('asignaciones_personal as ap')
            ->join('areas as a', 'a.cod_area', '=', 'ap.cod_area')
            ->where('ap.cod_jornada', $jornada->cod_jornada)
            ->where('ap.estado', 'ACTIVA')
            ->select('a.nombre as area', DB::raw('COUNT(DISTINCT ap.cod_personal) as asignados'))
            ->groupBy('a.nombre')->orderBy('a.nombre')->get() : collect();
        $personalAsignado = $jornada ? DB::table('asignaciones_personal')
            ->where('cod_jornada', $jornada->cod_jornada)->where('estado', 'ACTIVA')
            ->distinct('cod_personal')->count('cod_personal') : null;

        $visitas = DB::table('visitas')->where(function ($query) use ($hoy) {
            $query->whereDate('fecha_hora_programada', $hoy)
                ->orWhereDate('fecha_hora_ingreso', $hoy);
        })->distinct('cod_visita')->count('cod_visita');

        $actividades = DB::table('actividades as ac')
            ->leftJoin('personal as p', 'p.cod_personal', '=', 'ac.cod_personal')
            ->whereDate('ac.fecha_hora', $hoy)
            ->orderBy('ac.fecha_hora')->limit(5)
            ->get(['ac.cod_actividad', 'ac.nombre', 'ac.lugar', 'ac.fecha_hora', 'ac.estado', 'p.nombres as responsable']);

        $preadmisiones = DB::table('preadmisiones')
            ->orderByDesc('fecha_solicitud')->limit(5)
            ->get(['cod_preadmision', 'nombres', 'apellido_paterno', 'fecha_nacimiento', 'fecha_solicitud', 'prioridad', 'estado']);
        $preparacion = DB::table('preadmisiones as pre')
            ->where('pre.estado', 'APROBADA')
            ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('admisiones as ad')
                ->whereColumn('ad.cod_preadmision', 'pre.cod_preadmision'))
            ->orderBy('pre.fecha_revision')->limit(5)
            ->get(['pre.cod_preadmision', 'pre.nombres', 'pre.apellido_paterno', 'pre.fecha_revision']);
        $movimientos = DB::table('activity_log')->whereIn('log_name', [
            'preadmisiones', 'admisiones', 'visitas', 'documentos', 'alertas', 'jornadas', 'ocupaciones_cama',
            'Preadmisiones', 'Admisiones', 'Visitas', 'Documentos', 'Alertas', 'Jornadas', 'Ocupaciones',
        ])->orderByDesc('created_at')->limit(5)->get(['log_name', 'description', 'created_at']);

        $visitasPorDia = DB::table('visitas')->where('fecha_hora_ingreso', '>=', $hoy->subDays(6))
            ->where('fecha_hora_ingreso', '<', $hoy->addDay())->get(['fecha_hora_ingreso'])
            ->countBy(fn ($fila) => CarbonImmutable::parse($fila->fecha_hora_ingreso)->toDateString());
        $actividadesPorDia = DB::table('actividades')->where('fecha_hora', '>=', $hoy->subDays(6))
            ->where('fecha_hora', '<', $hoy->addDay())->get(['fecha_hora'])
            ->countBy(fn ($fila) => CarbonImmutable::parse($fila->fecha_hora)->toDateString());
        $dinamica = [];
        for ($n = 6; $n >= 0; $n--) {
            $dia = $hoy->subDays($n);
            $dinamica[] = [
                'etiqueta' => $dia->format('d/m'),
                'visitas' => (int) ($visitasPorDia[$dia->toDateString()] ?? 0),
                'actividades' => (int) ($actividadesPorDia[$dia->toDateString()] ?? 0),
            ];
        }

        return [
            'periodo' => $periodo,
            'residentes' => DB::table('residentes')->whereIn('estado', ['ACTIVO', 'ADMITIDO'])->count(),
            'ocupacion' => ['ocupadas' => $ocupadas, 'total' => $camas->count()],
            'preadmisiones_pendientes' => DB::table('preadmisiones')->where('estado', 'PENDIENTE')->count(),
            'admisiones_pendientes' => DB::table('preadmisiones as pre')->where('pre.estado', 'APROBADA')
                ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('admisiones as ad')
                    ->whereColumn('ad.cod_preadmision', 'pre.cod_preadmision'))->count(),
            'alertas_activas' => (clone $alertas)->count(),
            'alertas_prioridad' => $alertasPorPrioridad,
            'visitas_hoy' => $visitas,
            'jornada' => $jornada,
            'cobertura' => $cobertura,
            'personal_asignado' => $personalAsignado,
            'historia' => $historia,
            'dinamica' => $dinamica,
            'preadmisiones_recientes' => $preadmisiones,
            'admisiones_preparacion' => $preparacion,
            'agenda' => $actividades,
            'movimientos' => $movimientos,
        ];
    }
}
