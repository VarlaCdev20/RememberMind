<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** Concede solo la lectura de la fuente IADL aprobada, sin resincronizar roles ni crear cuentas. */
final class PermisosLecturaIadlPetersenSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $medicina = Role::findByName('MEDICO GENERAL/GERIATRA', 'web');
            $psicologia = Role::findByName('PSICOLOGO/A', 'web');
            $superadministracion = Role::findByName('SUPERADMINISTRADOR', 'web');
            $lectura = Permission::findOrCreate('controles_funcionales_iadl.ver', 'web');

            foreach ([$medicina, $psicologia, $superadministracion] as $rol) {
                $rol->givePermissionTo($lectura);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
