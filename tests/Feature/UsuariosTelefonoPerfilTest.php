<?php

namespace Tests\Feature;

use App\Models\Contacto;
use App\Models\Personal;
use App\Models\User;
use App\Modulos\Identidad\Aplicacion\Acciones\GestionarUsuariosPanel;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuariosPanel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsuariosTelefonoPerfilTest extends TestCase
{
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actor = User::factory()->create();
        foreach (['usuarios.ver', 'usuarios.crear', 'usuarios.editar'] as $permiso) {
            $this->actor->givePermissionTo(Permission::findOrCreate($permiso, 'web'));
        }
        $this->actingAs($this->actor);
    }

    private function contactoFormulario()
    {
        return Livewire::test(UsuariosPanel::class)
            ->set('pasoFormulario', 2)->set('correo', 'alta.sintetica@example.test')
            ->set('pais_telefono', 'Bolivia')->set('telefono', '70012345')
            ->set('departamento_domicilio', 'OTRO')->set('otro_departamento', 'Departamento prueba')
            ->set('municipio_domicilio', 'OTRO')->set('otro_municipio', 'Municipio prueba')
            ->set('otra_zona', 'Zona prueba')->set('calle', 'Calle prueba')->set('nro_domicilio', '123')
            ->set('contacto_emergencia', 'Contacto Prueba')->set('ap_paterno_emergencia', 'Sintetico')
            ->set('parentesco_emergencia', 'HERMANO')->set('celular_emergencia', '70054321');
    }

    public function test_contacto_nuevo_avanza_sin_consultar_columnas_inexistentes_en_usuarios(): void
    {
        $this->contactoFormulario()->call('siguientePaso')->assertHasNoErrors()->assertSet('pasoFormulario', 3);
        $this->assertDatabaseCount('usuarios', 1);
        $this->assertDatabaseCount('personal', 0);
    }

    public static function perfiles(): array
    {
        return [['personal', 'telefono'], ['contactos', 'telefono'], ['contactos', 'celular']];
    }

    #[DataProvider('perfiles')]
    public function test_telefono_existente_se_rechaza_y_edicion_excluye_su_propio_perfil(string $tipo, string $campo): void
    {
        $usuario = User::factory()->create();
        $datos = ['cod_usuario' => $usuario->getKey(), 'nombres' => 'Persona', 'apellido_paterno' => 'Prueba',
            'numero_documento' => 'TEL-TEST', 'estado' => 'ACTIVO', $campo => '70012345'];
        if ($tipo === 'personal') {
            Personal::create([...$datos, 'cod_personal' => 'PER_TEL_TEST', 'profesion' => 'GERENTE']);
        } else {
            Contacto::create([...$datos, 'cod_contacto' => 'CON_TEL_TEST']);
        }
        $this->contactoFormulario()->call('siguientePaso')->assertHasErrors(['telefono'])->assertSet('pasoFormulario', 2);
        $this->contactoFormulario()->set('isEdit', true)->set('usuarioId', $usuario->getKey())
            ->call('siguientePaso')->assertHasNoErrors()->assertSet('pasoFormulario', 3);
        $this->assertDatabaseCount('usuarios', 2);
    }

    public function test_alta_de_gerente_conserva_el_telefono_en_personal(): void
    {
        Role::findOrCreate('GERENTE', 'web');
        $resultado = app(GestionarUsuariosPanel::class)->guardarUsuario($this->actor, [
            'isEdit' => false, 'foto_de_perfil_upload' => null, 'passwordTemporalVisual' => null,
            'rol' => 'GERENTE', 'nombres' => 'Gerencia Sintetica', 'ap_paterno' => 'Prueba', 'ap_materno' => null,
            'numero_documento' => 'TEL-GER-TEST', 'telefono' => '70012345', 'fecha_ingreso' => today()->toDateString(),
        ], ['correo' => 'gerencia.sintetica@example.test'], 'ACTIVO', 'HABILITADO');

        $this->assertTrue($resultado['usuario']->hasRole('GERENTE'));
        $this->assertDatabaseHas('personal', ['cod_usuario' => $resultado['usuario']->getKey(), 'telefono' => '70012345']);
        $this->assertDatabaseCount('usuarios', 2);
    }
}
