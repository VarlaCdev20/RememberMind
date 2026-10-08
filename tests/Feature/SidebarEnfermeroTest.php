<?php

namespace Tests\Feature;

use App\Backend\Modulos\Identidad\Servicios\SidebarService;
use App\Models\Residente;
use App\Models\Alerta;
use App\Models\AsignacionResidenteJornada;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Turno;
use App\Models\User;
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

        $this->assertCount(7, $sidebar);
        $this->assertSame(['Mi turno', 'Mis residentes', 'Cuidados', 'Medicación', 'Alertas', 'Incidentes', 'Pase de turno'], array_column($sidebar, 'title'));

        $this->assertSame('Mi turno', $sidebar[0]['title']);
        $this->assertSame('admin.enfermeria.dashboard', $sidebar[0]['route']);

        $this->assertSame('Mis residentes', $sidebar[1]['title']);
        $this->assertSame('admin.enfermeria.pacientes', $sidebar[1]['route']);

        // Los diez accesos conservan un destino distinto mediante su contexto.
        $this->assertSame('Cuidados', $sidebar[2]['title']);
        $labelsCuidado = array_column($sidebar[2]['items'], 'label');
        $this->assertSame(['Signos', 'Dolor', 'Cognición', 'Conducta', 'Sueño', 'Ingesta', 'Hidratación', 'Eliminación', 'Movilidad', 'Heridas'], $labelsCuidado);
        $urls = array_map(fn ($item) => route($item['route'], $item['parameters']), $sidebar[2]['items']);
        $this->assertCount(10, array_unique($urls));
        foreach ($sidebar[2]['items'] as $item) {
            $this->assertFalse($item['disabled']);
            $clave = $item['parameters']['cuidado'];
            $opcion = \App\Backend\Modulos\Enfermeria\Servicios\NavegacionCuidadosService::opcion($clave);
            $this->assertSame($opcion['route'] ?? 'admin.enfermeria.pacientes', $item['route']);
            foreach ($opcion['parameters'] ?? [] as $parameter => $value) {
                $this->assertSame($value, $item['parameters'][$parameter]);
            }
        }
        $this->assertSame('admin.enfermeria.incidentes', $sidebar[5]['route']);

        // Verificar que elementos prohibidos NO están presentes
        $allTitles = array_column($sidebar, 'title');
        $allSubLabels = $labelsCuidado;
        $allVisible = array_merge($allTitles, $allSubLabels);

        $this->assertNotContains('Agenda', $allVisible);
        $this->assertNotContains('Registros', $allVisible);
        $this->assertNotContains('Ficha médica', $allVisible);
        $this->assertNotContains('Información', $allVisible);
        $this->assertNotContains('Reportes', $allVisible);
        $this->assertNotContains('Administración', $allVisible);
        $this->assertNotContains('Usuarios', $allVisible);
    }

    public function test_lecturas_aprobadas_no_conceden_nuevas_escrituras(): void
    {
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');
        foreach (['registros_conductuales', 'registros_sueno', 'registros_ingesta', 'registros_hidratacion', 'registros_eliminacion', 'registros_movilidad'] as $recurso) {
            $this->assertTrue($enfermero->can($recurso.'.ver'));
        }
        $this->assertFalse($enfermero->can('registros_sueno.crear'));
        $this->assertFalse($enfermero->can('registros_conductuales.crear'));
        $this->assertFalse($enfermero->can('prescripciones.crear'));
    }

    public function test_alertas_item_muestra_badge_con_cantidad_activa(): void
    {
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');
        $this->actingAs($enfermero);

        $turno = Turno::create([
            'orden' => 1,
            'nombre' => 'Mañana',
            'hora_inicio' => '07:00',
            'hora_fin' => '15:00',
            'estado' => 'ACTIVO',
        ]);

        $adulto = Residente::factory()->create(['cod_est_adul' => 'EST_001']);
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

        foreach (['ASIGNADA', 'RECONOCIDA', 'PENDIENTE'] as $estado) {
            Alerta::create(['cod_residente' => $adulto->cod_residente, 'tipo' => 'CLINICA', 'estado' => $estado]);
        }

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
        $otroAdulto = Residente::factory()->create(['cod_est_adul' => 'EST_001']);
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

        $itemAlertas = collect($sidebar)->firstWhere('title', 'Alertas');
        $this->assertNotNull($itemAlertas);
        $this->assertSame('5', $itemAlertas['badge']);
    }

    public function test_opcion_pendiente_no_se_muestra_sin_su_permiso_actual(): void
    {
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');
        $enfermero->roles->first()->revokePermissionTo('heridas.ver');

        $this->actingAs($enfermero);
        $cuidado = collect(app(SidebarService::class)->getSidebar())->firstWhere('title', 'Cuidados');

        $this->assertSame(['Signos', 'Dolor', 'Cognición', 'Conducta', 'Sueño', 'Ingesta', 'Hidratación', 'Eliminación', 'Movilidad'], array_column($cuidado['items'], 'label'));
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
        $itemAlertas = collect($sidebar)->firstWhere('title', 'Alertas');
        $this->assertNull($itemAlertas['badge']);
    }

    public function test_cod_usuario_usado_como_cod_personal_responsable_es_rechazado_por_fk(): void
    {
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $adulto = Residente::factory()->create(['cod_est_adul' => 'EST_001']);

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
        $adulto = Residente::factory()->create(['cod_est_adul' => 'EST_001']);

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
        $response->assertSee('id="sidebar-enfermeria"', false);
        $response->assertSee('Enfermeros');
        $response->assertSee('Mi turno');
        $response->assertSee('Mis residentes');
        $response->assertSee('Cuidados');
        $response->assertSee('Medicación');
        $response->assertSee('Cognición');
        $response->assertSee('Heridas');
        $response->assertDontSee('rm-sidebar__subitem--pending');
        $response->assertDontSee('href=""', false);
        $response->assertSee('Pase de turno');
        $response->assertSee('Incidentes');
        $response->assertSee('Alertas');

        // No debe aparecer la sección redundante
        $response->assertDontSee('Salud y Evaluación Geriátrica');
    }

    public function test_incidentes_abre_su_vista_como_seccion_activa(): void
    {
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');

        $response = $this->actingAs($enfermero)->get(route('admin.enfermeria.incidentes'));

        $response->assertOk();
        $response->assertSee('activeSectionKey:', false);
        $response->assertSee('openSection: null', false);
        $response->assertSee('class="rm-sidebar__item is-active" title="Incidentes"', false);
    }
}
