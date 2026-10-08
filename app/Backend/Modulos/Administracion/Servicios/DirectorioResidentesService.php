<?php

namespace App\Backend\Modulos\Administracion\Servicios;

use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Models\Cama;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DirectorioResidentesService
{
    public function consulta(): Builder
    {
        $responsables = DB::table('residentes_contactos')->select('cod_residente')
            ->selectRaw('MIN(cod_residente_contacto) as cod_vinculo')
            ->where('estado', 'ACTIVO')->where('responsable_principal', true)->groupBy('cod_residente');

        return DB::table('residentes as r')
            ->leftJoin('ocupaciones_cama as oc', fn ($join) => $join->on('oc.cod_residente', '=', 'r.cod_residente')
                ->whereIn('oc.estado', FormalizarAdmision::ESTADOS_OCUPACION_ACTIVA))
            ->leftJoin('camas as c', 'c.cod_cama', '=', 'oc.cod_cama')
            ->leftJoin('habitaciones as h', 'h.cod_habitacion', '=', 'c.cod_habitacion')
            ->leftJoinSub($responsables, 'principal', 'principal.cod_residente', '=', 'r.cod_residente')
            ->leftJoin('residentes_contactos as rc', 'rc.cod_residente_contacto', '=', 'principal.cod_vinculo')
            ->leftJoin('contactos as co', 'co.cod_contacto', '=', 'rc.cod_contacto')
            ->select('r.cod_residente as codigo', 'r.numero_documento as documento', 'r.fecha_nacimiento as fecha',
                'r.foto', 'r.estado', 'h.codigo as habitacion', 'h.nombre as sector', 'h.piso',
                'h.cod_habitacion', 'c.codigo as cama', 'c.cod_cama', 'oc.cod_ocupacion', 'rc.parentesco')
            ->selectRaw("TRIM(r.nombres || ' ' || r.apellido_paterno || ' ' || COALESCE(r.apellido_materno, '')) as titulo")
            ->selectRaw("TRIM(COALESCE(co.nombres, '') || ' ' || COALESCE(co.apellido_paterno, '') || ' ' || COALESCE(co.apellido_materno, '')) as responsable")
            ->selectRaw("CASE WHEN EXISTS (SELECT 1 FROM admisiones ad WHERE ad.cod_residente = r.cod_residente) THEN 'Registrada' ELSE 'Sin admisión' END as admision");
    }

    public function aplicarFiltros(Builder $query, array $filtros): Builder
    {
        $termino = trim((string) ($filtros['search'] ?? ''));
        if ($termino !== '') {
            $query->where(function ($where) use ($termino) {
                foreach (['r.cod_residente', 'r.nombres', 'r.apellido_paterno', 'r.apellido_materno', 'r.numero_documento', 'h.codigo', 'h.nombre', 'c.codigo'] as $campo) {
                    $where->orWhereRaw('LOWER('.$campo.') LIKE ?', ['%'.mb_strtolower($termino).'%']);
                }
            });
        }
        if (filled($filtros['estado'] ?? null)) {
            $query->where('r.estado', $filtros['estado']);
        }
        $this->filtrarEspacio($query, $filtros);
        if (($filtros['alojamiento'] ?? '') === 'con_cama') {
            $query->whereNotNull('oc.cod_ocupacion');
        } elseif (($filtros['alojamiento'] ?? '') === 'sin_cama') {
            $query->whereNull('oc.cod_ocupacion');
        }

        return $query;
    }

    public function datos(array $filtros, string $vista = 'tarjetas', int $porPagina = 10): array
    {
        $porPagina = in_array($porPagina, [10, 20, 50], true) ? $porPagina : 10;
        $query = $this->aplicarFiltros($this->consulta(), $filtros);
        $metricas = (clone $query)->select([])->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN oc.cod_ocupacion IS NOT NULL THEN 1 ELSE 0 END) as con_cama')
            ->selectRaw('SUM(CASE WHEN oc.cod_ocupacion IS NULL THEN 1 ELSE 0 END) as sin_cama')
            ->selectRaw('SUM(CASE WHEN principal.cod_vinculo IS NULL THEN 1 ELSE 0 END) as sin_responsable')
            ->selectRaw('SUM(CASE WHEN NOT EXISTS (SELECT 1 FROM admisiones ad WHERE ad.cod_residente = r.cod_residente) THEN 1 ELSE 0 END) as sin_admision')
            ->first();
        $resumen = [];
        foreach (['total', 'con_cama', 'sin_cama', 'sin_responsable', 'sin_admision'] as $clave) {
            $resumen[$clave] = (int) ($metricas?->{$clave} ?? 0);
        }
        $resumen['total_institucional'] = DB::table('residentes')->count();
        $camas = $this->filtrarEspacio($this->consultaCamas(), $filtros);
        $resumen['camas_ocupadas'] = (clone $camas)->whereNotNull('oc.cod_ocupacion')->count();
        $disponibles = FormalizarAdmision::filtrarCamasDisponibles(Cama::query());
        if (filled($filtros['cod_habitacion'] ?? null)) {
            $disponibles->where('cod_habitacion', $filtros['cod_habitacion']);
        }
        if (($filtros['piso'] ?? '') === '__sin_piso__') {
            $disponibles->whereHas('habitacion', fn ($q) => $q->whereNull('piso')->orWhere('piso', ''));
        } elseif (filled($filtros['piso'] ?? null)) {
            $disponibles->whereHas('habitacion', fn ($q) => $q->where('piso', $filtros['piso']));
        }
        $resumen['camas_disponibles'] = $disponibles->count();
        $resumen['camas_no_habilitadas'] = $camas->count() - $resumen['camas_ocupadas'] - $resumen['camas_disponibles'];
        $distribucionPisos = (clone $query)->select('h.piso')->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN oc.cod_ocupacion IS NOT NULL THEN 1 ELSE 0 END) as con_cama')
            ->selectRaw('SUM(CASE WHEN oc.cod_ocupacion IS NULL THEN 1 ELSE 0 END) as sin_cama')
            ->groupBy('h.piso')->orderBy('h.piso')->get();
        $habitacionesPaginadas = null;
        $pisosHabitaciones = collect();
        $registros = null;
        if ($vista === 'camas') {
            $habitacionesPaginadas = $this->habitacionesPaginadas($query, $filtros, $porPagina);
            $pisosHabitaciones = $habitacionesPaginadas->getCollection()->groupBy(fn ($habitacion) => $habitacion->piso ?? '');
        } else {
            $registros = $query->orderBy('r.apellido_paterno')->orderBy('r.apellido_materno')
                ->orderBy('r.nombres')->orderBy('r.cod_residente')->paginate($porPagina)->withQueryString();
        }

        return [
            'registros' => $registros,
            'resumenResidentes' => $resumen,
            'estadosResidentes' => DB::table('residentes')->whereNotNull('estado')->distinct()->orderBy('estado')->pluck('estado'),
            'pisosDisponibles' => DB::table('habitaciones')->whereNotNull('piso')->where('piso', '<>', '')->distinct()->orderBy('piso')->pluck('piso'),
            'habitacionesDisponibles' => DB::table('habitaciones')->orderBy('piso')->orderBy('codigo')
                ->get(['cod_habitacion', 'codigo', 'nombre', 'piso', 'estado']),
            'distribucionPisos' => $distribucionPisos,
            'distribucionAlojamiento' => collect([
                ['clave' => 'con_cama', 'cantidad' => $resumen['con_cama']],
                ['clave' => 'sin_cama', 'cantidad' => $resumen['sin_cama']],
            ]),
            'habitacionesPaginadas' => $habitacionesPaginadas,
            'pisosHabitaciones' => $pisosHabitaciones,
        ];
    }

    private function filtrarEspacio(Builder $query, array $filtros): Builder
    {
        if (($filtros['piso'] ?? '') === '__sin_piso__') {
            $query->where(fn ($q) => $q->whereNull('h.piso')->orWhere('h.piso', ''));
        } elseif (filled($filtros['piso'] ?? null)) {
            $query->where('h.piso', $filtros['piso']);
        }
        if (filled($filtros['cod_habitacion'] ?? null)) {
            $query->where('h.cod_habitacion', $filtros['cod_habitacion']);
        }

        return $query;
    }

    private function consultaCamas(): Builder
    {
        return DB::table('camas as c')->join('habitaciones as h', 'h.cod_habitacion', '=', 'c.cod_habitacion')
            ->leftJoin('ocupaciones_cama as oc', fn ($join) => $join->on('oc.cod_cama', '=', 'c.cod_cama')
                ->whereIn('oc.estado', FormalizarAdmision::ESTADOS_OCUPACION_ACTIVA))
            ->leftJoin('residentes as r', 'r.cod_residente', '=', 'oc.cod_residente');
    }

    private function habitacionesPaginadas(Builder $residentes, array $filtros, int $porPagina): LengthAwarePaginator
    {
        $habitaciones = $this->filtrarEspacio(DB::table('habitaciones as h'), $filtros);
        $filtrarResidentes = filled($filtros['search'] ?? null) || filled($filtros['estado'] ?? null) || filled($filtros['alojamiento'] ?? null);
        if ($filtrarResidentes) {
            $habitaciones->whereIn('h.cod_habitacion', (clone $residentes)->select('h.cod_habitacion')->whereNotNull('h.cod_habitacion'));
        }
        $pagina = $habitaciones->orderBy('h.piso')->orderBy('h.codigo')->orderBy('h.cod_habitacion')
            ->paginate($porPagina, ['h.cod_habitacion', 'h.codigo', 'h.nombre', 'h.piso', 'h.tipo', 'h.capacidad', 'h.estado'])->withQueryString();
        $camas = $this->consultaCamas()->whereIn('c.cod_habitacion', $pagina->getCollection()->pluck('cod_habitacion'));
        if ($filtrarResidentes) {
            $camas->whereIn('r.cod_residente', (clone $residentes)->select('r.cod_residente'));
        }
        $filas = $camas->select('c.cod_cama', 'c.cod_habitacion', 'c.codigo', 'c.tipo', 'c.estado as estado_real',
            'h.estado as estado_habitacion', 'oc.cod_ocupacion', 'r.cod_residente', 'r.nombres', 'r.apellido_paterno',
            'r.apellido_materno', 'r.numero_documento', 'r.foto', 'r.estado as estado_residente')
            ->orderBy('c.codigo')->orderBy('c.cod_cama')->get();
        $codigosDisponibles = FormalizarAdmision::filtrarCamasDisponibles(Cama::query())
            ->whereIn('cod_habitacion', $pagina->getCollection()->pluck('cod_habitacion'))->pluck('cod_cama')->flip();
        $filas->each(function ($cama) use ($codigosDisponibles) {
            $cama->estado_mapa = $cama->cod_ocupacion ? 'ocupado' : ($codigosDisponibles->has($cama->cod_cama) ? 'disponible' : 'no_habilitado');
            $cama->ocupante = $cama->cod_residente ? (object) [
                'codigo' => $cama->cod_residente,
                'titulo' => trim($cama->nombres.' '.$cama->apellido_paterno.' '.$cama->apellido_materno),
                'documento' => $cama->numero_documento,
                'foto' => $cama->foto,
                'estado' => $cama->estado_residente,
            ] : null;
            unset($cama->nombres, $cama->apellido_paterno, $cama->apellido_materno, $cama->numero_documento, $cama->foto, $cama->estado_residente);
        });
        $porHabitacion = $filas->groupBy('cod_habitacion');
        $pagina->getCollection()->each(fn ($habitacion) => $habitacion->camas = $porHabitacion->get($habitacion->cod_habitacion, collect()));

        return $pagina;
    }
}
