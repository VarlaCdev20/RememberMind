<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** Instala únicamente los tres permisos aprobados, sin sincronizar permisos ajenos ni crear cuentas. */
final class PermisosPetersenOriginalSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            // La instalación institucional de roles es una precondición, nunca un fallback.
            $medicina = Role::findByName('MEDICO GENERAL/GERIATRA', 'web');
            $psicologia = Role::findByName('PSICOLOGO/A', 'web');
            $consulta = Role::findByName('SUPERADMINISTRADOR', 'web');
            $lectura = Permission::findOrCreate('valoraciones_petersen.ver', 'web');
            $escritura = [
                Permission::findOrCreate('valoraciones_petersen.crear', 'web'),
                Permission::findOrCreate('valoraciones_petersen.rectificar', 'web'),
            ];
            foreach ([$medicina, $psicologia] as $rol) {
                $rol->givePermissionTo([$lectura, ...$escritura]);
            }
            $consulta->givePermissionTo($lectura);
            $consulta->revokePermissionTo($escritura);
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
