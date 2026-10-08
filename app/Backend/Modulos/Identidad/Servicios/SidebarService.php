<?php

namespace App\Backend\Modulos\Identidad\Servicios;

use App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService;
use App\Models\Alerta;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class SidebarService
{
    private const SUPERADMIN_ROLES = ['SUPERADMINISTRADOR'];

    /**
     * Rutas que existen en el router pero apuntan a DashboardController@index
     * como stub temporal. Se ocultan del sidebar hasta que se implementen.
     */
    private const PLACEHOLDER_ROUTES = [];

    public function __construct(
        private readonly RolePreviewService $rolePreview,
        private readonly VisibilidadNavegacion $visibilidadNavegacion,
    ) {}

    public function getSidebar()
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        if ($previewRole = $this->rolePreview->activeRole($user)) {
            return match ($previewRole) {
                'GERENTE' => $this->getGerenteSidebar(),
                'ADMINISTRADOR' => $this->getAdministradorSidebar($user),
                'ENFERMEROS' => $this->getEnfermeroSidebar($user, true),
                'MEDICO GENERAL/GERIATRA' => $this->getMedicoSidebar($user),
                'PSICOLOGO/A' => $this->getPsicologoSidebar($user),
                'PEDAGOGO' => $this->getPedagogoSidebar(),
                'FISIOTERAPEUTA' => $this->getFisioterapeutaSidebar(),
                'NUTRICIONISTA' => $this->getNutricionistaSidebar(),
                'FAMILIAR' => $this->getFamiliarSidebar(),
                default => $this->getSuperadminSidebar(),
            };
        }

        $isSuperadmin = $this->isSuperadmin($user);

        if ($isSuperadmin) {
            return $this->getSuperadminSidebar();
        }

        // Roles institucionales y profesionales
        if ($user->hasRole('GERENTE')) {
            return $this->getGerenteSidebar();
        }
        if ($user->hasRole('ADMINISTRADOR')) {
            return $this->getAdministradorSidebar();
        }
        if ($user->hasRole('ENFERMEROS')) {
            return $this->getEnfermeroSidebar($user);
        }
        if ($user->hasRole('MEDICO GENERAL/GERIATRA')) {
            return $this->getMedicoSidebar($user);
        }
        if ($user->hasRole('PSICOLOGO/A')) {
            return $this->getPsicologoSidebar($user);
        }
        if ($user->hasRole('PEDAGOGO')) {
            return $this->getPedagogoSidebar();
        }
        if ($user->hasRole('FISIOTERAPEUTA')) {
            return $this->getFisioterapeutaSidebar();
        }
        if ($user->hasRole('NUTRICIONISTA')) {
            return $this->getNutricionistaSidebar();
        }
        if ($user->hasRole('FAMILIAR')) {
            return $this->getFamiliarSidebar();
        }

        // Fallback: dashboard genérico
        return array_values(array_filter([
            $this->buildSection('Inicio', 'ph-house', 'dashboard'),
        ]));
    }

    // =========================================================
    //  SUPERADMINISTRADOR
    // =========================================================
    private function getSuperadminSidebar(): array
    {
        $badges = $this->getSuperadminBadges();
        $sidebar = [
            $this->buildSection('Dashboard', 'ph-house', 'dashboard', [], false, null, null, 'INICIO'),
            $this->buildSection('Gestión del sistema', 'ph-shield-check', null, [
                $this->buildItem('Usuarios', 'admin.usuarios.index', 'usuarios.ver'),
                $this->buildItem('Roles y permisos', 'admin.roles-permisos.index', 'roles.ver'),
                $this->buildItem('Auditoría', 'admin.bitacora.index', 'bitacora.ver'),
            ], false, null, null, 'SISTEMA'),
            $this->buildSection('Gestión institucional', 'ph-buildings', null, [
                $this->buildItem('Personal', 'admin.personal-institucional', 'personal_institucional.ver'),
                $this->buildItem('Áreas', 'admin.areas-institucionales.index', 'areas.ver'),
                $this->buildItem('Turnos, jornadas y asignaciones', 'admin.turnos-asignaciones.index', 'turnos.ver'),
            ], false, null, null, 'INSTITUCIÓN'),
            $this->buildSection('Gestión residencial', 'ph-house-line', null, [
                $this->buildItem('Preadmisiones', 'admin.admisiones.preadmisiones', 'admisiones.ver_dashboard', $badges['preadmisiones']),
                $this->buildItem('Residentes', 'admin.adultos-mayores.index', 'residentes.ver'),
                $this->buildItem('Habitaciones, camas y ocupación', 'admin.habitaciones.index', 'habitaciones.ver'),
            ], false, null, null, 'RESIDENCIA'),
            $this->buildSection('Gestión administrativa', 'ph-folders', null, [
                $this->buildItem('Contactos y red de apoyo', 'admin.familia-social.resumen', 'residentes_contactos.ver'),
                $this->buildItem('Documentos y consentimientos', 'admin.reportes.familiares.preview', 'documentos.ver'),
            ], false, null, null, 'RESIDENCIA'),
            $this->buildSection('Operación diaria', 'ph-calendar-check', null, [
                $this->buildItem('Actividades', 'admin.actividades.index', 'actividades.ver'),
                $this->buildItem('Visitas', 'admin.familia-social.visitas', 'visitas.ver'),
            ], false, null, null, 'OPERACIÓN'),
            $this->buildSection('Control', 'ph-warning-circle', null, [
                $this->buildItem('Alertas', 'admin.alertas-clinicas.index', 'alertas.ver', $badges['alertas']),
                $this->buildItem('Incidentes', 'admin.enfermeria.incidentes', 'incidentes.ver', $badges['incidentes']),
            ], false, null, null, 'OPERACIÓN'),
            $this->buildSection('Expediente clínico', 'ph-first-aid-kit', null, [
                $this->buildItem('Expedientes integrales', 'admin.salud-seguimiento.ficha.index', 'atenciones.ver'),
                $this->buildItem('Estudios clínicos', 'admin.medico.interconsultas', 'estudios_clinicos.ver'),
                $this->buildItem('Medicación', 'admin.salud-seguimiento.medicacion.index', 'prescripciones.ver'),
                $this->buildItem('Seguimiento', 'admin.salud-seguimiento.index', 'atenciones.ver'),
                $this->buildItem('Valoraciones', 'admin.salud-seguimiento.valoracion.index', 'valoraciones_funcionales.ver'),
                $this->buildItem('Instrumentos', 'admin.instrumentos.index', 'instrumentos.ver'),
            ], false, null, null, 'ÁREA CLÍNICA'),
            $this->buildSection('Reportes', 'ph-chart-bar', 'admin.reportes.institucional.preview', [], false, 'reportes.institucional', null, 'INFORMACIÓN'),
            $this->buildSection('Mi perfil', 'ph-user-circle', 'profile.show', [], false, null, null, 'CUENTA'),
        ];

        return $this->deduplicateSidebar(array_values(array_filter($sidebar)));
    }

    // =========================================================
    //  GERENTE
    // =========================================================
    private function getGerenteSidebar(): array
    {
        return array_values(array_filter([
            $this->buildSection('Inicio', 'ph-house', 'dashboard'),
            $this->buildSection('Personal', 'ph-identification-badge', null, [
                $this->buildItem('Personal institucional', 'admin.personal-institucional', 'personal_institucional.ver'),
                $this->buildItem('Personal por área', 'admin.areas-institucionales.index', 'areas.ver'),
            ]),
            $this->buildSection('Organización', 'ph-buildings', null, [
                $this->buildItem('Áreas', 'admin.areas-institucionales.index', 'areas.ver'),
                $this->buildItem('Turnos y planificación', 'admin.turnos-asignaciones.index', 'turnos.ver'),
                $this->buildItem('Cobertura', 'admin.turnos-enfermeria.index', 'jornadas.ver'),
            ]),
            $this->buildSection('Consulta institucional', 'ph-chart-line-up', null, [
                $this->buildItem('Residentes', 'admin.adultos-mayores.index', 'residentes.ver'),
                $this->buildItem('Ocupación', 'admin.habitaciones.index', 'habitaciones.ver'),
                $this->buildItem('Alertas relevantes', 'admin.alertas-clinicas.index', 'alertas.ver'),
                $this->buildItem('Reportes gerenciales', 'admin.reportes.institucional.preview', 'reportes.institucional'),
            ]),
        ]));
    }

    // =========================================================
    //  ADMINISTRADOR
    // =========================================================
    private function getAdministradorSidebar($user = null, bool $isSuperadminSupervising = false): array
    {
        $badges = $this->getAdminBadges();
        $rutaInicio = $this->visibilidadNavegacion->puedeVerRuta('admin.administracion.dashboard')
            ? 'admin.administracion.dashboard' : 'dashboard';
        return $this->deduplicateSidebar(array_values(array_filter([
            $this->buildSection('Inicio', 'ph-house', $rutaInicio),
            $this->buildAdministradorGroup('Admisión', 'ph-user-plus', [
                $this->buildItem('Preadmisiones', 'admin.admisiones.preadmisiones', 'admisiones.ver_dashboard', $badges['preadmisiones']),
                $this->buildItem('Admisiones', 'admin.administracion.admisiones', 'admisiones.ver_dashboard', $badges['admisiones']),
            ]),
            $this->buildAdministradorGroup('Residentes', 'ph-users-three', [
                $this->buildItem('Residentes', 'admin.administracion.residentes', 'residentes.ver'),
                $this->buildItem('Habitaciones y camas', 'admin.administracion.habitaciones', 'habitaciones.ver'),
                $this->buildItem('Ocupación', 'admin.administracion.ocupacion', 'ocupaciones_cama.ver'),
            ]),
            $this->buildAdministradorGroup('Operación diaria', 'ph-calendar-check', [
                $this->buildItem('Jornadas', 'admin.administracion.jornadas', 'jornadas.ver'),
                $this->buildItem('Asignaciones', 'admin.administracion.asignaciones', 'asignaciones_personal.ver'),
                $this->buildItem('Actividades', 'admin.administracion.actividades', 'actividades.ver'),
                $this->buildItem('Visitas', 'admin.administracion.visitas', 'visitas.ver'),
            ]),
            $this->buildAdministradorGroup('Documentación', 'ph-files', [
                $this->buildItem('Contactos y responsables', 'admin.administracion.contactos', 'contactos.ver'),
                $this->buildItem('Documentos', 'admin.administracion.documentacion', 'documentos.ver', $badges['documentacion']),
                $this->buildItem('Consentimientos', 'admin.administracion.consentimientos', 'consentimientos.ver'),
                $this->buildItem('Seguros', 'admin.administracion.seguros', 'seguros_residente.ver'),
            ]),
            $this->buildAdministradorGroup('Seguimiento', 'ph-bell-ringing', [
                $this->buildItem('Alertas', 'admin.administracion.alertas', 'alertas.ver', $badges['alertas']),
                $this->buildItem('Incidentes', 'admin.administracion.incidentes', 'incidentes.ver'),
            ]),
            $this->buildSection('Reportes', 'ph-chart-bar', 'admin.administracion.reportes', [], false, 'reportes.ver'),
        ])));
    }

    private function buildAdministradorGroup(string $title, string $icon, array $items): ?array
    {
        $items = array_values(array_filter($items));
        if ($items === []) {
            return null;
        }

        $pending = array_sum(array_map(static fn (array $item): int => (int) ($item['badge'] ?? 0), $items));

        return $this->buildSection($title, $icon, null, $items, false, null, $pending > 0 ? (string) $pending : null);
    }

    // =========================================================
    //  ENFERMEROS
    // =========================================================
    private function getEnfermeroSidebar($user = null, bool $isPreview = false): array
    {
        $user ??= auth()->user();
        $esSupervision = ! $isPreview && $this->isSuperadmin($user);

        $alertasBadge = null;
        try {
            if ($user) {
                $turnoService = app(TurnoEnfermeriaService::class);
                $pacientesQuery = $turnoService->obtenerPacientesAsignadosQuery($user);
                $codigosAm = $pacientesQuery->pluck('cod_residente');
                if ($codigosAm->isNotEmpty()) {
                    $alertasCount = Alerta::whereIn('cod_residente', $codigosAm)
                        ->whereIn('estado', ['ABIERTA', 'ASIGNADA', 'RECONOCIDA', 'EN_ATENCION', 'PENDIENTE'])
                        ->count();
                    if ($alertasCount > 0) {
                        $alertasBadge = (string) $alertasCount;
                    }
                }
            }
        } catch (\Throwable $e) {
            report($e);
            $alertasBadge = null;
        }

        $sections = [];

        if ($esSupervision) {
            $sections[] = $this->buildSection('Volver a Áreas de Atención', 'ph-arrow-u-up-left', 'admin.areas-atencion.index');

            // BLOQUE 1: ATENCIÓN DE ENFERMERÍA (VISTA COMPLETA)
            $sections[] = $this->buildSection('Atención de Enfermería', 'ph-first-aid', null, [
                $this->buildItem('Mi turno activo', 'admin.enfermeria.dashboard', 'enfermeria.ver_dashboard'),
                $this->buildItem('Todos los residentes', 'admin.enfermeria.pacientes', 'enfermeria.ver_pacientes_asignados'),
                $this->buildItem('Ficha de cuidados', 'admin.salud-seguimiento.ficha.index', 'salud.ver'),
                $this->buildItem('Agenda de cuidados', 'admin.enfermeria.agenda', 'enfermeria.ver_dashboard'),
                $this->buildItem('Medicación prescrita', 'admin.salud-seguimiento.medicacion.index', 'prescripciones.ver'),
                $this->buildItem('Kardex y administraciones', 'admin.salud-seguimiento.administracion.index', 'salud.ver'),
                $this->buildItem('Cuidados e incidentes', 'admin.enfermeria.registros', 'atenciones.ver'),
                $this->buildItem('Planes y tareas', 'admin.enfermeria.tareas', 'ejecuciones_cuidado.ver'),
                $this->buildItem('Valoraciones iniciales', 'admin.admision.valoracion-enfermeria', 'valoracion_enfermeria.ver'),
            ], true);

            // BLOQUE 2: SUPERVISIÓN Y COORDINACIÓN DE CUIDADOS
            $sections[] = $this->buildSection('Supervisión de Enfermería', 'ph-shield-check', null, [
                $this->buildItem('Resumen global de guardia', 'admin.enfermeria.dashboard', 'enfermeria.ver_dashboard'),
                $this->buildItem('Turnos de enfermería', 'admin.turnos-enfermeria.index', 'turnos.ver'),
                $this->buildItem('Asignación de pacientes', 'admin.asignacion-turno.index', 'asignacion_turno.ver'),
                $this->buildItem('Pases y relevos de turno', 'admin.enfermeria.pase-turno', 'pases_turno.ver'),
                $this->buildItem('Alertas de guardia', 'admin.enfermeria.alertas', 'alertas.ver', $alertasBadge),
                $this->buildItem('Reportes de enfermería', 'admin.enfermeria.reportes', 'enfermeria.ver_dashboard'),
                $this->buildItem('Auditoría de cuidados', 'admin.bitacora.index', 'bitacora.ver'),
            ], true);

            return array_values(array_filter($sections));
        }

        // Rol de enfermería: navegación operativa breve, sin módulos administrativos.
        $sections[] = $this->buildSection('Mi turno', 'ph-sun-horizon', 'admin.enfermeria.dashboard', [], false, 'enfermeria.ver_dashboard');
        $sections[] = $this->buildSection('Mis residentes', 'ph-users', 'admin.enfermeria.pacientes', [], false, 'enfermeria.ver_pacientes_asignados');
        $cuidados = [];
        foreach (\App\Backend\Modulos\Enfermeria\Servicios\NavegacionCuidadosService::opciones() as $clave => $opcion) {
            $cuidados[] = $this->buildItem(
                $opcion['label'], $opcion['route'] ?? 'admin.enfermeria.pacientes',
                $opcion['permission'], null,
                array_merge($opcion['parameters'] ?? [], ['cuidado' => $clave]),
                isset($opcion['route']) ? ['admin.enfermeria.pacientes'] : [],
            );
        }
        $sections[] = $this->buildSection('Cuidados', 'ph-heartbeat', null, $cuidados, true);
        $sections[] = $this->buildSection('Medicación', 'ph-pill', 'admin.enfermeria.medicacion', [], false, 'enfermeria.ver_dashboard');
        $sections[] = $this->buildSection('Alertas', 'ph-warning', 'admin.enfermeria.alertas', [], false, 'alertas.ver', $alertasBadge);
        $sections[] = $this->buildSection('Incidentes', 'ph-first-aid', 'admin.enfermeria.incidentes', [], false, 'atenciones.ver');
        $sections[] = $this->buildSection('Pase de turno', 'ph-arrows-clockwise', 'admin.enfermeria.pase-turno', [], false, 'pases_turno.ver');

        return array_values(array_filter($sections));
    }

    // =========================================================
    //  MEDICO GENERAL/GERIATRA
    // =========================================================
    private function getMedicoSidebar($user = null, bool $isSuperadminSupervising = false): array
    {
        $user ??= auth()->user();
        $sidebar = [];

        if ($isSuperadminSupervising) {
            $sidebar[] = $this->buildSection('Volver a Administración', 'ph-arrow-u-up-left', 'dashboard');
        }

        $sidebar[] = $this->buildSection('Inicio', 'ph-house', 'admin.medico.dashboard');
        $sidebar[] = $this->buildSection('Pacientes', 'ph-users-three', 'admin.medico.pacientes.observacion');
        $sidebar[] = $this->buildSection('Valoraciones de admisión', 'ph-clipboard-text', 'admin.medico.valoraciones');
        $sidebar[] = $this->buildSection('Interconsultas', 'ph-chats-circle', 'admin.medico.interconsultas');
        $sidebar[] = $this->buildSection('Signos vitales', 'ph-heartbeat', 'admin.medico.signos-vitales');
        $sidebar[] = $this->buildSection('Fichas clínicas', 'ph-folder-user', 'admin.salud-seguimiento.ficha.index');
        $sidebar[] = $this->buildSection('Medicación', 'ph-pill', 'admin.salud-seguimiento.medicacion.index');
        $sidebar[] = $this->buildSection('Alertas clínicas', 'ph-bell-ringing', 'admin.salud-seguimiento.alertas');
        $sidebar[] = $this->buildSection('Reportes', 'ph-chart-bar', 'admin.salud-seguimiento.reportes');

        return array_values(array_filter($sidebar));
    }

    // =========================================================
    //  PSICOLOGO/A
    // =========================================================
    private function getPsicologoSidebar($user = null, bool $isSuperadminSupervising = false): array
    {
        $user ??= auth()->user();
        $sidebar = [];

        if ($isSuperadminSupervising) {
            $sidebar[] = $this->buildSection('Volver a Administración', 'ph-arrow-u-up-left', 'dashboard');
        }

        $sidebar[] = $this->buildSection('Inicio', 'ph-house', 'admin.psicologia.dashboard');
        $sidebar[] = $this->buildSection('Mis pacientes', 'ph-users-three', 'admin.psicologia.evaluaciones');

        $sidebar[] = $this->buildSection('Valoraciones', 'ph-list-magnifying-glass', null, [
            $this->buildItem('Cognitiva', 'admin.psicologia.evaluacion.cognitiva'),
            $this->buildItem('Afectiva', 'admin.psicologia.evaluacion.afectiva'),
            $this->buildItem('Funcionamiento', 'admin.psicologia.evaluacion.funcionamiento'),
            $this->buildItem('Entorno y red social', 'admin.psicologia.evaluacion.entorno'),
        ]);

        return array_values(array_filter($sidebar));
    }

    // =========================================================
    //  PEDAGOGO
    // =========================================================
    private function getPedagogoSidebar(): array
    {
        $sidebar = [
            $this->buildSection('Inicio', 'ph-house', 'dashboard'),
            $this->buildSection('Residentes', 'ph-users-four', 'admin.adultos-mayores.index'),
            $this->buildSection('Reportes', 'ph-chart-bar', 'admin.reportes.adultos.preview'),
        ];

        return array_values(array_filter($sidebar));
    }

    // =========================================================
    //  FISIOTERAPEUTA (todas las rutas son placeholder)
    // =========================================================
    private function getFisioterapeutaSidebar(): array
    {
        $sidebar = [
            $this->buildSection('Inicio', 'ph-house', 'dashboard'),
        ];

        return array_values(array_filter($sidebar));
    }

    // =========================================================
    //  NUTRICIONISTA (sin módulo navegable activo en esta fase)
    // =========================================================
    private function getNutricionistaSidebar(): array
    {
        $sidebar = [
            $this->buildSection('Inicio', 'ph-house', 'dashboard'),
        ];

        return array_values(array_filter($sidebar));
    }

    // =========================================================
    //  FAMILIAR (rutas portal son placeholder)
    // =========================================================
    private function getFamiliarSidebar(): array
    {
        $sidebar = [
            $this->buildSection('Inicio', 'ph-house', 'dashboard'),
        ];

        return array_values(array_filter($sidebar));
    }

    // =========================================================
    //  BUILDERS
    // =========================================================

    private function buildSection($title, $icon, $route = null, $items = [], $defaultExpanded = false, $permission = null, $badge = null, $group = null)
    {
        if ($route && ($this->isPlaceholderRoute($route) || ! $this->visibilidadNavegacion->puedeVerRuta($route, $permission))) {
            return null;
        }

        $user = auth()->user();
        if ($permission && (! $user || ! $user->can($permission))) {
            return null;
        }

        // Filter out null items
        $items = array_filter($items);

        // If it's just a section with children, and all children are null, return null
        if (! $route && empty($items)) {
            return null;
        }

        $active = false;

        if ($route && Route::has($route)) {
            if (request()->routeIs($route)) {
                $active = true;
            } else {
                $base = preg_replace('/\.index$/', '.*', $route);
                if ($base !== $route && request()->routeIs($base)) {
                    $active = true;
                }
            }
        }

        foreach ($items as &$item) {
            if ($item['active']) {
                $active = true;
                break;
            }
        }

        return [
            'title' => $title,
            'icon' => $icon,
            'route' => $route,
            'items' => array_values($items),
            'active' => $active,
            'disabled' => $route ? ! Route::has($route) : false,
            'default_expanded' => $defaultExpanded,
            'badge' => $badge,
            'group' => $group,
        ];
    }

    private function buildItem($label, $route, $permission = null, $badge = null, array $parameters = [], array $activeRoutes = [])
    {
        if ($this->isPlaceholderRoute($route) || ! $this->visibilidadNavegacion->puedeVerRuta($route, $permission)) {
            return null;
        }

        $user = auth()->user();
        if ($permission && (! $user || ! $user->can($permission))) {
            return null;
        }

        $routeExists = Route::has($route);
        $active = false;
        if ($routeExists) {
            if (request()->routeIs($route, ...$activeRoutes)) {
                $active = true;
            } else {
                $base = preg_replace('/\.index$/', '.*', $route);
                if ($base !== $route && request()->routeIs($base)) {
                    $active = true;
                }
            }
        }

        foreach ($parameters as $key => $value) {
            $active = $active && (string) request()->query($key) === (string) $value;
        }

        return [
            'label' => $label,
            'route' => $route,
            'parameters' => $parameters,
            'active_routes' => $activeRoutes,
            'active' => $active,
            'badge' => $badge,
            'disabled' => ! $routeExists,
        ];
    }

    private function buildPendingItem(string $label, string $permission): ?array
    {
        $user = auth()->user();
        if (! $user || $user->estado !== 'ACTIVO' || ! $user->can($permission)) {
            return null;
        }

        return [
            'label' => $label,
            'route' => null,
            'active' => false,
            'badge' => null,
            'disabled' => true,
        ];
    }

    private function isPlaceholderRoute(string $route): bool
    {
        return in_array($route, self::PLACEHOLDER_ROUTES, true);
    }

    /**
     * Conserva la primera aparición de cada ruta y elimina accesos repetidos.
     * Las secciones sin ruta ni elementos se descartan.
     */
    private function deduplicateSidebar(array $sections): array
    {
        $seenRoutes = [];
        $result = [];

        foreach ($sections as $section) {
            if (! empty($section['route'])) {
                if (isset($seenRoutes[$section['route']])) {
                    continue;
                }

                $seenRoutes[$section['route']] = true;
            }

            $items = [];
            foreach ($section['items'] ?? [] as $item) {
                $route = $item['route'] ?? null;
                if ($route && isset($seenRoutes[$route])) {
                    continue;
                }

                if ($route) {
                    $seenRoutes[$route] = true;
                }
                $items[] = $item;
            }

            $section['items'] = $items;
            if (empty($section['route']) && empty($section['items'])) {
                continue;
            }

            $result[] = $section;
        }

        return $result;
    }

    private function isSuperadmin($user = null): bool
    {
        $user ??= auth()->user();

        return $user && $user->hasAnyRole(self::SUPERADMIN_ROLES);
    }

    private function getSuperadminBadges(): array
    {
        return Cache::remember('sidebar_superadmin_badges_v2', 60, function (): array {
            $badge = static fn (int $count): ?string => $count > 0 ? (string) $count : null;

            return [
                'preadmisiones' => $badge(DB::table('preadmisiones')->where('estado', 'PENDIENTE')->count()),
                'alertas' => $badge(DB::table('alertas')->whereNotIn('estado', ['CERRADA', 'ANULADA'])->count()),
                'incidentes' => $badge(DB::table('incidentes')->whereNotIn('estado', ['CERRADO', 'CERRADA', 'ANULADO', 'ANULADA'])->count()),
            ];
        });
    }

    private function getAdminBadges(): array
    {
        return Cache::remember('sidebar_admin_badges_v1', 60, static function (): array {
            $badge = static fn (int $count): ?string => $count > 0 ? (string) $count : null;
            return [
                'preadmisiones' => $badge(DB::table('preadmisiones')->where('estado', 'PENDIENTE')->count()),
                'admisiones' => $badge(DB::table('preadmisiones as pre')->where('pre.estado', 'APROBADA')
                    ->whereNotExists(fn ($query) => $query->selectRaw('1')->from('admisiones as ad')
                        ->whereColumn('ad.cod_preadmision', 'pre.cod_preadmision'))->count()),
                'documentacion' => $badge(DB::table('documentos')->whereIn('estado', ['PENDIENTE', 'POR_VALIDAR'])->count()),
                'alertas' => $badge(DB::table('alertas')->whereNotIn('estado', ['CERRADA', 'ANULADA'])->count()),
            ];
        });
    }
}
