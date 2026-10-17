<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\User;
use App\Modulos\Identidad\Presentacion\Livewire\Paginas\UsuariosPanel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UsuariosDirectorioFiltrosTest extends TestCase
{
    use RefreshDatabase;

    private User $activa;

    private User $inactiva;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        $this->activa = User::factory()->create(['correo' => 'directora@example.test', 'estado' => 'ACTIVO']);
        $this->inactiva = User::factory()->create(['correo' => 'otra@example.test', 'estado' => 'INACTIVO']);
        foreach ([[$this->activa, 'Directora', 'Sintetica', 'FEMENINO', 'INACTIVO'],
            [$this->inactiva, 'Otro', 'Sintetico', 'MASCULINO', 'ACTIVO']] as $i => [$user, $nombre, $apellido, $genero, $estado]) {
            Personal::create(['cod_personal' => 'PER_DIR_'.$i, 'cod_usuario' => $user->getKey(),
                'nombres' => $nombre, 'apellido_paterno' => $apellido, 'apellido_materno' => 'Prueba',
                'genero' => $genero, 'numero_documento' => 'DOC-DIR-'.$i, 'profesion' => 'ADMINISTRACION', 'estado' => $estado]);
        }
    }

    public static function busquedas(): array
    {
        return [['directora'], ['SINTETICA'], ['directora@example.test']];
    }

    #[DataProvider('busquedas')]
    public function test_busqueda_paginada_no_ambigua_y_sin_resultados_ajenos(string $termino): void
    {
        $this->assertSame([$this->activa->getKey()], $this->codigos(['search' => $termino]));
    }

    public function test_codigo_de_cuenta_usa_su_tabla_y_no_la_clave_del_perfil(): void
    {
        $this->assertSame([$this->activa->getKey()], $this->codigos(['search' => $this->activa->getKey()]));
        $this->assertSame([], $this->codigos(['search' => 'PER_DIR_0']));
    }

    public function test_estado_filtra_la_cuenta_y_no_el_estado_del_personal(): void
    {
        $this->assertSame([$this->activa->getKey()], $this->codigos(['search' => 'Sintetic', 'filtroEstado' => 'ACTIVO']));
        $this->assertSame([$this->inactiva->getKey()], $this->codigos(['search' => 'Sintetic', 'filtroEstado' => 'INACTIVO']));
    }

    public function test_genero_y_busqueda_se_combinan_sobre_el_perfil_real(): void
    {
        $this->assertSame([$this->activa->getKey()], $this->codigos(['search' => 'Sintetic', 'filtroGenero' => 'FEMENINO']));
        $this->assertSame([], $this->codigos(['search' => 'directora', 'filtroGenero' => 'MASCULINO']));
        $this->assertSame([$this->inactiva->getKey()], $this->codigos(['filtroEstado' => 'INACTIVO', 'filtroGenero' => 'MASCULINO']));
    }

    private function codigos(array $filtros): array
    {
        $panel = new UsuariosPanel;
        foreach ($filtros as $campo => $valor) {
            $panel->$campo = $valor;
        }

        return $panel->render()->getData()['usuarios']->pluck('cod_usuario')->all();
    }
}
