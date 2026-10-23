<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\Valoraciones\Presentacion\Livewire\Paginas\DashboardPsicologo;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
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
            ->assertDontSee('Aplicaciones registradas');
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
            ->assertSee('Aplicaciones registradas')
            ->assertDontSee('Ver mis residentes')
            ->assertDontSee('Actualizar panel')
            ->assertDontSee('Registros a tu nombre; cada tarjeta indica su período.')
            ->assertSee('Aplicaciones recientes')
            ->assertSee('Instrumento sintético')
            ->assertSee('Aplicaciones por instrumento')
            ->assertDontSee('Distribución por área clínica')
            ->assertSee('No hay atenciones futuras registradas a tu nombre.')
            ->assertDontSee('No hay alertas prioritarias abiertas a tu cargo.')
            ->assertDontSee('+12% respecto al mes anterior')
            ->assertDontSee('Mª Carmen López');

        $vista = Livewire::test(DashboardPsicologo::class);
        $this->assertSame(1, $vista->viewData('chartInstrumentos')['total']);
        $this->assertSame(['Instrumento sintético'], $vista->viewData('chartInstrumentos')['labels']);
        $this->assertSame(1, array_sum($vista->viewData('chartEvolucion')['aplicaciones']));
    }

    public function test_graficas_omiten_registros_anulados_y_muestran_vacio_sin_datos(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('PSICOLOGO/A');
        Personal::create([
            'cod_personal' => 'PER_PSI_GRAF', 'cod_usuario' => $usuario->cod_usuario,
            'nombres' => 'Ana', 'apellido_paterno' => 'Prueba',
            'numero_documento' => 'DOC-PSI-GRAF', 'profesion' => 'PSICOLOGIA', 'estado' => 'ACTIVO',
        ]);
        $residente = Residente::factory()->create();
        DB::table('instrumentos')->insert([
            'cod_instrumento' => 'INS_PSI_GRAF', 'codigo' => 'PSI-GRAF', 'nombre' => 'Escala de prueba', 'tipo' => 'CUANTITATIVO', 'estado' => 'ACTIVO',
        ]);
        DB::table('aplicaciones_instrumento')->insert([
            ['cod_aplicacion' => 'APL_PSI_GRAF_1', 'cod_instrumento' => 'INS_PSI_GRAF', 'cod_residente' => $residente->cod_residente, 'cod_personal' => 'PER_PSI_GRAF', 'fecha_hora' => now(), 'estado' => 'ANULADA'],
            ['cod_aplicacion' => 'APL_PSI_GRAF_2', 'cod_instrumento' => 'INS_PSI_GRAF', 'cod_residente' => $residente->cod_residente, 'cod_personal' => 'PER_PSI_GRAF', 'fecha_hora' => now()->subMonths(7), 'estado' => 'COMPLETA'],
        ]);

        $this->actingAs($usuario);
        $vista = Livewire::test(DashboardPsicologo::class)->assertSee('Sin actividad registrada en este período');
        $this->assertSame(0, $vista->viewData('chartInstrumentos')['total']);
        $this->assertFalse($vista->viewData('chartEvolucion')['tieneDatos']);

        DB::table('aplicaciones_instrumento')->where('cod_aplicacion', 'APL_PSI_GRAF_1')->update(['estado' => 'VIGENTE']);
        $vista = Livewire::test(DashboardPsicologo::class);
        $this->assertSame(['Escala de prueba'], $vista->viewData('chartInstrumentos')['labels']);
        $this->assertSame([1], $vista->viewData('chartInstrumentos')['data']);

        DB::table('aplicaciones_instrumento')->insert([
            'cod_aplicacion' => 'APL_PSI_GRAF_3', 'cod_instrumento' => 'INS_PSI_GRAF',
            'cod_residente' => $residente->cod_residente, 'cod_personal' => 'PER_PSI_GRAF',
            'fecha_hora' => now(), 'estado' => 'COMPLETA',
        ]);
        $vista = Livewire::test(DashboardPsicologo::class);
        $this->assertSame(2, $vista->viewData('chartInstrumentos')['total']);
        $this->assertSame(2, array_sum($vista->viewData('chartEvolucion')['aplicaciones']));
    }
}
