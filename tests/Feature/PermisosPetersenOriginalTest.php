<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\PermisosPetersenOriginalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class PermisosPetersenOriginalTest extends TestCase
{
    use RefreshDatabase;

    public function test_instalacion_acotada_es_idempotente_y_conserva_permisos_ajenos(): void
    {
        foreach (['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A', 'SUPERADMINISTRADOR', 'ENFERMEROS'] as $nombre) {
            Role::findOrCreate($nombre, 'web');
        }
        $ajeno = Permission::findOrCreate('permiso_sintetico.conservar', 'web');
        Role::findByName('ENFERMEROS', 'web')->givePermissionTo($ajeno);
        Role::findByName('PSICOLOGO/A', 'web')->givePermissionTo($ajeno);
        $permisosAntes = Permission::query()->pluck('name')->all();

        $this->seed(PermisosPetersenOriginalSeeder::class);
        $this->seed(PermisosPetersenOriginalSeeder::class);

        foreach (['MEDICO GENERAL/GERIATRA', 'PSICOLOGO/A'] as $nombre) {
            $rol = Role::findByName($nombre, 'web');
            foreach (['ver', 'crear', 'rectificar'] as $accion) {
                self::assertTrue($rol->hasPermissionTo('valoraciones_petersen.'.$accion));
            }
        }
        $consulta = Role::findByName('SUPERADMINISTRADOR', 'web');
        self::assertTrue($consulta->hasPermissionTo('valoraciones_petersen.ver'));
        self::assertFalse($consulta->hasPermissionTo('valoraciones_petersen.crear'));
        self::assertFalse($consulta->hasPermissionTo('valoraciones_petersen.rectificar'));
        self::assertFalse(Role::findByName('ENFERMEROS', 'web')->hasPermissionTo('valoraciones_petersen.ver'));
        self::assertTrue(Role::findByName('ENFERMEROS', 'web')->hasPermissionTo($ajeno));
        self::assertTrue(Role::findByName('PSICOLOGO/A', 'web')->hasPermissionTo($ajeno));
        $esperados = array_values(array_unique([...$permisosAntes, 'valoraciones_petersen.ver',
            'valoraciones_petersen.crear', 'valoraciones_petersen.rectificar']));
        sort($esperados);
        self::assertSame($esperados, Permission::query()->orderBy('name')->pluck('name')->all());
        self::assertDatabaseMissing('permissions', ['name' => 'controles_funcionales_iadl.ver']);
        self::assertDatabaseCount('usuarios', 0);
        self::assertDatabaseCount('personal', 0);
        self::assertDatabaseCount('residentes', 0);
    }
}
