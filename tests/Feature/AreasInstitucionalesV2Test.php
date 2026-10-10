<?php

namespace Tests\Feature;

use App\Backend\Modulos\Reportes\Servicios\AreasReportDataService;
use App\Backend\Modulos\Reportes\Servicios\ReportExportService;
use App\Exports\AreasInstitucionalesExport;
use App\Frontend\Livewire\Superadministrador\Identidad\AreasInstitucionalesPanel;
use App\Models\Area;
use App\Models\AsignacionPersonal;
use App\Models\Jornada;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class AreasInstitucionalesV2Test extends TestCase
{
    use RefreshDatabase;

    public static function exportacionesConError(): array
    {
        return [
            'excel general' => ['exportarReporteGeneralExcel', 'exportExcel', [], 'No se pudo generar el reporte en Excel. Vuelve a intentarlo.'],
            'excel de área' => ['exportarUsuariosAreaExcel', 'exportExcel', ['ARE_F4_EXPORT'], 'No se pudo generar el reporte en Excel de los usuarios. Vuelve a intentarlo.'],
            'csv general' => ['exportarReporteGeneralCsv', 'exportCsv', [], 'No se pudo generar el reporte en CSV. Vuelve a intentarlo.'],
        ];
    }

    #[DataProvider('exportacionesConError')]
    public function test_exportacion_fallida_reporta_sin_exponer_internos_ni_auditar_exito(string $metodo, string $exportador, array $argumentos, string $mensaje): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create(['estado' => 'ACTIVO']);
        $actor->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($actor);
        Area::create(['cod_area' => 'ARE_F4_EXPORT', 'nombre' => 'Área sintética', 'estado' => 'ACTIVO']);

        $fallo = new \RuntimeException('SQLSTATE[XX000] credencial_sintetica C:\\privado\\interno.php');
        $this->mock(ReportExportService::class, function ($mock) use ($exportador, $fallo) {
            $mock->shouldReceive($exportador)->once()->andThrow($fallo);
        });
        Exceptions::fake();

        $componente = Livewire::test(AreasInstitucionalesPanel::class);
        $auditorias = Activity::query()->count();
        $componente->call($metodo, ...$argumentos)
            ->assertDispatched('swal:modal', fn ($evento, $datos) => ($datos[0]['text'] ?? null) === $mensaje);

        Exceptions::assertReported(fn (\RuntimeException $excepcion) => $excepcion === $fallo);
        $this->assertSame($auditorias, Activity::query()->count(), 'Una exportación fallida no registra éxito.');
    }

    #[DataProvider('exportacionesConError')]
    public function test_exportacion_exitosa_conserva_descarga_y_auditoria(string $metodo, string $exportador, array $argumentos, string $mensaje): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create(['estado' => 'ACTIVO']);
        $actor->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($actor);
        Area::create(['cod_area' => 'ARE_F4_EXPORT', 'nombre' => 'Área sintética', 'estado' => 'ACTIVO']);
        $this->mock(ReportExportService::class, function ($mock) use ($exportador) {
            $mock->shouldReceive($exportador)->once()->andReturn(response()->streamDownload(function () {
                echo 'Contenido sintético';
            }, 'reporte-sintetico.csv'));
        });

        $componente = Livewire::test(AreasInstitucionalesPanel::class);
        $auditorias = Activity::query()->count();
        $componente->call($metodo, ...$argumentos)->assertFileDownloaded('reporte-sintetico.csv');
        $this->assertSame($auditorias + 1, Activity::query()->count());
        $this->assertSame($actor->cod_usuario, (string) Activity::query()->latest('id')->firstOrFail()->causer_id);
    }

    #[DataProvider('exportacionesConError')]
    public function test_exportacion_sin_permiso_no_invoca_exportador_ni_audita_exito(string $metodo, string $exportador, array $argumentos, string $mensaje): void
    {
        $this->actingAs(User::factory()->create(['estado' => 'ACTIVO']));
        $this->mock(ReportExportService::class, fn ($mock) => $mock->shouldNotReceive($exportador));
        $componente = Livewire::test(AreasInstitucionalesPanel::class);
        $auditorias = Activity::query()->count();

        $componente->call($metodo, ...$argumentos)
            ->assertDispatched('swal:modal', fn ($evento, $datos) => ($datos[0]['title'] ?? null) === 'Acceso denegado');
        $this->assertSame($auditorias, Activity::query()->count());
    }

    #[DataProvider('exportacionesConError')]
    public function test_exportacion_revalida_cuenta_desactivada_despues_de_abrir_panel(string $metodo, string $exportador, array $argumentos, string $mensaje): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create(['estado' => 'ACTIVO']);
        $actor->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($actor);
        $this->mock(ReportExportService::class, fn ($mock) => $mock->shouldNotReceive($exportador));
        $componente = Livewire::test(AreasInstitucionalesPanel::class);
        $actor->update(['estado' => 'INACTIVO']);
        $auditorias = Activity::query()->count();

        $componente->call($metodo, ...$argumentos)
            ->assertDispatched('swal:modal', fn ($evento, $datos) => ($datos[0]['title'] ?? null) === 'Acceso denegado');
        $this->assertSame($auditorias, Activity::query()->count());
    }

    public function test_exportacion_de_area_inexistente_es_error_esperado_sin_internos(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create(['estado' => 'ACTIVO']);
        $actor->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($actor);
        $this->mock(ReportExportService::class, fn ($mock) => $mock->shouldNotReceive('exportExcel'));
        Exceptions::fake();

        $componente = Livewire::test(AreasInstitucionalesPanel::class);
        $auditorias = Activity::query()->count();
        $componente->call('exportarUsuariosAreaExcel', 'ARE_INEXISTENTE')
            ->assertDispatched('swal:modal', fn ($evento, $datos) => ($datos[0]['text'] ?? null) === 'El área seleccionada ya no está disponible. Actualiza el listado.');
        Exceptions::assertNothingReported();
        $this->assertSame($auditorias, Activity::query()->count());
    }

    public function test_panel_guarda_solo_datos_reales_de_area_y_muestra_la_ficha(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($actor);

        Livewire::test(AreasInstitucionalesPanel::class)
            ->call('crearArea')
            ->set('nombre', 'Área de rehabilitación')
            ->set('descripcion', 'Atención funcional')
            ->set('estado', 'ACTIVO')
            ->call('guardarArea')
            ->assertHasNoErrors();

        $area = Area::query()->where('nombre', 'ÁREA DE REHABILITACIÓN')->firstOrFail();
        $this->assertDatabaseHas('areas', [
            'cod_area' => $area->cod_area,
            'descripcion' => 'Atención funcional',
            'estado' => 'ACTIVO',
        ]);

        Livewire::test(AreasInstitucionalesPanel::class)
            ->call('verArea', $area->cod_area)
            ->assertSee('ÁREA DE REHABILITACIÓN')
            ->assertSee('Sin Responsable Asignado');
    }

    public function test_responsable_y_personal_se_derivan_solo_de_asignaciones_activas(): void
    {
        $area = Area::create(['cod_area' => 'ARE_REPORTE', 'nombre' => 'Enfermería', 'estado' => 'ACTIVO']);
        $turno = Turno::create([
            'cod_turno' => 'TUR_REPORTE', 'nombre' => 'Mañana',
            'hora_inicio' => '07:00:00', 'hora_cierre' => '15:00:00', 'estado' => 'ACTIVO',
        ]);
        $jornada = Jornada::create([
            'cod_jornada' => 'JOR_REPORTE', 'cod_turno' => $turno->cod_turno,
            'fecha_jornada' => today(), 'estado' => 'ACTIVA',
        ]);
        $responsable = User::factory()->create(['nombres' => 'Rosa', 'apellido_paterno' => 'Quispe']);
        $historico = User::factory()->create(['nombres' => 'Juan', 'apellido_paterno' => 'Paz']);
        foreach ([[$responsable, 'ACTIVA'], [$historico, 'INACTIVA']] as [$usuario, $estado]) {
            AsignacionPersonal::create([
                'cod_jornada' => $jornada->cod_jornada,
                'cod_personal' => $usuario->personal->cod_personal,
                'cod_area' => $area->cod_area,
                'tipo_asignacion' => 'RESPONSABLE',
                'fecha_asignacion' => now(),
                'estado' => $estado,
            ]);
        }

        $datos = app(AreasReportDataService::class)->getAreaReportData($area->cod_area);

        $this->assertSame($responsable->cod_usuario, $datos['area']->responsable->cod_usuario);
        $this->assertSame(1, $datos['totalUsuarios']);
        $this->assertSame(1, $datos['usuariosActivos']);

        $exportacion = new AreasInstitucionalesExport;
        $this->assertContains('Descripción', $exportacion->headings());
        $this->assertSame(count($exportacion->headings()), count($exportacion->map($datos['area'])));
        $this->assertStringContainsString('Enfermería', view('pdf.exports.areas.area', $datos)->render());
    }

    public function test_usuario_sin_permiso_no_puede_crear_area(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(AreasInstitucionalesPanel::class)
            ->set('nombre', 'Área no autorizada')
            ->set('estado', 'ACTIVO')
            ->call('guardarArea');

        $this->assertDatabaseMissing('areas', ['nombre' => 'ÁREA NO AUTORIZADA']);
    }

    public function test_cuenta_inactiva_con_permiso_no_puede_crear_area(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create(['estado' => 'INACTIVO']);
        $actor->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($actor);

        Livewire::test(AreasInstitucionalesPanel::class)
            ->set('nombre', 'Área de cuenta inactiva')
            ->set('estado', 'ACTIVO')
            ->call('guardarArea');

        $this->assertDatabaseMissing('areas', ['nombre' => 'ÁREA DE CUENTA INACTIVA']);
    }

    public function test_panel_lee_y_cambia_el_estado_de_un_area_con_valor_femenino_existente(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create();
        $actor->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($actor);
        $area = Area::create(['cod_area' => 'ARE_EXISTENTE', 'nombre' => 'Área existente', 'estado' => 'ACTIVA']);

        Livewire::test(AreasInstitucionalesPanel::class)
            ->set('filtroEstado', 'ACTIVO')
            ->assertSee('Área existente')
            ->call('toggleEstado', $area->cod_area);

        $this->assertDatabaseHas('areas', ['cod_area' => $area->cod_area, 'estado' => 'INACTIVO']);
    }
}
