<?php

namespace App\Services\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReporteDataService
{
    // ──────────────────────────────────────────────────────────
    // ADULTOS MAYORES
    // ──────────────────────────────────────────────────────────

    public function adultosResumen(): array
    {
        if (!Schema::hasTable('adulto_mayor')) {
            return [
                'total' => 0, 'activos' => 0, 'archivados' => 0, 'nuevos_mes' => 0,
                'con_familiar' => 0, 'sin_familiar' => 0,
                'con_ficha' => 0, 'sin_ficha' => 0,
            ];
        }

        $total      = DB::table('adulto_mayor')->count();
        $activos    = DB::table('adulto_mayor')->whereNull('archivado_en')->count();
        $archivados = DB::table('adulto_mayor')->whereNotNull('archivado_en')->count();
        $nuevosMes  = DB::table('adulto_mayor')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $conFamiliar = 0;
        if (Schema::hasTable('familiar_adulto')) {
            $conFamiliar = DB::table('adulto_mayor')
                ->whereExists(fn($q) => $q->select(DB::raw(1))
                    ->from('familiar_adulto')
                    ->whereColumn('familiar_adulto.cod_am', 'adulto_mayor.cod_am')
                    ->whereNull('familiar_adulto.deleted_at'))
                ->count();
        }

        $conFicha = 0;
        if (Schema::hasTable('ficha_medica_adulto')) {
            $conFicha = DB::table('adulto_mayor')
                ->whereExists(fn($q) => $q->select(DB::raw(1))
                    ->from('ficha_medica_adulto')
                    ->whereColumn('ficha_medica_adulto.cod_am', 'adulto_mayor.cod_am')
                    ->whereNull('ficha_medica_adulto.deleted_at'))
                ->count();
        }

        return [
            'total'        => $total,
            'activos'      => $activos,
            'archivados'   => $archivados,
            'nuevos_mes'   => $nuevosMes,
            'con_familiar' => $conFamiliar,
            'sin_familiar' => max(0, $total - $conFamiliar),
            'con_ficha'    => $conFicha,
            'sin_ficha'    => max(0, $total - $conFicha),
        ];
    }

    public function adultosListaCompleta(int $limite = 50): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('adulto_mayor')) {
            return collect();
        }

        $tieneFamiliarSql = Schema::hasTable('familiar_adulto')
            ? 'EXISTS (SELECT 1 FROM familiar_adulto fa WHERE fa.cod_am = am.cod_am AND fa.deleted_at IS NULL)'
            : 'FALSE';

        $tieneFichaSql = Schema::hasTable('ficha_medica_adulto')
            ? 'EXISTS (SELECT 1 FROM ficha_medica_adulto fm WHERE fm.cod_am = am.cod_am AND fm.deleted_at IS NULL)'
            : 'FALSE';

        return DB::table('adulto_mayor as am')
            ->leftJoin('estado_adulto as ea', 'am.cod_est_adul', '=', 'ea.cod_est_adul')
            ->select(
                'am.cod_am',
                DB::raw("TRIM(am.nombres || ' ' || am.ap_paterno || CASE WHEN am.ap_materno IS NOT NULL THEN ' ' || am.ap_materno ELSE '' END) AS nombre_completo"),
                'am.ci',
                'am.genero',
                'am.fecha_nac',
                DB::raw("CASE WHEN am.fecha_nac IS NOT NULL THEN CAST(DATE_PART('year', AGE(CURRENT_DATE, am.fecha_nac)) AS INT) ELSE NULL END AS edad"),
                'am.estado_civil',
                'am.nivel_educat',
                'am.tipo_ing',
                'am.fecha_ing',
                'am.permanencia',
                DB::raw("COALESCE(ea.estado, 'Sin estado') AS nombre_estado"),
                'am.archivado_en',
                'am.created_at',
                DB::raw("({$tieneFamiliarSql}) AS tiene_familiar"),
                DB::raw("({$tieneFichaSql}) AS tiene_ficha")
            )
            ->orderBy('am.ap_paterno')
            ->limit($limite)
            ->get();
    }

    public function adultosLista(int $limite = 50): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('adulto_mayor')) {
            return collect();
        }

        return DB::table('adulto_mayor as am')
            ->leftJoin('estado_adulto as ea', 'am.cod_est_adul', '=', 'ea.cod_est_adul')
            ->select(
                'am.cod_am',
                DB::raw("CONCAT(am.nombres, ' ', am.ap_paterno, ' ', COALESCE(am.ap_materno, '')) AS nombre_completo"),
                'am.ci',
                'am.genero',
                'am.fecha_nac',
                'am.fecha_ing',
                'am.permanencia',
                DB::raw("ea.estado AS nombre_estado"),
                'am.archivado_en'
            )
            ->orderBy('am.ap_paterno')
            ->limit($limite)
            ->get();
    }

    public function adultosEstado(): array
    {
        if (!Schema::hasTable('adulto_mayor') || !Schema::hasTable('estado_adulto')) {
            return ['labels' => [], 'data' => [], 'colores' => []];
        }

        $resultados = DB::table('adulto_mayor as am')
            ->leftJoin('estado_adulto as ea', 'am.cod_est_adul', '=', 'ea.cod_est_adul')
            ->select(DB::raw("ea.estado AS nombre_estado"), DB::raw('COUNT(*) as total'))
            ->groupBy('ea.estado', 'ea.cod_est_adul')
            ->orderByDesc('total')
            ->get();

        $colores = ['#2A9D8F', '#E97A5F', '#D4843A', '#7A68B0', '#2F3E5C'];

        return [
            'labels'  => $resultados->pluck('nombre_estado')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            'colores' => array_slice($colores, 0, $resultados->count()),
        ];
    }

    public function adultosGenero(): array
    {
        if (!Schema::hasTable('adulto_mayor')) {
            return ['labels' => [], 'data' => [], 'colores' => []];
        }

        $resultados = DB::table('adulto_mayor')
            ->select('genero', DB::raw('COUNT(*) as total'))
            ->groupBy('genero')
            ->orderByDesc('total')
            ->get();

        $mapaColores = [
            'm'          => '#2F3E5C',
            'f'          => '#E97A5F',
            'masculino'  => '#2F3E5C',
            'femenino'   => '#E97A5F',
        ];

        $mapaLabels = [
            'M' => 'Masculino', 'F' => 'Femenino',
            'm' => 'Masculino', 'f' => 'Femenino',
            'masculino' => 'Masculino', 'femenino' => 'Femenino',
        ];

        $colores = $resultados->map(fn($r) => $mapaColores[strtolower($r->genero ?? '')] ?? '#7A68B0')->toArray();

        return [
            'labels'  => $resultados->pluck('genero')->map(fn($g) => $mapaLabels[$g] ?? ucfirst(strtolower($g ?? 'Sin dato')))->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            'colores' => $colores,
        ];
    }

    public function adultosEdad(): array
    {
        if (!Schema::hasTable('adulto_mayor')) {
            return ['labels' => [], 'data' => [], 'colores' => [], 'promedio' => 0];
        }

        $rangos = [
            '60-69' => [60, 69],
            '70-79' => [70, 79],
            '80-89' => [80, 89],
            '90+'   => [90, 150],
        ];

        $data   = [];
        $hoy    = now()->format('Y-m-d');

        foreach ($rangos as $etiqueta => [$min, $max]) {
            $count = DB::table('adulto_mayor')
                ->whereRaw("DATE_PART('year', AGE(CAST(? AS DATE), fecha_nac)) >= ?", [$hoy, $min])
                ->whereRaw("DATE_PART('year', AGE(CAST(? AS DATE), fecha_nac)) <= ?", [$hoy, $max])
                ->whereNotNull('fecha_nac')
                ->count();
            $data[] = (int) $count;
        }

        $promedio = (int) DB::table('adulto_mayor')
            ->whereNotNull('fecha_nac')
            ->selectRaw("ROUND(AVG(DATE_PART('year', AGE(CURRENT_DATE, fecha_nac)))) as promedio")
            ->value('promedio');

        return [
            'labels'   => array_keys($rangos),
            'data'     => $data,
            'colores'  => ['#2F3E5C', '#2A9D8F', '#D4843A', '#E97A5F'],
            'promedio' => $promedio,
        ];
    }

    // ──────────────────────────────────────────────────────────
    // SALUD Y SEGUIMIENTO
    // ──────────────────────────────────────────────────────────

    public function saludResumen(): array
    {
        $fichas      = Schema::hasTable('ficha_medica_adulto')
            ? DB::table('ficha_medica_adulto')->whereNull('deleted_at')->count() : 0;
        $medicaciones = Schema::hasTable('medicacion_adulto')
            ? DB::table('medicacion_adulto')->whereNull('deleted_at')->where('estado', 'activa')->count() : 0;
        $valoraciones = Schema::hasTable('valoracion_funcional_adulto')
            ? DB::table('valoracion_funcional_adulto')->where('estado', 'VIGENTE')->count() : 0;
        $atenciones   = Schema::hasTable('atenciones_adulto')
            ? DB::table('atenciones_adulto')->whereNull('deleted_at')->count() : 0;

        return [
            'fichas'       => $fichas,
            'medicaciones' => $medicaciones,
            'valoraciones' => $valoraciones,
            'atenciones'   => $atenciones,
        ];
    }

    public function fichasLista(int $limite = 50): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('ficha_medica_adulto')) {
            return collect();
        }

        return DB::table('ficha_medica_adulto as fm')
            ->join('adulto_mayor as am', 'fm.cod_am', '=', 'am.cod_am')
            ->whereNull('fm.deleted_at')
            ->select(
                'am.cod_am',
                DB::raw("CONCAT(am.nombres, ' ', am.ap_paterno) AS nombre"),
                'fm.estado',
                'fm.hipertension',
                'fm.diabetes',
                'fm.problemas_cardiacos',
                'fm.created_at'
            )
            ->orderByDesc('fm.created_at')
            ->limit($limite)
            ->get();
    }

    public function medicacionLista(int $limite = 50): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('medicacion_adulto')) {
            return collect();
        }

        return DB::table('medicacion_adulto as ma')
            ->join('adulto_mayor as am', 'ma.cod_am', '=', 'am.cod_am')
            ->whereNull('ma.deleted_at')
            ->select(
                'am.cod_am',
                DB::raw("CONCAT(am.nombres, ' ', am.ap_paterno) AS nombre"),
                'ma.nombre_medicamento',
                'ma.dosis',
                'ma.frecuencia',
                'ma.estado',
                'ma.fecha_inicio',
                'ma.fecha_fin'
            )
            ->orderByDesc('ma.created_at')
            ->limit($limite)
            ->get();
    }

    public function valoracionesLista(int $limite = 50): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('valoracion_funcional_adulto')) {
            return collect();
        }

        return DB::table('valoracion_funcional_adulto as vf')
            ->join('adulto_mayor as am', 'vf.cod_am', '=', 'am.cod_am')
            ->where('vf.estado', 'VIGENTE')
            ->select(
                'am.cod_am',
                DB::raw("CONCAT(am.nombres, ' ', am.ap_paterno) AS nombre"),
                'vf.nivel_dependencia',
                'vf.riesgo_caida',
                'vf.indice_barthel',
                'vf.fecha_valoracion'
            )
            ->orderByDesc('vf.fecha_valoracion')
            ->limit($limite)
            ->get();
    }

    public function atencionesPorMes(): array
    {
        if (!Schema::hasTable('atenciones_adulto')) {
            return ['labels' => [], 'data' => [], 'colores' => []];
        }

        $resultados = DB::table('atenciones_adulto')
            ->whereNull('deleted_at')
            ->whereYear('fecha', now()->year)
            ->select(
                DB::raw("TO_CHAR(fecha, 'MM') as mes"),
                DB::raw("TO_CHAR(fecha, 'Mon') as mes_nombre"),
                DB::raw('COUNT(*) as total')
            )
            ->groupByRaw("TO_CHAR(fecha, 'MM'), TO_CHAR(fecha, 'Mon')")
            ->orderByRaw("TO_CHAR(fecha, 'MM')")
            ->get();

        return [
            'labels'  => $resultados->pluck('mes_nombre')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            'colores' => array_fill(0, $resultados->count(), '#2A9D8F'),
        ];
    }

    public function dependenciaDistribucion(): array
    {
        if (!Schema::hasTable('valoracion_funcional_adulto')) {
            return ['labels' => [], 'data' => [], 'colores' => []];
        }

        $resultados = DB::table('valoracion_funcional_adulto')
            ->where('estado', 'VIGENTE')
            ->whereNotNull('nivel_dependencia')
            ->select('nivel_dependencia', DB::raw('COUNT(*) as total'))
            ->groupBy('nivel_dependencia')
            ->orderByDesc('total')
            ->get();

        $mapaColores = [
            'INDEPENDIENTE'      => '#2A9D8F',
            'LEVE'               => '#D4843A',
            'MODERADO'           => '#E97A5F',
            'SEVERO'             => '#991b1b',
            'TOTAL'              => '#2F3E5C',
        ];

        return [
            'labels'  => $resultados->pluck('nivel_dependencia')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            'colores' => $resultados->map(fn($r) => $mapaColores[strtoupper($r->nivel_dependencia)] ?? '#7A68B0')->toArray(),
        ];
    }

    public function riesgosCaida(): array
    {
        if (!Schema::hasTable('valoracion_funcional_adulto')) {
            return ['labels' => [], 'data' => [], 'colores' => []];
        }

        $resultados = DB::table('valoracion_funcional_adulto')
            ->where('estado', 'VIGENTE')
            ->whereNotNull('riesgo_caida')
            ->select('riesgo_caida', DB::raw('COUNT(*) as total'))
            ->groupBy('riesgo_caida')
            ->orderByDesc('total')
            ->get();

        $mapaColores = ['BAJO' => '#2A9D8F', 'MODERADO' => '#D4843A', 'ALTO' => '#E97A5F'];

        return [
            'labels'  => $resultados->pluck('riesgo_caida')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            'colores' => $resultados->map(fn($r) => $mapaColores[strtoupper($r->riesgo_caida)] ?? '#7A68B0')->toArray(),
        ];
    }

    // ──────────────────────────────────────────────────────────
    // FAMILIARES
    // ──────────────────────────────────────────────────────────

    public function familiaresResumen(): array
    {
        $totalFamiliares   = Schema::hasTable('familiares')
            ? DB::table('familiares')->count() : 0;
        $vinculosActivos   = Schema::hasTable('familiar_adulto')
            ? DB::table('familiar_adulto')->whereNull('deleted_at')->where('estado', 'activo')->count() : 0;
        $adultosSinFamiliar = $this->adultosSinFamiliar();

        return [
            'total_familiares'    => $totalFamiliares,
            'vinculos_activos'    => $vinculosActivos,
            'adultos_sin_familiar' => count($adultosSinFamiliar),
        ];
    }

    public function vinculosLista(int $limite = 50): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('familiar_adulto')) {
            return collect();
        }

        return DB::table('familiar_adulto as fa')
            ->join('adulto_mayor as am', 'fa.cod_am', '=', 'am.cod_am')
            ->join('familiares as f', 'fa.cod_fam', '=', 'f.cod_fam')
            ->join('users as u', 'f.cod_usu', '=', 'u.cod_usu')
            ->whereNull('fa.deleted_at')
            ->select(
                'am.cod_am',
                DB::raw("CONCAT(am.nombres, ' ', am.ap_paterno) AS adulto"),
                DB::raw("CONCAT(u.nombres, ' ', u.ap_paterno) AS familiar"),
                'fa.parentesco_vinculo',
                'fa.es_responsable',
                'fa.estado'
            )
            ->orderBy('am.ap_paterno')
            ->limit($limite)
            ->get();
    }

    public function adultosSinFamiliar(): array
    {
        if (!Schema::hasTable('adulto_mayor')) {
            return [];
        }

        $conFamiliar = Schema::hasTable('familiar_adulto')
            ? DB::table('familiar_adulto')->whereNull('deleted_at')->pluck('cod_am')->unique()->toArray()
            : [];

        return DB::table('adulto_mayor')
            ->whereNull('archivado_en')
            ->whereNotIn('cod_am', $conFamiliar)
            ->select('cod_am', DB::raw("CONCAT(nombres, ' ', ap_paterno) AS nombre"))
            ->orderBy('ap_paterno')
            ->get()
            ->toArray();
    }

    public function parentescosDistribucion(): array
    {
        if (!Schema::hasTable('familiar_adulto')) {
            return ['labels' => [], 'data' => [], 'colores' => []];
        }

        $resultados = DB::table('familiar_adulto')
            ->whereNull('deleted_at')
            ->whereNotNull('parentesco_vinculo')
            ->select('parentesco_vinculo', DB::raw('COUNT(*) as total'))
            ->groupBy('parentesco_vinculo')
            ->orderByDesc('total')
            ->get();

        $colores = ['#2F3E5C', '#2A9D8F', '#D4843A', '#E97A5F', '#7A68B0', '#63775B'];

        return [
            'labels'  => $resultados->pluck('parentesco_vinculo')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            'colores' => array_slice($colores, 0, $resultados->count()),
        ];
    }

    // ──────────────────────────────────────────────────────────
    // EQUIPO INSTITUCIONAL
    // ──────────────────────────────────────────────────────────

    public function equipoResumen(): array
    {
        $personalSalud = Schema::hasTable('personal_salud')
            ? DB::table('personal_salud')->whereNull('deleted_at')->count() : 0;
        $personalAdmin = Schema::hasTable('personal_admin')
            ? DB::table('personal_admin')->count() : 0;
        $voluntarios   = Schema::hasTable('voluntarios')
            ? DB::table('voluntarios')->where('estado', 'activo')->count() : 0;

        return [
            'personal_salud' => $personalSalud,
            'personal_admin' => $personalAdmin,
            'voluntarios'    => $voluntarios,
            'total'          => $personalSalud + $personalAdmin + $voluntarios,
        ];
    }

    public function personalSaludLista(int $limite = 50): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('personal_salud')) {
            return collect();
        }

        return DB::table('personal_salud as ps')
            ->join('users as u', 'ps.cod_usu', '=', 'u.cod_usu')
            ->leftJoin('especialidades as e', 'ps.cod_esp', '=', 'e.cod_esp')
            ->whereNull('ps.deleted_at')
            ->select(
                'ps.cod_per_sal',
                DB::raw("CONCAT(u.nombres, ' ', u.ap_paterno) AS nombre"),
                'e.nombre_especialidad',
                'ps.matricula_prof',
                'ps.estado_laboral',
                'ps.fecha_ing'
            )
            ->orderBy('u.ap_paterno')
            ->limit($limite)
            ->get();
    }

    public function personalAdminLista(int $limite = 50): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('personal_admin')) {
            return collect();
        }

        return DB::table('personal_admin as pa')
            ->join('users as u', 'pa.cod_usu', '=', 'u.cod_usu')
            ->select(
                'pa.cod_per_adm',
                DB::raw("CONCAT(u.nombres, ' ', u.ap_paterno) AS nombre"),
                'pa.cargo',
                'pa.area_admin',
                'pa.estado_laboral',
                'pa.fecha_ingreso'
            )
            ->orderBy('u.ap_paterno')
            ->limit($limite)
            ->get();
    }

    public function voluntariosLista(int $limite = 50): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('voluntarios')) {
            return collect();
        }

        return DB::table('voluntarios as v')
            ->join('users as u', 'v.cod_usu', '=', 'u.cod_usu')
            ->select(
                'v.cod_vol',
                DB::raw("CONCAT(u.nombres, ' ', u.ap_paterno) AS nombre"),
                'v.area_apoyo',
                'v.area_apoyo_preferente',
                'v.estado',
                'v.fecha_ing'
            )
            ->orderBy('u.ap_paterno')
            ->limit($limite)
            ->get();
    }

    public function especialidadesDistribucion(): array
    {
        if (!Schema::hasTable('personal_salud') || !Schema::hasTable('especialidades')) {
            return ['labels' => [], 'data' => [], 'colores' => []];
        }

        $resultados = DB::table('personal_salud as ps')
            ->leftJoin('especialidades as e', 'ps.cod_esp', '=', 'e.cod_esp')
            ->whereNull('ps.deleted_at')
            ->select('e.nombre_especialidad', DB::raw('COUNT(*) as total'))
            ->groupBy('e.nombre_especialidad')
            ->orderByDesc('total')
            ->get();

        $colores = ['#2F3E5C', '#2A9D8F', '#D4843A', '#E97A5F', '#7A68B0', '#63775B'];

        return [
            'labels'  => $resultados->pluck('nombre_especialidad')->map(fn($v) => $v ?? 'Sin especialidad')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            'colores' => array_slice($colores, 0, $resultados->count()),
        ];
    }

    public function areasVoluntariosDistribucion(): array
    {
        if (!Schema::hasTable('voluntarios')) {
            return ['labels' => [], 'data' => [], 'colores' => []];
        }

        $resultados = DB::table('voluntarios')
            ->whereNotNull('area_apoyo')
            ->select('area_apoyo', DB::raw('COUNT(*) as total'))
            ->groupBy('area_apoyo')
            ->orderByDesc('total')
            ->get();

        $colores = ['#2A9D8F', '#2F3E5C', '#D4843A', '#E97A5F', '#7A68B0'];

        return [
            'labels'  => $resultados->pluck('area_apoyo')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            'colores' => array_slice($colores, 0, $resultados->count()),
        ];
    }

    // ──────────────────────────────────────────────────────────
    // ACTIVIDADES
    // ──────────────────────────────────────────────────────────

    public function actividadesResumen(): array
    {
        if (!Schema::hasTable('actividades_adulto')) {
            return ['total' => 0, 'completadas' => 0, 'pendientes' => 0, 'canceladas' => 0];
        }

        $total       = DB::table('actividades_adulto')->whereNull('deleted_at')->count();
        $completadas = DB::table('actividades_adulto')->whereNull('deleted_at')
            ->whereIn('estado', ['COMPLETADA', 'REALIZADA', 'FINALIZADA'])->count();
        $pendientes  = DB::table('actividades_adulto')->whereNull('deleted_at')
            ->whereIn('estado', ['PROGRAMADA', 'PENDIENTE'])->count();
        $canceladas  = DB::table('actividades_adulto')->whereNull('deleted_at')
            ->whereIn('estado', ['CANCELADA', 'ANULADA'])->count();

        return [
            'total'       => $total,
            'completadas' => $completadas,
            'pendientes'  => $pendientes,
            'canceladas'  => $canceladas,
        ];
    }

    public function actividadesLista(int $limite = 50): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('actividades_adulto')) {
            return collect();
        }

        return DB::table('actividades_adulto as aa')
            ->join('adulto_mayor as am', 'aa.cod_am', '=', 'am.cod_am')
            ->leftJoin('tipo_actividades_adulto as ta', 'aa.cod_tipo_act', '=', 'ta.cod_tipo_act')
            ->whereNull('aa.deleted_at')
            ->select(
                'aa.cod_act_adul',
                DB::raw("CONCAT(am.nombres, ' ', am.ap_paterno) AS adulto"),
                'ta.tipo as tipo_actividad',
                'aa.fecha',
                'aa.hora',
                'aa.estado',
                'aa.obs'
            )
            ->orderByDesc('aa.fecha')
            ->limit($limite)
            ->get();
    }

    /**
     * Lista enriquecida con participantes, asistencia y evaluación.
     * Usada por el Excel de Fase 5.
     */
    public function actividadesListaEnriquecida(int $limite = 500): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('actividades_adulto')) {
            return collect();
        }

        return DB::table('actividades_adulto as aa')
            ->leftJoin('tipo_actividades_adulto as ta', 'aa.cod_tipo_act', '=', 'ta.cod_tipo_act')
            ->leftJoin('adulto_mayor as am', 'aa.cod_am', '=', 'am.cod_am')
            ->leftJoin(DB::raw("(
                SELECT
                    cod_act_adul,
                    COUNT(*)                                                          AS total_participantes,
                    SUM(CASE WHEN estado_asistencia = 'ASISTIO'      THEN 1 ELSE 0 END) AS asistieron,
                    SUM(CASE WHEN estado_asistencia = 'FALTO'        THEN 1 ELSE 0 END) AS faltaron,
                    SUM(CASE WHEN estado_asistencia = 'JUSTIFICADO'  THEN 1 ELSE 0 END) AS justificados,
                    SUM(CASE WHEN requiere_seguimiento = true         THEN 1 ELSE 0 END) AS seguimiento
                FROM actividad_participantes
                WHERE deleted_at IS NULL
                GROUP BY cod_act_adul
            ) AS ap_agg"), 'ap_agg.cod_act_adul', '=', 'aa.cod_act_adul')
            ->whereNull('aa.deleted_at')
            ->select(
                'aa.cod_act_adul',
                'aa.nombre',
                'ta.tipo as tipo_actividad',
                'ta.categoria',
                DB::raw("CASE WHEN am.cod_am IS NOT NULL THEN CONCAT(am.nombres,' ',am.ap_paterno) ELSE NULL END AS adulto"),
                'aa.fecha',
                'aa.hora',
                'aa.hora_fin',
                'aa.lugar',
                'aa.responsable_id',
                'aa.estado',
                DB::raw('COALESCE(ap_agg.total_participantes, 0) AS total_participantes'),
                DB::raw('COALESCE(ap_agg.asistieron, 0)          AS asistieron'),
                DB::raw('COALESCE(ap_agg.faltaron, 0)            AS faltaron'),
                DB::raw('COALESCE(ap_agg.justificados, 0)        AS justificados'),
                DB::raw('COALESCE(ap_agg.seguimiento, 0)         AS seguimiento'),
                'aa.resultado_general',
                'aa.nivel_cumplimiento',
                'aa.evaluacion_final',
                'aa.incidencias',
                'aa.recomendaciones',
                'aa.obs'
            )
            ->orderByDesc('aa.fecha')
            ->limit($limite)
            ->get();
    }

    public function actividadesPorMes(): array
    {
        if (!Schema::hasTable('actividades_adulto')) {
            return ['labels' => [], 'data' => [], 'colores' => []];
        }

        $resultados = DB::table('actividades_adulto')
            ->whereNull('deleted_at')
            ->whereYear('fecha', now()->year)
            ->select(
                DB::raw("TO_CHAR(fecha, 'MM') as mes"),
                DB::raw("TO_CHAR(fecha, 'Mon') as mes_nombre"),
                DB::raw('COUNT(*) as total')
            )
            ->groupByRaw("TO_CHAR(fecha, 'MM'), TO_CHAR(fecha, 'Mon')")
            ->orderByRaw("TO_CHAR(fecha, 'MM')")
            ->get();

        return [
            'labels'  => $resultados->pluck('mes_nombre')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            'colores' => array_fill(0, $resultados->count(), '#7A68B0'),
        ];
    }

    public function actividadesEstado(): array
    {
        if (!Schema::hasTable('actividades_adulto')) {
            return ['labels' => [], 'data' => [], 'colores' => []];
        }

        $resultados = DB::table('actividades_adulto')
            ->whereNull('deleted_at')
            ->whereNotNull('estado')
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->orderByDesc('total')
            ->get();

        $mapaColores = [
            'COMPLETADA'   => '#2A9D8F', 'REALIZADA'    => '#2A9D8F', 'FINALIZADA' => '#2A9D8F',
            'PROGRAMADA'   => '#D4843A', 'PENDIENTE'    => '#D4843A',
            'CANCELADA'    => '#E97A5F', 'ANULADA'      => '#E97A5F',
            'REPROGRAMADA' => '#7A68B0',
        ];

        $mapaEtiquetas = [
            'COMPLETADA'   => 'Realizada',    'REALIZADA'    => 'Realizada',   'FINALIZADA' => 'Realizada',
            'PROGRAMADA'   => 'Programada',   'PENDIENTE'    => 'Programada',
            'CANCELADA'    => 'Cancelada',    'ANULADA'      => 'Cancelada',
            'REPROGRAMADA' => 'Reprogramada',
        ];

        return [
            'labels'  => $resultados->pluck('estado')->map(
                fn($e) => $mapaEtiquetas[strtoupper($e ?? '')] ?? ucfirst(strtolower($e ?? 'Sin estado'))
            )->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            'colores' => $resultados->map(fn($r) => $mapaColores[strtoupper($r->estado ?? '')] ?? '#7A68B0')->toArray(),
        ];
    }

    public function actividadesPorAdulto(int $limite = 20): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('actividades_adulto')) {
            return collect();
        }

        return DB::table('actividades_adulto as aa')
            ->join('adulto_mayor as am', 'aa.cod_am', '=', 'am.cod_am')
            ->whereNull('aa.deleted_at')
            ->select(
                'am.cod_am',
                DB::raw("CONCAT(am.nombres, ' ', am.ap_paterno) AS adulto"),
                DB::raw('COUNT(*) as total_actividades')
            )
            ->groupBy('am.cod_am', 'am.nombres', 'am.ap_paterno')
            ->orderByDesc('total_actividades')
            ->limit($limite)
            ->get();
    }

    // ──────────────────────────────────────────────────────────
    // BITÁCORA
    // ──────────────────────────────────────────────────────────

    public function bitacoraResumen(): array
    {
        if (!Schema::hasTable('activity_log')) {
            return ['total' => 0, 'hoy' => 0, 'esta_semana' => 0, 'modulos' => 0];
        }

        $total      = DB::table('activity_log')->count();
        $hoy        = DB::table('activity_log')->whereDate('created_at', today())->count();
        $semana     = DB::table('activity_log')->where('created_at', '>=', now()->startOfWeek())->count();
        $modulos    = DB::table('activity_log')->distinct()->count('log_name');

        return [
            'total'       => $total,
            'hoy'         => $hoy,
            'esta_semana' => $semana,
            'modulos'     => $modulos,
        ];
    }

    public function bitacoraLista(int $limite = 50): \Illuminate\Support\Collection
    {
        if (!Schema::hasTable('activity_log')) {
            return collect();
        }

        return DB::table('activity_log as al')
            ->leftJoin('users as u', function ($join) {
                $join->on('al.causer_id', '=', 'u.cod_usu')
                     ->where('al.causer_type', '=', 'App\\Models\\User');
            })
            ->select(
                'al.id',
                'al.log_name',
                'al.description',
                'al.event',
                'al.subject_type',
                'al.created_at',
                DB::raw("CONCAT(COALESCE(u.nombres, ''), ' ', COALESCE(u.ap_paterno, '')) AS usuario")
            )
            ->orderByDesc('al.created_at')
            ->limit($limite)
            ->get();
    }

    public function bitacoraModulos(): array
    {
        if (!Schema::hasTable('activity_log')) {
            return ['labels' => [], 'data' => [], 'colores' => []];
        }

        $resultados = DB::table('activity_log')
            ->whereNotNull('log_name')
            ->select('log_name', DB::raw('COUNT(*) as total'))
            ->groupBy('log_name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $colores = ['#2F3E5C', '#2A9D8F', '#D4843A', '#E97A5F', '#7A68B0', '#63775B', '#C7B5A3', '#991b1b', '#1e40af', '#065f46'];

        return [
            'labels'  => $resultados->pluck('log_name')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            'colores' => array_slice($colores, 0, $resultados->count()),
        ];
    }

    public function bitacoraEventos(): array
    {
        if (!Schema::hasTable('activity_log')) {
            return ['labels' => [], 'data' => [], 'colores' => []];
        }

        $resultados = DB::table('activity_log')
            ->whereNotNull('event')
            ->select('event', DB::raw('COUNT(*) as total'))
            ->groupBy('event')
            ->orderByDesc('total')
            ->get();

        $mapaColores = [
            'created'  => '#2A9D8F',
            'updated'  => '#D4843A',
            'deleted'  => '#E97A5F',
            'restored' => '#7A68B0',
        ];

        return [
            'labels'  => $resultados->pluck('event')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            'colores' => $resultados->map(fn($r) => $mapaColores[$r->event] ?? '#2F3E5C')->toArray(),
        ];
    }

    public function bitacoraTendencia(): array
    {
        if (!Schema::hasTable('activity_log')) {
            return ['labels' => [], 'data' => [], 'colores' => []];
        }

        $resultados = DB::table('activity_log')
            ->whereYear('created_at', now()->year)
            ->select(
                DB::raw("TO_CHAR(created_at, 'MM') as mes"),
                DB::raw("TO_CHAR(created_at, 'Mon') as mes_nombre"),
                DB::raw('COUNT(*) as total')
            )
            ->groupByRaw("TO_CHAR(created_at, 'MM'), TO_CHAR(created_at, 'Mon')")
            ->orderByRaw("TO_CHAR(created_at, 'MM')")
            ->get();

        return [
            'labels'  => $resultados->pluck('mes_nombre')->toArray(),
            'data'    => $resultados->pluck('total')->map(fn($v) => (int) $v)->toArray(),
            'colores' => array_fill(0, $resultados->count(), '#2F3E5C'),
        ];
    }
}
