<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\Fortify\ResetUserPassword;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordInicialPersonalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (require base_path('database/propuestas/gerencia/2026_10_26_000001_documentacion_y_estado_laboral.php'))->up();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_cambio_obligatorio_bloquea_modulos_hasta_confirmar_password_diferente(): void
    {
        $usuario = User::factory()->create(['password' => 'TemporalDePrueba#123']);
        $usuario->assignRole('GERENTE');
        $usuario->forceFill(['cambio_password_pendiente' => true, 'fecha_hora_alta' => now()])->save();
        $this->actingAs($usuario)->get(route('dashboard'))->assertRedirect(route('password.inicial'));
        $this->getJson(route('admin.personal-institucional'))->assertForbidden();
        $this->get(route('password.inicial'))->assertOk()->assertSeeText('Crea tu contraseña personal');
        $this->from(route('password.inicial'))->post(route('password.inicial.guardar'), [
            'current_password' => 'NoEsLaCorrecta#123', 'password' => 'PersonalNueva#1234', 'password_confirmation' => 'PersonalNueva#1234',
        ])->assertSessionHasErrorsIn('updatePassword', 'current_password');
        $this->assertTrue($usuario->fresh()->cambio_password_pendiente);
        $this->post(route('password.inicial.guardar'), [
            'current_password' => 'TemporalDePrueba#123', 'password' => 'PersonalNueva#1234', 'password_confirmation' => 'PersonalNueva#1234',
        ])->assertRedirect(route('dashboard'));
        $this->assertFalse($usuario->fresh()->cambio_password_pendiente);
        $this->assertTrue(Hash::check('PersonalNueva#1234', $usuario->fresh()->getAuthPassword()));
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_historia_desconocida_no_se_interpreta_como_cambio_real_y_reset_valido_resuelve_obligacion(): void
    {
        $usuario = User::factory()->create(['password' => 'TemporalDePrueba#123']);
        $usuario->assignRole('GERENTE');
        $this->assertNull($usuario->fresh()->cambio_password_pendiente);
        $this->actingAs($usuario)->get(route('dashboard'))->assertOk();
        $usuario->forceFill(['cambio_password_pendiente' => true])->save();
        app(ResetUserPassword::class)->reset($usuario, ['password' => 'OtraPersonal#1234', 'password_confirmation' => 'OtraPersonal#1234']);
        $this->assertFalse($usuario->fresh()->cambio_password_pendiente);
        $this->assertNull($usuario->fresh()->fecha_hora_alta);
    }
}
