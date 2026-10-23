<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\MisResidentesPsicologia;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class MisResidentesPsicologiaTest extends TestCase
{
    use RefreshDatabase;

    private User $psicologa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->psicologa = User::factory()->create(['estado' => 'ACTIVO']);
        $this->psicologa->assignRole('PSICOLOGO/A');
        $this->psicologa->givePermissionTo('residentes.ver');
        Personal::create([
            'cod_usuario' => $this->psicologa->getKey(),
            'nombres' => 'Lorena',
            'apellido_paterno' => 'Castro',
            'numero_documento' => 'DOC-PSI-'.Str::random(6),
            'profesion' => 'PSICOLOGIA',
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_lista_residentes_admitidos_y_abre_la_ficha_sin_mostrar_codigos(): void
    {
        $admitida = Residente::factory()->create([
            'cod_residente' => 'RES_PSI_LISTA_01',
            'nombres' => 'Lucía',
            'apellido_paterno' => 'Quispe',
            'apellido_materno' => 'Mamani',
            'estado' => 'ADMITIDO',
        ]);
        Residente::factory()->create([
            'cod_residente' => 'RES_PSI_LISTA_02',
            'nombres' => 'Pedro',
            'apellido_paterno' => 'Flores',
            'estado' => 'EGRESADO',
        ]);

        $this->actingAs($this->psicologa)
            ->get(route('admin.psicologia.evaluaciones'))
            ->assertOk()
            ->assertSee('MIS RESIDENTES')
            ->assertSee('Lucía Quispe Mamani')
            ->assertSee(route('admin.psicologia.residente.ficha', $admitida))
            ->assertDontSee('Pedro Flores')
            ->assertDontSeeText('RES_PSI_LISTA_01');
    }

    public function test_busqueda_por_nombre_compuesto_y_cambio_de_vista(): void
    {
        Residente::factory()->create(['nombres' => 'Lucía', 'apellido_paterno' => 'Quispe', 'estado' => 'ADMITIDO']);
        Residente::factory()->create(['nombres' => 'Rosa', 'apellido_paterno' => 'Mamani', 'estado' => 'ADMITIDO']);

        $this->actingAs($this->psicologa);
        Livewire::test(MisResidentesPsicologia::class)
            ->set('search', 'Lucía Quispe')
            ->assertSee('Lucía Quispe')
            ->assertDontSee('Rosa Mamani')
            ->set('vistaModo', 'tabla')
            ->assertSee('Última atención psicológica')
            ->assertSee('Abrir ficha');
    }

    public function test_buscador_compartido_envia_la_busqueda_a_mis_residentes(): void
    {
        Residente::factory()->create(['nombres' => 'Lucía', 'apellido_paterno' => 'Quispe', 'estado' => 'ADMITIDO']);
        Residente::factory()->create(['nombres' => 'Rosa', 'apellido_paterno' => 'Mamani', 'estado' => 'ADMITIDO']);

        $this->actingAs($this->psicologa)
            ->get(route('admin.psicologia.dashboard'))
            ->assertOk()
            ->assertSee('action="'.route('admin.psicologia.evaluaciones').'"', false)
            ->assertSee('name="buscar"', false);

        $this->get(route('admin.psicologia.evaluaciones', ['buscar' => 'Lucía Quispe']))
            ->assertOk()
            ->assertSee('Lucía Quispe')
            ->assertDontSee('Rosa Mamani')
            ->assertSee('value="Lucía Quispe"', false);
    }

    public function test_rol_ajeno_y_personal_inactivo_no_pueden_consultar_el_listado(): void
    {
        $ajena = User::factory()->create(['estado' => 'ACTIVO']);
        $ajena->assignRole('ENFERMEROS');
        $ajena->givePermissionTo('residentes.ver');

        $this->actingAs($ajena)->get(route('admin.psicologia.evaluaciones'))->assertForbidden();

        $this->psicologa->personal()->update(['estado' => 'INACTIVO']);
        $this->actingAs($this->psicologa)->get(route('admin.psicologia.evaluaciones'))->assertForbidden();
    }

    public function test_permiso_revocado_impide_actualizar_una_vista_livewire_abierta(): void
    {
        $this->actingAs($this->psicologa);
        $vista = Livewire::test(MisResidentesPsicologia::class)->assertOk();

        $this->psicologa->revokePermissionTo('residentes.ver');
        Role::findByName('PSICOLOGO/A')->revokePermissionTo('residentes.ver');

        $vista->set('search', 'Lucía')->assertForbidden();
    }
}
