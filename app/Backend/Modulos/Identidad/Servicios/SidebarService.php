<?php

namespace App\Backend\Modulos\Identidad\Servicios;

use Illuminate\Support\Facades\Route;

class SidebarService
{
    private const SUPERADMIN_ROLES = ['SUPERADMINISTRADOR', 'Superadmin', 'Super Administrador', 'superadmin', 'admin'];

    /**
     * Rutas que existen en el router pero apuntan a DashboardController@index
     * como stub temporal. Se ocultan del sidebar hasta que se implementen.
     */
    private const PLACEHOLDER_ROUTES = [];

    public function getSidebar()
    {
        $user = auth()->user();
        if (!$user) {
            return [];
        }

        $isSuperadmin = $this->isSuperadmin($user);

        // Superadmin: sidebar contextual según ruta
        if ($isSuperadmin) {
            $isEnfermeriaModule = request()->is('admin/enfermeria*')
                || request()->routeIs('admin.enfermeria.*')
                || request()->is('admin/turnos-enfermeria*')
                || request()->routeIs('admin.turnos-enfermeria.*')
                || request()->is('admin/asignacion-turno*')
                || request()->routeIs('admin.asignacion-turno.*')
                || request()->is('admin/plan-cuidado*')
                || request()->routeIs('admin.plan-cuidado.*')
                || request()->routeIs('admin.admision.valoracion-enfermeria');
            $isMedicoModule = request()->is('admin/medico*') || request()->routeIs('admin.medico.*');
            $isPsicologiaModule = request()->is('admin/psicologia*') || request()->routeIs('admin.psicologia.*');

            if ($isEnfermeriaModule) {
                return $this->getEnfermeroSidebar($user);
            }
            if ($isMedicoModule) {
                return $this->getMedicoSidebar($user, true);
            }
            $isAdministracionModule = request()->is('admin/administracion*') || request()->routeIs('admin.administracion.*');

            if ($isPsicologiaModule) {
                return $this->getPsicologoSidebar($user, true);
            }
            if ($isAdministracionModule) {
                return $this->getAdministradorSidebar($user, true);
            }

            return $this->getSuperadminSidebar();
        }

        // Roles institucionales y profesionales
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
        $alertasBadge = null;
        try {
            $alertasCount = \App\Models\AlertaClinica::where('estado', 'ABIERTA')->count();
            if ($alertasCount > 0) {
                $alertasBadge = (string) $alertasCount;
            }
        } catch (\Throwable $e) {
            $alertasBadge = null;
        }

        $sidebar = [
            // GRUPO 1: PRINCIPAL
            $this->buildSection('Inicio', 'ph-house', 'dashboard', [], false, null, null, 'Principal'),

            // GRUPO 2: GESTIÓN INSTITUCIONAL
            $this->buildSection('Usuarios y accesos', 'ph-shield-check', null, [
                $this->buildItem('Usuarios', 'admin.usuarios.index', 'usuarios.ver'),
                $this->buildItem('Roles y permisos', 'admin.roles-permisos.index', 'roles.ver'),
            ], false, null, null, 'Gestión Institucional'),

            $this->buildSection('Residentes', 'ph-users-four', null, [
                $this->buildItem('Expedientes', 'admin.adultos-mayores.index', 'residentes.ver'),
                $this->buildItem('Preadmisiones', 'admin.admisiones.preadmisiones', 'admisiones.ver_dashboard'),
                $this->buildItem('Habitaciones y camas', 'admin.habitaciones.index', 'habitaciones.ver'),
                $this->buildItem('Familia / red de apoyo', 'admin.familia-social.resumen', 'residentes_contactos.ver'),
            ], false, null, null, 'Gestión Institucional'),

            $this->buildSection('Personal', 'ph-identification-badge', null, [
                $this->buildItem('Personal institucional', 'admin.personal-institucional', 'personal_institucional.ver'),
                $this->buildItem('Horarios y asignaciones', 'admin.turnos-asignaciones.index', 'turnos.ver'),
                $this->buildItem('Turnos de enfermería', 'admin.turnos-enfermeria.index', 'turnos.ver'),
                $this->buildItem('Asignación de pacientes', 'admin.asignacion-turno.index', 'asignacion_turno.ver'),
            ], false, null, null, 'Gestión Institucional'),

                        // GRUPO 3: SERVICIOS CLÍNICOS Y ÁREAS DE ATENCIÓN
            $this->buildSection('Áreas de atención', 'ph-stethoscope', null, [
                $this->buildItem('Centro de Áreas de Atención', 'admin.areas-atencion.index'),
                $this->buildItem('Enfermería y Cuidados', 'admin.enfermeria.dashboard', 'enfermeria.ver_dashboard'),
                $this->buildItem('Medicina y Geriatría', 'admin.medico.dashboard'),
                $this->buildItem('Psicología y Cognición', 'admin.psicologia.dashboard'),
                $this->buildItem('Terapia y Actividades', 'admin.actividades.index', 'actividades.ver'),
                $this->buildItem('Social y Familias', 'admin.familia-social.resumen', 'residentes_contactos.ver'),
                $this->buildItem('Dirección Administrativa', 'admin.administracion.dashboard'),
            ], false, null, null, 'Servicios Clínicos'),

            $this->buildSection('Supervisión de Enfermería', 'ph-first-aid', null, [
                // Vista Operativa Completa
                $this->buildItem('Resumen global de guardia', 'admin.enfermeria.dashboard', 'enfermeria.ver_dashboard'),
                $this->buildItem('Todos los residentes', 'admin.enfermeria.pacientes', 'enfermeria.ver_pacientes_asignados'),
                $this->buildItem('Ficha de cuidados', 'admin.salud-seguimiento.ficha.index', 'salud.ver'),
                $this->buildItem('Agenda de cuidados', 'admin.enfermeria.agenda', 'enfermeria.ver_dashboard'),
                $this->buildItem('Medicación prescrita', 'admin.salud-seguimiento.medicacion.index', 'prescripciones.ver'),
                $this->buildItem('Kardex y administraciones', 'admin.salud-seguimiento.administracion.index', 'salud.ver'),
                $this->buildItem('Cuidados e incidentes', 'admin.enfermeria.registros', 'atenciones.ver'),
                $this->buildItem('Planes y tareas', 'admin.enfermeria.tareas', 'ejecuciones_cuidado.ver'),
                $this->buildItem('Valoraciones iniciales', 'admin.admision.valoracion-enfermeria', 'valoracion_enfermeria.ver'),
                // Supervisión y Coordinación
                $this->buildItem('Turnos de enfermería', 'admin.turnos-enfermeria.index', 'turnos.ver'),
                $this->buildItem('Asignación de pacientes', 'admin.asignacion-turno.index', 'asignacion_turno.ver'),
                $this->buildItem('Pases de turno', 'admin.enfermeria.pase-turno', 'pases_turno.ver'),
                $this->buildItem('Alertas clínicas', 'admin.enfermeria.alertas', 'alertas.ver', $alertasBadge),
                $this->buildItem('Reportes de enfermería', 'admin.enfermeria.reportes', 'enfermeria.ver_dashboard'),
            ], false, null, null, 'Servicios Clínicos'),

            $this->buildSection('Alertas', 'ph-bell-ringing', 'admin.alertas-clinicas.index', [], false, 'alertas.ver', $alertasBadge, 'Servicios Clínicos'),

            // GRUPO 4: AUDITORÍA Y CONTROL
            $this->buildSection('Reportes', 'ph-chart-bar', null, [
                $this->buildItem('Reporte institucional', 'admin.reportes.institucional.preview', 'reportes.institucional'),
                $this->buildItem('Adultos mayores', 'admin.reportes.adultos.preview', 'reportes.ver'),
                $this->buildItem('Salud y evaluación', 'admin.reportes.salud.preview', 'reportes.ver'),
                $this->buildItem('Equipo institucional', 'admin.reportes.equipo.preview', 'reportes.ver'),
            ], false, null, null, 'Control y Auditoría'),

            $this->buildSection('Bitácora y auditoría', 'ph-scroll', 'admin.bitacora.index', [], false, 'bitacora.ver', null, 'Control y Auditoría'),
            $this->buildSection('Vistas extra', 'ph-stack-simple', 'admin.vistas-extra', [], false, null, null, 'Control y Auditoría'),
        ];

        return $this->deduplicateSidebar(array_values(array_filter($sidebar)));
    }

    // =========================================================
    //  ADMINISTRADOR
    // =========================================================
    private function getAdministradorSidebar($user = null, bool $isSuperadminSupervising = false): array
    {
        $user ??= auth()->user();
        $sidebar = [];

        if ($isSuperadminSupervising) {
            $sidebar[] = $this->buildSection('Volver a Superadministrador', 'ph-arrow-u-up-left', 'dashboard');
        }

        $sidebar[] = $this->buildSection('Inicio', 'ph-house', $isSuperadminSupervising ? 'admin.administracion.dashboard' : 'dashboard');

        $sidebar[] = $this->buildSection('Residentes', 'ph-users-four', null, [
            $this->buildItem('Expedientes', 'admin.adultos-mayores.index', 'residentes.ver'),
            $this->buildItem('Alertas y pendientes', 'admin.adultos-mayores.alertas-pendientes', 'residentes.ver'),
            $this->buildItem('Familia / red de apoyo', 'admin.familia-social.resumen', 'residentes_contactos.ver'),
        ]);

        $sidebar[] = $this->buildSection('Admisiones', 'ph-user-plus', null, [
            $this->buildItem('Preadmisiones', 'admin.admisiones.preadmisiones', 'admisiones.ver_dashboard'),
            $this->buildItem('Habitaciones y camas', 'admin.habitaciones.index', 'habitaciones.ver'),
        ]);

        $sidebar[] = $this->buildSection('Personal y turnos', 'ph-identification-badge', null, [
            $this->buildItem('Personal institucional', 'admin.personal-institucional', 'personal_institucional.ver'),
            $this->buildItem('Horarios y asignaciones', 'admin.turnos-asignaciones.index', 'turnos.ver'),
            $this->buildItem('Áreas institucionales', 'admin.areas-institucionales.index', 'areas.ver'),
            $this->buildItem('Turnos de enfermería', 'admin.turnos-enfermeria.index', 'turnos.ver'),
            $this->buildItem('Asignación de pacientes', 'admin.asignacion-turno.index', 'asignacion_turno.ver'),
        ]);

        $sidebar[] = $this->buildSection('Usuarios', 'ph-user-gear', 'admin.usuarios.index', [], false, 'usuarios.ver');

        $sidebar[] = $this->buildSection('Alertas', 'ph-bell-ringing', 'admin.alertas-clinicas.index', [], false, 'alertas.ver');

        $sidebar[] = $this->buildSection('Actividades y comunidad', 'ph-calendar-check', null, [
            $this->buildItem('Actividades', 'admin.actividades.index', 'actividades.ver'),

        ]);

        $sidebar[] = $this->buildSection('Reportes', 'ph-chart-bar', null, [
            $this->buildItem('Reporte institucional', 'admin.reportes.institucional.preview', 'reportes.institucional'),
            $this->buildItem('Adultos mayores', 'admin.reportes.adultos.preview', 'reportes.ver'),
            $this->buildItem('Salud y evaluación', 'admin.reportes.salud.preview', 'reportes.ver'),
            $this->buildItem('Equipo institucional', 'admin.reportes.equipo.preview', 'reportes.ver'),
        ]);

        return array_values(array_filter($sidebar));
    }

    // =========================================================
    //  ENFERMEROS
    // =========================================================
    private function getEnfermeroSidebar($user = null): array
    {
        $user ??= auth()->user();
        $esSupervision = $this->isSuperadmin($user);

        $alertasBadge = null;
        try {
            if ($user) {
                $turnoService = app(\App\Backend\Modulos\Enfermeria\Servicios\TurnoEnfermeriaService::class);
                $pacientesQuery = $turnoService->obtenerPacientesAsignadosQuery($user);
                $codigosAm = $pacientesQuery->pluck('cod_residente');
                if ($codigosAm->isNotEmpty()) {
                    $alertasCount = \App\Models\Alerta::whereIn('cod_residente', $codigosAm)
                        ->whereIn('estado', ['ABIERTA', 'EN_ATENCION'])
                        ->count();
                    if ($alertasCount > 0) {
                        $alertasBadge = (string) $alertasCount;
                    }
                }
            }
        } catch (\Throwable $e) {
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

                // ROL ENFERMEROS (ESTRUCTURA RESIDENCIAL APROBADA)
        $sections[] = $this->buildSection('Mi turno', 'ph-sun-horizon', 'admin.enfermeria.dashboard', [], false, 'enfermeria.ver_dashboard');
        $sections[] = $this->buildSection('Mis residentes', 'ph-users', 'admin.enfermeria.pacientes', [], false, 'enfermeria.ver_pacientes_asignados');

        // GRUPO: Cuidado
        $cuidadoItems = [
            $this->buildItem('Cuidados', 'admin.enfermeria.tareas', 'ejecuciones_cuidado.ver'),
            $this->buildItem('Medicación', 'admin.salud-seguimiento.medicacion.index', 'salud.ver'),
        ];
        $cuidadoSection = $this->buildSection('Cuidado', 'ph-heartbeat', null, $cuidadoItems, true);
        if ($cuidadoSection) {
            $sections[] = $cuidadoSection;
        }

        // GRUPO: Continuidad
        $continuidadItems = [
            $this->buildItem('Pase de turno', 'admin.enfermeria.pase-turno', 'pases_turno.ver'),
            $this->buildItem('Incidentes', 'admin.enfermeria.registros', 'atenciones.ver'),
            $this->buildItem('Alertas', 'admin.enfermeria.alertas', 'alertas.ver', $alertasBadge),
        ];
        $continuidadSection = $this->buildSection('Continuidad', 'ph-arrows-clockwise', null, $continuidadItems, true);
        if ($continuidadSection) {
            $sections[] = $continuidadSection;
        }

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
        if ($route && $this->isPlaceholderRoute($route)) {
            return null;
        }

        $user = auth()->user();
        $isSuperadmin = $this->isSuperadmin($user);

        if ($permission && (!$user || (!$user->can($permission) && !$isSuperadmin))) {
            return null;
        }

        // Filter out null items
        $items = array_filter($items);
        
        // If it's just a section with children, and all children are null, return null
        if (!$route && empty($items)) {
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
            'disabled' => $route ? !Route::has($route) : false,
            'default_expanded' => $defaultExpanded,
            'badge' => $badge,
            'group' => $group,
        ];
    }

    private function buildItem($label, $route, $permission = null, $badge = null)
    {
        if ($this->isPlaceholderRoute($route)) {
            return null;
        }

        $user = auth()->user();
        $isSuperadmin = $this->isSuperadmin($user);

        if ($permission && (!$user || (!$user->can($permission) && !$isSuperadmin))) {
            return null;
        }

        $routeExists = Route::has($route);
        $active = false;
        if ($routeExists) {
            if (request()->routeIs($route)) {
                $active = true;
            } else {
                $base = preg_replace('/\.index$/', '.*', $route);
                if ($base !== $route && request()->routeIs($base)) {
                    $active = true;
                }
            }
        }

        return [
            'label' => $label,
            'route' => $route,
            'active' => $active,
            'badge' => $badge,
            'disabled' => !$routeExists,
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
            if (!empty($section['route'])) {
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
}
