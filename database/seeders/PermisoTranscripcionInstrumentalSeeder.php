<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** Incorporación acotada: no sincroniza ni elimina permisos ajenos. */
final class PermisoTranscripcionInstrumentalSeeder extends Seeder
{
    public function run(): void
    {
        $permiso = Permission::findOrCreate('aplicaciones_instrumento.transcribir', 'web');
        foreach (['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A'] as $rol) {
            Role::findByName($rol, 'web')->givePermissionTo($permiso);
        }
        Role::findByName('SUPERADMINISTRADOR', 'web')->revokePermissionTo($permiso);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
