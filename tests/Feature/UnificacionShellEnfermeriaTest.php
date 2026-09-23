<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnificacionShellEnfermeriaTest extends TestCase
{
    use RefreshDatabase;

    protected User $enfermero;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->enfermero = User::factory()->create([
            'estado' => 'ACTIVO',
            'nombres' => 'ELENA',
            'ap_paterno' => 'VARGAS',
        ]);
        $this->enfermero->assignRole('ENFERMEROS');
    }

    public function test_todas_las_vistas_de_enfermeria_usan_el_shell_unificado(): void
    {
        $rutas = [
            'admin.enfermeria.dashboard',
            'admin.enfermeria.pacientes',
            'admin.enfermeria.agenda',
            'admin.enfermeria.medicacion',
            'admin.enfermeria.pase-turno',
            'admin.enfermeria.registros',
            'admin.enfermeria.alertas',
        ];

        foreach ($rutas as $nombreRuta) {
            $response = $this->actingAs($this->enfermero)->get(route($nombreRuta));

            $response->assertStatus(200);

            // 1. Sidebar institucional oficial unificado
            $response->assertSee('id="sidebar-enfermeria"', false);
            $response->assertSee('REMEMBERMIND');
            $response->assertSee('JARDÍN DE LOS RECUERDOS');
            $response->assertSee('BUSCAR MÓDULO...');

            // 2. Navegación en mayúsculas estandarizada
            $response->assertSee('MI TURNO');
            $response->assertSee('MIS RESIDENTES');
            $response->assertSee('CUIDADO');
            $response->assertSee('CUIDADOS');
            $response->assertSee('MEDICACIÓN');
            $response->assertSee('CONTINUIDAD');
            $response->assertSee('PASE DE TURNO');
            $response->assertSee('INCIDENTES');
            $response->assertSee('ALERTAS');

            // 3. Topbar institucional compartido
            $response->assertSee('Buscar residente, habitación o diagnóstico...');
            $response->assertSee('toggleDarkMode()', false);
            $response->assertSee('remembermind-theme', false);
        }
    }

    public function test_item_activo_se_marca_segun_la_ruta_en_el_sidebar(): void
    {
        // Medicación
        $responseMed = $this->actingAs($this->enfermero)->get(route('admin.enfermeria.medicacion'));
        $responseMed->assertStatus(200);
        $responseMed->assertSee('bg-[#8FA685]', false);

        // Mi Turno
        $responseTurno = $this->actingAs($this->enfermero)->get(route('admin.enfermeria.dashboard'));
        $responseTurno->assertStatus(200);
        $responseTurno->assertSee('bg-[#8FA685]', false);
    }
}
