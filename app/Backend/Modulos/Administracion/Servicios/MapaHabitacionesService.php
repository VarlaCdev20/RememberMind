<?php

namespace App\Backend\Modulos\Administracion\Servicios;

use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Models\Cama;
use App\Models\Residente;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MapaHabitacionesService
{
    public function datos(array $filtros, User $usuario): array
    {
        abort_unless($usuario->estado === 'ACTIVO' && $usuario->can('habitaciones.ver') && ! $usuario->hasRole('FAMILIAR'), 403);
        // Acceso físico no implica acceso a la identidad o historia de residentes.
        $verPersonas = $usuario->can('residentes.ver') && Gate::forUser($usuario)->allows('viewAny', Residente::class);
        $base = $this->filtrar($this->consulta($verPersonas), $filtros, false);
        $query = $this->filtrar($this->consulta($verPersonas), $filtros, true);
        $estadisticas = (clone $base)->selectRaw('c.estado_mapa, COUNT(*) as total')->groupBy('c.estado_mapa')->pluck('total', 'estado_mapa');
        $resumen = ['ocupado' => 0, 'disponible' => 0, 'no_habilitado' => 0];
        foreach ($estadisticas as $estado => $cantidad) {
            $resumen[$estado] = (int) $cantidad;
        }
        $resumen['total'] = array_sum($resumen);
        $pisos = (clone $base)->select('c.piso')->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN c.estado_mapa = 'ocupado' THEN 1 ELSE 0 END) as ocupadas")
            ->selectRaw("SUM(CASE WHEN c.estado_mapa = 'disponible' THEN 1 ELSE 0 END) as disponibles")
            ->groupBy('c.piso')->orderBy('c.piso')->get();
        $vista = $filtros['vista'] ?? 'camas';
        $porPagina = (int) ($filtros['por_pagina'] ?? 10);
        $habitaciones = null;
        $camas = null;
        if ($vista === 'camas') {
            $habQuery = DB::table('habitaciones as h')->select('h.*')
                ->selectSub(DB::table('camas')->selectRaw('COUNT(*)')->whereColumn('camas.cod_habitacion', 'h.cod_habitacion'), 'camas_registradas');
            $this->filtrarPiso($habQuery, $filtros, 'h');
            if (filled($filtros['cod_habitacion'] ?? '')) {
                $habQuery->where('h.cod_habitacion', $filtros['cod_habitacion']);
            }
            if (filled($filtros['estado'] ?? '') || filled($filtros['disponibilidad'] ?? '')) {
                $habQuery->whereIn('h.cod_habitacion', (clone $query)->select('c.cod_habitacion'));
            } elseif (filled($filtros['search'] ?? '')) {
                $termino = '%'.mb_strtolower(trim($filtros['search'])).'%';
                $habQuery->where(fn ($where) => $where->whereIn('h.cod_habitacion', (clone $query)->select('c.cod_habitacion'))
                    ->orWhereRaw('LOWER(h.codigo) LIKE ?', [$termino])->orWhereRaw('LOWER(h.nombre) LIKE ?', [$termino]));
            }
            $habitaciones = $habQuery->orderBy('h.piso')->orderBy('h.codigo')->orderBy('h.cod_habitacion')
                ->paginate($porPagina)->withQueryString();
            $filas = (clone $query)->whereIn('c.cod_habitacion', $habitaciones->pluck('cod_habitacion'))
                ->select('c.*')->orderBy('c.codigo')->get()->groupBy('cod_habitacion');
            $habitaciones->getCollection()->each(fn ($habitacion) => $habitacion->camas = $filas->get($habitacion->cod_habitacion, collect()));
        } else {
            $camas = $query->select('c.*')->orderBy('c.piso')->orderBy('c.habitacion')->orderBy('c.codigo')->orderBy('c.cod_cama')
                ->paginate($porPagina)->withQueryString();
        }
        $detalle = null;
        $historial = collect();
        $origenOcupacion = null;
        if (filled($filtros['cama'] ?? '')) {
            $detalle = $this->consulta($verPersonas)->where('c.cod_cama', $filtros['cama'])->first();
            abort_unless($detalle, 404);
            if ($verPersonas) {
                if ($detalle->cod_residente) {
                    Gate::forUser($usuario)->authorize('view', Residente::findOrFail($detalle->cod_residente));
                    $previa = DB::table('ocupaciones_cama')->where('cod_admision', $detalle->cod_admision)
                        ->where('cod_residente', $detalle->cod_residente)->where('cod_cama', '<>', $detalle->cod_cama)
                        ->whereNotNull('fecha_hora_liberacion')->where('fecha_hora_liberacion', '<=', $detalle->fecha_hora_asignacion)
                        ->orderByDesc('fecha_hora_liberacion')->first(['motivo_liberacion']);
                    $origenOcupacion = $previa
                        ? 'Cambio de alojamiento'.($previa->motivo_liberacion ? ': '.$previa->motivo_liberacion : ' · Motivo sin registrar.')
                        : 'Asignación registrada en la admisión formal.';
                }
                $historial = DB::table('ocupaciones_cama as oc')->join('residentes as r', 'r.cod_residente', '=', 'oc.cod_residente')
                    ->where('oc.cod_cama', $detalle->cod_cama)->orderByDesc('oc.fecha_hora_asignacion')->orderByDesc('oc.cod_ocupacion')
                    ->limit(12)->get(['oc.estado', 'oc.fecha_hora_asignacion', 'oc.fecha_hora_liberacion', 'oc.motivo_liberacion', 'oc.cod_admision'])
                    ->each(fn ($evento) => $evento->origen = 'Ocupación registrada en la admisión '.$evento->cod_admision);
            }
        }

        return compact('resumen', 'pisos', 'vista', 'porPagina', 'habitaciones', 'camas', 'detalle', 'historial', 'verPersonas', 'origenOcupacion') + [
            'pisosCatalogo' => DB::table('habitaciones')->whereNotNull('piso')->where('piso', '<>', '')->distinct()->orderBy('piso')->pluck('piso'),
            'habitacionesCatalogo' => DB::table('habitaciones')->orderBy('codigo')->get(['cod_habitacion', 'codigo', 'piso']),
            'estadosCama' => DB::table('camas')->distinct()->orderBy('estado')->pluck('estado'),
        ];
    }

    private function consulta(bool $verPersonas): Builder
    {
        $disponibles = FormalizarAdmision::filtrarCamasDisponibles(Cama::query())->select('cod_cama');
        $fisica = DB::table('camas as b')->join('habitaciones as h', 'h.cod_habitacion', '=', 'b.cod_habitacion')
            ->leftJoin('ocupaciones_cama as oc', fn ($join) => $join->on('oc.cod_cama', '=', 'b.cod_cama')->whereIn('oc.estado', FormalizarAdmision::ESTADOS_OCUPACION_ACTIVA))
            ->select('b.cod_cama', 'b.codigo', 'b.tipo', 'b.estado', 'b.observacion', 'h.cod_habitacion', 'h.codigo as habitacion', 'h.nombre as nombre_habitacion', 'h.piso', 'h.capacidad', 'h.estado as estado_habitacion', 'h.observacion as observacion_habitacion', 'oc.fecha_hora_asignacion', 'oc.cod_admision')
            ->selectRaw("CASE WHEN oc.cod_ocupacion IS NOT NULL THEN 'ocupado' WHEN b.cod_cama IN (".$disponibles->toSql().") THEN 'disponible' ELSE 'no_habilitado' END as estado_mapa", $disponibles->getBindings());
        if ($verPersonas) {
            $fisica->leftJoin('residentes as r', 'r.cod_residente', '=', 'oc.cod_residente')
                ->addSelect('r.cod_residente')->selectRaw("TRIM(r.nombres || ' ' || r.apellido_paterno || ' ' || COALESCE(r.apellido_materno, '')) as ocupante");
        } else {
            $fisica->selectRaw('NULL as cod_residente, NULL as ocupante');
        }

        return DB::query()->fromSub($fisica, 'c');
    }

    private function filtrar(Builder $query, array $filtros, bool $estado): Builder
    {
        $this->filtrarPiso($query, $filtros, 'c');
        if (filled($filtros['cod_habitacion'] ?? '')) {
            $query->where('c.cod_habitacion', $filtros['cod_habitacion']);
        }
        if (filled($filtros['search'] ?? '')) {
            $termino = '%'.mb_strtolower(trim($filtros['search'])).'%';
            $query->where(function ($where) use ($termino) {
                foreach (['c.codigo', 'c.cod_cama', 'c.habitacion', 'c.nombre_habitacion', 'c.tipo', 'c.ocupante'] as $campo) {
                    $where->orWhereRaw('LOWER('.$campo.') LIKE ?', [$termino]);
                }
            });
        }
        if ($estado && filled($filtros['disponibilidad'] ?? '')) {
            $query->where('c.estado_mapa', $filtros['disponibilidad']);
        }
        if ($estado && filled($filtros['estado'] ?? '')) {
            $query->where('c.estado', $filtros['estado']);
        }

        return $query;
    }

    private function filtrarPiso(Builder $query, array $filtros, string $alias): void
    {
        if (($filtros['piso'] ?? '') === '__sin_piso__') {
            $query->where(fn ($where) => $where->whereNull($alias.'.piso')->orWhere($alias.'.piso', ''));
        } elseif (filled($filtros['piso'] ?? '')) {
            $query->where($alias.'.piso', $filtros['piso']);
        }
    }
}
