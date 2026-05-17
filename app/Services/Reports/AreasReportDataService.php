<?php

namespace App\Services\Reports;

use App\Models\AreaInstitucional;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AreasReportDataService
{
    /**
     * Prepara los datos del reporte general.
     */
    public function getGeneralReportData(): array
    {
        $totalAreas = AreaInstitucional::count();
        $areasActivas = AreaInstitucional::activas()->count();
        $areasInactivas = AreaInstitucional::inactivas()->count();
        $usuariosVinculados = User::whereNotNull('cod_area')->count();
        $areasSinResponsable = AreaInstitucional::whereNull('responsable_id')->count();
        $areasSinUsuarios = AreaInstitucional::doesntHave('usuarios')->count();

        // Total usuarios activos vinculados a áreas
        $usuariosActivosVinculados = User::whereNotNull('cod_area')
            ->get()
            ->filter(fn($u) => in_array($u->estado, ['ACTIVO', 1, '1']))
            ->count();

        // Total usuarios inactivos vinculados a áreas
        $usuariosInactivosVinculados = User::whereNotNull('cod_area')
            ->get()
            ->filter(fn($u) => !in_array($u->estado, ['ACTIVO', 1, '1']))
            ->count();

        // Área con más usuarios
        $areaMasUsuarios = AreaInstitucional::withCount('usuarios')
            ->orderByDesc('usuarios_count')
            ->first();
        $areaMasUsuariosNombre = $areaMasUsuarios ? $areaMasUsuarios->nombre : 'Ninguna';
        $areaMasUsuariosCount = $areaMasUsuarios ? $areaMasUsuarios->usuarios_count : 0;

        // Porcentaje de áreas activas
        $porcentajeAreasActivas = $totalAreas > 0 ? round(($areasActivas / $totalAreas) * 100, 1) : 0;

        // Porcentaje de áreas con responsable
        $areasConResponsable = AreaInstitucional::whereNotNull('responsable_id')->count();
        $porcentajeAreasResponsable = $totalAreas > 0 ? round(($areasConResponsable / $totalAreas) * 100, 1) : 0;

        $areas = AreaInstitucional::with(['responsable', 'usuarios'])
            ->withCount('usuarios')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return [
            'totalAreas' => $totalAreas,
            'areasActivas' => $areasActivas,
            'areasInactivas' => $areasInactivas,
            'usuariosVinculados' => $usuariosVinculados,
            'areasSinResponsable' => $areasSinResponsable,
            'areasSinUsuarios' => $areasSinUsuarios,
            'usuariosActivosVinculados' => $usuariosActivosVinculados,
            'usuariosInactivosVinculados' => $usuariosInactivosVinculados,
            'areaMasUsuariosNombre' => $areaMasUsuariosNombre,
            'areaMasUsuariosCount' => $areaMasUsuariosCount,
            'porcentajeAreasActivas' => $porcentajeAreasActivas,
            'porcentajeAreasResponsable' => $porcentajeAreasResponsable,
            'areas' => $areas,
            'fecha' => date('d/m/Y H:i'),
            'usuario' => Auth::check() ? Auth::user()->name : 'Sistema',
        ];
    }

    /**
     * Prepara los datos del reporte por área específica.
     */
    public function getAreaReportData($codArea): array
    {
        $area = AreaInstitucional::with(['responsable', 'usuarios' => function($q) {
            $q->orderBy('nombres');
        }])->findOrFail($codArea);

        $totalUsuarios = $area->usuarios->count();
        $usuariosActivos = $area->usuarios->filter(fn($u) => in_array($u->estado, ['ACTIVO', 1, '1']))->count();
        $usuariosInactivos = $area->usuarios->filter(fn($u) => !in_array($u->estado, ['ACTIVO', 1, '1']))->count();
        $porcentajeActivos = $totalUsuarios > 0 ? round(($usuariosActivos / $totalUsuarios) * 100, 1) : 0;

        $roles = $this->getUsuariosPorRolArea($codArea);

        return [
            'area' => $area,
            'totalUsuarios' => $totalUsuarios,
            'usuariosActivos' => $usuariosActivos,
            'usuariosInactivos' => $usuariosInactivos,
            'porcentajeActivos' => $porcentajeActivos,
            'roles' => $roles,
            'fecha' => date('d/m/Y H:i'),
            'usuario' => Auth::check() ? Auth::user()->name : 'Sistema',
        ];
    }

    /**
     * Gráfico: Usuarios por área.
     */
    public function getUsuariosPorArea(): array
    {
        $areas = AreaInstitucional::withCount('usuarios')->orderBy('nombre')->get();
        return [
            'labels' => $areas->pluck('nombre')->toArray(),
            'data' => $areas->pluck('usuarios_count')->toArray(),
        ];
    }

    /**
     * Gráfico: Áreas por tipo.
     */
    public function getAreasPorTipo(): array
    {
        $tipos = AreaInstitucional::selectRaw('tipo_area, count(*) as total')
            ->groupBy('tipo_area')
            ->orderBy('tipo_area')
            ->get();
        return [
            'labels' => $tipos->pluck('tipo_area')->toArray(),
            'data' => $tipos->pluck('total')->toArray(),
        ];
    }

    /**
     * Gráfico: Activos vs Inactivos por área (apilado).
     */
    public function getActivosInactivosPorArea(): array
    {
        $areas = AreaInstitucional::with(['usuarios'])->orderBy('nombre')->get();
        $labels = [];
        $activos = [];
        $inactivos = [];
        foreach ($areas as $area) {
            $labels[] = $area->nombre;
            $activos[] = $area->usuarios->filter(fn($u) => in_array($u->estado, ['ACTIVO', 1, '1']))->count();
            $inactivos[] = $area->usuarios->filter(fn($u) => !in_array($u->estado, ['ACTIVO', 1, '1']))->count();
        }
        return [
            'labels' => $labels,
            'activos' => $activos,
            'inactivos' => $inactivos,
        ];
    }

    /**
     * Gráfico: Evolución mensual global de usuarios vinculados a áreas.
     */
    public function getEvolucionMensualGlobal(): array
    {
        $evolucion = User::whereNotNull('cod_area')
            ->selectRaw("to_char(created_at, 'YYYY-MM') as mes, count(*) as total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();
        return [
            'labels' => $evolucion->pluck('mes')->toArray(),
            'data' => $evolucion->pluck('total')->toArray(),
        ];
    }

    /**
     * Gráfico: Ranking visual - Top 5 áreas con más usuarios.
     */
    public function getRankingAreasUsuarios(): array
    {
        $areas = AreaInstitucional::withCount('usuarios')
            ->orderByDesc('usuarios_count')
            ->limit(5)
            ->get();
        return [
            'labels' => $areas->pluck('nombre')->toArray(),
            'data' => $areas->pluck('usuarios_count')->toArray(),
        ];
    }

    /**
     * Gráfico específico: Activos vs Inactivos de un área.
     */
    public function getActivosInactivosArea($codArea): array
    {
        $activos = User::where('cod_area', $codArea)->get()->filter(fn($u) => in_array($u->estado, ['ACTIVO', 1, '1']))->count();
        $inactivos = User::where('cod_area', $codArea)->get()->filter(fn($u) => !in_array($u->estado, ['ACTIVO', 1, '1']))->count();
        return [
            'activos' => $activos,
            'inactivos' => $inactivos,
        ];
    }

    /**
     * Gráfico específico: Usuarios por rol en un área.
     */
    public function getUsuariosPorRolArea($codArea): array
    {
        $usuarios = User::where('cod_area', $codArea)->with('roles')->get();
        $rolesDist = [];
        foreach ($usuarios as $u) {
            /** @var \App\Models\User $u */
            $rol = $u->getRoleNames()->first() ?? 'Sin Rol';
            $rolesDist[$rol] = ($rolesDist[$rol] ?? 0) + 1;
        }
        return $rolesDist;
    }

    /**
     * Gráfico específico: Evolución mensual de usuarios en un área.
     */
    public function getEvolucionArea($codArea): array
    {
        $evolucion = User::where('cod_area', $codArea)
            ->selectRaw("to_char(created_at, 'YYYY-MM') as mes, count(*) as total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();
        return $evolucion->pluck('total', 'mes')->toArray();
    }
}
