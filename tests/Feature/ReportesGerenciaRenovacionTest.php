<?php

namespace Tests\Feature;

use App\Models\Cama;
use App\Models\Habitacion;
use App\Models\Personal;
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
}
