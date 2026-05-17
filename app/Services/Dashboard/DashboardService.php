<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * DashboardService
 * 
 * Servicio de alto nivel para la analítica institucional de Casa Amandita.
 * Implementa optimización por caché, validación de esquemas y lógica multi-rol.
 * 
 * @author Arquitecto Senior Laravel
 * @version 3.0 (Profesional)
 */
class DashboardService
{
    /**
     * Obtiene el conjunto completo de datos para el dashboard con optimización por caché.
     * 
     * @param mixed $usuario Usuario autenticado
     * @return array
     */
    public function obtenerDatosDashboard($usuario)
    {
        // Clave única por usuario para evitar colisiones de datos
        $claveCache = 'dashboard_' . $usuario->cod_usu;

        return Cache::remember($claveCache, 60, function () use ($usuario) {
            
            // Preparación de lógica multi-rol para futuras etapas
            $esAdmin = $usuario->hasRole('admin');
            $esSalud = $usuario->hasRole('personal_salud');
            
            // Estructura de datos estandarizada en ESPAÑOL
            $datos = [
                'estadisticas'           => $this->obtenerEstadisticas($usuario),
                'usuariosPorRol'         => $this->obtenerDistribucionRoles(),
                'actividadMensual'       => $this->obtenerActividadMensual(),
                'usuariosDashboard'      => $this->obtenerListaUsuarios(),
                'actividadesDashboard'   => $this->obtenerUltimasActividades(),
                'alertasAdministrativas' => $this->generarAlertasInteligentes(),
                'modulos'                => $this->obtenerModulosInstitucionales(),
                'bitacoraDashboard'      => $this->obtenerBitacoraAuditoria(),
                'infoRol'                => [
                    'es_admin' => $esAdmin,
                    'es_salud' => $esSalud,
                    'nombre_rol' => $usuario->getRoleNames()->first() ?? 'Usuario'
                ]
            ];

            return $datos;
        });
    }

    /**
     * Obtiene métricas optimizadas usando selectRaw para reducir impacto en BD.
     */
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

    /**
     * Obtiene la distribución de roles utilizando Spatie Permissions.
     */
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

    /**
     * Analítica de actividad mensual agrupada.
     */
    private function obtenerActividadMensual(): array
    {
        $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $conteos = array_fill(0, 12, 0);

        if (Schema::hasTable('activity_log') && Schema::hasColumn('activity_log', 'created_at')) {
            $actividad = DB::table('activity_log')
                ->selectRaw('EXTRACT(MONTH FROM created_at) as mes, count(*) as total')
                ->whereYear('created_at', date('Y'))
                ->groupBy('mes')
                ->get();

            foreach ($actividad as $item) {
                $indice = (int)$item->mes - 1;
                if (isset($conteos[$indice])) {
                    $conteos[$indice] = (int)$item->total;
                }
            }
        }

        return [
            'labels' => $meses,
            'data'   => $conteos,
        ];
    }

    /**
     * Recupera los 10 usuarios más recientes con su rol principal.
     */
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
                'rol'     => $this->obtenerRolPrimario($u->cod_usu)
            ])
            ->toArray();
    }

    /**
     * Obtiene el historial reciente de actividades registradas.
     */
    private function obtenerUltimasActividades(): array
    {
        if (!Schema::hasTable('actividades_adulto')) return [];

        $query = DB::table('actividades_adulto')
            ->join('adulto_mayor', 'actividades_adulto.cod_am', '=', 'adulto_mayor.cod_am')
            ->select('actividades_adulto.*', 'adulto_mayor.nombres as adulto');

        // Validación dinámica de columnas temporales
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
                'icono'   => 'ph-calendar-check'
            ])
            ->toArray();
    }

    /**
     * Motor de alertas inteligentes basado en consistencia y vacíos de datos.
     */
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

        // Lógica de detección de brechas operativas
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

    /**
     * Módulos institucionales estandarizados.
     */
    private function obtenerModulosInstitucionales(): array
    {
        return [
            ['titulo' => 'Usuarios', 'descripcion' => 'Control de accesos y perfiles', 'icono' => 'ph-users-three', 'ruta' => route('admin.usuarios.index')],
            ['titulo' => 'Adultos Mayores', 'descripcion' => 'Seguimiento y fichas clínicas', 'icono' => 'ph-identification-card', 'ruta' => route('admin.adultos-mayores.index')],
            ['titulo' => 'Personal', 'descripcion' => 'Gestión de RRHH y especialistas', 'icono' => 'ph-stethoscope', 'ruta' => '#'],
            ['titulo' => 'Voluntarios', 'descripcion' => 'Apoyo social y asistencias', 'icono' => 'ph-hand-heart', 'ruta' => '#'],
            ['titulo' => 'Actividades', 'descripcion' => 'Planificación de talleres diarios', 'icono' => 'ph-calendar-check', 'ruta' => '#'],
            ['titulo' => 'Evaluaciones', 'descripcion' => 'Análisis cognitivo y emocional', 'icono' => 'ph-brain', 'ruta' => '#'],
        ];
    }

    /**
     * Auditoría de movimientos del sistema traducida a ESPAÑOL.
     */
    public function obtenerBitacoraAuditoria(): array
    {
        if (!Schema::hasTable('activity_log')) return [];

        // Asegurar que Carbon esté en español para tiempos relativos
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
            ->map(function($log) {
                return [
                    'fecha'   => isset($log->created_at) ? Carbon::parse($log->created_at)->diffForHumans() : '-',
                    'usuario' => $log->usuario_nombre ?? 'Sistema',
                    'accion'  => $this->traducirEvento($log->event),
                    'modulo'  => $this->traducirModulo($log->log_name),
                    'detalle' => $this->limpiarDescripcion($log->description)
                ];
            })
            ->toArray();
    }

    /**
     * Helper de traducción para consistencia institucional.
     */
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
            default               => 'Acción registrada'
        };
    }

    /**
     * Traduce los nombres técnicos de los logs a nombres de módulos institucionales.
     */
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
            default                        => $modulo ?? 'General'
        };
    }

    /**
     * Limpia y traduce descripciones técnicas.
     */
    private function limpiarDescripcion(string $descripcion): string
    {
        $traducciones = [
            'User updated'                          => 'Usuario actualizado',
            'Accessed dashboard'                    => 'Acceso al panel principal',
            'Access to institutional dashboard'     => 'Acceso al dashboard institucional',
            'Acceso al dashboard institucional'     => 'Acceso al panel institucional',
        ];

        return $traducciones[$descripcion] ?? $descripcion;
    }

    /**
     * Conteo resiliente con validación de existencia de tablas y columnas.
     */
    private function conteoSeguro(string $tabla, ?string $columna = null, ?string $valor = null): int
    {
        if (!Schema::hasTable($tabla)) return 0;

        $query = DB::table($tabla);
        if ($columna && $valor && Schema::hasColumn($tabla, $columna)) {
            $query->where($columna, $valor);
        }

        return $query->count();
    }

    /**
     * Obtiene el rol primario de un usuario mediante Spatie.
     */
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

    /**
     * Método para invalidación manual de caché.
     * Uso: app(DashboardService::class)->limpiarCache(auth()->id());
     */
    public function limpiarCache($idUsuario)
    {
        Cache::forget('dashboard_' . $idUsuario);
    }
}
