<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\GestionarSeguridadCuenta;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuarioFichaPanel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class ContratoSeguridadCuentaTest extends TestCase
{
    use RefreshDatabase;

    private function actor(): User
    {
        Permission::findOrCreate('usuarios.gestionar', 'web');
        Role::findOrCreate('SUPERADMINISTRADOR', 'web');
        $actor = User::factory()->create();
        $actor->givePermissionTo('usuarios.gestionar');

        return $actor;
    }

    public function test_bloqueo_persiste_estado_canonico_y_rehabilitacion(): void
    {
        $actor = $this->actor();
        $usuario = User::factory()->create();
        $servicio = app(GestionarSeguridadCuenta::class);
        $this->assertSame('BLOQUEADO', $servicio->toggleAcceso($actor, $usuario));
        $this->assertSame('INACTIVO', $usuario->fresh()->estado);
        $this->assertSame('HABILITADO', $servicio->toggleAcceso($actor, $usuario));
        $this->assertSame('ACTIVO', $usuario->fresh()->estado);
    }

    public function test_ficha_muestra_la_accion_correspondiente_al_acceso_persistido(): void
    {
        $actor = $this->actor();
        $usuario = User::factory()->create();
        Livewire::actingAs($actor)->test(UsuarioFichaPanel::class, ['usuario' => $usuario])
            ->set('activeTab', 'acceso_seguridad')
            ->assertSee('Bloquear Acceso')
            ->call('toggleAcceso')
            ->assertSee('Habilitar Acceso')
            ->assertSee('BLOQUEADO');
        $this->assertSame('INACTIVO', $usuario->fresh()->estado);
    }

    public function test_actor_persistido_inactivo_no_cambia_acceso(): void
    {
        $actor = $this->actor();
        $usuario = User::factory()->create();
        User::whereKey($actor->getKey())->update(['estado' => 'INACTIVO']);
        try {
            app(GestionarSeguridadCuenta::class)->toggleAcceso($actor, $usuario);
            $this->fail('Un actor inactivo debe rechazarse aunque el objeto esté desactualizado.');
        } catch (HttpException $error) {
            $this->assertSame(403, $error->getStatusCode());
        }
        $this->assertSame('ACTIVO', $usuario->fresh()->estado);
    }

    public function test_ultimo_superadministrador_y_cuenta_propia_no_se_bloquean(): void
    {
        User::whereHas('roles', fn ($q) => $q->where('name', 'SUPERADMINISTRADOR'))->update(['estado'=>'INACTIVO']);
        $actor = $this->actor();
        $superadmin = User::factory()->create();
        $superadmin->assignRole('SUPERADMINISTRADOR');
        $this->assertSame(1, User::where('estado','ACTIVO')->whereHas('roles', fn ($q) => $q->where('name','SUPERADMINISTRADOR'))->count());
        foreach ([$actor, $superadmin] as $usuario) {
            try {
                app(GestionarSeguridadCuenta::class)->toggleAcceso($actor, $usuario);
                $this->fail('Debe protegerse la cuenta.');
            } catch (HttpException $error) {
                $this->assertSame(422, $error->getStatusCode());
            }
            $this->assertSame('ACTIVO', $usuario->fresh()->estado);
        }
    }

    public function test_password_temporal_se_verifica_con_hash_canonico(): void
    {
        $actor = $this->actor();
        $usuario = User::factory()->create();
        $temporal = app(GestionarSeguridadCuenta::class)->restablecerPassword($actor, $usuario);
        $this->assertTrue(Hash::check($temporal, $usuario->fresh()->contrasena));
        $this->assertNotSame($temporal, $usuario->fresh()->contrasena);
    }
}
