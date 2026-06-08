<?php

namespace App\Services\Reports;

use App\Models\AreaInstitucional;
use App\Models\User;
use App\Models\AdultoMayor;
use App\Models\EvaluacionCognitiva;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;

class ReportChartDataService
{
    /**
     * Obtiene el número de usuarios por cada área institucional.
     */
    public function usuariosPorArea(): array
    {
        $areas = AreaInstitucional::withCount('usuarios')->orderBy('nombre')->get();
        
        return [
            'labels' => $areas->pluck('nombre')->toArray(),
            'data' => $areas->pluck('usuarios_count')->toArray(),
        ];
    }

    /**
     * Obtiene la distribución de áreas por su tipo.
     */
    public function areasPorTipo(): array
    {
        $tipos = AreaInstitucional::select('tipo_area', DB::raw('count(*) as total'))
            ->groupBy('tipo_area')
            ->orderBy('total', 'desc')
            ->get();

        return [
            'labels' => $tipos->pluck('tipo_area')->toArray(),
            'data' => $tipos->pluck('total')->toArray(),
        ];
    }

    /**
     * Obtiene los usuarios activos e inactivos por cada área institucional (para gráfico apilado).
     */
    public function usuariosActivosInactivosPorArea(): array
    {
        $areas = AreaInstitucional::with(['usuarios'])->orderBy('nombre')->get();
        
        $labels = [];
        $activos = [];
        $inactivos = [];

        foreach ($areas as $area) {
            $labels[] = $area->nombre;
            $activos[] = $area->usuarios->where('estado', 1)->count();
            $inactivos[] = $area->usuarios->where('estado', '!=', 1)->count();
        }

        return [
            'labels' => $labels,
            'activos' => $activos,
            'inactivos' => $inactivos,
        ];
    }

    /**
     * Obtiene la cantidad de usuarios agrupados por su rol Spatie principal.
     */
    public function usuariosPorRol(): array
    {
        $usuarios = User::with('roles')->get();
        $rolesDist = [];

        foreach ($usuarios as $u) {
            $rol = $u->getRoleNames()->first() ?? 'Sin Rol';
            $rolesDist[$rol] = ($rolesDist[$rol] ?? 0) + 1;
        }

        arsort($rolesDist);

        return [
            'labels' => array_keys($rolesDist),
            'data' => array_values($rolesDist),
        ];
    }

    /**
     * Obtiene la evolución de usuarios creados por mes.
     */
    public function evolucionUsuariosPorMes(): array
    {
        $evolucion = User::selectRaw("to_char(created_at, 'YYYY-MM') as mes, count(*) as total")
            ->whereNotNull('created_at')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return [
            'labels' => $evolucion->pluck('mes')->toArray(),
            'data' => $evolucion->pluck('total')->toArray(),
        ];
    }

    /**
     * Obtiene las evaluaciones cognitivas agrupadas por mes.
     */
    public function evaluacionesPorMes(): array
    {
        $evaluaciones = EvaluacionCognitiva::selectRaw("to_char(fecha_eval, 'YYYY-MM') as mes, count(*) as total")
            ->whereNotNull('fecha_eval')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        return [
            'labels' => $evaluaciones->pluck('mes')->toArray(),
            'data' => $evaluaciones->pluck('total')->toArray(),
        ];
    }

    /**
     * Obtiene los adultos mayores agrupados por su estado clínico actual.
     */
    public function adultosPorEstado(): array
    {
        $adultos = AdultoMayor::with('estado')->get();
        $estadosDist = [];

        foreach ($adultos as $am) {
            $est = $am->estado ? $am->estado->estado : 'Sin Estado';
            $estadosDist[$est] = ($estadosDist[$est] ?? 0) + 1;
        }

        return [
            'labels' => array_keys($estadosDist),
            'data' => array_values($estadosDist),
        ];
    }

    /**
     * Obtiene los movimientos registrados en la bitácora agrupados por mes.
     */
    public function movimientosBitacoraPorMes(): array
    {
        $movimientos = Activity::selectRaw("to_char(created_at, 'YYYY-MM') as mes, count(*) as total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->take(12)
            ->get();

        return [
            'labels' => $movimientos->pluck('mes')->toArray(),
            'data' => $movimientos->pluck('total')->toArray(),
        ];
    }
}
