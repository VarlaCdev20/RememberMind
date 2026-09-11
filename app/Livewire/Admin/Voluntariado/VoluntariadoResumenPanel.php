<?php

namespace App\Livewire\Admin\Voluntariado;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class VoluntariadoResumenPanel extends Component
{
    public string $seccionActiva = 'resumen';

    public function mount(): void
    {
        $this->seccionActiva = match (true) {
            request()->routeIs('admin.voluntariado.voluntarios.*') => 'voluntarios',
            request()->routeIs('admin.voluntariado.disponibilidad.*') => 'disponibilidad',
            request()->routeIs('admin.voluntariado.asignaciones.*') => 'asignaciones',
            request()->routeIs('admin.voluntariado.asistencia.*') => 'asistencia',
            request()->routeIs('admin.voluntariado.reportes.*') => 'reportes',
            default => 'resumen',
        };
    }

    public function render()
    {
        $stats = $this->obtenerMetricas();

        return view('livewire.voluntariado.voluntariado-resumen-panel', [
            'stats' => $stats,
            'metricas' => $this->metricasResumen($stats),
            'flujoOperativo' => $this->flujoOperativo(),
            'submodulos' => $this->submodulos($stats),
            'proximasAsignaciones' => $this->proximasAsignaciones(),
            'alertasOperativas' => $this->alertasOperativas($stats),
            'sinDatos' => collect($stats)->except('voluntarios_total')->sum() === 0,
        ]);
    }

    private function obtenerMetricas(): array
    {
        $stats = [
            'voluntarios_total' => 0,
            'voluntarios_activos' => 0,
            'inactivos' => 0,
            'disponibles_semana' => 0,
            'sin_disponibilidad' => 0,
            'asignaciones_activas' => 0,
            'asignaciones_hoy' => 0,
            'asistencias_registradas' => 0,
            'ausencias_pendientes' => 0,
        ];

        if (! $this->tablaExiste('voluntarios')) {
            return $stats;
        }

        $stats['voluntarios_total'] = DB::table('voluntarios')->count();
        $stats['voluntarios_activos'] = DB::table('voluntarios')
            ->where(fn (Builder $query) => $this->whereEstadoActivo($query))
            ->count();
        $stats['inactivos'] = max($stats['voluntarios_total'] - $stats['voluntarios_activos'], 0);

        if ($this->tablaExiste('disponibilidad_voluntarios')) {
            $stats['disponibles_semana'] = DB::table('voluntarios')
                ->where(fn (Builder $query) => $this->whereEstadoActivo($query))
                ->whereExists(function (Builder $query) {
                    $query->select('disponibilidad_voluntarios.cod_vol')
                        ->from('disponibilidad_voluntarios')
                        ->whereColumn('disponibilidad_voluntarios.cod_vol', 'voluntarios.cod_vol');
                })
                ->count();

            $stats['sin_disponibilidad'] = DB::table('voluntarios')
                ->where(fn (Builder $query) => $this->whereEstadoActivo($query))
                ->whereNotExists(function (Builder $query) {
                    $query->select('disponibilidad_voluntarios.cod_vol')
                        ->from('disponibilidad_voluntarios')
                        ->whereColumn('disponibilidad_voluntarios.cod_vol', 'voluntarios.cod_vol');
                })
                ->count();
        } else {
            $stats['sin_disponibilidad'] = $stats['voluntarios_activos'];
        }

        $hoy = today()->toDateString();

        if ($this->tablaExiste('asignacion_voluntarios')) {
            $stats['asignaciones_activas'] = DB::table('asignacion_voluntarios')
                ->where(function (Builder $query) use ($hoy) {
                    $query->where(fn (Builder $estado) => $this->whereEstadoActivo($estado))
                        ->orWhere(fn (Builder $vigente) => $this->whereAsignacionVigenteEn($vigente, $hoy));
                })
                ->count();

            $stats['asignaciones_hoy'] = DB::table('asignacion_voluntarios')
                ->where(fn (Builder $query) => $this->whereAsignacionVigenteEn($query, $hoy))
                ->count();
        }

        if ($this->tablaExiste('asistencia_voluntarios')) {
            $stats['asistencias_registradas'] = DB::table('asistencia_voluntarios')->count();
            $stats['ausencias_pendientes'] = DB::table('asistencia_voluntarios')
                ->where(fn (Builder $query) => $this->whereEstadoAusenteOPendiente($query))
                ->count();
        }

        if ($this->tablaExiste('asignacion_voluntarios') && $this->tablaExiste('asistencia_voluntarios')) {
            $stats['ausencias_pendientes'] += $this->asignacionesHoySinAsistencia($hoy);
        }

        return $stats;
    }

    private function metricasResumen(array $stats): array
    {
        return [
            ['label' => 'Voluntarios activos', 'valor' => $stats['voluntarios_activos'], 'icono' => 'ph-user-check', 'tono' => 'azul'],
            ['label' => 'Inactivos', 'valor' => $stats['inactivos'], 'icono' => 'ph-user-minus', 'tono' => 'neutro'],
            ['label' => 'Disponibles esta semana', 'valor' => $stats['disponibles_semana'], 'icono' => 'ph-calendar-check', 'tono' => 'verde'],
            ['label' => 'Sin disponibilidad', 'valor' => $stats['sin_disponibilidad'], 'icono' => 'ph-calendar-x', 'tono' => 'terracota'],
            ['label' => 'Asignaciones activas', 'valor' => $stats['asignaciones_activas'], 'icono' => 'ph-handshake', 'tono' => 'azul'],
            ['label' => 'Asignaciones de hoy', 'valor' => $stats['asignaciones_hoy'], 'icono' => 'ph-clock-countdown', 'tono' => 'dorado'],
            ['label' => 'Asistencias registradas', 'valor' => $stats['asistencias_registradas'], 'icono' => 'ph-clipboard-text', 'tono' => 'verde'],
            ['label' => 'Ausencias/pendientes', 'valor' => $stats['ausencias_pendientes'], 'icono' => 'ph-warning-circle', 'tono' => 'terracota'],
        ];
    }

    private function flujoOperativo(): array
    {
        return [
            ['label' => 'Registrar voluntario', 'icono' => 'ph-user-plus'],
            ['label' => 'Definir disponibilidad', 'icono' => 'ph-calendar-dots'],
            ['label' => 'Crear asignación', 'icono' => 'ph-handshake'],
            ['label' => 'Registrar asistencia', 'icono' => 'ph-clipboard-text'],
            ['label' => 'Generar reportes', 'icono' => 'ph-chart-bar'],
        ];
    }

    private function submodulos(array $stats): array
    {
        return collect([
            [
                'key' => 'voluntarios',
                'label' => 'Voluntarios',
                'route' => 'admin.voluntariado.voluntarios.index',
                'icono' => 'ph-users-three',
                'descripcion' => 'Equipo voluntario y estado institucional.',
                'dato' => $stats['voluntarios_activos'] . ' activos',
            ],
            [
                'key' => 'disponibilidad',
                'label' => 'Disponibilidad',
                'route' => 'admin.voluntariado.disponibilidad.index',
                'icono' => 'ph-calendar-dots',
                'descripcion' => 'Horarios semanales declarados.',
                'dato' => $stats['disponibles_semana'] . ' disponibles',
            ],
            [
                'key' => 'asignaciones',
                'label' => 'Asignaciones',
                'route' => 'admin.voluntariado.asignaciones.index',
                'icono' => 'ph-handshake',
                'descripcion' => 'Vinculación operativa con adultos mayores.',
                'dato' => $stats['asignaciones_activas'] . ' activas',
            ],
            [
                'key' => 'asistencia',
                'label' => 'Asistencia',
                'route' => 'admin.voluntariado.asistencia.index',
                'icono' => 'ph-clipboard-text',
                'descripcion' => 'Registro institucional de participación.',
                'dato' => $stats['asistencias_registradas'] . ' registros',
            ],
            [
                'key' => 'reportes',
                'label' => 'Reportes',
                'route' => 'admin.voluntariado.reportes.index',
                'icono' => 'ph-chart-bar',
                'descripcion' => 'Indicadores y seguimiento institucional.',
                'dato' => 'Resumen ejecutivo',
            ],
        ])->map(function (array $modulo) {
            $modulo['url'] = route($modulo['route']);
            $modulo['activo'] = $this->seccionActiva === $modulo['key'];

            return $modulo;
        })->all();
    }

    private function proximasAsignaciones(): Collection
    {
        if (! $this->tablaExiste('asignacion_voluntarios')) {
            return collect();
        }

        $hoy = today()->toDateString();

        $query = DB::table('asignacion_voluntarios');

        if ($this->tablaExiste('voluntarios')) {
            $query->leftJoin('voluntarios', 'asignacion_voluntarios.cod_vol', '=', 'voluntarios.cod_vol');
        }

        if ($this->tablaExiste('users')) {
            $query->leftJoin('users', 'voluntarios.cod_usu', '=', 'users.cod_usu');
        }

        if ($this->tablaExiste('adulto_mayor')) {
            $query->leftJoin('adulto_mayor', 'asignacion_voluntarios.cod_am', '=', 'adulto_mayor.cod_am');
        }

        return $query
            ->select([
                'asignacion_voluntarios.cod_asig_vol',
                'asignacion_voluntarios.fecha_asig',
                'asignacion_voluntarios.fecha_fin',
                'asignacion_voluntarios.estado',
                'asignacion_voluntarios.obser',
                'asignacion_voluntarios.cod_vol',
                'asignacion_voluntarios.cod_am',
                Schema::hasColumn('voluntarios', 'area_apoyo') ? 'voluntarios.area_apoyo' : DB::raw('NULL AS area_apoyo'),
                'users.nombres as voluntario_nombres',
                'users.ap_paterno as voluntario_ap_paterno',
                'users.ap_materno as voluntario_ap_materno',
                'adulto_mayor.nombres as adulto_nombres',
                'adulto_mayor.ap_paterno as adulto_ap_paterno',
                'adulto_mayor.ap_materno as adulto_ap_materno',
            ])
            ->where(function (Builder $query) use ($hoy) {
                $query->whereDate('asignacion_voluntarios.fecha_asig', '>=', $hoy)
                    ->orWhere(fn (Builder $vigente) => $this->whereAsignacionVigenteEn($vigente, $hoy, 'asignacion_voluntarios.'));
            })
            ->where(fn (Builder $query) => $this->whereEstadoOperativo($query, 'asignacion_voluntarios.estado'))
            ->orderBy('asignacion_voluntarios.fecha_asig')
            ->limit(5)
            ->get()
            ->map(function (object $asignacion) use ($hoy) {
                $fecha = Carbon::parse($asignacion->fecha_asig);

                return [
                    'codigo' => $asignacion->cod_asig_vol,
                    'fecha' => $fecha->format('d/m/Y'),
                    'relativa' => $fecha->toDateString() === $hoy ? 'Hoy' : ($fecha->isTomorrow() ? 'Mañana' : 'Próxima'),
                    'voluntario' => $this->nombrePersona($asignacion, 'voluntario', 'Voluntario #' . $asignacion->cod_vol),
                    'adulto' => $this->nombrePersona($asignacion, 'adulto', 'Adulto mayor #' . $asignacion->cod_am),
                    'area' => $asignacion->area_apoyo ?: 'Apoyo institucional',
                    'estado' => $asignacion->estado ?: 'Pendiente',
                ];
            });
    }

    private function alertasOperativas(array $stats): Collection
    {
        $alertas = collect();

        if (! $this->tablaExiste('voluntarios')) {
            return collect([
                [
                    'titulo' => 'Módulo listo para datos',
                    'detalle' => 'Aún no se encuentran tablas operativas disponibles para consolidar el resumen.',
                    'icono' => 'ph-database',
                    'tono' => 'neutro',
                ],
            ]);
        }

        if ($stats['voluntarios_total'] === 0) {
            $alertas->push([
                'titulo' => 'Sin voluntarios registrados',
                'detalle' => 'El resumen se completara cuando existan registros institucionales.',
                'icono' => 'ph-user-plus',
                'tono' => 'terracota',
            ]);
        }

        if ($stats['sin_disponibilidad'] > 0) {
            $alertas->push([
                'titulo' => 'Disponibilidad pendiente',
                'detalle' => $stats['sin_disponibilidad'] . ' voluntario(s) no tienen disponibilidad semanal registrada.',
                'icono' => 'ph-calendar-x',
                'tono' => 'terracota',
            ]);
        }

        if ($stats['asignaciones_hoy'] === 0 && $stats['voluntarios_activos'] > 0) {
            $alertas->push([
                'titulo' => 'Sin asignaciones para hoy',
                'detalle' => 'No se detectan asignaciones vigentes en la fecha actual.',
                'icono' => 'ph-clock-countdown',
                'tono' => 'dorado',
            ]);
        }

        if ($stats['ausencias_pendientes'] > 0) {
            $alertas->push([
                'titulo' => 'Asistencia por revisar',
                'detalle' => $stats['ausencias_pendientes'] . ' ausencia(s) o registro(s) pendientes requieren seguimiento.',
                'icono' => 'ph-warning-circle',
                'tono' => 'terracota',
            ]);
        }

        if ($stats['asignaciones_activas'] === 0 && $stats['voluntarios_activos'] > 0) {
            $alertas->push([
                'titulo' => 'Voluntarios sin asignación activa',
                'detalle' => 'El equipo activo no tiene asignaciones institucionales vigentes.',
                'icono' => 'ph-handshake',
                'tono' => 'neutro',
            ]);
        }

        return $alertas->take(4);
    }

    private function asignacionesHoySinAsistencia(string $fecha): int
    {
        return DB::table('asignacion_voluntarios')
            ->leftJoin('asistencia_voluntarios', function ($join) use ($fecha) {
                $join->on('asignacion_voluntarios.cod_vol', '=', 'asistencia_voluntarios.cod_vol')
                    ->where('asistencia_voluntarios.fecha', '=', $fecha);
            })
            ->where(fn (Builder $query) => $this->whereAsignacionVigenteEn($query, $fecha, 'asignacion_voluntarios.'))
            ->whereNull('asistencia_voluntarios.cod_asis_vol')
            ->count();
    }

    private function whereAsignacionVigenteEn(Builder $query, string $fecha, string $prefijo = ''): void
    {
        $query
            ->whereDate($prefijo . 'fecha_asig', '<=', $fecha)
            ->where(function (Builder $fin) use ($fecha, $prefijo) {
                $fin->whereNull($prefijo . 'fecha_fin')
                    ->orWhereDate($prefijo . 'fecha_fin', '>=', $fecha);
            })
            ->where(fn (Builder $estado) => $this->whereEstadoOperativo($estado, $prefijo . 'estado'));
    }

    private function whereEstadoActivo(Builder $query, string $column = 'estado'): void
    {
        $query->whereIn($column, [
            'ACTIVO',
            'ACTIVA',
            'Activo',
            'Activa',
            'activo',
            'activa',
            'VIGENTE',
            'Vigente',
            'vigente',
            '1',
        ]);
    }

    private function whereEstadoOperativo(Builder $query, string $column = 'estado'): void
    {
        $query->where(function (Builder $estado) use ($column) {
            $estado->whereNull($column)
                ->orWhereNotIn($column, [
                    'INACTIVO',
                    'INACTIVA',
                    'Inactivo',
                    'Inactiva',
                    'inactivo',
                    'inactiva',
                    'FINALIZADO',
                    'FINALIZADA',
                    'Finalizado',
                    'Finalizada',
                    'finalizado',
                    'finalizada',
                    'CANCELADO',
                    'CANCELADA',
                    'Cancelado',
                    'Cancelada',
                    'cancelado',
                    'cancelada',
                    'ANULADO',
                    'ANULADA',
                    'Anulado',
                    'Anulada',
                    'anulado',
                    'anulada',
                    '0',
                ]);
        });
    }

    private function whereEstadoAusenteOPendiente(Builder $query, string $column = 'estado'): void
    {
        $query->whereIn($column, [
            'AUSENTE',
            'Ausente',
            'ausente',
            'AUSENCIA',
            'Ausencia',
            'ausencia',
            'PENDIENTE',
            'Pendiente',
            'pendiente',
            'NO ASISTIO',
            'No asistio',
            'no asistio',
            'NO_ASISTIO',
            'No_asistio',
            'no_asistio',
            'FALTA',
            'Falta',
            'falta',
        ]);
    }

    private function nombrePersona(object $registro, string $prefijo, string $fallback): string
    {
        $nombre = trim(collect([
            data_get($registro, $prefijo . '_nombres'),
            data_get($registro, $prefijo . '_ap_paterno'),
            data_get($registro, $prefijo . '_ap_materno'),
        ])->filter()->implode(' '));

        return $nombre !== '' ? $nombre : $fallback;
    }

    private function tablaExiste(string $tabla): bool
    {
        return Schema::hasTable($tabla);
    }
}
