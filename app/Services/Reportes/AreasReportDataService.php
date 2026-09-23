<?php

namespace App\Services\Reportes;

use App\Models\AreaInstitucional;
use App\Models\AsignacionPersonal;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AreasReportDataService
{
    public function getGeneralReportData(): array
    {
        $areas = $this->areasConUsuarios();
        $totalAreas = $areas->count();
        $areasActivas = $areas->whereIn('estado', ['ACTIVA', 'ACTIVO'])->count();
        $usuarios = $areas->pluck('usuarios')->flatten()->unique('cod_usuario')->values();
        $areasConResponsable = $areas->filter(fn ($area) => $area->responsable !== null)->count();
        $areaMasUsuarios = $areas->sortByDesc('usuarios_count')->first();

        return [
            'totalAreas' => $totalAreas,
            'areasActivas' => $areasActivas,
            'areasInactivas' => $totalAreas - $areasActivas,
            'usuariosVinculados' => $usuarios->count(),
            'areasSinResponsable' => $totalAreas - $areasConResponsable,
            'areasSinUsuarios' => $areas->where('usuarios_count', 0)->count(),
            'usuariosActivosVinculados' => $usuarios->where('estado', 'ACTIVO')->count(),
            'usuariosInactivosVinculados' => $usuarios->where('estado', '!=', 'ACTIVO')->count(),
            'areaMasUsuariosNombre' => $areaMasUsuarios?->nombre ?? 'Ninguna',
            'areaMasUsuariosCount' => $areaMasUsuarios?->usuarios_count ?? 0,
            'porcentajeAreasActivas' => $totalAreas > 0 ? round(($areasActivas / $totalAreas) * 100, 1) : 0,
            'porcentajeAreasResponsable' => $totalAreas > 0 ? round(($areasConResponsable / $totalAreas) * 100, 1) : 0,
            'areas' => $areas,
            'fecha' => date('d/m/Y H:i'),
            'usuario' => Auth::user()?->name ?? 'Sistema',
        ];
    }

    public function getAreaReportData($codArea): array
    {
        $area = $this->prepararArea(AreaInstitucional::query()->with('asignaciones.personal.usuario.roles')->findOrFail($codArea));
        $totalUsuarios = $area->usuarios->count();
        $usuariosActivos = $area->usuarios->where('estado', 'ACTIVO')->count();

        return [
            'area' => $area,
            'totalUsuarios' => $totalUsuarios,
            'usuariosActivos' => $usuariosActivos,
            'usuariosInactivos' => $totalUsuarios - $usuariosActivos,
            'porcentajeActivos' => $totalUsuarios > 0 ? round(($usuariosActivos / $totalUsuarios) * 100, 1) : 0,
            'roles' => $this->distribucionRoles($area->usuarios),
            'fecha' => date('d/m/Y H:i'),
            'usuario' => Auth::user()?->name ?? 'Sistema',
        ];
    }

    public function getUsuariosPorArea(): array
    {
        $areas = $this->areasConUsuarios();
        return ['labels' => $areas->pluck('nombre')->all(), 'data' => $areas->pluck('usuarios_count')->all()];
    }

    public function getAreasPorTipo(): array
    {
        return ['labels' => ['INSTITUCIONAL'], 'data' => [AreaInstitucional::query()->count()]];
    }

    public function getActivosInactivosPorArea(): array
    {
        $areas = $this->areasConUsuarios();
        return [
            'labels' => $areas->pluck('nombre')->all(),
            'activos' => $areas->map(fn ($area) => $area->usuarios->where('estado', 'ACTIVO')->count())->all(),
            'inactivos' => $areas->map(fn ($area) => $area->usuarios->where('estado', '!=', 'ACTIVO')->count())->all(),
        ];
    }

    public function getEvolucionMensualGlobal(): array
    {
        $evolucion = AsignacionPersonal::query()->orderBy('fecha_asignacion')->get()
            ->groupBy(fn ($asignacion) => $asignacion->fecha_asignacion?->format('Y-m') ?? date('Y-m'))->map->count();
        return ['labels' => $evolucion->keys()->all(), 'data' => $evolucion->values()->all()];
    }

    public function getRankingAreasUsuarios(): array
    {
        $areas = $this->areasConUsuarios()->sortByDesc('usuarios_count')->take(5)->values();
        return ['labels' => $areas->pluck('nombre')->all(), 'data' => $areas->pluck('usuarios_count')->all()];
    }

    public function getActivosInactivosArea($codArea): array
    {
        $usuarios = $this->usuariosDelArea($codArea);
        $activos = $usuarios->where('estado', 'ACTIVO')->count();
        return ['activos' => $activos, 'inactivos' => $usuarios->count() - $activos];
    }

    public function getUsuariosPorRolArea($codArea): array
    {
        return $this->distribucionRoles($this->usuariosDelArea($codArea));
    }

    public function getEvolucionArea($codArea): array
    {
        return AsignacionPersonal::query()->where('cod_area', $codArea)->orderBy('fecha_asignacion')->get()
            ->groupBy(fn ($asignacion) => $asignacion->fecha_asignacion?->format('Y-m') ?? date('Y-m'))->map->count()->all();
    }

    private function areasConUsuarios(): EloquentCollection
    {
        return AreaInstitucional::query()->with('asignaciones.personal.usuario.roles')->orderBy('nombre')->get()
            ->map(fn (AreaInstitucional $area) => $this->prepararArea($area));
    }

    private function prepararArea(AreaInstitucional $area): AreaInstitucional
    {
        // En V2 áreas no almacena responsable_id ni usuarios directos. Ambos se
        // derivan de asignaciones_personal para respetar el esquema congelado.
        $usuarios = $area->asignaciones->pluck('personal.usuario')->filter()->unique('cod_usuario')
            ->sortBy(fn (User $usuario) => $usuario->name)->values();
        $responsable = $area->asignaciones
            ->first(fn ($asignacion) => in_array($asignacion->tipo_asignacion, ['RESPONSABLE', 'PRINCIPAL'], true))
            ?->personal?->usuario;
        // Las relaciones calculadas mantienen las vistas existentes sin generar
        // consultas contra columnas inexistentes en la tabla areas.
        $area->setRelation('usuarios', $usuarios);
        $area->setRelation('responsable', $responsable);
        $area->setAttribute('usuarios_count', $usuarios->count());
        $area->setAttribute('responsable_id', $responsable?->cod_usuario);
        return $area;
    }

    private function usuariosDelArea(string $codArea): Collection
    {
        return AsignacionPersonal::query()->with('personal.usuario.roles')->where('cod_area', $codArea)->get()
            ->pluck('personal.usuario')->filter()->unique('cod_usuario')->values();
    }

    private function distribucionRoles(Collection $usuarios): array
    {
        $roles = [];
        foreach ($usuarios as $usuario) {
            $rol = $usuario->getRoleNames()->first() ?? 'Sin Rol';
            $roles[$rol] = ($roles[$rol] ?? 0) + 1;
        }
        return $roles;
    }
}
