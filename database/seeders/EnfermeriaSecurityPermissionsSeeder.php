<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class EnfermeriaSecurityPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $rol = Role::query()->where('name', 'ENFERMEROS')->where('guard_name', 'web')->first();
        if (! $rol) return;

        $rol->revokePermissionTo(array_filter([
            'medicacion.crear', 'medicacion.editar', 'medicacion.suspender',
            'salud.medicacion.crear', 'salud.medicacion.editar', 'salud.medicacion.suspender',
            'salud.medicacion.finalizar', 'salud.medicacion.anular',
            'ficha_medica.crear', 'ficha_medica.editar', 'ficha_medica.archivar',
            'salud.ficha.crear', 'salud.ficha.editar', 'salud.ficha.archivar',
            'salud.ficha.anular', 'salud.ficha.restaurar',
            'signos_vitales.editar', 'salud.signos.editar', 'salud.signos.anular',
            'salud.administracion.editar', 'salud.administracion.anular',
        ], fn (string $permiso) => \Spatie\Permission\Models\Permission::where('name', $permiso)->where('guard_name', 'web')->exists()));

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
