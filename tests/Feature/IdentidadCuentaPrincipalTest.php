<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\GestionarUsuariosHttp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IdentidadCuentaPrincipalTest extends TestCase
{
    use RefreshDatabase;

    private function principal(): User
    {
        return User::where('correo', 'admincasaamandita@gmail.com')->first()
            ?? User::factory()->create(['correo' => 'admincasaamandita@gmail.com']);
    }

    public function test_proteccion_permanece_tras_cambiar_correo_y_no_se_transfiere(): void
    {
        $principal = $this->principal();
        config(['identidad.cuenta_principal_codigo' => $principal->getKey()]);
        $principal->update(['correo' => 'principal.actualizado@example.test']);
        $otraCuenta = User::factory()->create(['correo' => 'admincasaamandita@gmail.com']);

        $this->assertTrue($principal->fresh()->esCuentaPrincipal());
        $this->assertFalse($otraCuenta->esCuentaPrincipal());
    }

    public function test_orden_del_correlativo_no_asigna_la_identidad_principal(): void
    {
        $primera = User::find('COD_USU_000001')
            ?? User::factory()->create(['cod_usuario' => 'COD_USU_000001']);
        $principal = User::factory()->create();
        config(['identidad.cuenta_principal_codigo' => $principal->getKey()]);

        $this->assertFalse($primera->esCuentaPrincipal());
        $this->assertTrue($principal->esCuentaPrincipal());
    }

    public function test_editar_correo_y_rol_en_una_peticion_conserva_rol_y_estado_protegidos(): void
    {
        Permission::findOrCreate('usuarios.editar', 'web');
        Role::findOrCreate('SUPERADMINISTRADOR', 'web');
        Role::findOrCreate('ENFERMEROS', 'web');
        $actor = User::factory()->create();
        $actor->givePermissionTo('usuarios.editar');
        $principal = $this->principal();
        $principal->assignRole('SUPERADMINISTRADOR');
        config(['identidad.cuenta_principal_codigo' => $principal->getKey()]);

        app(GestionarUsuariosHttp::class)->update($actor, [
            'correo' => 'principal.actualizado@example.test',
            'rol' => 'ENFERMEROS',
            'estado' => 'INACTIVO',
        ], $principal);

        $guardado = $principal->fresh();
        $this->assertSame('principal.actualizado@example.test', $guardado->correo);
        $this->assertTrue($guardado->esCuentaPrincipal());
        $this->assertSame('ACTIVO', $guardado->estado);
        $this->assertSame(['SUPERADMINISTRADOR'], $guardado->getRoleNames()->all());
    }
}
