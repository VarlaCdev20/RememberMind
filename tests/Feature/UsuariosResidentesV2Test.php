<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Administracion\Identidad\UsuariosPanel;
use App\Models\AdultoMayor;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UsuariosResidentesV2Test extends TestCase
{
    use RefreshDatabase;

    public function test_familiar_solo_puede_vincular_residentes_admitidos_existentes(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create();
        $usuario->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($usuario);

        $activo = AdultoMayor::factory()->create([
            'nombres' => 'Residente Activo',
            'estado' => 'ACTIVO',
        ]);
        $inactivo = AdultoMayor::factory()->create([
            'nombres' => 'Residente Inactivo',
            'estado' => 'EGRESADO',
        ]);

        Livewire::test(UsuariosPanel::class)
            ->call('seleccionarAdultoMayor', $activo->cod_residente)
            ->assertSet('selected_cod_residente', $activo->cod_residente)
            ->call('seleccionarAdultoMayor', $inactivo->cod_residente)
            ->assertHasErrors(['selected_cod_residente']);

        $this->assertFalse(method_exists(UsuariosPanel::class, 'registrarYVincularAdulto'));
    }
}
