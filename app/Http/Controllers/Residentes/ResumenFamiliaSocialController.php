<?php

namespace App\Http\Controllers\Residentes;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ResumenFamiliaSocialController extends Controller
{
    public function __invoke(): View
    {
        return view('pages.familia-social.resumen', $this->dashboardData());
    }

    private function dashboardData(): array
    {
        $totalAdultos = $this->hasTable('adulto_mayor') ? $this->adultosBaseQuery()->count() : 0;

        $adultosConRedIds = $this->adultosConRedIds();
        $adultosConResponsableIds = $this->adultosConResponsableIds();
        $adultosSinContactoIds = $this->adultosSinContactoIds();
        $adultosSinFichaIds = $this->adultosSinFichaIds();

        $adultosConRed = $this->countAdultosByIds($adultosConRedIds);
        $adultosSinRed = max($totalAdultos - $adultosConRed, 0);
        $familiaresRegistrados = $this->countFamiliares();
        $vinculosActivos = $this->countVinculosActivos();

        $visitas = $this->visitasData();
        $fichaSocial = $this->fichaSocialData($totalAdultos, $adultosSinFichaIds);

        $seguimientoIds = collect()
            ->merge($this->adultosSinRedIds($adultosConRedIds))
            ->merge($this->adultosSinResponsableIds($adultosConResponsableIds))
            ->merge($adultosSinContactoIds)
            ->merge($adultosSinFichaIds)
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        $redIncompleta = $this->redIncompletaList($adultosConRedIds, $adultosConResponsableIds);
        $alertas = $this->alertasSociales($redIncompleta, $adultosSinFichaIds);

        $metricas = [
            [
                'label' => 'Adultos con red de apoyo',
                'valor' => $adultosConRed,
                'subtitulo' => 'Con al menos un familiar vinculado',
                'badge' => $this->percent($adultosConRed, $totalAdultos) . '% cobertura',
                'icono' => 'ph-users-three',
                'color' => 'emerald',
            ],
            [
                'label' => 'Adultos sin red de apoyo',
                'valor' => $adultosSinRed,
                'subtitulo' => 'Requieren completar red de apoyo',
                'badge' => $adultosSinRed > 0 ? 'Atencion' : 'Sin pendientes',
                'icono' => 'ph-warning-circle',
                'color' => 'amber',
            ],
            [
                'label' => 'Familiares vinculados',
                'valor' => $vinculosActivos,
                'subtitulo' => $familiaresRegistrados . ' familiares registrados',
                'badge' => 'Vinculos activos',
                'icono' => 'ph-hand-heart',
                'color' => 'salmon',
            ],
            [
                'label' => 'Visitas registradas',
                'valor' => $visitas['total'],
                'subtitulo' => $visitas['table'] ? 'Registros del modulo de visitas' : 'Sin tabla de visitas activa',
                'badge' => $visitas['table'] ? 'Historico' : 'Sin registros',
                'icono' => 'ph-door-open',
                'color' => 'blue',
            ],
            [
                'label' => 'Visitas recientes',
                'valor' => $visitas['recientes_count'],
                'subtitulo' => 'Durante los ultimos 30 dias',
                'badge' => $visitas['recientes_count'] > 0 ? 'Reciente' : 'Sin actividad',
                'icono' => 'ph-calendar-check',
                'color' => 'green',
            ],
            [
                'label' => 'Fichas sociales completas',
                'valor' => $fichaSocial['completas'],
                'subtitulo' => $fichaSocial['table'] ? 'Adultos con ficha registrada' : 'Ficha social aun sin tabla activa',
                'badge' => $this->percent($fichaSocial['completas'], $totalAdultos) . '% avance',
                'icono' => 'ph-clipboard-text',
                'color' => 'violet',
            ],
            [
                'label' => 'Fichas sociales pendientes',
                'valor' => $fichaSocial['pendientes'],
                'subtitulo' => 'Pendientes de actualizacion social',
                'badge' => $fichaSocial['pendientes'] > 0 ? 'Pendiente' : 'Completo',
                'icono' => 'ph-clipboard',
                'color' => 'rose',
            ],
            [
                'label' => 'Casos con seguimiento social',
                'valor' => $seguimientoIds->count(),
                'subtitulo' => 'Por red, contacto o ficha pendiente',
                'badge' => $seguimientoIds->count() > 0 ? 'Revisar' : 'Estable',
                'icono' => 'ph-heartbeat',
                'color' => 'indigo',
            ],
        ];

        $estadoSocial = [
            'red_apoyo' => $this->percent($adultosConRed, $totalAdultos),
            'sin_red' => $this->percent($adultosSinRed, $totalAdultos),
            'ficha_social' => $this->percent($fichaSocial['completas'], $totalAdultos),
            'visitas_recientes' => $this->percent($visitas['adultos_recientes'], $totalAdultos),
        ];
        $estadoSocial['nivel'] = $this->nivelSocial($estadoSocial);

        return [
            'metricas' => $metricas,
            'estadoSocial' => $estadoSocial,
            'chartData' => [
                'red' => [
                    'labels' => ['Con red de apoyo', 'Sin red de apoyo'],
                    'data' => [$adultosConRed, $adultosSinRed],
                ],
                'visitas' => $visitas['mensual'],
                'ficha' => [
                    'labels' => ['Completas', 'Pendientes', 'Sin registro'],
                    'data' => [
                        $fichaSocial['completas'],
                        max($fichaSocial['pendientes'] - $fichaSocial['sin_registro'], 0),
                        $fichaSocial['sin_registro'],
                    ],
                    'available' => $fichaSocial['table'],
                ],
            ],
            'alertas' => $alertas,
            'visitasRecientes' => $visitas['recientes'],
            'redIncompleta' => $redIncompleta,
            'fichaSocial' => $fichaSocial,
            'reportesSociales' => $this->reportesSociales(),
            'resumenDatos' => [
                'total_adultos' => $totalAdultos,
                'familiares_registrados' => $familiaresRegistrados,
                'adultos_sin_contacto' => count($adultosSinContactoIds),
            ],
            'rutasSubmodulos' => [
                'red_apoyo' => Route::has('admin.familia-social.red-apoyo') ? route('admin.familia-social.red-apoyo') : null,
                'visitas' => Route::has('admin.familia-social.visitas') ? route('admin.familia-social.visitas') : null,
                'ficha_social' => Route::has('admin.familia-social.ficha-social') ? route('admin.familia-social.ficha-social') : null,
            ],
        ];
    }

    private function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }

    private function hasColumn(string $table, string $column): bool
    {
        return $this->hasTable($table) && Schema::hasColumn($table, $column);
    }

    private function adultosBaseQuery(): Builder
    {
        $query = DB::table('adulto_mayor');

        if ($this->hasColumn('adulto_mayor', 'archivado_en')) {
            $query->whereNull('archivado_en');
        }

        return $query;
    }

    private function vinculosActivosQuery(): ?Builder
    {
        if (!$this->hasTable('familiar_adulto')) {
            return null;
        }

        $query = DB::table('familiar_adulto');

        if ($this->hasColumn('familiar_adulto', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if ($this->hasColumn('familiar_adulto', 'estado')) {
            $query->whereRaw('UPPER(estado) = ?', ['ACTIVO']);
        }

        return $query;
    }

    private function adultosConRedIds(): array
    {
        $query = $this->vinculosActivosQuery();

        if (!$query || !$this->hasColumn('familiar_adulto', 'cod_am')) {
            return [];
        }

        return $query->whereNotNull('cod_am')->distinct()->pluck('cod_am')->all();
    }

    private function adultosSinRedIds(array $adultosConRedIds): array
    {
        if (!$this->hasTable('adulto_mayor')) {
            return [];
        }

        $query = $this->adultosBaseQuery()->pluck('cod_am');

        return $query
            ->reject(fn ($id) => in_array((string) $id, array_map('strval', $adultosConRedIds), true))
            ->values()
            ->all();
    }

    private function adultosConResponsableIds(): array
    {
        $query = $this->vinculosActivosQuery();

        if (!$query || !$this->hasColumn('familiar_adulto', 'es_responsable')) {
            return [];
        }

        return $query
            ->where('es_responsable', true)
            ->whereNotNull('cod_am')
            ->distinct()
            ->pluck('cod_am')
            ->all();
    }

    private function adultosSinResponsableIds(array $adultosConResponsableIds): array
    {
        if (!$this->hasTable('adulto_mayor')) {
            return [];
        }

        return $this->adultosBaseQuery()
            ->pluck('cod_am')
            ->reject(fn ($id) => in_array((string) $id, array_map('strval', $adultosConResponsableIds), true))
            ->values()
            ->all();
    }

    private function adultosSinContactoIds(): array
    {
        if (!$this->hasTable('adulto_mayor')) {
            return [];
        }

        $columnas = array_values(array_filter([
            $this->hasColumn('adulto_mayor', 'contacto_emergencia_nombre') ? 'contacto_emergencia_nombre' : null,
            $this->hasColumn('adulto_mayor', 'contacto_emergencia_celular') ? 'contacto_emergencia_celular' : null,
        ]));

        if (empty($columnas)) {
            return [];
        }

        $query = $this->adultosBaseQuery();

        foreach ($columnas as $columna) {
            $query->where(function (Builder $subquery) use ($columna) {
                $subquery->whereNull($columna)->orWhere($columna, '');
            });
        }

        return $query->pluck('cod_am')->all();
    }

    private function adultosSinFichaIds(): array
    {
        if (!$this->hasTable('adulto_mayor')) {
            return [];
        }

        $table = $this->fichaSocialTable();

        if (!$table || !$this->hasColumn($table, 'cod_am')) {
            return $this->adultosBaseQuery()->pluck('cod_am')->all();
        }

        $conFicha = DB::table($table)->whereNotNull('cod_am')->distinct()->pluck('cod_am')->all();

        return $this->adultosBaseQuery()
            ->pluck('cod_am')
            ->reject(fn ($id) => in_array((string) $id, array_map('strval', $conFicha), true))
            ->values()
            ->all();
    }

    private function countAdultosByIds(array $ids): int
    {
        if (!$this->hasTable('adulto_mayor') || empty($ids)) {
            return 0;
        }

        return $this->adultosBaseQuery()->whereIn('cod_am', $ids)->count();
    }

    private function countFamiliares(): int
    {
        if (!$this->hasTable('familiares')) {
            return 0;
        }

        $query = DB::table('familiares');

        if ($this->hasColumn('familiares', 'estado')) {
            $query->whereRaw('UPPER(estado) = ?', ['ACTIVO']);
        }

        return $query->count();
    }

    private function countVinculosActivos(): int
    {
        $query = $this->vinculosActivosQuery();

        return $query ? $query->count() : 0;
    }

    private function visitasData(): array
    {
        $table = $this->firstExistingTable(['visitas', 'visitas_adulto', 'visitas_familiares', 'visitas_sociales']);

        if (!$table) {
            return [
                'table' => null,
                'total' => 0,
                'recientes_count' => 0,
                'adultos_recientes' => 0,
                'recientes' => collect(),
                'mensual' => ['labels' => [], 'data' => [], 'available' => false],
            ];
        }

        $fechaCol = $this->firstExistingColumn($table, ['fecha_visita', 'fecha', 'fecha_registro', 'created_at']);
        $total = DB::table($table)->count();
        $recientesCount = 0;
        $adultosRecientes = 0;
        $mensual = ['labels' => [], 'data' => [], 'available' => false];

        if ($fechaCol) {
            $recientesQuery = DB::table($table)->where($fechaCol, '>=', now()->subDays(30));
            $recientesCount = (clone $recientesQuery)->count();

            if ($this->hasColumn($table, 'cod_am')) {
                $adultosRecientes = (clone $recientesQuery)->whereNotNull('cod_am')->distinct()->count('cod_am');
            }

            $mensual = $this->visitasMensuales($table, $fechaCol);
        }

        return [
            'table' => $table,
            'total' => $total,
            'recientes_count' => $recientesCount,
            'adultos_recientes' => $adultosRecientes,
            'recientes' => $this->visitasRecientesList($table, $fechaCol),
            'mensual' => $mensual,
        ];
    }

    private function visitasMensuales(string $table, string $fechaCol): array
    {
        $inicio = now()->startOfMonth()->subMonths(5);
        $filas = DB::table($table)
            ->where($fechaCol, '>=', $inicio)
            ->select($fechaCol)
            ->get()
            ->groupBy(fn ($row) => Carbon::parse($row->{$fechaCol})->format('Y-m'));

        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $fecha = now()->startOfMonth()->subMonths($i);
            $key = $fecha->format('Y-m');
            $labels[] = $this->mesCorto((int) $fecha->format('n'));
            $data[] = $filas->get($key, collect())->count();
        }

        return ['labels' => $labels, 'data' => $data, 'available' => array_sum($data) > 0];
    }

    private function visitasRecientesList(string $table, ?string $fechaCol): Collection
    {
        $query = DB::table($table . ' as v')->select('v.*');

        if ($this->hasColumn($table, 'cod_am') && $this->hasTable('adulto_mayor')) {
            $query->leftJoin('adulto_mayor as am', 'v.cod_am', '=', 'am.cod_am')
                ->addSelect('am.nombres as adulto_nombres', 'am.ap_paterno as adulto_ap_paterno', 'am.ap_materno as adulto_ap_materno');
        }

        if ($fechaCol) {
            $query->orderByDesc('v.' . $fechaCol);
        }

        return $query->limit(8)->get()->map(function ($row) use ($fechaCol) {
            $adulto = $this->nombreDesdeCampos($row, 'adulto_') ?: 'Adulto mayor no especificado';
            $visitante = $this->firstFilled($row, ['visitante', 'nombre_visitante', 'familiar', 'nombre_familiar', 'registrado_por_nombre']) ?: 'Visitante sin identificar';
            $fecha = $fechaCol && isset($row->{$fechaCol}) ? Carbon::parse($row->{$fechaCol})->format('d/m/Y') : 'Sin fecha';

            return [
                'adulto' => $adulto,
                'visitante' => $visitante,
                'fecha' => $fecha,
                'hora' => $this->firstFilled($row, ['hora', 'hora_visita', 'hora_ingreso']) ?: null,
                'motivo' => $this->firstFilled($row, ['motivo', 'observacion', 'observaciones', 'detalle']) ?: 'Sin motivo registrado',
                'estado' => $this->firstFilled($row, ['estado']) ?: 'Registrada',
            ];
        });
    }

    private function fichaSocialData(int $totalAdultos, array $adultosSinFichaIds): array
    {
        $table = $this->fichaSocialTable();

        if (!$table) {
            return [
                'table' => null,
                'completas' => 0,
                'pendientes' => $totalAdultos,
                'sin_registro' => $totalAdultos,
                'ultimas' => collect(),
            ];
        }

        $codColumn = $this->hasColumn($table, 'cod_am') ? 'cod_am' : null;
        $estadoColumn = $this->firstExistingColumn($table, ['estado', 'estado_ficha', 'estado_social']);
        $fechaColumn = $this->firstExistingColumn($table, ['updated_at', 'fecha_actualizacion', 'fecha', 'created_at']);

        $completas = 0;

        if ($codColumn && $estadoColumn) {
            $completas = DB::table($table)
                ->whereNotNull($codColumn)
                ->whereIn(DB::raw('UPPER(' . $estadoColumn . ')'), ['COMPLETA', 'COMPLETADA', 'FINALIZADA', 'VIGENTE', 'ACTIVA'])
                ->distinct()
                ->count($codColumn);
        } elseif ($codColumn) {
            $completas = DB::table($table)->whereNotNull($codColumn)->distinct()->count($codColumn);
        } else {
            $completas = DB::table($table)->count();
        }

        return [
            'table' => $table,
            'completas' => $completas,
            'pendientes' => max($totalAdultos - $completas, 0),
            'sin_registro' => count($adultosSinFichaIds),
            'ultimas' => $this->ultimasFichasSociales($table, $fechaColumn),
        ];
    }

    private function ultimasFichasSociales(string $table, ?string $fechaColumn): Collection
    {
        $query = DB::table($table . ' as fs')->select('fs.*');

        if ($this->hasColumn($table, 'cod_am') && $this->hasTable('adulto_mayor')) {
            $query->leftJoin('adulto_mayor as am', 'fs.cod_am', '=', 'am.cod_am')
                ->addSelect('am.nombres as adulto_nombres', 'am.ap_paterno as adulto_ap_paterno', 'am.ap_materno as adulto_ap_materno');
        }

        if ($fechaColumn) {
            $query->orderByDesc('fs.' . $fechaColumn);
        }

        return $query->limit(5)->get()->map(function ($row) use ($fechaColumn) {
            return [
                'adulto' => $this->nombreDesdeCampos($row, 'adulto_') ?: 'Adulto mayor no especificado',
                'estado' => $this->firstFilled($row, ['estado', 'estado_ficha', 'estado_social']) ?: 'Registrada',
                'fecha' => $fechaColumn && isset($row->{$fechaColumn}) ? Carbon::parse($row->{$fechaColumn})->format('d/m/Y') : 'Sin fecha',
                'observacion' => $this->firstFilled($row, ['observacion', 'observaciones', 'diagnostico_social', 'resumen']) ?: 'Sin observacion registrada',
            ];
        });
    }

    private function fichaSocialTable(): ?string
    {
        return $this->firstExistingTable(['ficha_social', 'fichas_sociales', 'ficha_social_adulto', 'fichas_sociales_adulto']);
    }

    private function redIncompletaList(array $adultosConRedIds, array $adultosConResponsableIds): Collection
    {
        if (!$this->hasTable('adulto_mayor')) {
            return collect();
        }

        $conRed = array_map('strval', $adultosConRedIds);
        $conResponsable = array_map('strval', $adultosConResponsableIds);
        $sinContacto = array_map('strval', $this->adultosSinContactoIds());

        return $this->adultosBaseQuery()
            ->select($this->adultoSelectColumns())
            ->orderBy('ap_paterno')
            ->get()
            ->map(function ($adulto) use ($conRed, $conResponsable, $sinContacto) {
                $id = (string) $adulto->cod_am;
                $faltantes = [];

                if (!in_array($id, $conRed, true)) {
                    $faltantes[] = 'Sin familiares vinculados';
                }

                if (!in_array($id, $conResponsable, true)) {
                    $faltantes[] = 'Sin responsable principal';
                }

                if (in_array($id, $sinContacto, true)) {
                    $faltantes[] = 'Sin contacto de emergencia';
                }

                if (empty($faltantes)) {
                    return null;
                }

                return [
                    'adulto' => $this->nombreAdulto($adulto),
                    'estado' => in_array('Sin familiares vinculados', $faltantes, true) ? 'Red no registrada' : 'Red incompleta',
                    'faltante' => implode(' · ', $faltantes),
                    'url' => Route::has('admin.adultos-mayores.show')
                        ? route('admin.adultos-mayores.show', ['adulto_mayor' => $adulto->cod_am, 'tab' => 'familiares'])
                        : null,
                ];
            })
            ->filter()
            ->take(8)
            ->values();
    }

    private function alertasSociales(Collection $redIncompleta, array $adultosSinFichaIds): Collection
    {
        $alertas = $redIncompleta->take(5)->map(fn ($item) => [
            'adulto' => $item['adulto'],
            'motivo' => $item['faltante'],
            'estado' => $item['estado'],
            'prioridad' => str_contains($item['faltante'], 'Sin familiares') ? 'Alta' : 'Media',
            'fecha' => now()->format('d/m/Y'),
        ]);

        $adultosSinFicha = $this->adultosByIds($adultosSinFichaIds, 4)->map(fn ($adulto) => [
            'adulto' => $this->nombreAdulto($adulto),
            'motivo' => 'Ficha social pendiente',
            'estado' => 'Pendiente',
            'prioridad' => 'Media',
            'fecha' => now()->format('d/m/Y'),
        ]);

        return $alertas
            ->merge($adultosSinFicha)
            ->unique(fn ($item) => $item['adulto'] . '|' . $item['motivo'])
            ->take(8)
            ->values();
    }

    private function adultosByIds(array $ids, int $limit): Collection
    {
        if (!$this->hasTable('adulto_mayor') || empty($ids)) {
            return collect();
        }

        return $this->adultosBaseQuery()
            ->whereIn('cod_am', $ids)
            ->select($this->adultoSelectColumns())
            ->orderBy('ap_paterno')
            ->limit($limit)
            ->get();
    }

    private function adultoSelectColumns(): array
    {
        return array_values(array_filter([
            'cod_am',
            'nombres',
            'ap_paterno',
            $this->hasColumn('adulto_mayor', 'ap_materno') ? 'ap_materno' : null,
            $this->hasColumn('adulto_mayor', 'contacto_emergencia_nombre') ? 'contacto_emergencia_nombre' : null,
            $this->hasColumn('adulto_mayor', 'contacto_emergencia_celular') ? 'contacto_emergencia_celular' : null,
        ]));
    }

    private function reportesSociales(): array
    {
        return [
            [
                'titulo' => 'Reporte de red de apoyo',
                'descripcion' => 'Resumen de familiares y vinculos activos.',
                'estado' => Route::has('admin.reportes.familiares.preview') ? 'Disponible' : 'Preparado',
                'url' => Route::has('admin.reportes.familiares.preview') ? route('admin.reportes.familiares.preview') : null,
                'permiso' => 'reportes.ver',
                'icono' => 'ph-users-three',
            ],
            [
                'titulo' => 'Reporte de visitas',
                'descripcion' => 'Seguimiento de visitas familiares y sociales.',
                'estado' => 'Disponible proximamente',
                'url' => null,
                'permiso' => 'familiares.ver',
                'icono' => 'ph-calendar-check',
            ],
            [
                'titulo' => 'Fichas sociales pendientes',
                'descripcion' => 'Adultos mayores que requieren evaluacion social.',
                'estado' => 'Preparado',
                'url' => null,
                'permiso' => 'familiares.ver',
                'icono' => 'ph-clipboard-text',
            ],
            [
                'titulo' => 'Reporte social institucional',
                'descripcion' => 'Indicadores consolidados para seguimiento directivo.',
                'estado' => 'Preparado',
                'url' => null,
                'permiso' => 'familiares.ver',
                'icono' => 'ph-chart-pie-slice',
            ],
        ];
    }

    private function firstExistingTable(array $tables): ?string
    {
        foreach ($tables as $table) {
            if ($this->hasTable($table)) {
                return $table;
            }
        }

        return null;
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if ($this->hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function firstFilled(object $row, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (isset($row->{$column}) && trim((string) $row->{$column}) !== '') {
                return trim((string) $row->{$column});
            }
        }

        return null;
    }

    private function nombreDesdeCampos(object $row, string $prefix = ''): ?string
    {
        $nombre = trim(implode(' ', array_filter([
            $row->{$prefix . 'nombres'} ?? null,
            $row->{$prefix . 'ap_paterno'} ?? null,
            $row->{$prefix . 'ap_materno'} ?? null,
        ])));

        return $nombre !== '' ? $nombre : null;
    }

    private function nombreAdulto(object $adulto): string
    {
        return $this->nombreDesdeCampos($adulto) ?: 'Adulto mayor';
    }

    private function percent(int $value, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(($value / $total) * 100);
    }

    private function nivelSocial(array $estadoSocial): array
    {
        $score = (int) round(($estadoSocial['red_apoyo'] + $estadoSocial['ficha_social'] + $estadoSocial['visitas_recientes']) / 3);

        if ($score >= 70) {
            return ['texto' => 'Estable', 'score' => $score, 'color' => 'emerald'];
        }

        if ($score >= 40) {
            return ['texto' => 'Pendiente', 'score' => $score, 'color' => 'amber'];
        }

        return ['texto' => 'Requiere seguimiento', 'score' => $score, 'color' => 'rose'];
    }

    private function mesCorto(int $month): string
    {
        return [
            1 => 'Ene',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dic',
        ][$month] ?? '';
    }
}
