<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SaludSeguimientoPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'salud.ver',
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
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['name' => $perm]);
        }

        // Asignar al rol 'admin'
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo($permissions);

        // Asignar al rol 'personal_salud'
        $salud = Role::firstOrCreate(['name' => 'personal_salud']);
        $salud->givePermissionTo([
            'salud.ver',
            'salud.resumen.ver',
            'salud.ficha.ver',
            'salud.ficha.crear',
            'salud.ficha.editar',
            'salud.medicacion.ver',
            'salud.medicacion.crear',
            'salud.medicacion.editar',
            'salud.medicacion.suspender',
            'salud.medicacion.finalizar',
            'salud.administracion.ver',
            'salud.administracion.crear',
            'salud.administracion.editar',
            'salud.signos.ver',
            'salud.signos.crear',
            'salud.signos.editar',
            'salud.valoracion.ver',
            'salud.valoracion.crear',
            'salud.valoracion.editar',
            'salud.alertas.ver',
            'salud.alertas.gestionar',
            'salud.reportes.ver',
            'salud.reportes.generar',
        ]);

        // Asignar al rol 'personal_administrativo'
        $administrativo = Role::firstOrCreate(['name' => 'personal_administrativo']);
        $administrativo->givePermissionTo([
            'salud.ver',
            'salud.resumen.ver',
            'salud.ficha.ver',
            'salud.medicacion.ver',
            'salud.administracion.ver',
            'salud.signos.ver',
            'salud.valoracion.ver',
            'salud.alertas.ver',
            'salud.reportes.ver',
        ]);
    }
}
