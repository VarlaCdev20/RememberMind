<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PsychologyDashboardDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_sin_personal_activo_no_muestra_totales_institucionales(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('PSICOLOGO/A');

        $this->actingAs($usuario)->get(route('admin.psicologia.dashboard'))
            ->assertOk()
            ->assertSee('No hay una vinculación de personal activo')
            ->assertDontSee('Residentes atendidos');
    }

    public function test_panel_muestra_instrumentos_propios_sin_exponer_los_de_otro_profesional(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $otro = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('PSICOLOGO/A');
        foreach ([[$usuario, 'PER_PSI_1'], [$otro, 'PER_PSI_2']] as [$cuenta, $codigo]) {
            Personal::create([
                'cod_personal' => $codigo, 'cod_usuario' => $cuenta->cod_usuario,
                'nombres' => 'Elena', 'apellido_paterno' => 'Prueba',
                'numero_documento' => $codigo, 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO',
            ]);
        }
        $propio = Residente::factory()->create(['nombres' => 'Rosa', 'apellido_paterno' => 'Visible']);
        $ajeno = Residente::factory()->create(['nombres' => 'Eva', 'apellido_paterno' => 'Reservada']);
        DB::table('instrumentos')->insert([
            'cod_instrumento' => 'INS_PSI_1', 'codigo' => 'PSI-DEMO-1', 'nombre' => 'Instrumento sintético', 'tipo' => 'CUANTITATIVO', 'estado' => 'ACTIVO',
        ]);
        DB::table('aplicaciones_instrumento')->insert([
            ['cod_aplicacion' => 'APL_PSI_1', 'cod_instrumento' => 'INS_PSI_1', 'cod_residente' => $propio->cod_residente, 'cod_personal' => 'PER_PSI_1', 'fecha_hora' => now(), 'estado' => 'COMPLETADA'],
            ['cod_aplicacion' => 'APL_PSI_2', 'cod_instrumento' => 'INS_PSI_1', 'cod_residente' => $ajeno->cod_residente, 'cod_personal' => 'PER_PSI_2', 'fecha_hora' => now(), 'estado' => 'COMPLETADA'],
        ]);

        $this->actingAs($usuario)->get(route('admin.psicologia.dashboard'))
            ->assertOk()
            ->assertSee('Rosa Visible')
            ->assertDontSee('Eva Reservada')
            ->assertSee('Instrumentos aplicados');
    }
}
