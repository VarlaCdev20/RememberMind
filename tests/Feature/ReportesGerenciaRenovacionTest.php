<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\Jornada;
use App\Models\Personal;
use App\Models\Turno;
use App\Models\User;
use App\Modulos\Reportes\Aplicacion\Consultas\ConsultarIndicadoresGerencia;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportesGerenciaRenovacionTest extends TestCase
{
    use RefreshDatabase;

    private User $gerente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->gerente = User::factory()->create(['estado' => 'ACTIVO']);
        $this->gerente->assignRole('GERENTE');
        $this->actingAs($this->gerente);
    }

    public function test_gerencia_consulta_agregados_sin_payload_clinico_y_prepara_ambos_formatos(): void
    {
        $respuesta = $this->get(route('admin.reportes.institucional.preview'))->assertOk()
            ->assertSeeText('Reportes de Gerencia')->assertViewMissing('salud')->assertViewMissing('bitacora');
        $solicitud = $respuesta->viewData('solicitud');
        $this->get(route('admin.reportes.institucional.preparar', compact('solicitud')))
            ->assertOk()->assertSeeText('Preparar exportación')->assertSeeText('CSV');
        $this->get(route('admin.reportes.institucional.csv', compact('solicitud')))
            ->assertOk()->assertDownload()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $pdf = $this->get(route('admin.reportes.institucional.pdf', compact('solicitud')))->assertOk()->assertDownload();
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $this->get(route('admin.reportes.institucional.excel'))->assertForbidden();
    }

    public function test_descarga_retiene_las_filas_preparadas_sin_recalculo_silencioso(): void
    {
        $solicitud = $this->get(route('admin.reportes.institucional.preview'))->viewData('solicitud');
        Personal::create(['cod_usuario' => $this->gerente->getKey(), 'nombres' => 'Trabajador', 'apellido_paterno' => 'Sintetico', 'numero_documento' => '8765432', 'profesion' => 'GERENCIA', 'estado' => 'ACTIVO']);
        $csv = $this->get(route('admin.reportes.institucional.csv', compact('solicitud')))->assertOk()->streamedContent();
        $filas = array_map('str_getcsv', array_filter(explode("\n", str_replace("\r", '', $csv))));
        $personal = array_values(array_filter($filas, fn ($fila) => $fila[0] === 'Personal activo'));
        $this->assertSame('0', $personal[0][1]);
        $this->assertSame(1, app(ConsultarIndicadoresGerencia::class)->consultar($this->gerente)['personal']);
    }

    public function test_personal_no_cuenta_roles_y_cama_vacia_bloqueada_no_es_disponible(): void
    {
        $cuenta = User::factory()->create(['estado' => 'ACTIVO']);
        $cuenta->assignRole(['ENFERMEROS', 'PSICOLOGO/A']);
        Personal::create(['cod_usuario' => $cuenta->getKey(), 'nombres' => 'Trabajador', 'apellido_paterno' => 'Sintetico', 'numero_documento' => '8765432', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        $habitacion = Habitacion::create(['codigo' => 'Habitación de prueba', 'estado' => 'BLOQUEADA']);
        Cama::create(['codigo' => 'Cama de prueba', 'cod_habitacion' => $habitacion->getKey(), 'estado' => 'DISPONIBLE']);
        $reporte = app(ConsultarIndicadoresGerencia::class)->consultar($this->gerente);
        $this->assertSame(1, $reporte['personal']);
        $this->assertSame(0, $reporte['disponibles']);
        $this->assertSame(1, $reporte['no_habilitadas']);
    }

    public function test_otra_cuenta_no_recupera_preparacion_y_la_caducidad_no_finge_exito(): void
    {
        $solicitud = $this->get(route('admin.reportes.institucional.preview'))->viewData('solicitud');
        $otra = User::factory()->create(['estado' => 'ACTIVO']);
        $otra->assignRole('GERENTE');
        $this->actingAs($otra)->get(route('admin.reportes.institucional.csv', compact('solicitud')))->assertNotFound();
        $this->actingAs($this->gerente);
        $this->travel(16)->minutes();
        $this->get(route('admin.reportes.institucional.csv', compact('solicitud')))->assertStatus(409);
    }

    public function test_permiso_revocado_despues_de_preparar_impide_descarga(): void
    {
        $solicitud = $this->get(route('admin.reportes.institucional.preview'))->viewData('solicitud');
        Role::findByName('GERENTE', 'web')->revokePermissionTo('habitaciones.ver');
        $this->get(route('admin.reportes.institucional.csv', compact('solicitud')))->assertForbidden();
        $this->get(route('admin.reportes.institucional.preview'))->assertForbidden();
    }

    public function test_seccion_exporta_solo_las_filas_mostradas_y_rechaza_entrada_arbitraria(): void
    {
        $respuesta = $this->get(route('admin.reportes.institucional.preview', ['seccion' => 'personal']))->assertOk();
        $this->assertCount(1, $respuesta->viewData('reporte')['filas']);
        $solicitud = $respuesta->viewData('solicitud');
        $csv = $this->get(route('admin.reportes.institucional.csv', compact('solicitud')))->assertOk()->streamedContent();
        $this->assertStringNotContainsString('Camas ocupadas', $csv);
        $this->getJson(route('admin.reportes.institucional.preview', ['seccion' => 'expedientes']))->assertUnprocessable();
    }

    public function test_las_vistas_gerenciales_ofrecen_su_propio_reporte(): void
    {
        foreach ([
            'inicio' => 'dashboard', 'personal' => 'admin.personal-institucional', 'documentacion' => 'admin.personal.documentacion',
            'areas' => 'admin.areas-institucionales.index', 'planificacion' => 'admin.turnos-asignaciones.index',
            'cobertura' => 'admin.cobertura-equipo.index', 'residentes' => 'admin.residentes.index',
            'contactos' => 'admin.familia-social.red-apoyo', 'ocupacion' => 'admin.habitaciones.index',
            'alertas' => 'admin.alertas-clinicas.index', 'galeria' => 'admin.galeria.index',
        ] as $vista => $ruta) {
            $preparar = route('admin.reportes.institucional.preparar', compact('vista'));
            $this->get(route($ruta))->assertOk()->assertSeeText('Exportar reporte')->assertSee($preparar, false);
            $respuesta = $this->get($preparar)->assertOk();
            $reporte = $respuesta->viewData('reporte');
            $this->assertSame($vista, $reporte['vista_origen']);
            $this->assertSame(route($ruta, absolute: false), $reporte['volver']);
            $this->assertNotEmpty($reporte['filas']);
        }
        $this->get(route('admin.reportes.institucional.preparar', ['vista' => 'ficha-clinica']))->assertNotFound();
    }

    public function test_cobertura_exporta_la_seleccion_sin_incluir_identidades_y_revalida_su_permiso(): void
    {
        $cuenta = User::factory()->create(['estado' => 'ACTIVO']);
        $persona = Personal::create(['cod_usuario' => $cuenta->getKey(), 'nombres' => 'PERSONA NOMINAL DE PRUEBA', 'apellido_paterno' => 'Sintética',
            'numero_documento' => '6582145', 'profesion' => 'ENFERMERIA', 'estado' => 'ACTIVO']);
        $area = Area::create(['nombre' => 'Área seleccionada', 'estado' => 'ACTIVO']);
        $otra = Area::create(['nombre' => 'Otra área', 'estado' => 'ACTIVO']);
        $turno = Turno::create(['nombre' => 'Mañana', 'hora_inicio' => '08:00', 'hora_cierre' => '16:00', 'estado' => 'ACTIVO']);
        $fecha = '2026-10-26';
        $jornada = Jornada::create(['cod_turno' => $turno->getKey(), 'fecha_jornada' => $fecha, 'estado' => 'ABIERTA']);
        foreach ([$area, $otra] as $origen) {
            AsignacionPersonal::create(['cod_jornada' => $jornada->getKey(), 'cod_personal' => $persona->getKey(),
                'cod_area' => $origen->getKey(), 'estado' => 'ACTIVO']);
        }
        Role::findByName('GERENTE', 'web')->revokePermissionTo('habitaciones.ver');
        $seleccion = ['fecha' => $fecha, 'area' => $area->getKey()];
        $preparar = route('admin.reportes.institucional.preparar', ['vista' => 'cobertura'] + $seleccion);
        $respuesta = $this->get($preparar)->assertOk()->assertSeeText('Fecha de jornada 26/10/2026');
        $solicitud = $respuesta->viewData('solicitud');
        $reporte = $respuesta->viewData('reporte');
        $this->assertSame(route('admin.cobertura-equipo.index', $seleccion, absolute: false), $reporte['volver']);
        $this->assertSame(1, collect($reporte['filas'])->firstWhere('indicador', 'Asignaciones registradas')['valor']);
        $csv = $this->get(route('admin.reportes.institucional.csv', compact('solicitud')))->assertOk()->streamedContent();
        $this->assertStringContainsString('"Asignaciones registradas",1,asignaciones', $csv);
        $this->assertStringNotContainsString('PERSONA NOMINAL DE PRUEBA', $csv);
        $this->get(route('admin.reportes.institucional.pdf', compact('solicitud')))->assertOk()
            ->assertDownload('reporte-gerencial-cobertura-'.today()->toDateString().'.pdf');
        $planificacion = $this->get(route('admin.reportes.institucional.preparar', ['vista' => 'planificacion', 'periodo' => 'dia'] + $seleccion))
            ->assertOk()->viewData('reporte');
        $this->assertSame(1, collect($planificacion['filas'])->firstWhere('indicador', 'Asignaciones planificadas')['valor']);
        Role::findByName('GERENTE', 'web')->revokePermissionTo('asignaciones_personal.ver');
        $this->get($preparar)->assertForbidden();
        $this->get(route('admin.reportes.institucional.csv', compact('solicitud')))->assertForbidden();
    }

    public function test_la_preparacion_contextual_no_se_extiende_a_otros_roles_con_permiso_de_reportes(): void
    {
        $administrador = User::factory()->create(['estado' => 'ACTIVO']);
        $administrador->assignRole('ADMINISTRADOR');
        $administrador->givePermissionTo('reportes.institucional');

        $this->actingAs($administrador)->get(route('admin.reportes.institucional.preparar', ['vista' => 'ocupacion']))->assertForbidden();
        $this->get(route('admin.reportes.institucional.pdf', ['solicitud' => (string) \Illuminate\Support\Str::uuid()]))->assertForbidden();
        $this->get(route('admin.galeria.index'))->assertOk()->assertDontSeeText('Exportar reporte');
    }

    public function test_exportacion_emergente_ofrece_contenido_y_descarga_solo_las_partes_elegidas(): void
    {
        $this->get(route('admin.personal-institucional'))->assertOk()
            ->assertSee('data-rm-export-vista="personal"', false)->assertSeeText('¿En qué formato?')
            ->assertSeeText('¿Qué parte quieres exportar?');
        $respuesta = $this->getJson(route('admin.reportes.institucional.preparar', ['vista' => 'personal']))
            ->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('titulo', 'Personal institucional')->assertJsonCount(3, 'partes');
        $solicitud = $respuesta->json('solicitud');
        $this->assertSame('Trabajadores activos', $respuesta->json('partes.1.etiqueta'));
        $seleccion = ['solicitud' => $solicitud, 'partes' => ['1']];
        $csv = $this->get(route('admin.reportes.institucional.csv', $seleccion))->assertOk()->streamedContent();
        $this->assertStringContainsString('"Trabajadores activos",0,personas', $csv);
        $this->assertStringNotContainsString('Trabajadores registrados', $csv);
        $this->assertStringNotContainsString('Trabajadores inactivos', $csv);
        $this->get(route('admin.reportes.institucional.pdf', $seleccion))->assertOk()->assertDownload();
        $this->getJson(route('admin.reportes.institucional.csv', ['solicitud' => $solicitud, 'partes' => ['90']]))->assertUnprocessable();
        $this->getJson(route('admin.reportes.institucional.csv', ['solicitud' => $solicitud, 'partes' => ['1', '1']]))->assertUnprocessable();
        $this->getJson(route('admin.reportes.institucional.pdf', ['solicitud' => $solicitud, 'partes' => '']))->assertUnprocessable();
        $completo = $this->get(route('admin.reportes.institucional.csv', compact('solicitud')))->assertOk()->streamedContent();
        $this->assertStringContainsString('Trabajadores registrados', $completo);
        Role::findByName('GERENTE', 'web')->revokePermissionTo('personal_institucional.ver');
        $this->getJson(route('admin.reportes.institucional.csv', $seleccion))->assertForbidden();
    }

    public function test_graficas_de_vista_y_seleccion_revalidan_permisos_y_conservan_el_conjunto_preparado(): void
    {
        $this->getJson(route('admin.reportes.institucional.graficas', ['vista' => 'personal']))->assertOk()
            ->assertJsonPath('graficas.0.recomendado', 'dona')->assertJsonPath('graficas.0.total', 0);
        $solicitud = $this->getJson(route('admin.reportes.institucional.preparar', ['vista' => 'personal']))->json('solicitud');
        Personal::create(['cod_usuario' => $this->gerente->getKey(), 'nombres' => 'Registro', 'apellido_paterno' => 'Sintetico', 'numero_documento' => '8765432', 'profesion' => 'GERENCIA', 'estado' => 'ACTIVO']);
        $seleccion = ['solicitud' => $solicitud, 'partes' => ['1']];
        $this->getJson(route('admin.reportes.institucional.graficas', $seleccion))->assertOk()
            ->assertJsonCount(1, 'graficas.0.serie')->assertJsonPath('graficas.0.serie.0.valor', 0)->assertJsonPath('graficas.0.tipos', ['barras']);
        $this->getJson(route('admin.reportes.institucional.pdf', $seleccion + ['tipo_grafica' => 'inventado']))->assertUnprocessable();
        Role::findByName('GERENTE', 'web')->revokePermissionTo('personal_institucional.ver');
        $this->getJson(route('admin.reportes.institucional.graficas', $seleccion))->assertForbidden();
        $this->getJson(route('admin.reportes.institucional.graficas', ['vista' => 'personal']))->assertForbidden();
    }

    public function test_graficas_de_reporte_separan_unidades_y_conservan_ceros_y_datos_no_disponibles(): void
    {
        $reporte = ['filas' => [
            ['indicador' => 'Trabajadores activos', 'valor' => 0, 'unidad' => 'personas'],
            ['indicador' => 'Trabajadores sin fuente disponible', 'valor' => null, 'unidad' => 'personas'],
            ['indicador' => 'Camas disponibles', 'valor' => 8, 'unidad' => 'camas'],
        ]];
        $html = view('reportes::paginas.gerencia.partials.graficas-reporte', compact('reporte'))->render();
        $this->assertSame(2, substr_count($html, 'data-report-chart="valores"'));
        $this->assertStringContainsString('Indicadores en personas', $html);
        $this->assertStringContainsString('Indicadores en camas', $html);
        $this->assertStringContainsString('width: 0%', $html);
        $this->assertStringContainsString('No disponible', $html);
        $this->assertStringNotContainsString('Sin datos disponibles', $html);
        $this->assertStringContainsString('Escala de 0 a 8 camas', $html);

        $reporte['filas'] = [['indicador' => 'Fuente restringida', 'valor' => null, 'unidad' => 'personas']];
        $sinDatos = view('reportes::paginas.gerencia.partials.graficas-reporte', compact('reporte'))->render();
        $this->assertStringContainsString('Sin datos disponibles', $sinDatos);
        $this->assertStringNotContainsString('width: 0%', $sinDatos);
    }
}
