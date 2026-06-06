<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardService
{
    // ── MÉTODO PRINCIPAL ──────────────────────────────────────────────────────

    public function obtenerDatosDashboard($usuario): array
    {
        $claveCache = 'dashboard_' . $usuario->cod_usu;

        return Cache::remember($claveCache, 60, function () use ($usuario) {
            $esAdmin = $usuario->hasRole(['SUPERADMINISTRADOR', 'ADMINISTRADOR']);
            $esSalud = $usuario->hasRole(['ENFERMEROS', 'MEDICO GENERAL/GERIATRA']);

            return [
                // ── NUEVAS CLAVES — FASE 3A ───────────────────────────────
                'saludo'                        => $this->obtenerSaludoUsuario($usuario),
                'kpisInstitucionales'           => $this->obtenerKpisInstitucionales(),
                'resumenSalud'                  => $this->obtenerResumenSalud(),
                'alertasEstructuradas'          => $this->obtenerAlertasEstructuradas(),
                'equipoInstitucional'           => $this->obtenerEquipoInstitucional(),
                'adultosPorEstado'              => $this->obtenerAdultosPorEstado(),
                'distribucionEquipoInstitucional' => $this->obtenerDistribucionEquipoInstitucional(),

                // ── NUEVAS CLAVES — FASE 3B COMPLEMENTO ─────────────────
                'redFamiliar'                   => $this->obtenerRedFamiliar(),

                // ── CLAVES EXISTENTES — COMPATIBILIDAD CON VISTA ACTUAL ──
                'estadisticas'           => $this->obtenerEstadisticas($usuario),
                'usuariosPorRol'         => $this->obtenerDistribucionRoles(),
                'actividadMensual'       => $this->obtenerActividadMensual(),
                'usuariosDashboard'      => $this->obtenerListaUsuarios(),
                'actividadesDashboard'   => $this->obtenerUltimasActividades(),
                'alertasAdministrativas' => $this->generarAlertasInteligentes(),
                'modulos'                => $this->obtenerModulosInstitucionales(),
                'bitacoraDashboard'      => $this->obtenerBitacoraAuditoria(),
                'infoRol'                => [
                    'es_admin'   => $esAdmin,
                    'es_salud'   => $esSalud,
                    'nombre_rol' => $usuario->getRoleNames()->first() ?? 'Usuario',
                ],
            ];
        });
    }

    // ── NUEVOS MÉTODOS — FASE 3A ──────────────────────────────────────────────

    public function obtenerSaludoUsuario($usuario): array
    {
        $partes = array_filter([
            $usuario->nombres    ?? null,
            $usuario->ap_paterno ?? null,
            $usuario->ap_materno ?? null,
        ]);

        $nombre = !empty($partes)
            ? trim(implode(' ', $partes))
            : ($usuario->name ?? $usuario->correo ?? 'Usuario del sistema');

        $rolesLegibles = [
            'SUPERADMINISTRADOR'      => 'Superadministrador',
            'ADMINISTRADOR'           => 'Administrador',
            'ENFERMEROS'              => 'Enfermero/a',
            'MEDICO GENERAL/GERIATRA' => 'Médico',
            'PSICOLOGO/A'             => 'Psicólogo/a',
            'PEDAGOGO'                => 'Pedagogo/a',
            'NUTRICIONISTA'           => 'Nutricionista',
            'FISIOTERAPEUTA'          => 'Fisioterapeuta',
            'VOLUNTARIO'              => 'Voluntario',
            'FAMILIAR'                => 'Familiar',
        ];
        $rolClave   = $usuario->getRoleNames()->first();
        $rolLegible = $rolesLegibles[$rolClave] ?? 'Usuario del sistema';

        $hora   = (int) now()->format('H');
        $saludo = match(true) {
            $hora < 12 => 'Buenos días',
            $hora < 19 => 'Buenas tardes',
            default    => 'Buenas noches',
        };

        Carbon::setLocale('es');
        $fecha = ucfirst(now()->translatedFormat('l, d \d\e F \d\e Y'));

        return compact('nombre', 'rolLegible', 'saludo', 'fecha');
    }

    public function obtenerKpisInstitucionales(): array
    {
        $adultosActivos      = $this->conteoAdultosConEstado('ACTIVO');
        $seguimientoEspecial = $this->conteoAdultosConEstado('SEGUIMIENTO_ESPECIAL');

        $fichasActivas = Schema::hasTable('ficha_medica_adulto')
            ? DB::table('ficha_medica_adulto')
                ->where('estado', 'ACTIVO')
                ->whereNull('deleted_at')
                ->distinct('cod_am')
                ->count('cod_am')
            : 0;

        $personalSalud = Schema::hasTable('personal_salud')
            ? DB::table('personal_salud')->whereNull('deleted_at')->count()
            : 0;

        $personalAdmin = Schema::hasTable('personal_admin')
            ? DB::table('personal_admin')->count()
            : 0;

        $voluntariosActivos = Schema::hasTable('voluntarios')
            ? DB::table('voluntarios')->where('estado', 'ACTIVO')->count()
            : 0;

        return [
            [
                'clave'     => 'adultos_activos',
                'titulo'    => 'Adultos mayores',
                'valor'     => $adultosActivos,
                'subtitulo' => 'Residentes activos',
                'icono'     => 'ph-users-three',
                'color'     => 'azul-profundo',
                'badge'     => $adultosActivos > 0 ? 'Registrados' : 'Sin registros',
                'nivel'     => 'normal',
            ],
            [
                'clave'     => 'seguimiento_especial',
                'titulo'    => 'Seguimiento especial',
                'valor'     => $seguimientoEspecial,
                'subtitulo' => 'Alerta orientativa',
                'icono'     => 'ph-eye',
                'color'     => 'naranja',
                'badge'     => $seguimientoEspecial > 0 ? 'Requiere atención' : 'Sin novedades',
                'nivel'     => $seguimientoEspecial > 0 ? 'alerta' : 'normal',
            ],
            [
                'clave'     => 'fichas_activas',
                'titulo'    => 'Fichas médicas',
                'valor'     => $fichasActivas,
                'subtitulo' => 'Con ficha activa',
                'icono'     => 'ph-clipboard-text',
                'color'     => 'verde-salud',
                'badge'     => ($adultosActivos > 0 && $fichasActivas >= $adultosActivos)
                    ? 'Al día' : 'Revisar',
                'nivel'     => ($adultosActivos > 0 && $fichasActivas >= $adultosActivos)
                    ? 'ok' : 'advertencia',
            ],
            [
                'clave'     => 'personal_salud',
                'titulo'    => 'Personal de salud',
                'valor'     => $personalSalud,
                'subtitulo' => 'Registrados',
                'icono'     => 'ph-stethoscope',
                'color'     => 'morado-cog',
                'badge'     => 'Equipo activo',
                'nivel'     => 'normal',
            ],
            [
                'clave'     => 'personal_admin',
                'titulo'    => 'Personal administrativo',
                'valor'     => $personalAdmin,
                'subtitulo' => 'Registrados',
                'icono'     => 'ph-briefcase',
                'color'     => 'terracota',
                'badge'     => 'Equipo activo',
                'nivel'     => 'normal',
            ],
            [
                'clave'     => 'voluntarios_activos',
                'titulo'    => 'Voluntarios',
                'valor'     => $voluntariosActivos,
                'subtitulo' => 'Activos',
                'icono'     => 'ph-hand-heart',
                'color'     => 'verde-olivo',
                'badge'     => $voluntariosActivos > 0 ? 'Con participación' : 'Sin voluntarios',
                'nivel'     => 'normal',
            ],
        ];
    }

    public function obtenerResumenSalud(): array
    {
        $fichasActivas = Schema::hasTable('ficha_medica_adulto')
            ? DB::table('ficha_medica_adulto')
                ->where('estado', 'ACTIVO')
                ->whereNull('deleted_at')
                ->distinct('cod_am')
                ->count('cod_am')
            : 0;

        // Adultos activos sin ficha médica activa
        $adultosSinFicha = 0;
        if (
            Schema::hasTable('ficha_medica_adulto') &&
            Schema::hasTable('adulto_mayor') &&
            Schema::hasTable('estado_adulto')
        ) {
            $adultosActivos = $this->conteoAdultosConEstado('ACTIVO');
            $conFicha = DB::table('adulto_mayor')
                ->join('estado_adulto', 'adulto_mayor.cod_est_adul', '=', 'estado_adulto.cod_est_adul')
                ->join('ficha_medica_adulto', 'adulto_mayor.cod_am', '=', 'ficha_medica_adulto.cod_am')
                ->where('estado_adulto.estado', 'ACTIVO')
                ->where('ficha_medica_adulto.estado', 'ACTIVO')
                ->whereNull('ficha_medica_adulto.deleted_at')
                ->distinct('adulto_mayor.cod_am')
                ->count('adulto_mayor.cod_am');
            $adultosSinFicha = max(0, $adultosActivos - $conFicha);
        }

        $medicacionesActivas = Schema::hasTable('medicacion_adulto')
            ? DB::table('medicacion_adulto')
                ->where('estado', 'ACTIVO')
                ->whereNull('deleted_at')
                ->count()
            : 0;

        // atenciones_adulto usa columna 'fecha' (date), no tiene timestamps()
        $atencionesMes = 0;
        if (Schema::hasTable('atenciones_adulto') && Schema::hasColumn('atenciones_adulto', 'fecha')) {
            $atencionesMes = DB::table('atenciones_adulto')
                ->whereYear('fecha', now()->year)
                ->whereMonth('fecha', now()->month)
                ->count();
        }

        // Valoraciones registradas en los últimos 30 días
        $valoracionesRecientes = 0;
        if (
            Schema::hasTable('valoracion_funcional_adulto') &&
            Schema::hasColumn('valoracion_funcional_adulto', 'fecha_valoracion')
        ) {
            $valoracionesRecientes = DB::table('valoracion_funcional_adulto')
                ->where('fecha_valoracion', '>=', now()->subDays(30)->toDateString())
                ->count();
        }

        // Alta dependencia: valoración más reciente por adulto con nivel ALTA_DEPENDENCIA
        // DISTINCT ON es PostgreSQL-compatible y ya se usa en el proyecto
        $altaDependencia = 0;
        if (Schema::hasTable('valoracion_funcional_adulto')) {
            $resultado = DB::select("
                SELECT COUNT(*) AS total
                FROM (
                    SELECT DISTINCT ON (cod_am) cod_am, nivel_dependencia
                    FROM valoracion_funcional_adulto
                    ORDER BY cod_am, fecha_valoracion DESC
                ) AS ultima_val
                WHERE nivel_dependencia = 'ALTA_DEPENDENCIA'
            ");
            $altaDependencia = (int) ($resultado[0]->total ?? 0);
        }

        // Signos vitales registrados en los últimos 7 días
        $signosVitales7d = 0;
        if (
            Schema::hasTable('signos_vitales_adulto') &&
            Schema::hasColumn('signos_vitales_adulto', 'fecha')
        ) {
            $signosVitales7d = DB::table('signos_vitales_adulto')
                ->where('fecha', '>=', now()->subDays(7)->toDateString())
                ->count();
        }

        // Evaluaciones cognitivas en los últimos 30 días
        $evalCognitivas30d = 0;
        if (
            Schema::hasTable('evaluaciones_cognitivas') &&
            Schema::hasColumn('evaluaciones_cognitivas', 'fecha_eval')
        ) {
            $evalCognitivas30d = DB::table('evaluaciones_cognitivas')
                ->where('fecha_eval', '>=', now()->subDays(30)->toDateString())
                ->count();
        }

        // Riesgo de caída alto: valoración más reciente por adulto
        $riesgoCaidaAlto = 0;
        if (
            Schema::hasTable('valoracion_funcional_adulto') &&
            Schema::hasColumn('valoracion_funcional_adulto', 'riesgo_caida')
        ) {
            $resultado = DB::select("
                SELECT COUNT(*) AS total
                FROM (
                    SELECT DISTINCT ON (cod_am) cod_am, riesgo_caida
                    FROM valoracion_funcional_adulto
                    ORDER BY cod_am, fecha_valoracion DESC
                ) AS ultima_val
                WHERE riesgo_caida = 'ALTO'
            ");
            $riesgoCaidaAlto = (int) ($resultado[0]->total ?? 0);
        }

        // Administraciones de medicación registradas hoy
        $adminMedicacionHoy = 0;
        if (
            Schema::hasTable('administracion_medicacion') &&
            Schema::hasColumn('administracion_medicacion', 'fecha')
        ) {
            $adminMedicacionHoy = DB::table('administracion_medicacion')
                ->whereDate('fecha', now()->toDateString())
                ->count();
        }

        return compact(
            'fichasActivas',
            'adultosSinFicha',
            'medicacionesActivas',
            'atencionesMes',
            'valoracionesRecientes',
            'altaDependencia',
            'signosVitales7d',
            'evalCognitivas30d',
            'riesgoCaidaAlto',
            'adminMedicacionHoy'
        );
    }

    public function obtenerAlertasEstructuradas(): array
    {
        $alertas = [];

        // Fuente 1: tabla alertas pendientes
        if (
            Schema::hasTable('alertas') &&
            Schema::hasColumn('alertas', 'estado') &&
            Schema::hasColumn('alertas', 'descripcion')
        ) {
            $alertasDB = DB::table('alertas')
                ->where('estado', 'PENDIENTE')
                ->limit(3)
                ->get();
            foreach ($alertasDB as $a) {
                $alertas[] = [
                    'nivel'       => 'URGENTE',
                    'descripcion' => $a->descripcion,
                    'icono'       => 'ph-warning-octagon',
                    'accion'      => 'Revisar en módulo de alertas',
                ];
            }
        }

        // Fuente 2: adultos activos sin ficha médica activa
        if (
            Schema::hasTable('ficha_medica_adulto') &&
            Schema::hasTable('adulto_mayor') &&
            Schema::hasTable('estado_adulto')
        ) {
            $adultosActivos = $this->conteoAdultosConEstado('ACTIVO');
            if ($adultosActivos > 0) {
                $conFicha = DB::table('adulto_mayor')
                    ->join('estado_adulto', 'adulto_mayor.cod_est_adul', '=', 'estado_adulto.cod_est_adul')
                    ->join('ficha_medica_adulto', 'adulto_mayor.cod_am', '=', 'ficha_medica_adulto.cod_am')
                    ->where('estado_adulto.estado', 'ACTIVO')
                    ->where('ficha_medica_adulto.estado', 'ACTIVO')
                    ->whereNull('ficha_medica_adulto.deleted_at')
                    ->distinct('adulto_mayor.cod_am')
                    ->count('adulto_mayor.cod_am');
                $sinFicha = max(0, $adultosActivos - $conFicha);
                if ($sinFicha > 0) {
                    $etiqueta = $sinFicha === 1
                        ? '1 adulto mayor no tiene ficha médica activa'
                        : "{$sinFicha} adultos mayores no tienen ficha médica activa";
                    $alertas[] = [
                        'nivel'       => 'URGENTE',
                        'descripcion' => "{$etiqueta} — requiere revisión.",
                        'icono'       => 'ph-warning-circle',
                        'accion'      => 'Ir a Salud y Seguimiento',
                    ];
                }
            }
        }

        // Fuente 3: adultos en seguimiento especial
        $seguimiento = $this->conteoAdultosConEstado('SEGUIMIENTO_ESPECIAL');
        if ($seguimiento > 0) {
            $etiqueta = $seguimiento === 1
                ? '1 adulto mayor requiere'
                : "{$seguimiento} adultos mayores requieren";
            $alertas[] = [
                'nivel'       => 'URGENTE',
                'descripcion' => "{$etiqueta} seguimiento especial — alerta orientativa.",
                'icono'       => 'ph-eye',
                'accion'      => 'Ver adultos mayores',
            ];
        }

        // Fuente 4: alta dependencia funcional (valoración más reciente por adulto)
        if (Schema::hasTable('valoracion_funcional_adulto')) {
            $resultado = DB::select("
                SELECT COUNT(*) AS total
                FROM (
                    SELECT DISTINCT ON (cod_am) cod_am, nivel_dependencia
                    FROM valoracion_funcional_adulto
                    ORDER BY cod_am, fecha_valoracion DESC
                ) AS ultima_val
                WHERE nivel_dependencia = 'ALTA_DEPENDENCIA'
            ");
            $altaDep = (int) ($resultado[0]->total ?? 0);
            if ($altaDep > 0) {
                $etiqueta = $altaDep === 1
                    ? '1 adulto mayor clasificado'
                    : "{$altaDep} adultos mayores clasificados";
                $alertas[] = [
                    'nivel'       => 'INFORMATIVA',
                    'descripcion' => "{$etiqueta} con alta dependencia funcional — seguimiento pendiente.",
                    'icono'       => 'ph-info',
                    'accion'      => 'Revisar valoraciones funcionales',
                ];
            }
        }

        // Fuente 5: sin actividades registradas hoy
        if (
            Schema::hasTable('actividades_adulto') &&
            Schema::hasColumn('actividades_adulto', 'fecha')
        ) {
            $actividadesHoy = DB::table('actividades_adulto')
                ->whereDate('fecha', now()->toDateString())
                ->count();
            if ($actividadesHoy === 0) {
                $alertas[] = [
                    'nivel'       => 'INFORMATIVA',
                    'descripcion' => 'No se han registrado actividades institucionales para hoy — alerta orientativa.',
                    'icono'       => 'ph-calendar-x',
                    'accion'      => 'Registrar actividad',
                ];
            }
        }

        if (empty($alertas)) {
            $alertas[] = [
                'nivel'       => 'OK',
                'descripcion' => 'El sistema no presenta alertas administrativas pendientes.',
                'icono'       => 'ph-check-circle',
                'accion'      => null,
            ];
        }

        return $alertas;
    }

    public function obtenerEquipoInstitucional(): array
    {
        // ── PERSONAL DE SALUD ─────────────────────────────────────────
        $psTotales      = 0;
        $psActivos      = 0;
        $psSinEsp       = 0;
        $especialidades = [];
        $especialidadesTotal = 0;

        if (Schema::hasTable('personal_salud')) {
            $psTotales = DB::table('personal_salud')->whereNull('deleted_at')->count();
            $psActivos = DB::table('personal_salud')
                ->where('estado_laboral', 'ACTIVO')
                ->whereNull('deleted_at')
                ->count();
            $psSinEsp = DB::table('personal_salud')
                ->whereNull('cod_esp')
                ->whereNull('deleted_at')
                ->count();

            if (Schema::hasTable('especialidades')) {
                $especialidadesTotal = DB::table('especialidades')->count();

                $especialidades = DB::table('especialidades')
                    ->leftJoin('personal_salud', function ($join) {
                        $join->on('especialidades.cod_esp', '=', 'personal_salud.cod_esp')
                             ->whereNull('personal_salud.deleted_at');
                    })
                    ->selectRaw('especialidades.nombre, COUNT(personal_salud.cod_per_sal) as total')
                    ->groupBy('especialidades.cod_esp', 'especialidades.nombre')
                    ->orderByDesc('total')
                    ->get()
                    ->map(fn($e) => [
                        'nombre' => $e->nombre,
                        'total'  => (int) $e->total,
                    ])
                    ->toArray();
            }
        }

        // ── PERSONAL ADMINISTRATIVO ───────────────────────────────────
        // personal_admin NO tiene soft deletes
        $paTotales = 0;
        $paActivos = 0;
        $cargos    = [];

        if (Schema::hasTable('personal_admin')) {
            $paTotales = DB::table('personal_admin')->count();
            $paActivos = DB::table('personal_admin')
                ->where('estado_laboral', 'ACTIVO')
                ->count();

            // Usar cargos_administrativos si la columna FK ya fue migrada
            $usarCargosTabla = Schema::hasTable('cargos_administrativos') &&
                               Schema::hasColumn('personal_admin', 'cod_cargo_admin');

            if ($usarCargosTabla) {
                $cargos = DB::table('personal_admin')
                    ->leftJoin('cargos_administrativos',
                        'personal_admin.cod_cargo_admin', '=', 'cargos_administrativos.cod_cargo_admin')
                    ->selectRaw('COALESCE(cargos_administrativos.nombre, personal_admin.cargo) AS cargo_nombre, COUNT(*) AS total')
                    ->groupBy('cargo_nombre')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get()
                    ->map(fn($c) => [
                        'nombre' => $c->cargo_nombre ?? 'Sin cargo',
                        'total'  => (int) $c->total,
                    ])
                    ->toArray();
            } else {
                $cargos = DB::table('personal_admin')
                    ->selectRaw('cargo AS cargo_nombre, COUNT(*) AS total')
                    ->groupBy('cargo')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get()
                    ->map(fn($c) => [
                        'nombre' => $c->cargo_nombre ?? 'Sin cargo',
                        'total'  => (int) $c->total,
                    ])
                    ->toArray();
            }
        }

        // ── VOLUNTARIOS ───────────────────────────────────────────────
        // voluntarios NO tiene soft deletes
        $volTotal    = 0;
        $volActivos  = 0;
        $volAsignados = 0;
        $volAreas    = [];

        if (Schema::hasTable('voluntarios')) {
            $volTotal   = DB::table('voluntarios')->count();
            $volActivos = DB::table('voluntarios')->where('estado', 'ACTIVO')->count();

            $volAreas = DB::table('voluntarios')
                ->selectRaw('area_apoyo, COUNT(*) AS total')
                ->groupBy('area_apoyo')
                ->orderByDesc('total')
                ->limit(4)
                ->get()
                ->map(fn($v) => [
                    'area'  => $v->area_apoyo ?? 'Sin área',
                    'total' => (int) $v->total,
                ])
                ->toArray();

            if (Schema::hasTable('asignacion_voluntarios')) {
                $volAsignados = DB::table('asignacion_voluntarios')
                    ->where('estado', 'ACTIVO')
                    ->distinct('cod_vol')
                    ->count('cod_vol');
            }
        }

        return [
            'personal_salud' => [
                'total'           => $psTotales,
                'activos'         => $psActivos,
                'sin_especialidad' => $psSinEsp,
                'especialidades'  => $especialidades,
            ],
            'personal_admin' => [
                'total'   => $paTotales,
                'activos' => $paActivos,
                'cargos'  => $cargos,
            ],
            'voluntarios' => [
                'total'    => $volTotal,
                'activos'  => $volActivos,
                'asignados' => $volAsignados,
                'areas'    => $volAreas,
            ],
            'especialidades_total' => $especialidadesTotal,
        ];
    }

    public function obtenerAdultosPorEstado(): array
    {
        if (!Schema::hasTable('adulto_mayor') || !Schema::hasTable('estado_adulto')) {
            return ['labels' => [], 'data' => [], 'colores' => []];
        }

        $distribucion = DB::table('adulto_mayor')
            ->join('estado_adulto', 'adulto_mayor.cod_est_adul', '=', 'estado_adulto.cod_est_adul')
            ->selectRaw('estado_adulto.estado, COUNT(*) AS total')
            ->groupBy('estado_adulto.estado')
            ->get()
            ->keyBy('estado');

        $activo = (int) ($distribucion->get('ACTIVO')->total ?? 0);
        $seguim = (int) ($distribucion->get('SEGUIMIENTO_ESPECIAL')->total ?? 0);
        $otros  = (int) $distribucion
            ->filter(fn($item) => !in_array($item->estado, ['ACTIVO', 'SEGUIMIENTO_ESPECIAL']))
            ->sum('total');

        $labels  = [];
        $data    = [];
        $colores = [];

        if ($activo > 0) { $labels[] = 'Activos';              $data[] = $activo; $colores[] = '#2F3E5C'; }
        if ($seguim > 0) { $labels[] = 'Seguimiento especial'; $data[] = $seguim; $colores[] = '#F4A261'; }
        if ($otros  > 0) { $labels[] = 'Otros';                $data[] = $otros;  $colores[] = '#C7B5A3'; }

        return compact('labels', 'data', 'colores');
    }

    public function obtenerDistribucionEquipoInstitucional(): array
    {
        $salud  = Schema::hasTable('personal_salud') ? DB::table('personal_salud')->whereNull('deleted_at')->count() : 0;
        $admin  = Schema::hasTable('personal_admin')  ? DB::table('personal_admin')->count() : 0;
        $volunt = Schema::hasTable('voluntarios')     ? DB::table('voluntarios')->count() : 0;

        return [
            'labels' => ['Personal de Salud', 'Personal Administrativo', 'Voluntarios'],
            'data'   => [$salud, $admin, $volunt],
            'colores' => ['#9B8AC7', '#E97A5F', '#8DA280'],
        ];
    }

    public function obtenerRedFamiliar(): array
    {
        $totalFamiliares    = 0;
        $adultosConFamiliar = 0;
        $adultosSinFamiliar = 0;
        $responsables       = 0;
        $parentescos        = [];

        if (Schema::hasTable('familiares')) {
            $totalFamiliares = DB::table('familiares')->count();

            if (Schema::hasColumn('familiares', 'parentesco')) {
                $parentescos = DB::table('familiares')
                    ->selectRaw('parentesco, COUNT(*) AS total')
                    ->whereNotNull('parentesco')
                    ->groupBy('parentesco')
                    ->orderByDesc('total')
                    ->limit(5)
                    ->get()
                    ->map(fn($p) => [
                        'parentesco' => $p->parentesco,
                        'total'      => (int) $p->total,
                    ])
                    ->toArray();
            }
        }

        if (Schema::hasTable('familiar_adulto')) {
            $adultosConFamiliar = DB::table('familiar_adulto')
                ->whereNull('deleted_at')
                ->distinct('cod_am')
                ->count('cod_am');

            if (Schema::hasColumn('familiar_adulto', 'es_responsable')) {
                $responsables = DB::table('familiar_adulto')
                    ->whereNull('deleted_at')
                    ->where('es_responsable', true)
                    ->count();
            }
        }

        // Adultos activos sin ningún familiar vinculado
        if (
            Schema::hasTable('adulto_mayor') &&
            Schema::hasTable('estado_adulto') &&
            Schema::hasTable('familiar_adulto')
        ) {
            $adultosActivos = $this->conteoAdultosConEstado('ACTIVO');
            $activosConFam  = DB::table('adulto_mayor')
                ->join('estado_adulto', 'adulto_mayor.cod_est_adul', '=', 'estado_adulto.cod_est_adul')
                ->join('familiar_adulto', 'adulto_mayor.cod_am', '=', 'familiar_adulto.cod_am')
                ->where('estado_adulto.estado', 'ACTIVO')
                ->whereNull('familiar_adulto.deleted_at')
                ->distinct('adulto_mayor.cod_am')
                ->count('adulto_mayor.cod_am');
            $adultosSinFamiliar = max(0, $adultosActivos - $activosConFam);
        }

        return compact('totalFamiliares', 'adultosConFamiliar', 'adultosSinFamiliar', 'responsables', 'parentescos');
    }

    // ── HELPER PRIVADO — FASE 3A ──────────────────────────────────────────────

    private function conteoAdultosConEstado(string $estado): int
    {
        if (!Schema::hasTable('adulto_mayor') || !Schema::hasTable('estado_adulto')) return 0;

        return DB::table('adulto_mayor')
            ->join('estado_adulto', 'adulto_mayor.cod_est_adul', '=', 'estado_adulto.cod_est_adul')
            ->where('estado_adulto.estado', $estado)
            ->count();
    }

    // ── MÉTODOS EXISTENTES SIN CAMBIOS ───────────────────────────────────────

    private function obtenerEstadisticas($usuario): array
    {
        return [
            'usuarios_activos'        => $this->conteoSeguro('users', 'estado', 'ACTIVO'),
            'adultos_mayores'         => $this->conteoSeguro('adulto_mayor'),
            'voluntarios'             => $this->conteoSeguro('voluntarios'),
            'alertas_pendientes'      => $this->conteoSeguro('alertas', 'estado', 'PENDIENTE'),
            'usuarios_con_rol'        => $this->conteoSeguro('model_has_roles'),
            'adultos_con_familiar'    => $this->conteoSeguro('familiar_adulto'),
            'voluntarios_asignados'   => $this->conteoSeguro('asignacion_voluntarios'),
            'actividades_programadas' => $this->conteoSeguro('actividades_adulto'),
        ];
    }

    private function obtenerDistribucionRoles(): array
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('model_has_roles')) {
            return ['labels' => [], 'data' => []];
        }

        $roles = DB::table('roles')
            ->leftJoin('model_has_roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->selectRaw('roles.name, count(model_has_roles.model_id) as total')
            ->groupBy('roles.name')
            ->get();

        return [
            'labels' => $roles->pluck('name')->toArray(),
            'data'   => $roles->pluck('total')->toArray(),
        ];
    }

    private function obtenerActividadMensual(): array
    {
        $meses   = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $conteos = array_fill(0, 12, 0);

        if (Schema::hasTable('activity_log') && Schema::hasColumn('activity_log', 'created_at')) {
            $actividad = DB::table('activity_log')
                ->selectRaw('EXTRACT(MONTH FROM created_at) as mes, count(*) as total')
                ->whereYear('created_at', date('Y'))
                ->groupBy('mes')
                ->get();

            foreach ($actividad as $item) {
                $indice = (int) $item->mes - 1;
                if (isset($conteos[$indice])) {
                    $conteos[$indice] = (int) $item->total;
                }
            }
        }

        return ['labels' => $meses, 'data' => $conteos];
    }

    private function obtenerListaUsuarios(): array
    {
        if (!Schema::hasTable('users')) return [];

        return DB::table('users')
            ->select('nombres', 'ap_paterno', 'correo', 'estado', 'cod_usu')
            ->orderByRaw('cod_usu DESC')
            ->limit(10)
            ->get()
            ->map(fn($u) => [
                'cod_usu' => $u->cod_usu,
                'nombre'  => trim($u->nombres . ' ' . $u->ap_paterno),
                'correo'  => $u->correo,
                'estado'  => $u->estado ?? 'ACTIVO',
                'rol'     => $this->obtenerRolPrimario($u->cod_usu),
            ])
            ->toArray();
    }

    private function obtenerUltimasActividades(): array
    {
        if (!Schema::hasTable('actividades_adulto')) return [];

        $query = DB::table('actividades_adulto')
            ->join('adulto_mayor', 'actividades_adulto.cod_am', '=', 'adulto_mayor.cod_am')
            ->select('actividades_adulto.*', 'adulto_mayor.nombres as adulto');

        if (Schema::hasColumn('actividades_adulto', 'created_at')) {
            $query->orderByDesc('actividades_adulto.created_at');
        } elseif (Schema::hasColumn('actividades_adulto', 'fecha')) {
            $query->orderByDesc('actividades_adulto.fecha');
        }

        return $query->limit(5)
            ->get()
            ->map(fn($a) => [
                'titulo'  => 'Actividad: ' . ($a->tipo_actividad ?? 'General'),
                'detalle' => ($a->adulto ?? 'S/N') . ' - ' . (isset($a->fecha) ? Carbon::parse($a->fecha)->format('d/m/Y') : 'Hoy'),
                'icono'   => 'ph-calendar-check',
            ])
            ->toArray();
    }

    private function generarAlertasInteligentes(): array
    {
        $alertas = [];

        if (Schema::hasTable('alertas')) {
            $alertas = DB::table('alertas')
                ->where('estado', 'PENDIENTE')
                ->limit(3)
                ->pluck('descripcion')
                ->toArray();
        }

        if (count($alertas) < 5) {
            if ($this->conteoSeguro('users') > $this->conteoSeguro('model_has_roles')) {
                $alertas[] = "Hay usuarios en el sistema sin un rol asignado.";
            }
            if ($this->conteoSeguro('adulto_mayor') > $this->conteoSeguro('asignacion_voluntarios')) {
                $alertas[] = "Existen adultos mayores sin voluntarios asignados actualmente.";
            }
            if ($this->conteoSeguro('actividades_adulto', 'fecha', date('Y-m-d')) == 0) {
                $alertas[] = "No se han registrado actividades para la fecha de hoy.";
            }
            if ($this->conteoSeguro('adulto_mayor') > $this->conteoSeguro('familiar_adulto')) {
                $alertas[] = "Se detectaron registros de adultos mayores sin vínculos familiares.";
            }
            if ($this->conteoSeguro('obs_adulto') == 0) {
                $alertas[] = "No existen observaciones clínicas registradas recientemente.";
            }
        }

        return count($alertas) > 0 ? $alertas : ["El sistema no presenta alertas administrativas pendientes."];
    }

    private function obtenerModulosInstitucionales(): array
    {
        return [
            ['titulo' => 'Usuarios',       'descripcion' => 'Control de accesos y perfiles',    'icono' => 'ph-users-three',        'ruta' => route('admin.usuarios.index')],
            ['titulo' => 'Adultos Mayores','descripcion' => 'Seguimiento y fichas clínicas',     'icono' => 'ph-identification-card', 'ruta' => route('admin.adultos-mayores.index')],
            ['titulo' => 'Personal',       'descripcion' => 'Gestión de RRHH y especialistas',  'icono' => 'ph-stethoscope',         'ruta' => '#'],
            ['titulo' => 'Voluntarios',    'descripcion' => 'Apoyo social y asistencias',        'icono' => 'ph-hand-heart',          'ruta' => '#'],
            ['titulo' => 'Actividades',    'descripcion' => 'Planificación de talleres diarios', 'icono' => 'ph-calendar-check',      'ruta' => '#'],
            ['titulo' => 'Evaluaciones',   'descripcion' => 'Análisis cognitivo y emocional',    'icono' => 'ph-brain',               'ruta' => '#'],
        ];
    }

    public function obtenerBitacoraAuditoria(): array
    {
        if (!Schema::hasTable('activity_log')) return [];

        Carbon::setLocale('es');

        $query = DB::table('activity_log')
            ->leftJoin('users',
                DB::raw('activity_log.causer_id::text'),
                '=',
                DB::raw('users.cod_usu::text')
            )
            ->select('activity_log.*', 'users.nombres as usuario_nombre');

        if (Schema::hasColumn('activity_log', 'created_at')) {
            $query->orderByDesc('activity_log.created_at');
        }

        return $query->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'fecha'   => isset($log->created_at) ? Carbon::parse($log->created_at)->diffForHumans() : '-',
                    'usuario' => $log->usuario_nombre ?? 'Sistema',
                    'accion'  => $this->traducirEvento($log->event),
                    'modulo'  => $this->traducirModulo($log->log_name),
                    'detalle' => $this->limpiarDescripcion($log->description),
                ];
            })
            ->toArray();
    }

    private function traducirEvento(?string $evento): string
    {
        return match($evento) {
            'created', 'registro' => 'Registro creado',
            'updated', 'edicion'  => 'Registro actualizado',
            'deleted', 'borrado'  => 'Registro eliminado',
            'restored'            => 'Registro restaurado',
            'accessed', 'acceso'  => 'Acceso al sistema',
            'login'               => 'Inicio de sesión',
            'logout'              => 'Cierre de sesión',
            'assigned'            => 'Asignación realizada',
            default               => 'Acción registrada',
        };
    }

    private function traducirModulo(?string $modulo): string
    {
        return match($modulo) {
            'dashboard', 'Panel principal' => 'Panel principal',
            'users', 'usuarios'            => 'Usuarios',
            'roles'                        => 'Roles',
            'evaluaciones'                 => 'Evaluaciones',
            'actividades'                  => 'Actividades',
            'asignaciones'                 => 'Asignaciones',
            'voluntarios'                  => 'Voluntarios',
            'default'                      => 'General',
            default                        => $modulo ?? 'General',
        };
    }

    private function limpiarDescripcion(string $descripcion): string
    {
        $traducciones = [
            'User updated'                      => 'Usuario actualizado',
            'Accessed dashboard'                => 'Acceso al panel principal',
            'Access to institutional dashboard' => 'Acceso al dashboard institucional',
            'Acceso al dashboard institucional' => 'Acceso al panel institucional',
        ];

        return $traducciones[$descripcion] ?? $descripcion;
    }

    private function conteoSeguro(string $tabla, ?string $columna = null, ?string $valor = null): int
    {
        if (!Schema::hasTable($tabla)) return 0;

        $query = DB::table($tabla);
        if ($columna && $valor && Schema::hasColumn($tabla, $columna)) {
            $query->where($columna, $valor);
        }

        return $query->count();
    }

    private function obtenerRolPrimario($cod_usu): string
    {
        if (!Schema::hasTable('model_has_roles') || !Schema::hasTable('roles')) {
            return 'Sin rol';
        }

        return DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('model_has_roles.model_id', $cod_usu)
            ->value('roles.name') ?? 'Sin rol';
    }

    public function limpiarCache($idUsuario): void
    {
        Cache::forget('dashboard_' . $idUsuario);
    }
}
