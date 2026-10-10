<?php

namespace Tests\Feature;

use App\Frontend\Livewire\Administracion\Identidad\UsuariosPanel;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\Residente;
use App\Models\Turno;
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

        $activo = Residente::factory()->create([
            'nombres' => 'Residente Activo',
            'estado' => 'ACTIVO',
        ]);
        $inactivo = Residente::factory()->create([
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

    public function test_identidad_incompleta_se_rechaza_sin_generar_apellidos_o_documentos_de_respaldo(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create();
        $usuario->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($usuario);

        Livewire::test(UsuariosPanel::class)
            ->set('nombres', 'Ana')
            ->set('ap_paterno', null)
            ->set('ap_materno', 'Quispe')
            ->set('fecha_nacimiento', now()->subYears(30)->toDateString())
            ->set('genero', 'FEMENINO')
            ->set('pais_documento', 'PERU')
            ->set('tipo_documento', 'PASAPORTE')
            ->set('numero_documento', str_repeat('1', 31))
            ->call('siguientePaso')
            ->assertHasErrors(['ap_paterno', 'numero_documento']);

        $fuente = file_get_contents(app_path('Frontend/Livewire/Administracion/Identidad/UsuariosPanel.php'));
        $this->assertStringNotContainsString("'numero_documento' => \$this->numero_documento ?:", $fuente);
        $this->assertStringNotContainsString("'apellido_paterno' => \$this->ap_paterno ?:", $fuente);
    }

    public function test_filtro_de_area_usa_asignaciones_activas_reales(): void
    {
        $area = Area::create(['cod_area' => 'ARE_FILTRO', 'nombre' => 'Área de prueba', 'estado' => 'ACTIVO']);
        $turno = Turno::create([
            'cod_turno' => 'TUR_FILTRO', 'nombre' => 'Turno de prueba',
            'hora_inicio' => '08:00:00', 'hora_cierre' => '16:00:00', 'estado' => 'ACTIVO',
        ]);
        $jornada = Jornada::create([
            'cod_jornada' => 'JOR_FILTRO', 'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(), 'estado' => 'ACTIVA',
        ]);
        $asignado = User::factory()->create(['nombres' => 'Rosa', 'apellido_paterno' => 'Asignada']);
        $noAsignado = User::factory()->create(['nombres' => 'Luis', 'apellido_paterno' => 'SinAsignar']);
        AsignacionPersonal::create([
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $asignado->personal->cod_personal,
            'cod_area' => $area->cod_area,
            'tipo_asignacion' => 'TITULAR',
            'fecha_asignacion' => now(),
            'estado' => 'ACTIVA',
        ]);

        $panel = new UsuariosPanel;
        $panel->filtroArea = $area->cod_area;
        $codigos = $panel->render()->getData()['usuarios']->pluck('cod_usuario')->all();

        $this->assertSame([$asignado->cod_usuario], $codigos);
        $this->assertNotContains($noAsignado->cod_usuario, $codigos);
    }
}
