<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** Instala solo los dos permisos aprobados, sin resincronizar permisos ajenos. */
final class PermisosCapturaFuncionamientoCotidianoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $roles = [Role::findByName('MEDICO GENERAL/GERIATRA', 'web'), Role::findByName('PSICOLOGO/A', 'web')];
            foreach (['controles_funcionales_iadl.crear', 'controles_funcionales_iadl.rectificar'] as $nombre) {
                $permiso = Permission::findOrCreate($nombre, 'web');
                foreach ($roles as $rol) {
                    $rol->givePermissionTo($permiso);
                }
            }
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
