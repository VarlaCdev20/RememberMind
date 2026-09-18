<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperadminDashboardHubTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            EstadoAdultoSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);
    }

    private function crearSuperadmin(): User
    {
        $user = User::factory()->create([
            'estado' => 'ACTIVO',
        ]);
        $user->assignRole('SUPERADMINISTRADOR');
        return $user;
    }

    public function test_dashboard_superadmin_muestra_centro_de_mando_con_todas_las_vistas(): void
    {
        $superadmin = $this->crearSuperadmin();

        $response = $this->actingAs($superadmin)->get(route('dashboard'));
        $response->assertOk();

        // Verificar encabezado del Hub
        $response->assertSee('Centro de Mando Institucional');
        $response->assertSee('Vistas Operativas');

        // Módulos clave de Residentes
        $response->assertSee('Padrón de Residentes');
        $response->assertSee('Preadmisiones');
        $response->assertSee('Habitaciones y Camas');
        $response->assertSee('Red Familiar y Apoyo');

        // Módulos clave de Medicina
        $response->assertSee('Dashboard Médico');
        $response->assertSee('Valoraciones Médicas');
        $response->assertSee('Signos Vitales');
        $response->assertSee('Ficha Médica de Residentes');
        $response->assertSee('Medicación Prescrita');

        // Módulos clave de Enfermería
        $response->assertSee('Dashboard de Turno');
        $response->assertSee('Pacientes en Cuidado');
        $response->assertSee('Agenda de Cuidados');
        $response->assertSee('Pase y Relevo de Turno');

        // Módulos clave de Especialidades
        $response->assertSee('Panel de Psicología');
        $response->assertSee('Evaluación Cognitiva');
        $response->assertSee('Valoración Nutricional');
        $response->assertSee('Actividades y Talleres');

        // Módulos clave de Administración y Auditoría
        $response->assertSee('Directorio de Usuarios');
        $response->assertSee('Roles y Permisos');
        $response->assertSee('Personal Institucional');
        $response->assertSee('Centro de Reportes');
        $response->assertSee('Bitácora y Auditoría');
    }

    public function test_superadmin_puede_acceder_a_las_rutas_del_hub_sin_403(): void
    {
        $superadmin = $this->crearSuperadmin();
        $this->actingAs($superadmin);

        $rutas = [
            'admin.adultos-mayores.index',
            'admin.habitaciones.index',
            'admin.medico.dashboard',
            'admin.medico.pacientes.observacion',
            'admin.salud-seguimiento.medicacion.index',
            'admin.enfermeria.dashboard',
            'admin.turnos-enfermeria.index',
            'admin.psicologia.dashboard',
            'admin.usuarios.index',
            'admin.roles-permisos.index',
            'admin.bitacora.index',
        ];

        foreach ($rutas as $ruta) {
            $this->get(route($ruta))->assertOk();
        }
    }
}
