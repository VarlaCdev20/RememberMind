<?php

namespace Tests\Feature;

use App\Backend\Modulos\Reportes\Servicios\RoleDashboardDataService;
use App\Models\Residente;
use App\Models\Personal;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RoleDashboardDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_institucional_usa_cuatro_metricas_y_estados_vacios_reales(): void
    {
        $data = app(RoleDashboardDataService::class)->forRole(User::factory()->create(), 'SUPERADMINISTRADOR');

        $this->assertSame([
            'Residentes activos', 'Camas disponibles', 'Alertas prioritarias', 'Personal de hoy',
        ], array_column($data['metrics'], 'label'));
        $this->assertSame([0, 0, 0, 0], array_column($data['metrics'], 'value'));
        $this->assertNull($data['activeShift']);
        $this->assertContains('Preadmisiones por estado', array_column($data['panels'], 'title'));
        $this->assertContains('Cobertura por área hoy', array_column($data['panels'], 'title'));
    }

    public function test_kpi_institucional_deriva_capacidad_cobertura_y_jornada_de_registros_reales(): void
    {
        $usuario = User::factory()->create();
        $residente = Residente::factory()->create();

        DB::table('habitaciones')->insert(['cod_habitacion' => 'HAB_KPI', 'codigo' => 'H-KPI', 'capacidad' => 2, 'estado' => 'ACTIVA']);
        DB::table('camas')->insert([
            ['cod_cama' => 'CAM_KPI_1', 'cod_habitacion' => 'HAB_KPI', 'codigo' => 'C-KPI-1', 'estado' => 'ACTIVA'],
            ['cod_cama' => 'CAM_KPI_2', 'cod_habitacion' => 'HAB_KPI', 'codigo' => 'C-KPI-2', 'estado' => 'ACTIVA'],
        ]);
        DB::table('admisiones')->insert([
            'cod_admision' => 'ADM_KPI', 'cod_residente' => $residente->cod_residente,
            'cod_usuario_registro' => $usuario->cod_usuario, 'fecha_hora_admision' => now()->subHour(),
            'motivo_ingreso' => 'Ingreso de prueba', 'estado' => 'ACTIVA',
        ]);
        DB::table('ocupaciones_cama')->insert([
            'cod_ocupacion' => 'OCU_KPI', 'cod_residente' => $residente->cod_residente,
            'cod_cama' => 'CAM_KPI_1', 'cod_admision' => 'ADM_KPI',
            'cod_usuario_registro' => $usuario->cod_usuario,
            'fecha_hora_asignacion' => now()->subHour(), 'estado' => 'ACTIVO',
        ]);

        $trabajador = User::factory()->create();
        Personal::create([
            'cod_personal' => 'PER_KPI', 'cod_usuario' => $trabajador->cod_usuario,
            'nombres' => 'Ana', 'apellido_paterno' => 'Prueba',
            'numero_documento' => 'CI-KPI', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO',
        ]);
        DB::table('areas')->insert(['cod_area' => 'ARE_KPI', 'nombre' => 'Cuidados de prueba', 'estado' => 'ACTIVA']);
        DB::table('turnos')->insert(['cod_turno' => 'TUR_KPI', 'nombre' => 'Mañana', 'hora_inicio' => '07:00', 'hora_cierre' => '15:00', 'orden' => 1, 'estado' => 'ACTIVO']);
        DB::table('jornadas')->insert(['cod_jornada' => 'JOR_KPI', 'cod_turno' => 'TUR_KPI', 'fecha_jornada' => today(), 'estado' => 'ABIERTA']);
        DB::table('asignaciones_personal')->insert([
            'cod_asignacion_personal' => 'ASP_KPI', 'cod_jornada' => 'JOR_KPI',
            'cod_personal' => 'PER_KPI', 'cod_area' => 'ARE_KPI', 'tipo_asignacion' => 'REGULAR',
            'fecha_asignacion' => now(), 'estado' => 'ACTIVA',
        ]);

        $data = app(RoleDashboardDataService::class)->forRole($usuario, 'SUPERADMINISTRADOR');

        $this->assertSame(1, $data['metrics'][0]['value']);
        $this->assertSame(1, array_sum($data['metrics'][0]['sparkbars']));
        $this->assertSame(['value' => 1, 'capacity' => 2, 'occupied' => 1], array_intersect_key(
            $data['metrics'][1], array_flip(['value', 'capacity', 'occupied'])
        ));
        $this->assertSame(1, $data['metrics'][3]['value']);
        $this->assertSame(1, $data['metrics'][3]['capacity']);
        $this->assertSame([['label' => 'Cuidados de prueba', 'value' => 1]], $data['metrics'][3]['areas']);
        $this->assertSame('Mañana', $data['activeShift']);
    }

    public function test_familiar_solo_recibe_residentes_autorizados_y_sus_propias_visitas(): void
    {
        $familiar = User::factory()->create();
        $otroUsuario = User::factory()->create();
        $residenteAutorizado = Residente::factory()->create(['nombres' => 'Rosa', 'apellido_paterno' => 'Autorizada']);
        $residenteSinPermiso = Residente::factory()->create(['nombres' => 'Eva', 'apellido_paterno' => 'Reservada']);

        DB::table('contactos')->insert([
            ['cod_contacto' => 'CTO_FAM_1', 'cod_usuario' => $familiar->cod_usuario, 'nombres' => 'Ana', 'apellido_paterno' => 'Prueba', 'estado' => 'ACTIVO'],
            ['cod_contacto' => 'CTO_FAM_2', 'cod_usuario' => $otroUsuario->cod_usuario, 'nombres' => 'Luis', 'apellido_paterno' => 'Prueba', 'estado' => 'ACTIVO'],
        ]);
        DB::table('residentes_contactos')->insert([
            ['cod_residente_contacto' => 'RCO_FAM_1', 'cod_residente' => $residenteAutorizado->cod_residente, 'cod_contacto' => 'CTO_FAM_1', 'parentesco' => 'HIJA', 'responsable_principal' => true, 'contacto_emergencia' => true, 'autoriza_informacion' => true, 'autoriza_salida' => false, 'estado' => 'ACTIVO'],
            ['cod_residente_contacto' => 'RCO_FAM_2', 'cod_residente' => $residenteSinPermiso->cod_residente, 'cod_contacto' => 'CTO_FAM_1', 'parentesco' => 'HIJA', 'responsable_principal' => false, 'contacto_emergencia' => false, 'autoriza_informacion' => false, 'autoriza_salida' => false, 'estado' => 'ACTIVO'],
            ['cod_residente_contacto' => 'RCO_FAM_3', 'cod_residente' => $residenteSinPermiso->cod_residente, 'cod_contacto' => 'CTO_FAM_2', 'parentesco' => 'HIJO', 'responsable_principal' => true, 'contacto_emergencia' => true, 'autoriza_informacion' => true, 'autoriza_salida' => false, 'estado' => 'ACTIVO'],
        ]);
        DB::table('visitas')->insert([
            ['cod_visita' => 'VIS_FAM_1', 'cod_residente' => $residenteAutorizado->cod_residente, 'cod_contacto' => 'CTO_FAM_1', 'cod_usuario_autorizacion' => $otroUsuario->cod_usuario, 'fecha_hora_programada' => now()->addDay(), 'estado' => 'AUTORIZADA'],
            ['cod_visita' => 'VIS_FAM_2', 'cod_residente' => $residenteSinPermiso->cod_residente, 'cod_contacto' => 'CTO_FAM_1', 'cod_usuario_autorizacion' => $otroUsuario->cod_usuario, 'fecha_hora_programada' => now()->addDay(), 'estado' => 'AUTORIZADA'],
            ['cod_visita' => 'VIS_FAM_3', 'cod_residente' => $residenteSinPermiso->cod_residente, 'cod_contacto' => 'CTO_FAM_2', 'cod_usuario_autorizacion' => $otroUsuario->cod_usuario, 'fecha_hora_programada' => now()->addDay(), 'estado' => 'AUTORIZADA'],
        ]);

        $data = app(RoleDashboardDataService::class)->forRole($familiar, 'FAMILIAR');
        $panels = collect($data['panels'])->keyBy('title');

        $this->assertSame([], $data['metrics']);
        $this->assertSame(['Rosa Autorizada'], array_column($panels['Tu familiar']['items'], 'label'));
        $this->assertSame(['Rosa'], array_column($panels['Próximas visitas']['items'], 'label'));
        $this->assertSame([], $panels['Documentos compartidos']['items']);
    }

    public function test_especialista_sin_personal_activo_no_consulta_registros_globales(): void
    {
        $data = app(RoleDashboardDataService::class)->forRole(User::factory()->create(), 'NUTRICIONISTA');

        $this->assertSame([], $data['metrics']);
        $this->assertCount(1, $data['panels']);
        $this->assertSame([], $data['panels'][0]['items']);
    }

    public function test_medico_con_personal_activo_ve_solo_indicadores_con_fuente_real(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('MEDICO GENERAL/GERIATRA');
        Personal::create([
            'cod_personal' => 'PER_MED_DASH', 'cod_usuario' => $usuario->cod_usuario,
            'nombres' => 'Luis', 'apellido_paterno' => 'Prueba',
            'numero_documento' => 'CI-MED-DASH', 'profesion' => 'MEDICO', 'estado' => 'ACTIVO',
        ]);

        $data = app(RoleDashboardDataService::class)->forRole($usuario, 'MEDICO GENERAL/GERIATRA');
        $this->assertSame(['Residentes atendidos', 'Atenciones próximas', 'Alertas prioritarias', 'Estudios pendientes'], array_column($data['metrics'], 'label'));
        $this->assertSame([0, 0, 0, 0], array_column($data['metrics'], 'value'));
        $residente = Residente::factory()->create(['nombres' => 'Rosa', 'apellido_paterno' => 'Programada']);
        DB::table('areas')->insert(['cod_area' => 'ARE_MED_DASH', 'nombre' => 'Medicina de prueba', 'estado' => 'ACTIVA']);
        DB::table('atenciones')->insert([
            ['cod_atencion' => 'ATE_MED_PLAN', 'cod_residente' => $residente->cod_residente, 'cod_area' => 'ARE_MED_DASH', 'cod_personal' => 'PER_MED_DASH', 'tipo_atencion' => 'CONTROL', 'fecha_hora' => now()->addDay(), 'estado' => 'PROGRAMADA'],
            ['cod_atencion' => 'ATE_MED_DONE', 'cod_residente' => $residente->cod_residente, 'cod_area' => 'ARE_MED_DASH', 'cod_personal' => 'PER_MED_DASH', 'tipo_atencion' => 'CONTROL', 'fecha_hora' => now()->addDays(2), 'estado' => 'COMPLETADA'],
        ]);
        $planned = app(RoleDashboardDataService::class)->forRole($usuario, 'MEDICO GENERAL/GERIATRA');
        $this->assertSame(1, $planned['metrics'][1]['value']);
        $this->assertCount(1, $planned['panels'][0]['items']);
        $this->actingAs($usuario)->get(route('admin.medico.dashboard'))
            ->assertOk()->assertSee('Estudios pendientes')->assertDontSee('Distribución IMC');
    }

    public function test_especialistas_con_personal_activo_muestran_solo_fuentes_reales_vacias(): void
    {
        $usuario = User::factory()->create(['nombres' => 'Ana', 'ap_paterno' => 'Prueba']);
        $service = app(RoleDashboardDataService::class);

        foreach (['NUTRICIONISTA', 'FISIOTERAPEUTA', 'PEDAGOGO'] as $role) {
            $data = $service->forRole($usuario, $role);
            $this->assertCount(4, $data['metrics']);
            $this->assertSame([0, 0, 0, 0], array_column($data['metrics'], 'value'));
            $this->assertNotEmpty($data['panels']);
        }
    }

    public function test_dashboard_compartido_renderiza_los_bloques_institucionales(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('SUPERADMINISTRADOR');

        $html = $this->actingAs($usuario)->get(route('dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('rm-dashboard-data-grid', $html);
        $this->assertStringContainsString('Residentes por estado', $html);
        $this->assertStringContainsString('No hay alertas prioritarias abiertas', $html);
        $this->assertStringNotContainsString('rm-role-focus__grid', $html);
    }

    public function test_dashboard_familiar_sin_vinculo_no_muestra_metricas_institucionales(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('FAMILIAR');

        $html = $this->actingAs($usuario)->get(route('dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('No hay un vínculo activo con autorización de información', $html);
        $this->assertStringNotContainsString('Camas disponibles', $html);
        $this->assertStringNotContainsString('Medicaciones pendientes', $html);
    }
}
