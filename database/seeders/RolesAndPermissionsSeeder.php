<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Definir la lista estricta de permisos por módulo
        $permisos = [
            // Administración
            'personal_institucional.ver',
            'usuarios.ver',
            'usuarios.crear',
            'usuarios.editar',
            'usuarios.cambiar_estado',
            'roles.ver',
            'roles.editar_permisos',
            'areas.ver',
            'areas.crear',
            'areas.editar',
            'areas.cambiar_estado',
            'areas.reportes',
            'turnos.ver',
            'turnos.crear',
            'turnos.editar',
            'turnos.cambiar_estado',
            'turnos.asignar',
            'turnos.finalizar',
            'turnos.reportes',
            'bitacora.ver',

            // Admisiones
            'admisiones.ver_dashboard',
            'admisiones.crear',
            'valoracion_enfermeria.ver',
            'valoracion_medica.ver',

            // Nuevos permisos de Ficha de Usuario
            'documentos_usuarios.ver',
            'documentos_usuarios.subir',
            'documentos_usuarios.validar',
            'documentos_usuarios.observar',
            'documentos_usuarios.reemplazar',
            'documentos_usuarios.anular',
            'documentos_usuarios.descargar',
            'documentos_usuarios.reportes',
            
            'usuarios.acceso.ver',
            'usuarios.acceso.bloquear',
            'usuarios.acceso.restablecer_password',
            
            'usuarios.historial.ver',
            
            'usuarios.reportes',
            'usuarios.reportes.pdf',
            'usuarios.reportes.excel',
            
            'usuarios.horarios.ver',
            'usuarios.horarios.asignar',
            'usuarios.horarios.finalizar',

            // Adultos mayores
            'adultos.ver',
            'adultos.crear',
            'adultos.editar',
            'adultos.cambiar_estado',
            'adultos.archivar',
            'adultos.restaurar',
            'adultos.ver_expediente',

            // Familiares y documentos
            'familiares.ver',
            'familiares.crear',
            'familiares.editar',
            'familiares.anular',
            'documentos.ver',
            'documentos.subir',
            'documentos.descargar',
            'documentos.archivar',

            // Salud y seguimiento
            'salud.ver',
            'atenciones.ver',
            'atenciones.crear',
            'atenciones.editar',
            'atenciones.anular',
            'observaciones.ver',
            'observaciones.crear',
            'observaciones.editar',
            'observaciones.anular',
            'signos_vitales.ver',
            'signos_vitales.crear',
            'signos_vitales.editar',
            'medicacion.ver',
            'medicacion.crear',
            'medicacion.editar',
            'medicacion.suspender',

            // Salud — permisos específicos de escritura médica
            'ficha_medica.crear',
            'ficha_medica.editar',
            'ficha_medica.archivar',
            'valoracion_funcional.crear',
            'valoracion_funcional.editar',
            'administracion_medicacion.registrar',
            
            // Enfermería Operativa
            'enfermeria.ver_dashboard',
            'enfermeria.ver_pacientes_asignados',
            'enfermeria.ver_ficha_paciente',

            // Evaluaciones cognitivas
            'evaluaciones.ver',
            'evaluaciones.crear',
            'evaluaciones.editar',
            'evaluaciones.anular',
            'evaluaciones.historial',
            'evaluaciones.resultados',

            // Actividades y voluntariado
            'actividades.ver',
            'actividades.crear',
            'actividades.editar',
            'actividades.anular',
            'voluntarios.ver',
            'voluntarios.crear',
            'voluntarios.editar',
            'voluntarios.cambiar_estado',
            'asignaciones.ver',
            'asignaciones.crear',
            'asignaciones.editar',
            'asistencia.ver',
            'asistencia.registrar',

            // Reportes y alertas
            'reportes.ver',
            'reportes.individual',
            'reportes.institucional',
            'reportes.bienestar',
            'reportes.exportar_pdf',
            'alertas.ver',
            'alertas.gestionar',
            'alertas.crear',
            'alertas.atender',
            'alertas.cerrar',
            'alertas.anular',
        ];

        // 2. Crear permisos
        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // 3. Crear/asegurar roles institucionales
        $rolesInstitucionales = [
            'SUPERADMINISTRADOR',
            'ADMINISTRADOR',
            'ENFERMEROS',
            'MEDICO GENERAL/GERIATRA',
            'PSICOLOGO/A',
            'PEDAGOGO',
            'NUTRICIONISTA',
            'FISIOTERAPEUTA',
            'VOLUNTARIO',
            'FAMILIAR'
        ];

        $rolesModels = [];
        foreach ($rolesInstitucionales as $roleName) {
            $rolesModels[$roleName] = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 4. Asignar permisos

        // SUPERADMINISTRADOR: Todos los permisos creados arriba y los que existan en BD.
        $todosLosPermisos = Permission::all();
        $rolesModels['SUPERADMINISTRADOR']->syncPermissions($todosLosPermisos);

        // ADMINISTRADOR
        $rolesModels['ADMINISTRADOR']->syncPermissions([
            'personal_institucional.ver',
            'usuarios.ver',
            'areas.ver',
            'areas.reportes',
            'turnos.ver',
            'turnos.crear',
            'turnos.editar',
            'turnos.asignar',
            'turnos.finalizar',
            'turnos.reportes',

            'admisiones.ver_dashboard',
            'admisiones.crear',
            'salud.ver',
            'valoracion_enfermeria.ver',
            'valoracion_medica.ver',
            
            // Permisos de Ficha del Usuario
            'documentos_usuarios.ver',
            'documentos_usuarios.subir',
            'documentos_usuarios.validar',
            'documentos_usuarios.observar',
            'documentos_usuarios.reemplazar',
            'documentos_usuarios.descargar',
            'documentos_usuarios.reportes',
            'usuarios.acceso.ver',
            'usuarios.acceso.bloquear',
            'usuarios.acceso.restablecer_password',
            'usuarios.historial.ver',
            'usuarios.reportes',
            'usuarios.reportes.pdf',
            'usuarios.reportes.excel',
            'usuarios.horarios.ver',
            'usuarios.horarios.asignar',
            'usuarios.horarios.finalizar',

            'adultos.ver',
            'adultos.crear',
            'adultos.editar',
            'adultos.cambiar_estado',
            'adultos.ver_expediente',
            'familiares.ver',
            'familiares.crear',
            'familiares.editar',
            'familiares.anular',
            'documentos.ver',
            'documentos.subir',
            'documentos.descargar',
            'documentos.archivar',
            'actividades.ver',
            'actividades.crear',
            'actividades.editar',
            'actividades.anular',
            'voluntarios.ver',
            'voluntarios.crear',
            'voluntarios.editar',
            'voluntarios.cambiar_estado',
            'asignaciones.ver',
            'asignaciones.crear',
            'asignaciones.editar',
            'reportes.ver',
            'reportes.institucional',
            'reportes.bienestar',
            'alertas.ver',
            'alertas.gestionar',
            'alertas.atender',
            'alertas.cerrar',
        ]);

        // ENFERMEROS Y MEDICO GENERAL/GERIATRA (basados en personal_salud + enfermería)
        $permisosClinicos = [
            'areas.ver',
            'turnos.ver',
            'turnos.reportes',
            'adultos.ver',
            'adultos.ver_expediente',
            'salud.ver',
            'atenciones.ver',
            'atenciones.crear',
            'atenciones.editar',
            'atenciones.anular',
            'observaciones.ver',
            'observaciones.crear',
            'observaciones.editar',
            'observaciones.anular',
            'signos_vitales.ver',
            'signos_vitales.crear',
            'signos_vitales.editar',
            'medicacion.ver',
            'medicacion.crear',
            'medicacion.editar',
            'medicacion.suspender',
            'ficha_medica.crear',
            'ficha_medica.editar',
            'ficha_medica.archivar',
            'valoracion_funcional.crear',
            'valoracion_funcional.editar',
            'administracion_medicacion.registrar',
            'evaluaciones.ver',
            'evaluaciones.crear',
            'evaluaciones.editar',
            'evaluaciones.anular',
            'evaluaciones.historial',
            'evaluaciones.resultados',
            'reportes.ver',
            'reportes.individual',
            'reportes.bienestar',
            'alertas.ver',
            'alertas.gestionar',
            'alertas.crear',
            'alertas.atender',
            'alertas.cerrar',
            'alertas.anular',
        ];

        // Añadir permisos específicos de enfermería
        $permisosEnfermeria = [
            'enfermeria.ver_dashboard',
            'enfermeria.ver_pacientes_asignados',
            'enfermeria.ver_ficha_paciente',
            'salud.resumen.ver',
            'salud.ficha.ver',
            'salud.ficha.crear',
            'salud.ficha.editar',
            'salud.ficha.archivar',
            'salud.ficha.anular',
            'salud.ficha.restaurar',
            'salud.medicacion.ver',
            'salud.medicacion.crear',
            'salud.medicacion.editar',
            'salud.medicacion.suspender',
            'salud.medicacion.finalizar',
            'salud.medicacion.anular',
            'salud.administracion.ver',
            'salud.administracion.crear',
            'salud.administracion.editar',
            'salud.administracion.anular',
            'salud.signos.ver',
            'salud.signos.crear',
            'salud.signos.editar',
            'salud.signos.anular',
            'salud.valoracion.ver',
            'salud.valoracion.crear',
            'salud.valoracion.editar',
            'salud.valoracion.anular',
            'salud.alertas.ver',
            'salud.alertas.gestionar',
            'salud.reportes.ver',
            'salud.reportes.generar',
            'habitaciones.ver',
            'habitaciones.crear',
            'habitaciones.editar',
            'habitaciones.eliminar',
            'camas.ver',
            'camas.crear',
            'camas.editar',
            'turnos_enfermeria.ver',
            'turnos_enfermeria.crear',
            'turnos_enfermeria.editar',
            'valoracion_enfermeria.ver',
            'valoracion_enfermeria.crear',
            'valoracion_enfermeria.editar',
            'valoracion_enfermeria.anular',
            'valoracion_medica.ver',
            'valoracion_medica.crear',
            'valoracion_medica.editar',
            'valoracion_medica.anular',
            'asignacion_turno.ver',
            'asignacion_turno.crear',
            'asignacion_turno.editar',
            'asignacion_turno.anular',
            'plan_cuidado.ver',
            'plan_cuidado.crear',
            'plan_cuidado.editar',
            'plan_cuidado.validar',
            'plan_cuidado.cerrar',
            'plan_cuidado.anular',
            'tareas.ver',
            'tareas.crear',
            'tareas.registrar_resultado',
            'tareas.omitir',
            'tareas.anular',
            'seguimiento.ver',
            'seguimiento.crear',
            'seguimiento.editar',
            'alertas.crear',
            'alertas.atender',
            'alertas.cerrar',
            'alertas.anular',
            'pase_turno.ver',
            'pase_turno.generar',
            'pase_turno.recibir',
        ];

        // Asegurar que estos permisos existan (por si no se corrió FlujoClinicoPermissionsSeeder)
        foreach ($permisosEnfermeria as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $rolesModels['ENFERMEROS']->syncPermissions(array_merge(
            $permisosClinicos,
            array_diff($permisosEnfermeria, ['valoracion_medica.ver', 'valoracion_medica.crear', 'valoracion_medica.editar', 'valoracion_medica.anular']),
            ['salud.ver', 'valoracion_enfermeria.ver']
        ));

        $rolesModels['MEDICO GENERAL/GERIATRA']->syncPermissions(array_merge(
            $permisosClinicos,
            array_diff($permisosEnfermeria, ['valoracion_enfermeria.ver', 'valoracion_enfermeria.crear', 'valoracion_enfermeria.editar', 'valoracion_enfermeria.anular']),
            ['salud.ver', 'valoracion_medica.ver']
        ));

        // PROFESIONALES ESPECÍFICOS (PSICOLOGO/A, PEDAGOGO, NUTRICIONISTA, FISIOTERAPEUTA)
        $permisosProfesionales = [
            'adultos.ver',
            'adultos.ver_expediente',
            'actividades.ver',
            'actividades.crear',
            'observaciones.ver',
            'observaciones.crear',
            'reportes.ver',
            'reportes.individual'
        ];
        
        $rolesModels['PSICOLOGO/A']->syncPermissions($permisosProfesionales);
        $rolesModels['PEDAGOGO']->syncPermissions($permisosProfesionales);
        $rolesModels['NUTRICIONISTA']->syncPermissions($permisosProfesionales);
        $rolesModels['FISIOTERAPEUTA']->syncPermissions($permisosProfesionales);

        // VOLUNTARIO
        $rolesModels['VOLUNTARIO']->syncPermissions([
            'adultos.ver',
            'actividades.ver',
            'asignaciones.ver',
            'asistencia.ver',
            'asistencia.registrar',
            'observaciones.ver',
            'observaciones.crear',
            'turnos.ver',
        ]);

        // FAMILIAR
        $rolesModels['FAMILIAR']->syncPermissions([
            'adultos.ver',
            'actividades.ver',
            'reportes.ver',
            'reportes.individual',
        ]);
    }
}
