<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;

class SidebarService
{
    private const SUPERADMIN_ROLES = ['SUPERADMINISTRADOR', 'Superadmin', 'Super Administrador', 'superadmin', 'admin'];

    private const PLACEHOLDER_ROUTES = [
        'admin.psicologia.dashboard',
        'admin.psicologia.evaluaciones',
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

        if ($isAdministrative || $isSuperadmin) {
            return $this->getAdministrativeSidebar();
        }

        $sections = [];

        // 1. DASHBOARD DINÁMICO
        if ($isMedico) {
            $sections[] = $this->buildSection('Dashboard Médico', 'ph-house', 'admin.medico.dashboard');
        } elseif ($isEnfermero) {
            $sections[] = $this->buildSection('Dashboard Enfermería', 'ph-house', 'admin.enfermeria.dashboard');
        } else {
            $sections[] = $this->buildSection('Inicio', 'ph-house', 'dashboard');
        }

        // 2. SECCIONES CLÍNICAS Y OPERATIVAS
        if ($isMedico || $isSuperadmin) {
            $sections[] = $this->buildSection('Admisiones Médicas', 'ph-stethoscope', null, [
                $this->buildItem('Valoraciones médicas', 'admin.medico.valoraciones'),
                $this->buildItem('Decisiones de admisión', 'admin.medico.decisiones'),
            ]);
        }

        if ($isEnfermero || $isSuperadmin) {
            $sections[] = $this->buildSection('Enfermería', 'ph-first-aid', null, [
                $this->buildItem('Valoraciones iniciales', 'admin.admision.valoracion-enfermeria'),
                $this->buildItem('Mis pacientes', 'admin.enfermeria.pacientes'),
                $this->buildItem('Tareas del turno', 'admin.enfermeria.tareas'),
                $this->buildItem('Actividades del turno', 'admin.enfermeria.actividades'),
                $this->buildItem('Alertas', 'admin.enfermeria.alertas'),
                $this->buildItem('Pase de turno', 'admin.enfermeria.pase-turno'),
                $this->buildItem('Habitaciones y camas', 'admin.habitaciones.index', 'habitaciones.ver'),
                $this->buildItem('Turnos de enfermería', 'admin.turnos-enfermeria.index', 'turnos_enfermeria.ver'),
                $this->buildItem('Asignación de turno', 'admin.asignacion-turno.index', 'asignacion_turno.ver'),
                $this->buildItem('Plan de cuidado', 'admin.plan-cuidado.index', 'plan_cuidado.ver'),
            ]);
        }

        if ($isPsicologo || $isSuperadmin) {
            $sections[] = $this->buildSection('Psicología', 'ph-brain', null, [
                $this->buildItem('Evaluaciones asignadas', 'admin.psicologia.evaluaciones'),
                $this->buildItem('Seguimiento emocional', 'admin.psicologia.seguimiento'),
                $this->buildItem('Alertas conductuales', 'admin.psicologia.alertas'),
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
        $pacientesItems = [];
        if ($isMedico || $isSuperadmin) {
            $pacientesItems[] = $this->buildItem('Pacientes en observación', 'admin.medico.pacientes.observacion');
            $pacientesItems[] = $this->buildItem('Historial clínico', 'admin.medico.pacientes.historial');
        }
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

        // 4. SALUD (Integrado)
        $saludItems = [];
        if ($isMedico || $isSuperadmin) {
            $saludItems[] = $this->buildItem('Ficha médica', 'admin.salud-seguimiento.ficha.index', 'salud.ficha.ver');
            $saludItems[] = $this->buildItem('Signos vitales', 'admin.salud-seguimiento.signos.index', 'salud.signos.ver');
            $saludItems[] = $this->buildItem('Medicación', 'admin.salud-seguimiento.medicacion.index', 'salud.medicacion.ver');
            $saludItems[] = $this->buildItem('Alertas clínicas', 'admin.salud-seguimiento.alertas', 'salud.alertas.ver');
        }
        if (!empty(array_filter($saludItems))) {
            $sections[] = $this->buildSection('Salud y Evaluación Geriátrica', 'ph-heartbeat', null, $saludItems);
        }

        // 5. REPORTES
        $reportesItems = [];
        if ($isMedico || $isSuperadmin) {
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
