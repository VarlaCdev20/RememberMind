<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;

class SidebarService
{
    public function getSidebar()
    {
        $user = auth()->user();
        if (!$user) {
            return [];
        }

        $superadminRoles = ['SUPERADMINISTRADOR', 'Superadmin', 'Super Administrador', 'superadmin', 'admin'];
        $isSuperadmin = $user->hasAnyRole($superadminRoles);
        
        // Rol booleans
        $isMedico = $user->hasRole(['Medico', 'Médico', 'Medico General']);
        $isEnfermero = $user->hasRole(['Enfermero', 'Enfermera', 'Enfermería']);
        $isPsicologo = $user->hasRole(['Psicologo', 'Psicólogo']);
        $isFisioterapeuta = $user->hasRole(['Fisioterapeuta', 'Fisioterapia', 'Kinesiologo']);
        $isNutricionista = $user->hasRole(['Nutricionista', 'Nutriologo', 'Nutriólogo']);
        $isVoluntario = $user->hasRole(['Voluntario']);
        $isFamiliar = $user->hasRole(['Familiar', 'Familia', 'Familiar Responsable']);
        
        $isClinico = $isMedico || $isEnfermero || $isPsicologo || $isFisioterapeuta || $isNutricionista;
        $isExternal = $isVoluntario || $isFamiliar;
        
        $isAdministrative = !$isClinico && !$isExternal;

        $sections = [];

        // 1. DASHBOARD DINÁMICO
        if ($isMedico) {
            $sections[] = $this->buildSection('Dashboard Médico', 'ph-house', 'admin.medico.dashboard');
        } elseif ($isEnfermero) {
            $sections[] = $this->buildSection('Dashboard Enfermería', 'ph-house', 'admin.enfermeria.dashboard');
        } elseif ($isPsicologo) {
            $sections[] = $this->buildSection('Dashboard Psicológico', 'ph-house', 'admin.psicologia.dashboard');
        } elseif ($isFisioterapeuta) {
            $sections[] = $this->buildSection('Dashboard Fisioterapia', 'ph-house', 'admin.fisioterapia.dashboard');
        } elseif ($isNutricionista) {
            $sections[] = $this->buildSection('Dashboard Nutrición', 'ph-house', 'admin.nutricion.dashboard');
        } elseif ($isVoluntario) {
            $sections[] = $this->buildSection('Dashboard Voluntario', 'ph-house', 'admin.voluntario.dashboard');
        } elseif ($isFamiliar) {
            $sections[] = $this->buildSection('Portal Familiar', 'ph-house', 'admin.familiar.dashboard');
        } else {
            $sections[] = $this->buildSection('Inicio', 'ph-house', 'dashboard');
        }

        // 2. GESTIÓN DEL SISTEMA (Admins)
        if ($isAdministrative || $isSuperadmin) {
            $sections[] = $this->buildSection('Gestión del Sistema', 'ph-gear', null, [
                $this->buildItem('Usuarios', 'admin.usuarios.index', 'usuarios.ver'),
                $this->buildItem('Roles y permisos', 'admin.roles-permisos.index', 'roles.ver'),
                $this->buildItem('Configuración general', 'admin.areas-institucionales.index', 'areas.ver'),
            ]);

            $sections[] = $this->buildSection('Admisiones', 'ph-user-plus', null, [
                $this->buildItem('Preadmisiones', 'admin.admisiones.preadmisiones', 'admisiones.ver_dashboard'),
                $this->buildItem('Flujo de admisión', 'admin.admisiones.preadmision', 'admisiones.ver_dashboard'),
            ]);
        }

        // 3. SECCIONES CLÍNICAS Y OPERATIVAS
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

        // 4. PACIENTES / ADULTOS MAYORES
        $pacientesItems = [];
        if ($isAdministrative || $isSuperadmin) {
            $pacientesItems[] = $this->buildItem('Expedientes', 'admin.adultos-mayores.index', 'adultos.ver');
            $pacientesItems[] = $this->buildItem('Familiares y documentos', 'admin.familia-social.resumen', 'familiares.ver');
        }
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

        // 5. PERSONAL Y APOYO
        if ($isAdministrative || $isSuperadmin) {
            $sections[] = $this->buildSection('Personal y Apoyo', 'ph-users-three', null, [
                $this->buildItem('Personal institucional', 'admin.personal-institucional', 'personal_institucional.ver'),
                $this->buildItem('Voluntariado', 'admin.voluntariado.index', 'voluntarios.ver'),
            ]);
        }

        // 6. SALUD (Integrado)
        $saludItems = [];
        if ($isAdministrative || $isSuperadmin) {
            $saludItems[] = $this->buildItem('Panel clínico integrado', 'admin.salud-seguimiento.index', 'salud.ver');
        }
        if ($isMedico || $isSuperadmin) {
            $saludItems[] = $this->buildItem('Ficha médica', 'admin.salud-seguimiento.ficha.index', 'salud.ficha.ver');
            $saludItems[] = $this->buildItem('Signos vitales', 'admin.salud-seguimiento.signos.index', 'salud.signos.ver');
            $saludItems[] = $this->buildItem('Medicación', 'admin.salud-seguimiento.medicacion.index', 'salud.medicacion.ver');
            $saludItems[] = $this->buildItem('Alertas clínicas', 'admin.salud-seguimiento.alertas.index', 'salud.alertas.ver');
        }
        if (!empty(array_filter($saludItems))) {
            $sections[] = $this->buildSection('Salud', 'ph-heartbeat', null, $saludItems);
        }

        // 7. ACTIVIDADES
        if ($isAdministrative || $isSuperadmin) {
            $sections[] = $this->buildSection('Actividades', 'ph-calendar-check', null, [
                $this->buildItem('Actividades institucionales', 'admin.actividades.index', 'actividades.ver'),
                $this->buildItem('Programación', 'admin.actividades.tipos', 'actividades.ver'),
                $this->buildItem('Asistencia', 'admin.actividades.asistencia', 'actividades.ver'),
            ]);
        }

        // 8. REPORTES
        $reportesItems = [];
        if ($isAdministrative || $isSuperadmin) {
            $reportesItems[] = $this->buildItem('Reportes administrativos', 'admin.adultos-mayores.reporte-institucional', 'reportes.ver');
        }
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

        // 9. AUDITORÍA Y CONTROL
        if ($isAdministrative || $isSuperadmin) {
            $sections[] = $this->buildSection('Auditoría y Control', 'ph-shield-check', null, [
                $this->buildItem('Auditoría', 'admin.bitacora.index', 'bitacora.ver'),
            ]);
        }

        // Clean up empty sections
        $sections = array_filter($sections, function($section) {
            return !is_null($section) && (!empty($section['route']) || !empty($section['items']));
        });

        return array_values($sections);
    }

    private function buildSection($title, $icon, $route = null, $items = [])
    {
        // Filter out null items
        $items = array_filter($items);
        
        // If it's just a section with children, and all children are null, return null
        if (!$route && empty($items)) {
            return null;
        }

        // Si es un enlace directo y la ruta no existe, no mostramos la seccion.
        if ($route && !Route::has($route)) {
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
            'route' => $route && Route::has($route) ? route($route) : null,
            'items' => array_values($items),
            'active' => $active,
        ];
    }

    private function buildItem($label, $route, $permission = null, $badge = null)
    {
        if ($permission && !auth()->user()->can($permission) && !auth()->user()->hasRole('Superadmin')) {
            return null;
        }

        if (!Route::has($route)) {
            return null; 
        }

        return [
            'label' => $label,
            'route' => route($route),
            'active' => request()->routeIs($route),
            'badge' => $badge,
        ];
    }
}
