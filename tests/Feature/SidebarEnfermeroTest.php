<?php

namespace Tests\Feature;

use App\Models\AdultoMayor;
use App\Models\Alerta;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\AsignacionResidenteJornada;
use App\Models\TurnoEnfermeria;
use App\Models\User;
use App\Backend\Modulos\Identidad\Servicios\SidebarService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SidebarEnfermeroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RolesAndPermissionsSeeder::class]);
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }
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
        $personal = Personal::create([
            'cod_personal' => 'PER_SIDEBAR',
            'cod_usuario' => $enfermero->cod_usuario,
            'nombres' => 'Enfermero',
            'apellido_paterno' => 'Sidebar',
            'numero_documento' => 'SIDEBAR-01',
            'profesion' => 'ENFERMERIA',
            'estado' => 'ACTIVO',
        ]);
        $jornada = Jornada::create([
            'cod_jornada' => 'JOR_SIDEBAR',
            'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(),
            'estado' => 'ABIERTA',
        ]);

        // Asignar el residente al enfermero
        AsignacionResidenteJornada::create([
            'cod_residente' => $adulto->cod_residente,
            'cod_jornada' => $jornada->cod_jornada,
            'cod_personal' => $personal->cod_personal,
            'fecha_hora' => now(),
            'estado' => 'ACTIVO',
        ]);

        // Crear 2 alertas activas con personal válido (una con columna canónica y otra vía adaptador)
        Alerta::create([
            'cod_residente' => $adulto->cod_residente,
            'cod_turno' => $turno->cod_turno,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'CLINICA',
            'nivel' => 'ALTA',
            'motivo' => 'Presión arterial elevada',
            'cod_personal_responsable' => $personal->cod_personal,
            'estado' => 'ABIERTA',
        ]);

        Alerta::create([
            'cod_residente' => $adulto->cod_residente,
            'cod_turno' => $turno->cod_turno,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'CONDUCTUAL',
            'nivel' => 'MEDIA',
            'motivo' => 'Desorientación temporoespacial',
            'responsable_id' => $personal->cod_personal,
            'estado' => 'EN_ATENCION',
        ]);

        // Alerta cerrada del mismo residente no debe sumarse al badge activo
        Alerta::create([
            'cod_residente' => $adulto->cod_residente,
            'cod_turno' => $turno->cod_turno,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'CLINICA',
            'nivel' => 'BAJA',
            'motivo' => 'Alerta ya resuelta',
            'cod_personal_responsable' => $personal->cod_personal,
            'estado' => 'CERRADA',
        ]);

        // Alerta de otro residente no asignado a la jornada no debe sumarse al badge
        $otroAdulto = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);
        Alerta::create([
            'cod_residente' => $otroAdulto->cod_residente,
            'cod_turno' => $turno->cod_turno,
            'origen' => 'MANUAL',
            'tipo_alerta' => 'CLINICA',
            'nivel' => 'ALTA',
            'motivo' => 'Alerta de paciente no asignado',
            'cod_personal_responsable' => $personal->cod_personal,
            'estado' => 'ABIERTA',
        ]);

        $sidebarService = app(SidebarService::class);
        $sidebar = $sidebarService->getSidebar();

        $secContinuidad = collect($sidebar)->firstWhere('title', 'Continuidad');
        $this->assertNotNull($secContinuidad);
        $itemAlertas = collect($secContinuidad['items'])->firstWhere('label', 'Alertas');
        $this->assertNotNull($itemAlertas);
        $this->assertSame('2', $itemAlertas['badge']);
    }

    public function test_usuario_enfermero_sin_personal_no_provoca_creacion_automatica_de_personal(): void
    {
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');
        $this->actingAs($enfermero);

        $this->assertNull($enfermero->personal);
        $this->assertSame(0, Personal::where('cod_usuario', $enfermero->cod_usuario)->count());

        $sidebarService = app(SidebarService::class);
        $sidebar = $sidebarService->getSidebar();

        // El servicio no debe fabricar Personal como fallback
        $this->assertSame(0, Personal::where('cod_usuario', $enfermero->cod_usuario)->count());
        $secContinuidad = collect($sidebar)->firstWhere('title', 'Continuidad');
        $itemAlertas = collect($secContinuidad['items'])->firstWhere('label', 'Alertas');
        $this->assertNull($itemAlertas['badge']);
    }

    public function test_cod_usuario_usado_como_cod_personal_responsable_es_rechazado_por_fk(): void
    {
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $adulto = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);

        // cod_usuario pertenece a usuarios, no a personal
        $this->expectException(QueryException::class);

        Alerta::create([
            'cod_residente' => $adulto->cod_residente,
            'cod_personal_responsable' => $enfermero->cod_usuario,
            'tipo' => 'CLINICA',
            'descripcion' => 'Intento inválido de usar cod_usuario como FK de personal',
            'estado' => 'ABIERTA',
        ]);
    }

    public function test_personal_inexistente_como_responsable_es_rechazado_por_fk(): void
    {
        $adulto = AdultoMayor::factory()->create(['cod_est_adul' => 'EST_001']);

        $this->expectException(QueryException::class);

        Alerta::create([
            'cod_residente' => $adulto->cod_residente,
            'cod_personal_responsable' => 'PER_INEXISTENTE',
            'tipo' => 'CLINICA',
            'descripcion' => 'Intento con personal inexistente',
            'estado' => 'ABIERTA',
        ]);
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
