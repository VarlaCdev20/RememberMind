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
            'bitacora.ver',

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
        ];

        // 2. Crear permisos
        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso, 'guard_name' => 'web']);
        }

        // 3. Crear roles
        $roleAdmin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $rolePersonalSalud = Role::firstOrCreate(['name' => 'personal_salud', 'guard_name' => 'web']);
        $rolePersonalAdmin = Role::firstOrCreate(['name' => 'personal_admin', 'guard_name' => 'web']);
        $roleVoluntario = Role::firstOrCreate(['name' => 'voluntario', 'guard_name' => 'web']);
        $roleFamiliar = Role::firstOrCreate(['name' => 'familiar', 'guard_name' => 'web']);

        // 4. Asignar permisos

        // ADMIN: Todos los permisos creados arriba.
        // Usar $permisos en lugar de Permission::all() previene re-asignar permisos obsoletos como adulto_mayor.eliminar
        $roleAdmin->syncPermissions($permisos);

        // PERSONAL ADMIN
        $rolePersonalAdmin->syncPermissions([
            'usuarios.ver',
            'areas.ver',
            'areas.reportes',
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
        ]);

        // PERSONAL SALUD
        $rolePersonalSalud->syncPermissions([
            'areas.ver',
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
        ]);

        // VOLUNTARIO
        $roleVoluntario->syncPermissions([
            'adultos.ver',
            'actividades.ver',
            'asignaciones.ver',
            'asistencia.ver',
            'asistencia.registrar',
            'observaciones.ver',
            'observaciones.crear',
        ]);

        // FAMILIAR
        $roleFamiliar->syncPermissions([
            'adultos.ver',
            'actividades.ver',
            'reportes.ver',
            'reportes.individual',
        ]);
        
        // El permiso antiguo 'adulto_mayor.eliminar' ya no se asigna a nadie y queda obsoleto/huerfano,
        // sin romper la base de datos ni los usuarios.
    }
}
