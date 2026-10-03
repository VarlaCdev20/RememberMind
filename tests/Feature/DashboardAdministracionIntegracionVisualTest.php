<?php

namespace Tests\Feature;

use App\Backend\Modulos\Admisiones\Acciones\FormalizarAdmision;
use App\Models\Cama;
use App\Models\Contacto;
use App\Models\Habitacion;
use App\Models\Preadmision;
use App\Models\User;
use App\Frontend\Livewire\Admisiones\PreadmisionesPanel;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardAdministracionIntegracionVisualTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_dashboard_muestra_operacion_real_y_sidebar_administrativo(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ADMINISTRADOR');

        $html = $this->actingAs($usuario)->get(route('admin.administracion.dashboard'))
            ->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        foreach ([
            'Centro de Coordinación Residencial', 'Requiere atención ahora',
            'Indicadores de coordinación', 'Preadmisiones pendientes', 'Camas disponibles',
            'Personal activo', 'Documentos por revisar',
            'Ingresos en proceso', 'Lo que ocurre hoy', 'Visitas de hoy',
            'Cómo funciona la operación', 'Evolución de ocupación y admisiones', 'Dinámica residencial',
            'Preadmisiones recientes', 'Agenda residencial de hoy',
            'Admisiones en preparación', 'Cobertura de la jornada', 'Movimientos recientes',
            'Habitaciones y camas', 'Documentos', 'Consentimientos', 'Seguros', 'Incidentes',
        ] as $texto) {
            $this->assertStringContainsString($texto, $html);
        }
        $this->assertLessThan(strpos($html, 'Indicadores de coordinación'), strpos($html, 'Requiere atención ahora'));
        $this->assertLessThan(strpos($html, 'Cómo funciona la operación'), strpos($html, 'Lo que ocurre hoy'));
        $this->assertStringNotContainsString('Crear residente', $html);
        $this->assertStringNotContainsString('Voluntarios', $html);
        $this->assertStringNotContainsString('Diagnósticos', $html);
        $this->assertStringNotContainsString('href="#"', $html);
        $this->assertStringContainsString('Sin actividades programadas', $html);
    }

    public function test_dashboard_administrativo_exige_rol_administrador_activo(): void
    {
        $administrador = User::factory()->create(['estado' => 'ACTIVO']);
        $administrador->assignRole('ADMINISTRADOR');
        $enfermero = User::factory()->create(['estado' => 'ACTIVO']);
        $enfermero->assignRole('ENFERMEROS');

        $this->actingAs($administrador)->get(route('admin.administracion.dashboard'))->assertOk();
        $this->actingAs($enfermero)->get(route('admin.administracion.dashboard'))->assertForbidden();
    }

    public function test_modulos_administrativos_vacios_tienen_estado_y_no_permiten_entrada_sin_permiso(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ADMINISTRADOR');

        foreach (['admisiones', 'residentes', 'habitaciones', 'ocupacion', 'jornadas',
            'asignaciones', 'contactos', 'documentacion', 'consentimientos', 'seguros',
            'actividades', 'visitas', 'alertas', 'incidentes', 'reportes'] as $modulo) {
            $response = $this->actingAs($usuario)->get(route('admin.administracion.'.$modulo));
            $response->assertOk();
            $this->assertSame(1, substr_count($response->getContent(), '<h1'));
            if (in_array($modulo, ['admisiones', 'ocupacion', 'jornadas', 'documentacion', 'consentimientos', 'visitas', 'alertas'], true)) {
                $this->assertStringContainsString('name="tab"', $response->getContent());
                $this->assertStringContainsString('id="admin-vista-'.$modulo.'"', $response->getContent());
                $this->assertStringNotContainsString('<nav class="rm-admin-page__tabs"', $response->getContent());
            }
        }

        $sinPermiso = User::factory()->create(['estado' => 'ACTIVO']);
        $this->actingAs($sinPermiso)->get(route('admin.administracion.admisiones'))->assertForbidden();
    }

    public function test_admision_formal_alimenta_indicadores_y_detalle_administrativo(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ADMINISTRADOR');
        $contacto = Contacto::query()->create([
            'cod_contacto' => 'CTO_ADMIN_1', 'nombres' => 'Ana', 'apellido_paterno' => 'Pérez', 'estado' => 'ACTIVO',
        ]);
        $habitacion = Habitacion::query()->create([
            'cod_habitacion' => 'HAB_ADMIN_1', 'codigo' => 'H-1', 'capacidad' => 1, 'estado' => 'DISPONIBLE',
        ]);
        $cama = Cama::query()->create([
            'cod_cama' => 'CAM_ADMIN_1', 'cod_habitacion' => $habitacion->cod_habitacion,
            'codigo' => 'C-1', 'estado' => 'ACTIVA',
        ]);
        $preadmision = Preadmision::query()->create([
            'cod_preadmision' => 'PRE_ADMIN_1', 'cod_contacto' => $contacto->cod_contacto,
            'cod_usuario_registro' => $usuario->cod_usuario, 'nombres' => 'Rosa',
            'apellido_paterno' => 'Flores', 'fecha_nacimiento' => '1945-05-05',
            'motivo_ingreso' => 'Cuidado integral', 'fecha_solicitud' => now(), 'estado' => 'APROBADA',
        ]);
        $residente = app(FormalizarAdmision::class)->ejecutar($preadmision, [
            'cod_cama' => $cama->cod_cama, 'cod_contacto' => $contacto->cod_contacto,
        ], $usuario);

        $dashboard = $this->actingAs($usuario)->get(route('admin.administracion.dashboard'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/>0<\/strong>\s*<h2[^>]*>Camas disponibles<\/h2>/', $dashboard);
        $this->assertSame(1, app(\App\Backend\Modulos\Administracion\Servicios\CentroCoordinacionService::class)->resumen()['ocupacion']['ocupadas']);
        $admisiones = $this->get(route('admin.administracion.admisiones'))->assertOk()->getContent();
        $this->assertStringContainsString('Rosa Flores', $admisiones);
        $this->assertStringContainsString('H-1', $admisiones);
        $this->assertStringContainsString('C-1', $admisiones);
        $this->assertStringContainsString(route('admin.administracion.residentes.show', $residente->cod_residente), $admisiones);
        $tarjetas = $this->get(route('admin.administracion.residentes'))->assertOk()->getContent();
        $this->assertStringContainsString('Rosa', $tarjetas);
        $this->assertStringContainsString('rm-admin-residents-cards', $tarjetas);
        $this->assertStringContainsString('Ver resumen', $tarjetas);
        $this->assertDoesNotMatchRegularExpression('/<table class="[^"]*\brm-table\b[^"]*">/', $tarjetas);
        $tabla = $this->get(route('admin.administracion.residentes', ['vista' => 'tabla']))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<table class="[^"]*\brm-table\b[^"]*">/', $tabla);
        $this->assertStringNotContainsString('class="rm-admin-residents-cards"', $tabla);
        $panel = $this->get(route('admin.administracion.residentes', ['residente' => $residente->cod_residente]))
            ->assertOk()->getContent();
        $this->assertStringContainsString('Rosa Flores', $panel);
        $this->assertStringContainsString('Ver expediente completo', $panel);
        $this->assertSame(1, preg_match('/<dl class="rm-admin-resident-panel__summary"[^>]*>(.*?)<\/dl>/s', $panel, $coincidencia));
        $resumen = $coincidencia[1];
        $this->assertSame(4, substr_count($resumen, 'class="rm-admin-resident-panel__fact'));
        foreach (['Habitación', 'Responsable principal', 'Nivel de cuidado', 'Observación breve', 'H-1', 'Ana Pérez', 'No registrado'] as $texto) {
            $this->assertStringContainsString($texto, $resumen);
        }
        foreach (['Fecha de nacimiento', 'Teléfono', 'Email', 'Documentos', 'Historial', 'Diagnósticos'] as $texto) {
            $this->assertStringNotContainsString($texto, $resumen);
        }
        $datosPanel = $this->get(route('admin.administracion.residentes', [
            'residente' => $residente->cod_residente, 'panel_tab' => 'datos',
        ]))->assertOk()->getContent();
        $this->assertStringContainsString('Fecha de nacimiento', $datosPanel);
        $this->assertStringNotContainsString('rm-admin-resident-panel__summary', $datosPanel);
        $detalle = $this->get(route('admin.administracion.residentes.show', $residente->cod_residente))->assertOk()->getContent();
        $this->assertStringContainsString('Rosa Flores', $detalle);
        $this->assertStringContainsString('Habitación H-1 · Cama C-1', $detalle);
        $this->assertStringContainsString('Ana Pérez', $detalle);
        $this->assertStringNotContainsString('Diagnósticos', $detalle);
        $busqueda = $this->get(route('admin.administracion.buscar', ['q' => 'Rosa']))->assertOk()->getContent();
        $this->assertStringContainsString('Rosa Flores', $busqueda);
        $this->assertStringContainsString(route('admin.administracion.residentes.show', $residente->cod_residente), $busqueda);
        foreach (['residentes', 'habitaciones', 'ocupacion', 'contactos', 'consentimientos', 'reportes'] as $modulo) {
            $this->get(route('admin.administracion.'.$modulo))->assertOk();
        }
        DB::table('ocupaciones_cama')->where('cod_residente', $residente->cod_residente)->update([
            'estado' => 'LIBERADA', 'fecha_hora_liberacion' => now(),
        ]);
        $this->assertStringNotContainsString('Rosa', $this->get(route('admin.administracion.ocupacion', ['tab' => 'actual']))->assertOk()->getContent());
        $this->assertStringContainsString('Rosa', $this->get(route('admin.administracion.ocupacion', ['tab' => 'historial']))->assertOk()->getContent());
    }

    public function test_formalizar_desde_panel_administrativo_no_escribe_ficha_clinica(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ADMINISTRADOR');
        $contacto = Contacto::query()->create([
            'cod_contacto' => 'CTO_ADMIN_2', 'nombres' => 'Ana', 'apellido_paterno' => 'Pérez', 'estado' => 'ACTIVO',
        ]);
        $habitacion = Habitacion::query()->create([
            'cod_habitacion' => 'HAB_ADMIN_2', 'codigo' => 'H-2', 'capacidad' => 1, 'estado' => 'ACTIVA',
        ]);
        $cama = Cama::query()->create([
            'cod_cama' => 'CAM_ADMIN_2', 'cod_habitacion' => $habitacion->cod_habitacion,
            'codigo' => 'C-2', 'estado' => 'ACTIVA',
        ]);
        $preadmision = Preadmision::query()->create([
            'cod_preadmision' => 'PRE_ADMIN_2', 'cod_contacto' => $contacto->cod_contacto,
            'cod_usuario_registro' => $usuario->cod_usuario, 'nombres' => 'Luisa',
            'apellido_paterno' => 'López', 'fecha_nacimiento' => '1947-01-05',
            'motivo_ingreso' => 'Acompañamiento', 'fecha_solicitud' => now(), 'estado' => 'APROBADA',
        ]);

        $this->actingAs($usuario);
        Livewire::test(PreadmisionesPanel::class)
            ->call('abrirAdmision', $preadmision->cod_preadmision)
            ->call('irPasoAdmision', 5)
            ->assertSet('pasoAdmision', 5)
            ->set('seguro_entidad', 'Seguro de prueba')
            ->set('seguro_plan', 'Plan institucional')
            ->set('habitacion_id', $habitacion->cod_habitacion)
            ->set('cama_id', $cama->cod_cama)
            ->set('autoriza_informacion_medica', true)
            ->set('consentimiento_datos', true)
            ->call('formalizarAdmision')
            ->assertHasNoErrors();

        $this->assertDatabaseCount('residentes', 1);
        $this->assertDatabaseCount('admisiones', 1);
        $this->assertDatabaseCount('ocupaciones_cama', 1);
        $this->assertDatabaseHas('seguros_residente', ['entidad' => 'Seguro de prueba', 'plan' => 'Plan institucional']);
        $this->assertDatabaseCount('atenciones', 0);
        $this->assertDatabaseCount('diagnosticos', 0);
        $this->assertDatabaseCount('alergias', 0);
    }

    public function test_admision_rechaza_cama_de_otra_habitacion(): void
    {
        $usuario = User::factory()->create(['estado' => 'ACTIVO']);
        $usuario->assignRole('ADMINISTRADOR');
        $contacto = Contacto::query()->create([
            'cod_contacto' => 'CTO_ADMIN_3', 'nombres' => 'Ana', 'apellido_paterno' => 'Pérez', 'estado' => 'ACTIVO',
        ]);
        Habitacion::query()->create(['cod_habitacion' => 'HAB_ADMIN_3A', 'codigo' => 'H-3A', 'capacidad' => 1, 'estado' => 'ACTIVA']);
        $otra = Habitacion::query()->create(['cod_habitacion' => 'HAB_ADMIN_3B', 'codigo' => 'H-3B', 'capacidad' => 1, 'estado' => 'ACTIVA']);
        $cama = Cama::query()->create(['cod_cama' => 'CAM_ADMIN_3', 'cod_habitacion' => $otra->cod_habitacion, 'codigo' => 'C-3', 'estado' => 'ACTIVA']);
        $preadmision = Preadmision::query()->create([
            'cod_preadmision' => 'PRE_ADMIN_3', 'cod_contacto' => $contacto->cod_contacto,
            'cod_usuario_registro' => $usuario->cod_usuario, 'nombres' => 'Eva',
            'apellido_paterno' => 'López', 'fecha_nacimiento' => '1947-01-05',
            'motivo_ingreso' => 'Acompañamiento', 'fecha_solicitud' => now(), 'estado' => 'APROBADA',
        ]);

        $this->actingAs($usuario);
        Livewire::test(PreadmisionesPanel::class)
            ->call('abrirAdmision', $preadmision->cod_preadmision)
            ->set('habitacion_id', 'HAB_ADMIN_3A')
            ->set('cama_id', $cama->cod_cama)
            ->set('autoriza_informacion_medica', true)
            ->set('consentimiento_datos', true)
            ->call('formalizarAdmision')
            ->assertHasErrors(['cama_id']);

        $this->assertDatabaseCount('residentes', 0);
        $this->assertDatabaseCount('ocupaciones_cama', 0);
    }
}
