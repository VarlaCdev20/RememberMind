<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\PermisosLecturaIadlPetersenSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class PermisosLecturaIadlPetersenTest extends TestCase
{
    use RefreshDatabase;

    public function test_instalacion_acotada_es_idempotente_y_no_modifica_permisos_ajenos(): void
    {
        foreach (['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'SUPERADMINISTRADOR', 'ENFERMEROS'] as $nombre) {
            Role::findOrCreate($nombre, 'web');
        }
        $ajeno = Permission::findOrCreate('permiso_sintetico.conservar', 'web');
        Role::findByName('ENFERMEROS', 'web')->givePermissionTo($ajeno);
        Role::findByName('PSICOLOGO/A', 'web')->givePermissionTo($ajeno);
        $permisosAntes = Permission::query()->pluck('name')->all();

        $this->seed(PermisosLecturaIadlPetersenSeeder::class);
        $this->seed(PermisosLecturaIadlPetersenSeeder::class);

        foreach (['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'SUPERADMINISTRADOR'] as $nombre) {
            self::assertTrue(Role::findByName($nombre, 'web')->hasPermissionTo('controles_funcionales_iadl.ver'));
        }
        self::assertFalse(Role::findByName('ENFERMEROS', 'web')->hasPermissionTo('controles_funcionales_iadl.ver'));
        self::assertTrue(Role::findByName('ENFERMEROS', 'web')->hasPermissionTo($ajeno));
        self::assertTrue(Role::findByName('PSICOLOGO/A', 'web')->hasPermissionTo($ajeno));
        $esperados = array_values(array_unique([...$permisosAntes, 'controles_funcionales_iadl.ver']));
        sort($esperados);
        self::assertSame($esperados, Permission::query()->orderBy('name')->pluck('name')->all());
        self::assertDatabaseMissing('permissions', ['name' => 'controles_funcionales_iadl.crear']);
        self::assertDatabaseCount('usuarios', 0);
        self::assertDatabaseCount('personal', 0);
        self::assertDatabaseCount('residentes', 0);
    }
}
