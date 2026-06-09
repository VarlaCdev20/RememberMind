<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;

class SidebarService
{
    private const SUPERADMIN_ROLES = ['SUPERADMINISTRADOR', 'Superadmin', 'Super Administrador', 'superadmin', 'admin'];

    private const PLACEHOLDER_ROUTES = [
        'admin.psicologia.seguimiento',
        'admin.psicologia.alertas',
        'admin.psicologia.pacientes.derivados',
        'admin.psicologia.pacientes.historial',
        'admin.psicologia.reportes',
        'admin.fisioterapia.dashboard',
        'admin.fisioterapia.pacientes.derivados',
        'admin.fisioterapia.valoracion',
        'admin.fisioterapia.plan',
        'admin.fisioterapia.evolucion',
        'admin.fisioterapia.riesgo.caida',
        'admin.fisioterapia.alertas',
        'admin.fisioterapia.reportes',
        'admin.nutricion.dashboard',
        'admin.nutricion.pacientes.derivados',
        'admin.nutricion.valoracion',
        'admin.nutricion.plan',
        'admin.nutricion.seguimiento',
        'admin.nutricion.control.peso',
        'admin.nutricion.control.hidratacion',
        'admin.nutricion.alertas',
        'admin.nutricion.reportes',
        'admin.voluntario.dashboard',
        'admin.voluntario.actividades',
        'admin.voluntario.asistencia',
        'admin.voluntario.disponibilidad',
        'admin.voluntario.adultos.asignados',
        'admin.voluntario.reportes',
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
        
        // Rol booleans
        $isMedico = $user->hasRole('MEDICO GENERAL/GERIATRA');
        $isEnfermero = $user->hasRole('ENFERMEROS');
        $isPsicologo = $user->hasRole('PSICOLOGO/A');
        $isFisioterapeuta = $user->hasRole('FISIOTERAPEUTA');
        $isNutricionista = $user->hasRole('NUTRICIONISTA');
        $isVoluntario = $user->hasRole('VOLUNTARIO');
        $isFamiliar = $user->hasRole('FAMILIAR');
        
        $isAdministrative = $user->hasRole('ADMINISTRADOR');

        // Si es superadmin o admin, por defecto ven el menú administrativo
        // EXCEPTO si están navegando explícitamente en el módulo de enfermería,
        // para que puedan ver la vista exacta que ve el enfermero.
        $isEnfermeriaRoute = request()->is('admin/enfermeria*') || request()->routeIs('admin.enfermeria.*');

        if (($isAdministrative || $isSuperadmin) && !$isEnfermeriaRoute) {
            return $this->getAdministrativeSidebar();
        }

        $sections = [];

        // Si es superadmin viendo el módulo de enfermería, simulamos que es enfermero para el menú
        if ($isSuperadmin && $isEnfermeriaRoute) {
            $isEnfermero = true;
        }

        // 1. DASHBOARD DINÁMICO Y RETORNO ADMIN
        if ($isSuperadmin && $isEnfermeriaRoute) {
            $sections[] = $this->buildSection('Volver a Administración', 'ph-arrow-u-up-left', 'dashboard');
        }

        if ($isMedico) {
            $sections[] = $this->buildSection('Dashboard Médico', 'ph-house', 'admin.medico.dashboard');
        } elseif ($isEnfermero) {
            $sections[] = $this->buildSection('Dashboard Enfermería', 'ph-house', 'admin.enfermeria.dashboard');
        } else {
            // Si es superadmin viendo enfermería, el botón de inicio principal será el de enfermería
            if ($isSuperadmin && $isEnfermeriaRoute) {
                $sections[] = $this->buildSection('Dashboard Enfermería', 'ph-house', 'admin.enfermeria.dashboard');
            } else {
                $sections[] = $this->buildSection('Inicio', 'ph-house', 'dashboard');
            }
        }

        // 2. MÓDULO MÉDICO — secciones clínicas bien diferenciadas
        if ($isMedico || ($isSuperadmin && request()->is('admin/medico*'))) {
            // Admisiones
            $sections[] = $this->buildSection('Admisiones Médicas', 'ph-stethoscope', null, [
                $this->buildItem('Valoraciones pendientes', 'admin.medico.valoraciones'),
                $this->buildItem('Decisiones de admisión',  'admin.medico.decisiones'),
            ]);

            // Seguimiento clínico de pacientes
            $sections[] = $this->buildSection('Seguimiento Clínico', 'ph-users-three', null, [
                $this->buildItem('Pacientes activos',  'admin.medico.pacientes.observacion'),
                $this->buildItem('Interconsultas',     'admin.medico.interconsultas'),
                $this->buildItem('Historial clínico',  'admin.medico.pacientes.historial'),
            ]);

            // Monitoreo de signos vitales — sección dedicada y destacada
            $sections[] = $this->buildSection('Monitoreo de Signos Vitales', 'ph-heartbeat', 'admin.medico.signos-vitales');

            // Salud y evaluación geriátrica (fichas, medicación, alertas)
            $sections[] = $this->buildSection('Salud Geriátrica', 'ph-first-aid-kit', null, [
                $this->buildItem('Fichas médicas',   'admin.salud-seguimiento.ficha.index',    'salud.ficha.ver'),
                $this->buildItem('Medicación activa','admin.salud-seguimiento.medicacion.index','salud.medicacion.ver'),
                $this->buildItem('Alertas clínicas', 'admin.salud-seguimiento.alertas',        'salud.alertas.ver'),
            ]);

            // Reportes médicos
            $sections[] = $this->buildSection('Reportes', 'ph-chart-bar', null, [
                $this->buildItem('Reportes médicos', 'admin.salud-seguimiento.reportes', 'salud.reportes.ver'),
            ]);
        } elseif ($isSuperadmin) {
            // Superadmin en rutas no-médicas: secciones médicas en su sidebar admin
            $sections[] = $this->buildSection('Módulo Médico', 'ph-stethoscope', null, [
                $this->buildItem('Dashboard médico',           'admin.medico.dashboard'),
                $this->buildItem('Signos vitales',             'admin.medico.signos-vitales'),
                $this->buildItem('Valoraciones pendientes',    'admin.medico.valoraciones'),
                $this->buildItem('Decisiones de admisión',     'admin.medico.decisiones'),
                $this->buildItem('Pacientes activos',          'admin.medico.pacientes.observacion'),
                $this->buildItem('Interconsultas',             'admin.medico.interconsultas'),
            ]);
        }

        if ($isEnfermero || $isSuperadmin) {
            $sections[] = $this->buildSection('Enfermería', 'ph-first-aid', null, [
                $this->buildItem('Dashboard operativo', 'admin.enfermeria.dashboard', 'enfermeria.ver_dashboard'),
                $this->buildItem('Valoraciones iniciales', 'admin.admision.valoracion-enfermeria', 'valoracion_enfermeria.ver'),
                $this->buildItem('Seguimiento diario', 'admin.seguimiento-diario.index', 'seguimiento.ver'),
                $this->buildItem('Mis pacientes', 'admin.enfermeria.pacientes', 'enfermeria.ver_pacientes_asignados'),
                $this->buildItem('Tareas del turno', 'admin.enfermeria.tareas', 'tareas.ver'),
                $this->buildItem('Alertas clínicas', 'admin.enfermeria.alertas', 'alertas.ver'),
                $this->buildItem('Pase de turno', 'admin.enfermeria.pase-turno', 'pase_turno.ver'),
                $this->buildItem('Reportes del turno', 'admin.enfermeria.reportes', 'enfermeria.ver_dashboard'),
            ]);
        }

        if ($isPsicologo || $isSuperadmin) {
            $sections[] = $this->buildSection('Dashboard Psicología', 'ph-brain', 'admin.psicologia.dashboard');
            $sections[] = $this->buildSection('Evaluaciones Geriátricas', 'ph-list-magnifying-glass', null, [
                $this->buildItem('Cognitivo', 'admin.psicologia.evaluacion.cognitiva'),
                $this->buildItem('Afectivo', 'admin.psicologia.evaluacion.afectiva'),
                $this->buildItem('Funcionamiento', 'admin.psicologia.evaluacion.funcionamiento'),
                $this->buildItem('Nutricional', 'admin.psicologia.evaluacion.nutricional'),
                $this->buildItem('Entorno y Red Social', 'admin.psicologia.evaluacion.entorno'),
            ]);
        }

        if ($isFisioterapeuta || $isSuperadmin) {
            $sections[] = $this->buildSection('Fisioterapia', 'ph-person-simple-walk', null, [
                $this->buildItem('Pacientes derivados', 'admin.fisioterapia.pacientes.derivados'),
                $this->buildItem('Valoración funcional', 'admin.fisioterapia.valoracion'),
                $this->buildItem('Plan funcional', 'admin.fisioterapia.plan'),
                $this->buildItem('Evolución física', 'admin.fisioterapia.evolucion'),
            ]);
            $sections[] = $this->buildSection('Riesgos', 'ph-warning-circle', null, [
                $this->buildItem('Riesgo de caída', 'admin.fisioterapia.riesgo.caida'),
                $this->buildItem('Alertas funcionales', 'admin.fisioterapia.alertas'),
            ]);
        }

        if ($isNutricionista || $isSuperadmin) {
            $sections[] = $this->buildSection('Nutrición', 'ph-apple-logo', null, [
                $this->buildItem('Pacientes derivados', 'admin.nutricion.pacientes.derivados'),
                $this->buildItem('Valoración nutricional', 'admin.nutricion.valoracion'),
                $this->buildItem('Plan alimentario', 'admin.nutricion.plan'),
                $this->buildItem('Seguimiento nutricional', 'admin.nutricion.seguimiento'),
            ]);
            $sections[] = $this->buildSection('Control Físico/Nutricional', 'ph-scales', null, [
                $this->buildItem('Peso e IMC', 'admin.nutricion.control.peso'),
                $this->buildItem('Hidratación', 'admin.nutricion.control.hidratacion'),
                $this->buildItem('Alertas nutricionales', 'admin.nutricion.alertas'),
            ]);
        }

        if ($isVoluntario || $isSuperadmin) {
            $sections[] = $this->buildSection('Voluntariado', 'ph-hand-heart', null, [
                $this->buildItem('Mis actividades', 'admin.voluntario.actividades'),
                $this->buildItem('Asistencia', 'admin.voluntario.asistencia'),
                $this->buildItem('Disponibilidad', 'admin.voluntario.disponibilidad'),
            ]);
        }

        if ($isFamiliar || $isSuperadmin) {
            $sections[] = $this->buildSection('Mi Familiar', 'ph-heart', null, [
                $this->buildItem('Resumen de estado', 'admin.familiar.resumen'),
                $this->buildItem('Actividades recientes', 'admin.familiar.actividades'),
                $this->buildItem('Visitas programadas', 'admin.familiar.visitas'),
            ]);
            $sections[] = $this->buildSection('Pagos y Documentos', 'ph-file-text', null, [
                $this->buildItem('Estado de cuenta', 'admin.familiar.pagos'),
                $this->buildItem('Documentos legales', 'admin.familiar.documentos'),
            ]);
        }

        // 3. PACIENTES / ADULTOS MAYORES
        // (Los ítems de médico ya están en la sección 2 "Seguimiento Médico" — no duplicar)
        $pacientesItems = [];
        if ($isPsicologo || $isSuperadmin) {
            $pacientesItems[] = $this->buildItem('Pacientes derivados (Psico)', 'admin.psicologia.pacientes.derivados');
            $pacientesItems[] = $this->buildItem('Historial psicológico', 'admin.psicologia.pacientes.historial');
        }
        if ($isVoluntario || $isSuperadmin) {
            $pacientesItems[] = $this->buildItem('Adultos asignados', 'admin.voluntario.adultos.asignados');
        }
        if (!empty(array_filter($pacientesItems))) {
            $title = ($isMedico || $isPsicologo) ? 'Pacientes' : 'Adultos Mayores';
            $sections[] = $this->buildSection($title, 'ph-users-four', null, $pacientesItems);
        }

        // 4. SALUD (Integrado) — solo para roles que NO son médicos (los médicos ya tienen sus secciones propias arriba)
        $saludItems = [];
        if (!$isMedico && $isSuperadmin && !request()->is('admin/medico*')) {
            $saludItems[] = $this->buildItem('Ficha médica', 'admin.salud-seguimiento.ficha.index', 'salud.ficha.ver');
            $saludItems[] = $this->buildItem('Signos vitales', 'admin.salud-seguimiento.signos.index', 'salud.signos.ver');
            $saludItems[] = $this->buildItem('Medicación', 'admin.salud-seguimiento.medicacion.index', 'salud.medicacion.ver');
            $saludItems[] = $this->buildItem('Alertas clínicas', 'admin.salud-seguimiento.alertas', 'salud.alertas.ver');
        } elseif ($isEnfermero) {
            // Enfermeros también acceden a signos y medicación
            $saludItems[] = $this->buildItem('Signos vitales', 'admin.salud-seguimiento.signos.index', 'salud.signos.ver');
            $saludItems[] = $this->buildItem('Medicación', 'admin.salud-seguimiento.medicacion.index', 'salud.medicacion.ver');
            $saludItems[] = $this->buildItem('Alertas clínicas', 'admin.salud-seguimiento.alertas', 'salud.alertas.ver');
        }
        if (!empty(array_filter($saludItems))) {
            $sections[] = $this->buildSection('Salud y Evaluación Geriátrica', 'ph-heartbeat', null, $saludItems);
        }

        // 5. REPORTES
        $reportesItems = [];
        if ($isMedico) {
            // Reportes ya incluidos en la sección médica arriba — no duplicar aquí
        } elseif ($isSuperadmin) {
            $reportesItems[] = $this->buildItem('Reportes médicos', 'admin.salud-seguimiento.reportes', 'salud.reportes.ver');
        }
        if ($isEnfermero || $isSuperadmin) {
            $reportesItems[] = $this->buildItem('Reportes de turno', 'admin.enfermeria.reportes');
        }
        if ($isPsicologo || $isSuperadmin) {
            $reportesItems[] = $this->buildItem('Reportes psicológicos', 'admin.psicologia.reportes');
        }
        if ($isFisioterapeuta || $isSuperadmin) {
            $reportesItems[] = $this->buildItem('Reportes fisioterapia', 'admin.fisioterapia.reportes');
        }
        if ($isNutricionista || $isSuperadmin) {
            $reportesItems[] = $this->buildItem('Reportes nutricionales', 'admin.nutricion.reportes');
        }
        if ($isVoluntario || $isSuperadmin) {
            $reportesItems[] = $this->buildItem('Reporte de actividades', 'admin.voluntario.reportes');
        }
        if (!empty(array_filter($reportesItems))) {
            $sections[] = $this->buildSection('Reportes', 'ph-chart-bar', null, $reportesItems);
        }

        // Clean up empty sections
        $sections = array_filter($sections, function($section) {
            return !is_null($section) && (!empty($section['route']) || !empty($section['items']));
        });

        return array_values($sections);
    }

    private function getAdministrativeSidebar(): array
    {
        $sections = [
            $this->buildSection('Inicio', 'ph-house', 'dashboard'),

            $this->buildSection('Administración y Accesos', 'ph-shield-check', null, [
                $this->buildItem('Cuentas de Usuario', 'admin.usuarios.index', 'usuarios.ver'),
                $this->buildItem('Roles y Permisos', 'admin.roles-permisos.index', 'roles.ver'),
            ]),

            $this->buildSection('Estructura Institucional', 'ph-buildings', null, [
                $this->buildItem('Áreas Institucionales', 'admin.areas-institucionales.index', 'areas.ver'),
                $this->buildItem('Personal Institucional', 'admin.personal-institucional', 'personal_institucional.ver'),
                $this->buildItem('Horarios y Asignaciones', 'admin.turnos-asignaciones.index', 'turnos.ver'),
            ]),

            $this->buildSection('Admisiones', 'ph-user-plus', null, [
                $this->buildItem('Preadmisiones', 'admin.admisiones.preadmisiones', 'admisiones.ver_dashboard'),
                $this->buildItem('Registrar Preadmisión', 'admin.admisiones.preadmision', 'admisiones.ver_dashboard'),
            ]),

            $this->buildSection('Adultos Mayores', 'ph-users-four', null, [
                $this->buildItem('Expedientes', 'admin.adultos-mayores.index', 'adultos.ver'),
                $this->buildItem('Alertas y pendientes', 'admin.adultos-mayores.alertas-pendientes', 'adultos.ver'),
                $this->buildItem('Familiares y documentos', 'admin.familia-social.resumen', 'familiares.ver'),
            ]),

            $this->buildSection('Salud y Evaluación Geriátrica', 'ph-heartbeat', null, [
                $this->buildItem('Panel clínico integrado', 'admin.salud-seguimiento.index', 'salud.ver'),
                $this->buildItem('Fichas médicas', 'admin.salud-seguimiento.ficha.index', 'salud.ficha.ver'),
                $this->buildItem('Signos vitales', 'admin.salud-seguimiento.signos.index', 'salud.signos.ver'),
                $this->buildItem('Medicación', 'admin.salud-seguimiento.medicacion.index', 'salud.medicacion.ver'),
                $this->buildItem('Valoraciones funcionales', 'admin.salud-seguimiento.valoracion.index', 'salud.valoracion.ver'),
                $this->buildItem('Evaluaciones geriátricas', 'admin.salud-seguimiento.evaluaciones-geriatricas.index', 'salud.ver'),
                $this->buildItem('Alertas clínicas', 'admin.salud-seguimiento.alertas', 'salud.alertas.ver'),
            ]),

            $this->buildSection('Enfermería y Seguimiento', 'ph-first-aid', null, [
                $this->buildItem('Valoraciones iniciales', 'admin.admision.valoracion-enfermeria', 'valoracion_enfermeria.ver'),
                $this->buildItem('Habitaciones y camas', 'admin.habitaciones.index', 'habitaciones.ver'),
                $this->buildItem('Turnos de enfermería', 'admin.turnos-enfermeria.index', 'turnos_enfermeria.ver'),
                $this->buildItem('Asignación de turno', 'admin.asignacion-turno.index', 'asignacion_turno.ver'),
                $this->buildItem('Plan de cuidado', 'admin.plan-cuidado.index', 'plan_cuidado.ver'),
                $this->buildItem('Seguimiento diario', 'admin.seguimiento-diario.index', 'seguimiento.ver'),
                $this->buildItem('Alertas clínicas', 'admin.alertas-clinicas.index', 'alertas.ver'),
                $this->buildItem('Pase de turno', 'admin.pase-turno.index', 'pase_turno.ver'),
            ]),

            $this->buildSection('Actividades y Voluntariado', 'ph-calendar-check', null, [
                $this->buildItem('Actividades institucionales', 'admin.actividades.index', 'actividades.ver'),
                $this->buildItem('Programación', 'admin.actividades.tipos', 'actividades.ver'),
                $this->buildItem('Participación', 'admin.actividades.participacion', 'actividades.ver'),
                $this->buildItem('Asistencia', 'admin.actividades.asistencia', 'actividades.ver'),
                $this->buildItem('Voluntariado', 'admin.voluntariado.index', 'voluntarios.ver'),
            ]),

            $this->buildSection('Reportes y Trazabilidad', 'ph-chart-bar', null, [
                $this->buildItem('Reporte institucional', 'admin.reportes.institucional.preview', 'reportes.institucional'),
                $this->buildItem('Adultos mayores', 'admin.reportes.adultos.preview', 'reportes.ver'),
                $this->buildItem('Salud y evaluación', 'admin.reportes.salud.preview', 'reportes.ver'),
                $this->buildItem('Equipo institucional', 'admin.reportes.equipo.preview', 'reportes.ver'),
                $this->buildItem('Actividades', 'admin.reportes.actividades.preview', 'reportes.ver'),
                $this->buildItem('Bitácora y trazabilidad', 'admin.bitacora.index', 'bitacora.ver'),
            ]),
        ];

        return array_values(array_filter($sections));
    }

    private function buildSection($title, $icon, $route = null, $items = [])
    {
        if ($route && $this->isPlaceholderRoute($route)) {
            return null;
        }

        // Filter out null items
        $items = array_filter($items);
        
        // If it's just a section with children, and all children are null, return null
        if (!$route && empty($items)) {
            return null;
        }

        $active = false;
        
        if ($route && Route::has($route) && request()->routeIs($route)) {
            $active = true;
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

        return [
            'label' => $label,
            'route' => $route,
            'active' => $routeExists && request()->routeIs($route),
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
