<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
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

        $this->command->info('Permisos del flujo clínico creados.');
    }
}
