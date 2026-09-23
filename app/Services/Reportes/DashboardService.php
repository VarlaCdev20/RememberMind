<?php

namespace App\Services\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Personal;

class DashboardService
{
    public function obtenerDatosDashboard($usuario): array
    {
        $claveCache = 'dashboard_' . $usuario->cod_usu;

        return Cache::remember($claveCache, 60, function () use ($usuario) {
            $esAdmin = $usuario->hasRole(['SUPERADMINISTRADOR', 'ADMINISTRADOR']);
            $esSalud = $usuario->hasRole(['ENFERMEROS', 'MEDICO GENERAL/GERIATRA']);

            return [
                'saludo'                          => $this->obtenerSaludoUsuario($usuario),
                'kpisInstitucionales'             => $this->obtenerKpisInstitucionales(),
                'resumenSalud'                    => $this->obtenerResumenSalud(),
                'alertasEstructuradas'            => $this->obtenerAlertasEstructuradas(),
                'equipoInstitucional'             => $this->obtenerEquipoInstitucional(),
                'adultosPorEstado'                => $this->obtenerAdultosPorEstado(),
                'distribucionEquipoInstitucional' => $this->obtenerDistribucionEquipoInstitucional(),
                'redFamiliar'                     => $this->obtenerRedFamiliar(),
                'estadisticas'                    => $this->obtenerEstadisticas($usuario),
                'usuariosPorRol'                  => $this->obtenerDistribucionRoles(),
                'actividadMensual'                => $this->obtenerActividadMensual(),
                'usuariosDashboard'               => $this->obtenerListaUsuarios(),
                'actividadesDashboard'            => $this->obtenerUltimasActividades(),
                'alertasAdministrativas'          => $this->generarAlertasInteligentes(),
                'modulos'                         => $this->obtenerModulosInstitucionales(),
                'bitacoraDashboard'               => $this->obtenerBitacoraAuditoria(),
                'infoRol'                         => [
                    'es_admin'   => $esAdmin,
                    'es_salud'   => $esSalud,
                    'nombre_rol' => $usuario->getRoleNames()->first() ?? 'Usuario',
                ],
            ];
        });
    }

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

        $fichasActivas = DB::table('atenciones')
            ->where('estado', 'ACTIVA')
            ->distinct('cod_residente')
            ->count('cod_residente');

        $personalSalud = Personal::where('estado', 'ACTIVO')
            ->whereIn('profesion', ['ENFERMERÍA', 'MEDICINA', 'PSICOLOGÍA', 'NUTRICIÓN', 'FISIOTERAPIA'])
            ->count();

        $personalAdmin = Personal::where('estado', 'ACTIVO')
            ->whereNotIn('profesion', ['ENFERMERÍA', 'MEDICINA', 'PSICOLOGÍA', 'NUTRICIÓN', 'FISIOTERAPIA'])
            ->count();

        $prescripcionesActivas = DB::table('prescripciones')
            ->where('estado', 'ACTIVA')
            ->count();

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
                'titulo'    => 'Fichas clínicas',
                'valor'     => $fichasActivas,
                'subtitulo' => 'Con atención activa',
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
                'clave'     => 'prescripciones_activas',
                'titulo'    => 'Prescripciones',
                'valor'     => $prescripcionesActivas,
                'subtitulo' => 'Órdenes médicas vigentes',
                'icono'     => 'ph-pill',
                'color'     => 'verde-olivo',
                'badge'     => $prescripcionesActivas > 0 ? 'Vigentes' : 'Sin órdenes',
                'nivel'     => 'normal',
            ],
        ];
    }

    public function obtenerResumenSalud(): array
    {
        $adultosActivos = $this->conteoAdultosConEstado('ACTIVO');

        $fichasActivas = DB::table('atenciones')
            ->where('estado', 'ACTIVA')
            ->distinct('cod_residente')
            ->count('cod_residente');

        $adultosSinFicha = max(0, $adultosActivos - $fichasActivas);

        $medicacionesActivas = DB::table('prescripciones')
            ->where('estado', 'ACTIVA')
            ->count();

        $atencionesMes = DB::table('atenciones')
            ->whereYear('fecha_hora', now()->year)
            ->whereMonth('fecha_hora', now()->month)
            ->count();

        $valoracionesRecientes = DB::table('aplicaciones_instrumento')
            ->where('fecha_hora', '>=', now()->subDays(30))
            ->count();

        $altaDependencia = DB::table('valoraciones_funcionales')
            ->where('nivel_dependencia', 'ALTA_DEPENDENCIA')
            ->count();

        $signosVitales7d = DB::table('signos_vitales')
            ->where('fecha_hora', '>=', now()->subDays(7))
            ->count();

        $evalCognitivas30d = DB::table('aplicaciones_instrumento')
            ->where('fecha_hora', '>=', now()->subDays(30))
            ->count();

        $riesgoCaidaAlto = DB::table('registros_movilidad')
            ->where('riesgo_caida', 'ALTO')
            ->count();

        $adminMedicacionHoy = DB::table('administraciones_medicacion')
            ->whereDate('fecha_hora_programada', today())
            ->count();

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

        $alertasDB = DB::table('alertas')
            ->whereIn('estado', ['ABIERTA', 'PENDIENTE', 'EN_PROCESO'])
            ->orderByDesc('fecha_hora')
            ->limit(4)
            ->get();

        foreach ($alertasDB as $a) {
            $alertas[] = [
                'nivel'       => $a->nivel_gravedad ?? 'URGENTE',
                'descripcion' => $a->descripcion,
                'icono'       => 'ph-warning-octagon',
                'accion'      => 'Revisar en módulo de alertas',
                'url'         => route('admin.alertas.index'),
            ];
        }

        $adultosActivos = $this->conteoAdultosConEstado('ACTIVO');
        $conAtencion = DB::table('atenciones')
            ->where('estado', 'ACTIVA')
            ->distinct('cod_residente')
            ->count('cod_residente');
        $sinFicha = max(0, $adultosActivos - $conAtencion);
        if ($sinFicha > 0) {
            $etiqueta = $sinFicha === 1
                ? '1 residente no tiene atención clínica activa'
                : "{$sinFicha} residentes no tienen atención clínica activa";
            $alertas[] = [
                'nivel'       => 'URGENTE',
                'descripcion' => "{$etiqueta} — requiere revisión.",
                'icono'       => 'ph-warning-circle',
                'accion'      => 'Ir a Residentes',
                'url'         => route('admin.residentes.index'),
            ];
        }

        $seguimiento = $this->conteoAdultosConEstado('SEGUIMIENTO_ESPECIAL');
        if ($seguimiento > 0) {
            $etiqueta = $seguimiento === 1
                ? '1 residente requiere'
                : "{$seguimiento} residentes requieren";
            $alertas[] = [
                'nivel'       => 'URGENTE',
                'descripcion' => "{$etiqueta} seguimiento especial — alerta orientativa.",
                'icono'       => 'ph-eye',
                'accion'      => 'Ver residentes',
                'url'         => route('admin.residentes.index'),
            ];
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
        $rolesSalud = [
            'ENFERMEROS',
            'MEDICO GENERAL/GERIATRA',
            'PSICOLOGO/A',
            'PEDAGOGO',
            'NUTRICIONISTA',
            'FISIOTERAPEUTA'
        ];

        $psTotales = User::role($rolesSalud)->count();
        $psActivos = User::role($rolesSalud)->where('estado', 'ACTIVO')->count();

        $especialidades = DB::table('model_has_roles as mhr')
            ->join('roles as r', 'mhr.role_id', '=', 'r.id')
            ->join('usuarios as u', 'mhr.model_id', '=', 'u.cod_usuario')
            ->where('mhr.model_type', '=', User::class)
            ->whereIn('r.name', $rolesSalud)
            ->where('u.estado', '=', 'ACTIVO')
            ->select('r.name as nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('r.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn($e) => [
                'nombre' => $e->nombre,
                'total'  => (int) $e->total,
            ])
            ->toArray();

        $rolesAdmin = [
            'SUPERADMINISTRADOR',
            'ADMINISTRADOR'
        ];

        $paTotales = User::role($rolesAdmin)->count();
        $paActivos = User::role($rolesAdmin)->where('estado', 'ACTIVO')->count();

        $cargos = DB::table('model_has_roles as mhr')
            ->join('roles as r', 'mhr.role_id', '=', 'r.id')
            ->join('usuarios as u', 'mhr.model_id', '=', 'u.cod_usuario')
            ->where('mhr.model_type', '=', User::class)
            ->whereIn('r.name', $rolesAdmin)
            ->where('u.estado', '=', 'ACTIVO')
            ->select('r.name as cargo_nombre', DB::raw('COUNT(*) as total'))
            ->groupBy('r.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn($c) => [
                'nombre' => $c->cargo_nombre,
                'total'  => (int) $c->total,
            ])
            ->toArray();

        return [
            'personal_salud' => [
                'total'           => $psTotales,
                'activos'         => $psActivos,
                'sin_especialidad' => 0,
                'especialidades'  => $especialidades,
            ],
            'personal_admin' => [
                'total'   => $paTotales,
                'activos' => $paActivos,
                'cargos'  => $cargos,
            ],
        ];
    }

    public function obtenerAdultosPorEstado(): array
    {
        return DB::table('residentes')
            ->select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->orderByDesc('total')
            ->get()
            ->map(fn($e) => [
                'estado' => $e->estado,
                'total'  => (int) $e->total,
                'color'  => match($e->estado) {
                    'ACTIVO', 'ADMITIDO' => 'verde',
                    'BAJA', 'FALLECIDO'  => 'rojo',
                    'HOSPITALIZADO'      => 'naranja',
                    default              => 'azul',
                },
            ])
            ->toArray();
    }

    public function obtenerDistribucionEquipoInstitucional(): array
    {
        $ps = Personal::where('estado', 'ACTIVO')
            ->whereIn('profesion', ['ENFERMERÍA', 'MEDICINA', 'PSICOLOGÍA', 'NUTRICIÓN', 'FISIOTERAPIA'])
            ->count();
        $pa = Personal::where('estado', 'ACTIVO')
            ->whereNotIn('profesion', ['ENFERMERÍA', 'MEDICINA', 'PSICOLOGÍA', 'NUTRICIÓN', 'FISIOTERAPIA'])
            ->count();

        return [
            'personal_salud' => $ps,
            'personal_admin' => $pa,
        ];
    }

    public function obtenerRedFamiliar(): array
    {
        $totalContactos = DB::table('contactos')->count();
        $vinculosActivos = DB::table('residentes_contactos')->where('estado', 'ACTIVO')->count();
        $residentesConFamiliar = DB::table('residentes_contactos')->distinct('cod_residente')->count('cod_residente');
        $totalResidentes = DB::table('residentes')->where('estado', 'ACTIVO')->count();
        $residentesSinFamiliar = max(0, $totalResidentes - $residentesConFamiliar);

        return [
            'total_familiares' => $totalContactos,
            'vinculos_activos' => $vinculosActivos,
            'residentes_con_familiar' => $residentesConFamiliar,
            'residentes_sin_familiar' => $residentesSinFamiliar,
        ];
    }

    private function conteoAdultosConEstado(string $estado): int
    {
        return DB::table('residentes')->where('estado', $estado)->count();
    }

    private function obtenerEstadisticas($usuario): array
    {
        return [
            'total_adultos'        => DB::table('residentes')->count(),
            'total_usuarios'       => DB::table('usuarios')->count(),
            'total_atenciones'     => DB::table('atenciones')->count(),
            'total_prescripciones' => DB::table('prescripciones')->count(),
            'total_alertas'        => DB::table('alertas')->whereIn('estado', ['PENDIENTE', 'ABIERTA'])->count(),
        ];
    }

    private function obtenerDistribucionRoles(): array
    {
        return DB::table('model_has_roles as mhr')
            ->join('roles as r', 'mhr.role_id', '=', 'r.id')
            ->select('r.name as rol', DB::raw('COUNT(*) as total'))
            ->groupBy('r.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn($item) => [
                'rol'   => $item->rol,
                'total' => (int) $item->total,
            ])
            ->toArray();
    }

    private function obtenerActividadMensual(): array
    {
        $dateExpr = DB::connection()->getDriverName() === 'sqlite' ? "strftime('%Y-%m', fecha_hora)" : "TO_CHAR(fecha_hora, 'YYYY-MM')";
        return DB::table('atenciones')
            ->select(
                DB::raw("{$dateExpr} as periodo"),
                DB::raw('COUNT(*) as total')
            )
            ->where('fecha_hora', '>=', now()->subMonths(6))
            ->groupBy(DB::raw($dateExpr))
            ->orderBy('periodo')
            ->get()
            ->map(fn($item) => [
                'mes'   => $item->periodo,
                'total' => (int) $item->total,
            ])
            ->toArray();
    }

    private function obtenerListaUsuarios(): array
    {
        return DB::table('usuarios as u')
            ->leftJoin('personal as p', 'u.cod_usuario', '=', 'p.cod_usuario')
            ->select(
                'u.cod_usuario as cod_usu',
                'u.correo',
                'u.estado',
                'p.nombres',
                'p.apellido_paterno as ap_paterno'
            )
            ->orderByDesc('u.cod_usuario')
            ->limit(10)
            ->get()
            ->map(fn($u) => [
                'cod_usu' => $u->cod_usu,
                'nombre'  => trim(($u->nombres ?? '') . ' ' . ($u->ap_paterno ?? '')) ?: $u->correo,
                'correo'  => $u->correo,
                'estado'  => $u->estado ?? 'ACTIVO',
                'rol'     => $this->obtenerRolPrimario($u->cod_usu),
            ])
            ->toArray();
    }

    private function obtenerUltimasActividades(): array
    {
        return DB::table('actividades')
            ->orderByDesc('fecha_hora')
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                'titulo'  => 'Actividad: ' . ($a->tipo ?? 'General'),
                'detalle' => ($a->nombre ?? 'Sin título') . ' - ' . ($a->fecha_hora ? Carbon::parse($a->fecha_hora)->format('d/m/Y') : 'Hoy'),
                'icono'   => 'ph-calendar-check',
            ])
            ->toArray();
    }

    private function generarAlertasInteligentes(): array
    {
        $alertas = DB::table('alertas')
            ->whereIn('estado', ['ABIERTA', 'PENDIENTE'])
            ->limit(5)
            ->pluck('descripcion')
            ->toArray();

        return count($alertas) > 0 ? $alertas : ["El sistema no presenta alertas administrativas pendientes."];
    }

    private function obtenerModulosInstitucionales(): array
    {
        return [
            ['titulo' => 'Usuarios',       'descripcion' => 'Control de accesos y perfiles',    'icono' => 'ph-users-three',        'ruta' => route('admin.usuarios.index')],
            ['titulo' => 'Residentes',     'descripcion' => 'Seguimiento y fichas clínicas',     'icono' => 'ph-identification-card', 'ruta' => route('admin.residentes.index')],
            ['titulo' => 'Personal',       'descripcion' => 'Gestión de RRHH y especialistas',  'icono' => 'ph-stethoscope',         'ruta' => route('admin.personal-institucional')],
            ['titulo' => 'Preadmisiones',  'descripcion' => 'Casos y admisiones formalizadas',   'icono' => 'ph-door',                'ruta' => route('admin.preadmisiones.index')],
            ['titulo' => 'Actividades',    'descripcion' => 'Planificación de talleres diarios', 'icono' => 'ph-calendar-check',      'ruta' => route('admin.actividades.index')],
            ['titulo' => 'Alertas',        'descripcion' => 'Gestión de alertas y eventos',     'icono' => 'ph-warning',             'ruta' => route('admin.alertas.index')],
        ];
    }

    public function obtenerBitacoraAuditoria(): array
    {
        Carbon::setLocale('es');

        return DB::table('activity_log')
            ->leftJoin('usuarios as u', 'activity_log.causer_id', '=', 'u.cod_usuario')
            ->leftJoin('personal as p', 'p.cod_usuario', '=', 'u.cod_usuario')
            ->select('activity_log.*', 'p.nombres as usuario_nombre')
            ->orderByDesc('activity_log.created_at')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'fecha'   => isset($log->created_at) ? Carbon::parse($log->created_at)->diffForHumans() : '-',
                    'usuario' => $log->usuario_nombre ?? 'Sistema',
                    'accion'  => $this->traducirEvento($log->event),
                    'modulo'  => $this->traducirModulo($log->log_name),
                    'detalle' => $this->limpiarDescripcion($log->description ?? ''),
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

    private function obtenerRolPrimario($cod_usu): string
    {
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