<?php

namespace Tests\Feature;

use App\Models\AdultoMayor;
use App\Models\AlertaAdulto;
use App\Models\AsignacionTurnoAdulto;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Identidad\SidebarService;
use Database\Seeders\EstadoAdultoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarEnfermeroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([EstadoAdultoSeeder::class, RolesAndPermissionsSeeder::class]);
    }

    public function test_sidebar_enfermero_tiene_estructura_simplificada(): void
    {
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');
        $this->actingAs($enfermero);

        $sidebarService = app(SidebarService::class);
        $sidebar = $sidebarService->getSidebar();

        // Debe contener únicamente 2 secciones: Enfermería e Información
        $this->assertCount(2, $sidebar);

        // Sección 1: Enfermería
        $secEnfermeria = $sidebar[0];
        $this->assertSame('Enfermería', $secEnfermeria['title']);
        $labelsEnfermeria = array_column($secEnfermeria['items'], 'label');
        $this->assertSame([
            'Mi turno',
            'Mis pacientes',
            'Agenda',
            'Medicación',
            'Alertas',
            'Registros',
            'Evolución 360°',
            'Entrega de turno',
        ], $labelsEnfermeria);

        // Rutas de Enfermería
        $routesEnfermeria = array_column($secEnfermeria['items'], 'route');
        $this->assertSame([
            'admin.enfermeria.dashboard',
            'admin.enfermeria.pacientes',
            'admin.enfermeria.agenda',
            'admin.salud-seguimiento.medicacion.index',
            'admin.enfermeria.alertas',
            'admin.enfermeria.registros',
            'admin.enfermeria.pacientes',
            'admin.enfermeria.pase-turno',
        ], $routesEnfermeria);

        // Sección 2: Información
        $secInformacion = $sidebar[1];
        $this->assertSame('Información', $secInformacion['title']);
        $labelsInfo = array_column($secInformacion['items'], 'label');
        $this->assertSame(['Reportes'], $labelsInfo);
        $this->assertSame('admin.enfermeria.reportes', $secInformacion['items'][0]['route']);

        // Verificar que accesos redundantes NO están presentes
        $allLabels = array_merge($labelsEnfermeria, $labelsInfo);
        $this->assertNotContains('Seguimiento diario', $allLabels);
        $this->assertNotContains('Tareas del turno', $allLabels);
        $this->assertNotContains('Dashboard operativo', $allLabels);
        $this->assertNotContains('Dashboard Enfermería', $allLabels);
        $this->assertNotContains('Salud y Evaluación Geriátrica', array_column($sidebar, 'title'));
    }

    public function test_alertas_item_muestra_badge_con_cantidad_activa(): void
    {
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');
        $this->actingAs($enfermero);

        $turno = TurnoEnfermeria::create([
            'orden' => 1,
            'nombre' => 'Mañana',
            'hora_inicio' => '07:00',
            'hora_fin' => '15:00',
            'estado' => 'ACTIVO',
        ]);

        $adulto = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);

        // Asignar el residente al enfermero
        AsignacionTurnoAdulto::create([
            'cod_am' => $adulto->cod_am,
            'cod_turno' => $turno->cod_turno,
            'cod_usu_enfermero' => $enfermero->cod_usu,
            'fecha_asignacion' => today(),
            'fecha_inicio' => today(),
            'estado' => 'ACTIVO',
        ]);

        // Crear 2 alertas activas
        AlertaAdulto::create([
            'cod_am' => $adulto->cod_am,
            'cod_turno' => $turno->cod_turno,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'CLINICA',
            'nivel' => 'ALTA',
            'motivo' => 'Presión arterial elevada',
            'responsable_id' => $enfermero->cod_usu,
            'estado' => 'ABIERTA',
        ]);

        AlertaAdulto::create([
            'cod_am' => $adulto->cod_am,
            'cod_turno' => $turno->cod_turno,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'CONDUCTUAL',
            'nivel' => 'MEDIA',
            'motivo' => 'Desorientación temporoespacial',
            'responsable_id' => $enfermero->cod_usu,
            'estado' => 'EN_ATENCION',
        ]);

        // Crear una alerta ya resuelta (no debe sumar al badge)
        AlertaAdulto::create([
            'cod_am' => $adulto->cod_am,
            'cod_turno' => $turno->cod_turno,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'CUIDADO',
            'nivel' => 'BAJA',
            'motivo' => 'Control rutinario',
            'responsable_id' => $enfermero->cod_usu,
            'estado' => 'RESUELTA',
        ]);

        $sidebarService = app(SidebarService::class);
        $sidebar = $sidebarService->getSidebar();

        $itemAlertas = collect($sidebar[0]['items'])->firstWhere('label', 'Alertas');
        $this->assertNotNull($itemAlertas);
        $this->assertSame('2', $itemAlertas['badge']);
    }

    public function test_render_sidebar_enfermero_en_dashboard(): void
    {
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');

        $response = $this->actingAs($enfermero)->get(route('admin.enfermeria.dashboard'));
        $response->assertStatus(200);

        // Textos del nuevo sidebar
        $response->assertSee('Enfermería');
        $response->assertSee('Mi turno');
        $response->assertSee('Mis pacientes');
        $response->assertSee('Agenda');
        $response->assertSee('Medicación');
        $response->assertSee('Alertas');
        $response->assertSee('Registros');
        $response->assertSee('Evolución 360°');
        $response->assertSee('Entrega de turno');
        $response->assertSee('Información');
        $response->assertSee('Reportes');

        // No debe aparecer la sección redundante
        $response->assertDontSee('Salud y Evaluación Geriátrica');
    }
}
