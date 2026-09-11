<?php

namespace App\Services\Identidad;

use Illuminate\Support\Facades\Route;

class SidebarService
{
    private const SUPERADMIN_ROLES = ['SUPERADMINISTRADOR', 'Superadmin', 'Super Administrador', 'superadmin', 'admin'];

    /**
     * Rutas que existen en el router pero apuntan a DashboardController@index
     * como stub temporal. Se ocultan del sidebar hasta que se implementen.
     */
    private const PLACEHOLDER_ROUTES = [
        // Psicología — stubs
        'admin.psicologia.seguimiento',
        'admin.psicologia.alertas',
        'admin.psicologia.pacientes.derivados',
        'admin.psicologia.pacientes.historial',
        'admin.psicologia.reportes',
        // Fisioterapia — todo es stub
        'admin.fisioterapia.dashboard',
        'admin.fisioterapia.pacientes.derivados',
        'admin.fisioterapia.valoracion',
        'admin.fisioterapia.plan',
        'admin.fisioterapia.evolucion',
        'admin.fisioterapia.riesgo.caida',
        'admin.fisioterapia.alertas',
        'admin.fisioterapia.reportes',
        // Nutrición — stubs (valoracion y seguimiento son funcionales y NO están aquí)
        'admin.nutricion.dashboard',
        'admin.nutricion.pacientes.derivados',
        'admin.nutricion.plan',
        'admin.nutricion.control.peso',
        'admin.nutricion.control.hidratacion',
        'admin.nutricion.alertas',
        'admin.nutricion.reportes',
        // Voluntario portal — stubs
        'admin.voluntario.dashboard',
        'admin.voluntario.actividades',
        'admin.voluntario.asistencia',
        'admin.voluntario.disponibilidad',
        'admin.voluntario.adultos.asignados',
        'admin.voluntario.reportes',
        // Familiar portal — stubs
        'admin.familiar.dashboard',
        'admin.familiar.resumen',
        'admin.familiar.actividades',
        'admin.familiar.visitas',
        'admin.familiar.pagos',
        'admin.familiar.documentos',
    ];

    public function getSidebar()
    {
        $user = auth()->user();
        if (!$user) {
            return [];
        }

        $isSuperadmin = $this->isSuperadmin($user);

        // Superadmin: sidebar contextual según ruta
        if ($isSuperadmin) {
            $isEnfermeriaModule = request()->is('admin/enfermeria*') || request()->routeIs('admin.enfermeria.*');
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
        if ($user->hasRole('VOLUNTARIO')) {
            return $this->getVoluntarioSidebar();
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
        $sidebar = [
            $this->buildSection('Inicio', 'ph-house', 'dashboard'),

            $this->buildSection('Usuarios y accesos', 'ph-shield-check', null, [
                $this->buildItem('Usuarios', 'admin.usuarios.index', 'usuarios.ver'),
                $this->buildItem('Roles y permisos', 'admin.roles-permisos.index', 'roles.ver'),
            ]),

            $this->buildSection('Residentes', 'ph-users-four', null, [
                $this->buildItem('Expedientes', 'admin.adultos-mayores.index', 'adultos.ver'),
                $this->buildItem('Preadmisiones', 'admin.admisiones.preadmisiones', 'admisiones.ver_dashboard'),
                $this->buildItem('Habitaciones y camas', 'admin.habitaciones.index', 'habitaciones.ver'),
                $this->buildItem('Familia / red de apoyo', 'admin.familia-social.resumen', 'familiares.ver'),
            ]),

            $this->buildSection('Personal', 'ph-identification-badge', null, [
                $this->buildItem('Personal institucional', 'admin.personal-institucional', 'personal_institucional.ver'),
                $this->buildItem('Horarios y asignaciones', 'admin.turnos-asignaciones.index', 'turnos.ver'),
                $this->buildItem('Turnos de enfermería', 'admin.turnos-enfermeria.index', 'turnos_enfermeria.ver'),
                $this->buildItem('Asignación de pacientes', 'admin.asignacion-turno.index', 'asignacion_turno.ver'),
            ]),

            $this->buildSection('Áreas de atención', 'ph-stethoscope', null, [
                $this->buildItem('Administración', 'admin.administracion.dashboard'),
                $this->buildItem('Medicina', 'admin.medico.dashboard'),
                $this->buildItem('Enfermería', 'admin.enfermeria.dashboard', 'enfermeria.ver_dashboard'),
                $this->buildItem('Psicología', 'admin.psicologia.dashboard'),
            ]),

            $this->buildSection('Alertas', 'ph-bell-ringing', 'admin.alertas-clinicas.index', [], false, 'alertas.ver'),

            $this->buildSection('Reportes', 'ph-chart-bar', null, [
                $this->buildItem('Reporte institucional', 'admin.reportes.institucional.preview', 'reportes.institucional'),
                $this->buildItem('Adultos mayores', 'admin.reportes.adultos.preview', 'reportes.ver'),
                $this->buildItem('Salud y evaluación', 'admin.reportes.salud.preview', 'reportes.ver'),
                $this->buildItem('Equipo institucional', 'admin.reportes.equipo.preview', 'reportes.ver'),
            ]),

            $this->buildSection('Bitácora y auditoría', 'ph-scroll', 'admin.bitacora.index', [], false, 'bitacora.ver'),
            $this->buildSection('Vistas extra', 'ph-stack-simple', 'admin.vistas-extra'),
        ];

        return array_values(array_filter($sidebar));
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
            $this->buildItem('Expedientes', 'admin.adultos-mayores.index', 'adultos.ver'),
            $this->buildItem('Alertas y pendientes', 'admin.adultos-mayores.alertas-pendientes', 'adultos.ver'),
            $this->buildItem('Familia / red de apoyo', 'admin.familia-social.resumen', 'familiares.ver'),
        ]);

        $sidebar[] = $this->buildSection('Admisiones', 'ph-user-plus', null, [
            $this->buildItem('Preadmisiones', 'admin.admisiones.preadmisiones', 'admisiones.ver_dashboard'),
            $this->buildItem('Habitaciones y camas', 'admin.habitaciones.index', 'habitaciones.ver'),
        ]);

        $sidebar[] = $this->buildSection('Personal y turnos', 'ph-identification-badge', null, [
            $this->buildItem('Personal institucional', 'admin.personal-institucional', 'personal_institucional.ver'),
            $this->buildItem('Horarios y asignaciones', 'admin.turnos-asignaciones.index', 'turnos.ver'),
            $this->buildItem('Áreas institucionales', 'admin.areas-institucionales.index', 'areas.ver'),
            $this->buildItem('Turnos de enfermería', 'admin.turnos-enfermeria.index', 'turnos_enfermeria.ver'),
            $this->buildItem('Asignación de pacientes', 'admin.asignacion-turno.index', 'asignacion_turno.ver'),
        ]);

        $sidebar[] = $this->buildSection('Usuarios', 'ph-user-gear', 'admin.usuarios.index', [], false, 'usuarios.ver');

        $sidebar[] = $this->buildSection('Alertas', 'ph-bell-ringing', 'admin.alertas-clinicas.index', [], false, 'alertas.ver');

        $sidebar[] = $this->buildSection('Actividades y comunidad', 'ph-calendar-check', null, [
            $this->buildItem('Actividades', 'admin.actividades.index', 'actividades.ver'),
            $this->buildItem('Voluntariado', 'admin.voluntariado.index', 'voluntarios.ver'),
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

        $alertasBadge = null;
        try {
            if ($user) {
                $turnoService = app(\App\Services\Enfermeria\TurnoEnfermeriaService::class);
                $pacientesQuery = $turnoService->obtenerPacientesAsignadosQuery($user);
                $codigosAm = $pacientesQuery->pluck('cod_am');
                if ($codigosAm->isNotEmpty()) {
                    $alertasCount = \App\Models\AlertaAdulto::whereIn('cod_am', $codigosAm)
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

        // Si es superadmin navegando en enfermería, mantener el botón para retornar
        if ($this->isSuperadmin($user) && (request()->is('admin/enfermeria*') || request()->routeIs('admin.enfermeria.*'))) {
            $sections[] = $this->buildSection('Volver a Administración', 'ph-arrow-u-up-left', 'dashboard');
        }

        // GRUPO 1: ENFERMERÍA
        $enfermeriaItems = [
            $this->buildItem('Inicio', 'admin.enfermeria.dashboard', 'enfermeria.ver_dashboard'),
            $this->buildItem('Mis pacientes', 'admin.enfermeria.pacientes', 'enfermeria.ver_pacientes_asignados'),
            $this->buildItem('Valoraciones iniciales', 'admin.admision.valoracion-enfermeria', 'valoracion_enfermeria.ver'),
            $this->buildItem('Alertas', 'admin.enfermeria.alertas', 'alertas.ver', $alertasBadge),
            $this->buildItem('Pase de turno', 'admin.enfermeria.pase-turno', 'pase_turno.ver'),
        ];

        $enfermeriaSection = $this->buildSection('Enfermería', 'ph-first-aid', null, $enfermeriaItems, true);
        if ($enfermeriaSection) {
            $sections[] = $enfermeriaSection;
        }

        // GRUPO 2: INFORMACIÓN
        $informacionItems = [
            $this->buildItem('Reportes', 'admin.enfermeria.reportes', 'enfermeria.ver_dashboard'),
        ];

        $informacionSection = $this->buildSection('Información', 'ph-chart-bar', null, $informacionItems, true);
        if ($informacionSection) {
            $sections[] = $informacionSection;
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
    //  NUTRICIONISTA (muestra Inicio + las 2 rutas funcionales)
    // =========================================================
    private function getNutricionistaSidebar(): array
    {
        $sidebar = [
            $this->buildSection('Inicio', 'ph-house', 'dashboard'),
            $this->buildSection('Valoración nutricional', 'ph-clipboard-text', 'admin.nutricion.valoracion'),
            $this->buildSection('Seguimiento nutricional', 'ph-chart-line-up', 'admin.nutricion.seguimiento'),
        ];

        return array_values(array_filter($sidebar));
    }

    // =========================================================
    //  VOLUNTARIO (rutas portal son placeholder)
    // =========================================================
    private function getVoluntarioSidebar(): array
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

    private function buildSection($title, $icon, $route = null, $items = [], $defaultExpanded = false, $permission = null)
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

    private function isSuperadmin($user = null): bool
    {
        $user ??= auth()->user();

        return $user && $user->hasAnyRole(self::SUPERADMIN_ROLES);
    }
}
