<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResidentesSistemaExperto;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

final class NavegacionSistemaExpertoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function profesional(string $rol = 'PSICOLOGO/A'): User
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole($rol);
        Personal::create([
            'cod_usuario' => $usuario->getKey(),
            'nombres' => 'Profesional',
            'apellido_paterno' => 'Sintético',
            'numero_documento' => 'NAV-'.Str::random(10),
            'profesion' => $rol === 'PSICOLOGO/A' ? 'PSICOLOGIA' : 'MEDICINA',
            'estado' => 'ACTIVO',
        ]);

        return $usuario;
    }

    public function test_psicologia_encuentra_residente_sin_aplicaciones_y_accede_a_informe_y_captura(): void
    {
        $this->actingAs($this->profesional());
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO', 'nombres' => 'Elena']);

        $this->get(route('admin.psicologia.dashboard'))
            ->assertOk()->assertSee('Abrir sistema experto');
        $this->get(route('admin.sistema-experto.residentes'))
            ->assertOk()
            ->assertSee($residente->getKey())
            ->assertSee(route('admin.psicologia.residente.resultados-experto', $residente), false)
            ->assertSee(route('admin.sistema-experto.petersen-original', $residente), false);
        Livewire::test(ResidentesSistemaExperto::class)
            ->set('busqueda', $residente->getKey())
            ->assertSee($residente->getKey());
    }

    public function test_medicina_encuentra_su_ruta_de_resultados_y_captura(): void
    {
        $this->actingAs($this->profesional('MEDICO GENERAL/GERIATRA'));
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);

        $this->get(route('admin.sistema-experto.residentes'))
            ->assertOk()
            ->assertSee(route('admin.medico.residente.resultados-experto', $residente), false)
            ->assertSee(route('admin.sistema-experto.petersen-original', $residente), false);
    }

    public function test_visitante_y_superadministracion_no_acceden_a_entrada_de_captura_clinica(): void
    {
        $this->get(route('admin.sistema-experto.residentes'))->assertRedirect();

        $superadmin = User::factory()->create(['estado' => 'ACTIVO']);
        $superadmin->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($superadmin)->get(route('admin.sistema-experto.residentes'))->assertForbidden();
    }

    public function test_cuenta_inactiva_o_sin_personal_competente_no_recibe_el_listado(): void
    {
        $sinPersonal = User::factory()->create(['estado' => 'ACTIVO']);
        $sinPersonal->assignRole('PSICOLOGO/A');
        $this->actingAs($sinPersonal)->get(route('admin.sistema-experto.residentes'))->assertForbidden();

        $inactivo = $this->profesional();
        $inactivo->update(['estado' => 'INACTIVO']);
        $this->actingAs($inactivo)->get(route('admin.sistema-experto.residentes'))->assertForbidden();
    }
}
