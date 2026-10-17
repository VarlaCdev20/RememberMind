<?php

namespace Tests\Feature;

use App\Models\Contacto;
use App\Models\Personal;
use App\Models\User;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuariosPanel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UsuariosDocumentoIdentidadTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actor = User::factory()->create();
        foreach (['usuarios.ver', 'usuarios.crear', 'usuarios.editar'] as $permission) {
            Permission::findOrCreate($permission, 'web');
            $this->actor->givePermissionTo($permission);
        }
        $this->actingAs($this->actor);
    }

    private function formulario(): Testable
    {
        return Livewire::test(UsuariosPanel::class)
            ->set('nombres', 'Persona Sintetica')->set('ap_paterno', 'Prueba')
            ->set('fecha_nacimiento', now()->subYears(30)->toDateString())
            ->set('genero', 'FEMENINO')->set('pais_documento', 'BOLIVIA')
            ->set('tipo_documento', 'CI')->set('numero_documento', '987654321')->set('expedido', 'LP');
    }

    public function test_documento_nuevo_avanza_sin_consultar_columnas_de_identidad_en_usuarios(): void
    {
        $this->formulario()->call('siguientePaso')->assertHasNoErrors()->assertSet('pasoFormulario', 2);
        $this->assertDatabaseCount('usuarios', 1);
        $this->assertDatabaseCount('personal', 0);
        $this->assertDatabaseCount('contactos', 0);
    }

    public static function perfiles(): array
    {
        return [['personal', false], ['contactos', false], ['contactos', true]];
    }

    #[DataProvider('perfiles')]
    public function test_documento_repetido_se_rechaza_incluso_sin_cuenta_y_con_otra_expedicion(string $perfil, bool $sinCuenta): void
    {
        $usuario = $sinCuenta ? null : User::factory()->create();
        $this->crearPerfil($perfil, $usuario);
        $cuentas = User::count();
        $this->formulario()->call('siguientePaso')->assertHasErrors(['numero_documento'])->assertSet('pasoFormulario', 1);
        $this->assertDatabaseCount('usuarios', $cuentas);
        $this->assertDatabaseCount($perfil, 1);
    }

    public static function tipos(): array
    {
        return [['personal'], ['contactos']];
    }

    #[DataProvider('tipos')]
    public function test_edicion_excluye_su_propio_perfil_sin_excluir_a_otras_personas(string $perfil): void
    {
        $usuario = User::factory()->create();
        $this->crearPerfil($perfil, $usuario);
        $this->formulario()->set('isEdit', true)->set('usuarioId', $usuario->getKey())
            ->call('siguientePaso')->assertHasNoErrors()->assertSet('pasoFormulario', 2);

        $this->formulario()->set('isEdit', true)->set('usuarioId', $this->actor->getKey())
            ->call('siguientePaso')->assertHasErrors(['numero_documento'])->assertSet('pasoFormulario', 1);
    }

    private function crearPerfil(string $tipo, ?User $usuario): void
    {
        $datos = ['cod_usuario' => $usuario?->getKey(), 'nombres' => 'Persona', 'apellido_paterno' => 'Existente',
            'numero_documento' => '987654321', 'estado' => 'ACTIVO'];
        if ($tipo === 'personal') {
            Personal::create([...$datos, 'cod_personal' => 'PER_DOC_TEST', 'profesion' => 'GERENCIA', 'expedicion_documento' => 'SC']);
        } else {
            Contacto::create([...$datos, 'cod_contacto' => 'CON_DOC_TEST']);
        }
    }
}
