<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** Únicamente el permiso y los dos roles expresamente autorizados el 04/11/2026. */
final class PermisoEjecucionInstitucionalSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $p = Permission::findOrCreate('evaluaciones_expertas.ejecutar_institucional', 'web');
            foreach (Role::query()->where('guard_name', 'web')->whereNotIn('name', ['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A'])->get() as $otro) {
                $otro->revokePermissionTo($p);
            }
            foreach (['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A'] as $rol) { Role::findByName($rol, 'web')->givePermissionTo($p); }
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
