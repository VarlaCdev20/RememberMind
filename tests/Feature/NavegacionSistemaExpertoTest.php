<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResidentesSistemaExperto;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\EvaluacionesAreaPanel;
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

    public function test_psicologia_consulta_resultados_y_abre_valoracion_desde_area_cognitiva(): void
    {
        $this->actingAs($this->profesional());
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO', 'nombres' => 'Elena', 'apellido_paterno' => 'Vargas']);

        $this->get(route('admin.psicologia.dashboard'))
            ->assertOk()->assertSee('Mis residentes')
            ->assertSee('Abrir sistema experto')
            ->assertSee(route('admin.sistema-experto.residentes'), false)
            ->assertSee(route('admin.psicologia.evaluaciones'), false);
        $this->get(route('admin.sistema-experto.residentes'))
            ->assertOk()
            ->assertSee('Sistema experto por residente')
            ->assertSee($residente->nombre_completo)
            ->assertSee('Tarjetas')
            ->assertSee('Tabla')
            ->assertSee('<table', false)
            ->assertDontSee('>'.$residente->getKey().'<', false)
            ->assertDontSee('Buscar residente por nombre o código')
            ->assertSee(route('admin.psicologia.residente.resultados-experto', $residente), false)
            ->assertDontSee(route('admin.sistema-experto.petersen-original', $residente), false)
            ->assertDontSee('Registrar valoración original');
        $this->get(route('admin.sistema-experto.residentes', ['buscar' => 'Nombre inexistente']))
            ->assertOk()->assertDontSee($residente->nombre_completo);
        $this->get(route('admin.sistema-experto.residentes', ['buscar' => 'elena vargas']))
            ->assertOk()->assertSee($residente->nombre_completo);
        $this->get(route('admin.psicologia.evaluacion.cognitiva'))
            ->assertOk()
            ->assertSee('Valoración cognitiva y funcional')
            ->assertSee(route('admin.sistema-experto.petersen-original', $residente), false);
        Livewire::test(EvaluacionesAreaPanel::class, ['codArea' => 'ARE_COG'])
            ->set('busquedaValoracion', 'Nombre inexistente')
            ->assertDontSee(route('admin.sistema-experto.petersen-original', $residente), false)
            ->set('busquedaValoracion', 'Elena')
            ->assertSee(route('admin.sistema-experto.petersen-original', $residente), false);
        Livewire::test(ResidentesSistemaExperto::class)
            ->set('busqueda', $residente->getKey())
            ->assertSee($residente->nombre_completo)
            ->set('busqueda', 'elena')
            ->assertSee($residente->nombre_completo)
            ->set('busqueda', 'elena vargas')
            ->assertSee($residente->nombre_completo)
            ->set('busqueda', 'nombre inexistente')
            ->assertDontSee($residente->nombre_completo);
    }

    public function test_acceso_del_inicio_y_menu_requiere_personal_activo_y_permiso_existente(): void
    {
        $usuario = $this->profesional();
        $this->actingAs($usuario);
        $ruta = route('admin.sistema-experto.residentes');

        $this->get(route('admin.psicologia.dashboard'))->assertOk()
            ->assertSee('Abrir sistema experto')->assertSee($ruta, false);

        $usuario->personal->update(['estado' => 'INACTIVO']);
        $this->get(route('admin.psicologia.dashboard'))->assertOk()->assertDontSee('Abrir sistema experto');
        $this->get($ruta)->assertForbidden();

        $usuario->personal->update(['estado' => 'ACTIVO']);
        $usuario->roles()->each(fn ($rol) => $rol->revokePermissionTo('residentes.ver'));
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($usuario->fresh());
        $this->get(route('admin.psicologia.dashboard'))->assertOk()
            ->assertDontSee('Abrir sistema experto')->assertDontSee('href="'.$ruta.'"', false);
        $this->get($ruta)->assertForbidden();
    }

    public function test_medicina_consulta_resultados_y_abre_valoracion_desde_ficha(): void
    {
        $this->actingAs($this->profesional('MEDICO GENERAL/GERIATRA'));
        $residente = Residente::factory()->create(['estado' => 'ADMITIDO']);

        $this->get(route('admin.sistema-experto.residentes'))
            ->assertOk()
            ->assertSee(route('admin.medico.residente.resultados-experto', $residente), false)
            ->assertDontSee(route('admin.sistema-experto.petersen-original', $residente), false);
        $this->get(route('admin.medico.residente.ficha', ['adulto' => $residente]))
            ->assertOk()
            ->assertSee('Valoración cognitiva y funcional')
            ->assertSee(route('admin.sistema-experto.petersen-original', $residente), false);
    }

    public function test_visitante_no_accede_y_superadministracion_consulta_sin_captura_clinica(): void
    {
        $this->get(route('admin.sistema-experto.residentes'))->assertRedirect();

        $superadmin = User::factory()->create(['estado' => 'ACTIVO']);
        $superadmin->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($superadmin)->get(route('admin.sistema-experto.residentes'))->assertOk()
            ->assertSee('Sistema experto por residente')->assertDontSee('Registrar valoración');
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
