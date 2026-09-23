<?php

namespace Tests\Feature;

use App\Models\AdultoMayor;
use App\Models\Alerta;
use App\Models\AsignacionResidenteJornada;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Services\Identidad\SidebarService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarEnfermeroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesAndPermissionsSeeder::class]);
    }

    public function test_sidebar_enfermero_tiene_estructura_simplificada(): void
    {
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');
        $this->actingAs($enfermero);

        $sidebarService = app(SidebarService::class);
        $sidebar = $sidebarService->getSidebar();

        // Estructura: Mi turno, Mis residentes, Cuidado (Cuidados, Medicación), Continuidad (Pase de turno, Incidentes, Alertas)
        $this->assertCount(4, $sidebar);

        $this->assertSame('Mi turno', $sidebar[0]['title']);
        $this->assertSame('admin.enfermeria.dashboard', $sidebar[0]['route']);

        $this->assertSame('Mis residentes', $sidebar[1]['title']);
        $this->assertSame('admin.enfermeria.pacientes', $sidebar[1]['route']);

        // Grupo Cuidado
        $this->assertSame('Cuidado', $sidebar[2]['title']);
        $labelsCuidado = array_column($sidebar[2]['items'], 'label');
        $this->assertSame(['Cuidados', 'Medicación'], $labelsCuidado);

        // Grupo Continuidad
        $this->assertSame('Continuidad', $sidebar[3]['title']);
        $labelsContinuidad = array_column($sidebar[3]['items'], 'label');
        $this->assertSame(['Pase de turno', 'Incidentes', 'Alertas'], $labelsContinuidad);

        // Verificar que elementos prohibidos NO están presentes
        $allTitles = array_column($sidebar, 'title');
        $allSubLabels = array_merge($labelsCuidado, $labelsContinuidad);
        $allVisible = array_merge($allTitles, $allSubLabels);

        $this->assertNotContains('Agenda', $allVisible);
        $this->assertNotContains('Registros', $allVisible);
        $this->assertNotContains('Ficha médica', $allVisible);
        $this->assertNotContains('Información', $allVisible);
        $this->assertNotContains('Reportes', $allVisible);
        $this->assertNotContains('Administración', $allVisible);
        $this->assertNotContains('Usuarios', $allVisible);
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
        AsignacionResidenteJornada::create([
            'cod_am' => $adulto->cod_am,
            'cod_turno' => $turno->cod_turno,
            'cod_usu_enfermero' => $enfermero->cod_usu,
            'fecha_asignacion' => today(),
            'fecha_inicio' => today(),
            'estado' => 'ACTIVO',
        ]);

        // Crear 2 alertas activas
        Alerta::create([
            'cod_am' => $adulto->cod_am,
            'cod_turno' => $turno->cod_turno,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'CLINICA',
            'nivel' => 'ALTA',
            'motivo' => 'Presión arterial elevada',
            'responsable_id' => $enfermero->cod_usu,
            'estado' => 'ABIERTA',
        ]);

        Alerta::create([
            'cod_am' => $adulto->cod_am,
            'cod_turno' => $turno->cod_turno,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'CONDUCTUAL',
            'nivel' => 'MEDIA',
            'motivo' => 'Desorientación temporoespacial',
            'responsable_id' => $enfermero->cod_usu,
            'estado' => 'EN_ATENCION',
        ]);

        $sidebarService = app(SidebarService::class);
        $sidebar = $sidebarService->getSidebar();

        $secContinuidad = collect($sidebar)->firstWhere('title', 'Continuidad');
        $this->assertNotNull($secContinuidad);
        $itemAlertas = collect($secContinuidad['items'])->firstWhere('label', 'Alertas');
        $this->assertNotNull($itemAlertas);
        $this->assertSame('2', $itemAlertas['badge']);
    }

    public function test_render_sidebar_enfermero_en_dashboard(): void
    {
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');

        $response = $this->actingAs($enfermero)->get(route('admin.enfermeria.dashboard'));
        $response->assertStatus(200);

        // Textos del sidebar de Enfermería
        $response->assertSee('ENFERMERÍA');
        $response->assertSee('Mi turno');
        $response->assertSee('Mis residentes');
        $response->assertSee('Cuidados');
        $response->assertSee('Medicación');
        $response->assertSee('Continuidad');
        $response->assertSee('Pase de turno');
        $response->assertSee('Incidentes');
        $response->assertSee('Alertas');

        // No debe aparecer la sección redundante
        $response->assertDontSee('Salud y Evaluación Geriátrica');
    }
}