<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class FlujoClinicoPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permisos = [
            // ── Habitaciones y camas ─────────────────────────────────────────────
            'habitaciones.ver',
            'habitaciones.crear',
            'habitaciones.editar',
            'habitaciones.eliminar',
            'camas.ver',
            'camas.crear',
            'camas.editar',

            // ── Turnos de enfermería ─────────────────────────────────────────────
            'turnos_enfermeria.ver',
            'turnos_enfermeria.crear',
            'turnos_enfermeria.editar',

            // ── Valoraciones de admisión ─────────────────────────────────────────
            'valoracion_enfermeria.ver',
            'valoracion_enfermeria.crear',
            'valoracion_enfermeria.editar',
            'valoracion_enfermeria.anular',
            'valoracion_medica.ver',
            'valoracion_medica.crear',
            'valoracion_medica.editar',
            'valoracion_medica.anular',

            // ── Asignación de turno adulto ───────────────────────────────────────
            'asignacion_turno.ver',
            'asignacion_turno.crear',
            'asignacion_turno.editar',
            'asignacion_turno.anular',

            // ── Plan de cuidado ──────────────────────────────────────────────────
            'plan_cuidado.ver',
            'plan_cuidado.crear',
            'plan_cuidado.editar',
            'plan_cuidado.validar',
            'plan_cuidado.cerrar',
            'plan_cuidado.anular',

            // ── Tareas del plan ──────────────────────────────────────────────────
            'tareas.ver',
            'tareas.crear',
            'tareas.registrar_resultado',
            'tareas.omitir',
            'tareas.anular',

            // ── Seguimiento diario ───────────────────────────────────────────────
            'seguimiento.ver',
            'seguimiento.crear',
            'seguimiento.editar',

            // ── Alertas ──────────────────────────────────────────────────────────
            'alertas.ver',
            'alertas.crear',
            'alertas.atender',
            'alertas.cerrar',
            'alertas.anular',

            // ── Pase de turno ────────────────────────────────────────────────────
            'pase_turno.ver',
            'pase_turno.generar',
            'pase_turno.recibir',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(
                ['name' => $permiso, 'guard_name' => 'web']
            );
        }

        // Asignar permisos a roles existentes
        $this->asignarARol('Super Admin', $permisos);
        $this->asignarARol('Administrador', $permisos);

        // Rol médico/enfermero — permisos clínicos
        $permisosMedicos = [
            'habitaciones.ver', 'camas.ver', 'turnos_enfermeria.ver',
            'valoracion_enfermeria.ver', 'valoracion_enfermeria.crear', 'valoracion_enfermeria.editar',
            'valoracion_medica.ver', 'valoracion_medica.crear', 'valoracion_medica.editar',
            'asignacion_turno.ver', 'asignacion_turno.crear', 'asignacion_turno.editar',
            'plan_cuidado.ver', 'plan_cuidado.crear', 'plan_cuidado.editar',
            'plan_cuidado.validar', 'plan_cuidado.cerrar',
            'tareas.ver', 'tareas.crear', 'tareas.registrar_resultado', 'tareas.omitir',
            'seguimiento.ver', 'seguimiento.crear', 'seguimiento.editar',
            'alertas.ver', 'alertas.crear', 'alertas.atender', 'alertas.cerrar',
            'pase_turno.ver', 'pase_turno.generar', 'pase_turno.recibir',
        ];

        $this->asignarARol('Personal Salud', $permisosMedicos);
        $this->asignarARol('Enfermero', $permisosMedicos);
        $this->asignarARol('Médico', $permisosMedicos);

        $this->command->info('Permisos del flujo clínico creados y asignados.');
    }

    private function asignarARol(string $nombreRol, array $permisos): void
    {
        $rol = Role::where('name', $nombreRol)->first();
        if ($rol) {
            $rol->givePermissionTo(
                array_filter($permisos, fn($p) => Permission::where('name', $p)->exists())
            );
            $this->command->line("  Permisos asignados al rol: {$nombreRol}");
        }
    }
}
