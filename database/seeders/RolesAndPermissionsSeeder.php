<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $rolesActivos = [
            'SUPERADMINISTRADOR', 'GERENTE', 'ADMINISTRADOR', 'ENFERMEROS', 'MEDICO GENERAL/GERIATRA',
            'PSICOLOGO/A', 'PEDAGOGO', 'NUTRICIONISTA', 'FISIOTERAPEUTA', 'FAMILIAR',
        ];
        Role::query()->whereNotIn('name', $rolesActivos)->delete();

        $recursos = [
            'usuarios', 'personal', 'areas', 'turnos', 'contactos', 'residentes', 'habitaciones', 'camas',
            'tipos_estudio_clinico', 'medicamentos', 'instrumentos', 'jornadas', 'asignaciones_personal',
            'preadmisiones', 'admisiones', 'residentes_contactos', 'historial_estados_residente',
            'ocupaciones_cama', 'documentos', 'consentimientos', 'atenciones', 'notas_clinicas',
            'antecedentes_clinicos', 'diagnosticos', 'alergias', 'seguros_residente', 'dispositivos_clinicos',
            'signos_vitales', 'objetivos_signos_vitales', 'valoraciones_dolor', 'mediciones_antropometricas', 'componentes_estudio',
            'estudios_clinicos', 'resultados_estudio', 'informes_estudio', 'documentos_clinicos', 'derivaciones',
            'incidentes', 'indicaciones_clinicas', 'asignaciones_residente_jornada', 'controles_cognitivos',
            'registros_conductuales', 'registros_sueno', 'registros_ingesta', 'registros_hidratacion',
            'registros_eliminacion', 'registros_movilidad', 'heridas', 'curaciones_herida', 'pases_turno',
            'planes_cuidado', 'intervenciones_cuidado', 'programaciones_cuidado', 'ejecuciones_cuidado',
            'prescripciones', 'horarios_prescripcion', 'administraciones_medicacion', 'preguntas_instrumento',
            'opciones_pregunta', 'aplicaciones_instrumento', 'respuestas_instrumento',
            'valoraciones_psicologicas', 'valoraciones_nutricionales', 'valoraciones_funcionales',
            'seguimientos_pedagogicos', 'actividades', 'participantes_actividad', 'visitas', 'alertas', 'eventos_alerta',
        ];

        $permisos = array_map(fn (string $recurso) => $recurso.'.ver', $recursos);
        $permisosCompatibilidadLectura = [
            'admisiones.ver_dashboard', 'personal_institucional.ver',
            'roles.ver', 'areas.reportes', 'salud.ver',
            'valoracion_enfermeria.ver', 'valoracion_medica.ver',
            'enfermeria.ver_dashboard', 'enfermeria.ver_pacientes_asignados',
            'enfermeria.ver_ficha_paciente',
            'asignacion_turno.ver', 'asignaciones.ver',
            'reportes.ver', 'reportes.institucional',
            'reportes.exportar_pdf', 'reportes.individual', 'bitacora.ver',
        ];
        $permisosCompatibilidadInstitucional = [
            'admisiones.crear',
            'usuarios.crear', 'usuarios.editar', 'usuarios.cambiar_estado',
            'usuarios.reportes.pdf', 'usuarios.reportes.excel',
            'areas.crear', 'areas.editar', 'areas.cambiar_estado',
            'turnos.crear', 'turnos.editar', 'turnos.cambiar_estado', 'turnos.asignar', 'turnos.finalizar',
            'habitaciones.crear', 'habitaciones.editar',
            'camas.crear', 'camas.editar',
            'documentos.subir', 'documentos.archivar',
            'roles.editar_permisos',
        ];
        $permisos = array_merge($permisos, [
            'auditoria.ver', 'usuarios.gestionar', 'personal.gestionar', 'areas.gestionar', 'turnos.gestionar',
            'jornadas.gestionar', 'asignaciones_personal.gestionar',
            'preadmisiones.crear', 'preadmisiones.revisar', 'admisiones.formalizar',
            'residentes.gestionar', 'residentes.actualizar_administrativo', 'residentes_contactos.gestionar',
            'habitaciones.gestionar', 'camas.gestionar', 'contactos.gestionar', 'documentos.gestionar',
            'ocupaciones_cama.gestionar', 'documentos.validar', 'consentimientos.gestionar',
            'atenciones.crear', 'notas_clinicas.crear', 'diagnosticos.crear',
            'antecedentes_clinicos.crear', 'alergias.crear', 'signos_vitales.crear', 'objetivos_signos_vitales.gestionar',
            'valoraciones_dolor.crear', 'mediciones_antropometricas.crear', 'estudios_clinicos.crear',
            'resultados_estudio.crear', 'informes_estudio.crear', 'documentos_clinicos.crear',
            'indicaciones_clinicas.crear', 'derivaciones.crear', 'incidentes.crear', 'asignaciones_residente_jornada.gestionar', 'controles_cognitivos.crear',
            'registros_conductuales.crear', 'registros_sueno.crear', 'registros_ingesta.crear',
            'registros_hidratacion.crear', 'registros_eliminacion.crear', 'registros_movilidad.crear',
            'heridas.crear', 'curaciones_herida.crear', 'pases_turno.crear', 'planes_cuidado.crear',
            'pases_turno.editar', 'planes_cuidado.editar', 'planes_cuidado.cerrar', 'ejecuciones_cuidado.gestionar',
            'ejecuciones_cuidado.crear', 'prescripciones.crear', 'prescripciones.editar', 'prescripciones.suspender',
            'atenciones.editar', 'atenciones.anular', 'notas_clinicas.editar', 'notas_clinicas.anular',
            'signos_vitales.editar', 'signos_vitales.anular', 'administraciones_medicacion.crear',
            'aplicaciones_instrumento.crear', 'aplicaciones_instrumento.editar', 'aplicaciones_instrumento.anular',
            'valoraciones_psicologicas.crear', 'valoraciones_nutricionales.crear',
            'valoraciones_funcionales.crear', 'valoraciones_funcionales.editar', 'seguimientos_pedagogicos.crear', 'actividades.gestionar',
            'visitas.gestionar', 'alertas.gestionar', 'alertas.reconocer', 'alertas.asignar',
            'alertas.seguimiento', 'alertas.cerrar',
            'valoracion_enfermeria.registrar', 'valoracion_enfermeria.editar',
        ], $permisosCompatibilidadLectura, $permisosCompatibilidadInstitucional);

        $permisos = array_values(array_unique($permisos));
        foreach ($permisos as $permiso) {
            Permission::findOrCreate($permiso, 'web');
        }

        $roles = collect($rolesActivos)->mapWithKeys(fn (string $nombre) => [$nombre => Role::findOrCreate($nombre, 'web')]);

        // Durante la construcción del sistema recibe todos los permisos web,
        // incluidos los que se hayan creado fuera de este seeder.
        $roles['SUPERADMINISTRADOR']->syncPermissions(
            Permission::query()->where('guard_name', 'web')->get()
        );

        // Dirección institucional: personal, organización, planificación maestra
        // y lectura de los indicadores necesarios para conducir la residencia.
        $roles['GERENTE']->syncPermissions(array_values(array_unique(array_merge($this->permitir($permisos, [
            'personal', 'areas', 'turnos', 'jornadas.ver', 'asignaciones_personal.ver',
            'residentes.ver', 'habitaciones.ver', 'camas.ver', 'ocupaciones_cama.ver',
            'preadmisiones.ver', 'admisiones.ver', 'alertas.ver', 'incidentes.ver',
            'reportes.ver', 'reportes.institucional',
        ]), [
            'personal_institucional.ver', 'areas.reportes', 'turnos.crear', 'turnos.editar',
            'turnos.cambiar_estado', 'asignacion_turno.ver', 'asignaciones.ver',
        ]))));

        $roles['ADMINISTRADOR']->syncPermissions(array_values(array_unique(array_merge($this->permitir($permisos, [
            'personal.ver', 'areas.ver', 'turnos.ver', 'jornadas', 'asignaciones_personal', 'preadmisiones',
            'admisiones', 'residentes.ver', 'residentes.gestionar', 'residentes.actualizar_administrativo',
            'contactos', 'residentes_contactos', 'habitaciones', 'camas',
            'ocupaciones_cama', 'documentos', 'consentimientos', 'seguros_residente', 'actividades', 'visitas',
            'alertas.ver', 'alertas.reconocer', 'alertas.asignar', 'alertas.seguimiento', 'alertas.cerrar',
            'incidentes.ver',
        ]), [
            'admisiones.ver_dashboard', 'admisiones.crear', 'asignacion_turno.ver', 'asignaciones.ver',
            'turnos.asignar', 'turnos.finalizar',
            'reportes.ver', 'reportes.institucional', 'reportes.exportar_pdf', 'reportes.individual',
            'documentos.subir', 'documentos.archivar',
        ]))));

        $roles['MEDICO GENERAL/GERIATRA']->syncPermissions(array_values(array_unique(array_merge($this->permitir($permisos, [
            'residentes.ver', 'atenciones', 'notas_clinicas', 'antecedentes_clinicos', 'diagnosticos', 'alergias',
            'seguros_residente.ver', 'dispositivos_clinicos', 'signos_vitales', 'objetivos_signos_vitales', 'valoraciones_dolor',
            'mediciones_antropometricas', 'estudios_clinicos', 'resultados_estudio', 'informes_estudio',
            'documentos_clinicos', 'derivaciones', 'incidentes', 'indicaciones_clinicas', 'controles_cognitivos',
            'registros_', 'heridas', 'curaciones_herida', 'planes_cuidado', 'prescripciones',
            'horarios_prescripcion.ver', 'administraciones_medicacion', 'aplicaciones_instrumento',
            'valoraciones_', 'alertas.ver',
        ]), ['salud.ver', 'valoracion_medica.ver', 'valoracion_enfermeria.ver']))));

        $roles['ENFERMEROS']->syncPermissions(array_values(array_unique(array_merge($this->permitir($permisos, [
            'residentes.ver', 'ocupaciones_cama.ver', 'atenciones.ver', 'atenciones.crear', 'atenciones.editar', 'notas_clinicas', 'antecedentes_clinicos.ver',
            'diagnosticos.ver', 'alergias.ver', 'dispositivos_clinicos', 'signos_vitales', 'valoraciones_dolor',
            'estudios_clinicos.ver', 'resultados_estudio.ver', 'indicaciones_clinicas.ver', 'incidentes',
            'asignaciones_residente_jornada', 'controles_cognitivos', 'registros_', 'registros_ingesta.crear', 'registros_hidratacion.crear', 'registros_eliminacion.crear', 'registros_movilidad.crear', 'heridas', 'curaciones_herida', 'pases_turno', 'planes_cuidado',
            'intervenciones_cuidado.ver', 'programaciones_cuidado.ver', 'ejecuciones_cuidado',
            'prescripciones.ver', 'horarios_prescripcion.ver', 'administraciones_medicacion', 'alertas',
        ]), [
            'salud.ver', 'turnos.ver', 'objetivos_signos_vitales.ver', 'valoracion_enfermeria.ver', 'valoracion_enfermeria.registrar', 'valoracion_enfermeria.editar',
            'enfermeria.ver_dashboard', 'enfermeria.ver_pacientes_asignados',
            'enfermeria.ver_ficha_paciente',
            // Lecturas de cuidados aprobadas por la propietaria el 2026-10-07.
            'registros_conductuales.ver', 'registros_sueno.ver', 'registros_ingesta.ver',
            'registros_hidratacion.ver', 'registros_eliminacion.ver', 'registros_movilidad.ver',
        ]))));

        $roles['PSICOLOGO/A']->syncPermissions($this->permitir($permisos, ['residentes.ver', 'atenciones', 'notas_clinicas', 'controles_cognitivos.ver', 'registros_conductuales', 'registros_sueno.ver', 'instrumentos.ver', 'preguntas_instrumento.ver', 'opciones_pregunta.ver', 'aplicaciones_instrumento', 'respuestas_instrumento.ver', 'valoraciones_psicologicas', 'planes_cuidado.ver', 'alertas.ver']));
        $roles['NUTRICIONISTA']->syncPermissions($this->permitir($permisos, ['residentes.ver', 'atenciones', 'notas_clinicas', 'diagnosticos.ver', 'alergias.ver', 'indicaciones_clinicas.ver', 'mediciones_antropometricas', 'registros_ingesta.ver', 'registros_hidratacion.ver', 'registros_eliminacion.ver', 'valoraciones_nutricionales', 'planes_cuidado']));
        $roles['FISIOTERAPEUTA']->syncPermissions($this->permitir($permisos, ['residentes.ver', 'atenciones', 'notas_clinicas', 'diagnosticos.ver', 'dispositivos_clinicos.ver', 'signos_vitales.ver', 'valoraciones_dolor', 'registros_movilidad', 'valoraciones_funcionales', 'planes_cuidado']));
        $roles['PEDAGOGO']->syncPermissions($this->permitir($permisos, ['residentes.ver', 'atenciones', 'notas_clinicas', 'indicaciones_clinicas.ver', 'controles_cognitivos.ver', 'registros_conductuales.ver', 'planes_cuidado', 'seguimientos_pedagogicos', 'actividades']));
        $roles['FAMILIAR']->syncPermissions($this->permitir($permisos, ['residentes.ver', 'residentes_contactos.ver', 'documentos.ver', 'consentimientos.ver', 'actividades.ver', 'participantes_actividad.ver', 'visitas.ver']));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function permitir(array $permisos, array $prefijos): array
    {
        return array_values(array_filter($permisos, function (string $permiso) use ($prefijos): bool {
            foreach ($prefijos as $prefijo) {
                if ((str_starts_with($prefijo, '.') && str_ends_with($permiso, $prefijo))
                    || $permiso === $prefijo
                    || str_starts_with($permiso, $prefijo.'.')) {
                    return true;
                }
            }

            return false;
        }));
    }
}
