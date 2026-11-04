<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Documento;
use App\Models\Personal;
use App\Models\User;
use App\Modulos\Identidad\Presentacion\Policies\DocumentoLaboralPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

final class DocumentacionLaboralGateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    private function documentoDelTrabajador(): Documento
    {
        $titular = User::factory()->create(['estado' => 'ACTIVO']);
        $titular->assignRole('ENFERMEROS');
        Personal::create(['cod_usuario' => $titular->getKey(), 'nombres' => 'Persona',
            'apellido_paterno' => 'Sintetica', 'numero_documento' => 'PR20-DOC-TEST',
            'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);

        return new Documento(['cod_usuario' => $titular->getKey()]);
    }

    public function test_las_puertas_conservan_la_policy_y_los_limites_de_cada_actor(): void
    {
        $documento = $this->documentoDelTrabajador();
        $policy = app(DocumentoLaboralPolicy::class);
        $titular = User::findOrFail($documento->cod_usuario);
        $casos = [[$titular, true, false]];
        foreach ([['GERENTE', true, true], ['FAMILIAR', false, false], ['ADMINISTRADOR', false, false]] as [$rol, $ver, $revisar]) {
            $actor = User::factory()->create(['estado' => 'ACTIVO']);
            $actor->assignRole($rol);
            if ($rol !== 'GERENTE') {
                $actor->givePermissionTo(['personal_institucional.ver', 'personal.gestionar']);
            }
            $casos[] = [$actor, $ver, $revisar];
        }
        foreach ($casos as [$actor, $ver, $revisar]) {
            self::assertSame($ver, Gate::forUser($actor)->allows('consultar-documento-laboral', [$documento]));
            self::assertSame($revisar, Gate::forUser($actor)->allows('revisar-documento-laboral', [$documento]));
            self::assertSame($policy->view($actor, $documento), $ver);
            self::assertSame($policy->review($actor, $documento), $revisar);
        }
        self::assertSame(0, Documento::count());
    }

    public function test_cuenta_inactivada_no_recibe_el_bypass_global_de_lectura(): void
    {
        $documento = $this->documentoDelTrabajador();
        $actor = User::factory()->create(['estado' => 'ACTIVO']);
        $actor->assignRole('SUPERADMINISTRADOR');
        self::assertTrue(Gate::forUser($actor)->allows('consultar-documento-laboral', [$documento]));
        DB::table('usuarios')->where('cod_usuario', $actor->getKey())->update(['estado' => 'INACTIVO']);
        self::assertFalse(Gate::forUser($actor)->allows('consultar-documento-laboral', [$documento]));
        self::assertFalse(Gate::forUser($actor)->allows('revisar-documento-laboral', [$documento]));
        self::assertSame(0, Documento::count());
    }
}
