<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Acciones\GestionarConocimientoExperimental;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarLaboratorioExperimental;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ExplorarConocimientoExperto;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarResultadoLaboratorio;
use App\Modulos\ApoyoDecision\Aplicacion\Servicios\EscenariosPerfilExperimental;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Eloquent\CargadorConocimiento;
use App\Modulos\ApoyoDecision\Infraestructura\Simulacion\SesionLaboratorioExperimental;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ConocimientoPanel;
use App\Modulos\Identidad\Aplicacion\Acciones\RolePreviewService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use MotorExperto\adaptadores\entrada\EjemploTecnicoCOGMEM;
use MotorExperto\dominio\conocimiento\PaqueteConocimiento;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\Support\InformeCognitivoFixture;
use Tests\TestCase;

final class LaboratorioExpertoFrontendTest extends TestCase
{
    use RefreshDatabase;

    private User $autor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->autor = User::factory()->create(['estado' => 'ACTIVO']);
        $this->autor->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($this->autor);
    }

    public function test_ruta_real_sin_ejecutar_automaticamente_ni_publicar_metricas(): void
    {
        $this->get(route('admin.sistema-experto.index'))->assertOk()
            ->assertSee('Conocimiento bajo control')->assertSee('No hay versiones cargadas')
            ->assertSee('activación clínica pendiente')->assertDontSee('Ejecutar evaluación');
        self::assertSame([], app(SesionLaboratorioExperimental::class)->historial($this->autor));
    }

    public static function escenarios(): array
    {
        return array_map(static fn ($id) => [$id], array_keys(EscenariosPerfilExperimental::NOMBRES));
    }

    #[DataProvider('escenarios')]
    public function test_ejecucion_completa_muestra_salida_real_cinco_dominios_y_traza(string $escenario): void
    {
        $antes = DB::table('evaluaciones_expertas')->count();
        $componente = Livewire::test(ConocimientoPanel::class)
            ->set('escenarioSeleccionado', $escenario)->call('ejecutarPerfil')
            ->assertHasNoErrors()->assertSet('seccion', 'resultados')
            ->assertSee('Aprendizaje y memoria')->assertSee('Atención')->assertSee('Función ejecutiva')
            ->assertSee('Lenguaje')->assertSee('Función visuoespacial')->assertSee('DCL-C01')->assertSee('DCL-C05')
            ->assertSee('Evidencias y fuentes')->assertSee('Fundamento por dominio y trazabilidad')
            ->assertDontSee('literal_A')->assertDontSee('FALLO_SIMULADO');
        $e = app(SesionLaboratorioExperimental::class)->consultar($this->autor, $componente->get('ejecucion'));
        $componente->assertSee($e['salida']['resultado']['resumen']['petersen']['titulo']);
        $dto = app(PresentarResultadoLaboratorio::class)->construir($e);
        self::assertCount(5, $dto['dominios']);
        self::assertSame($e['salida']['resultado']['petersen'], $dto['petersen']);
        self::assertSame($antes, DB::table('evaluaciones_expertas')->count());
        self::assertStringNotContainsString('valor_original', json_encode($dto, JSON_THROW_ON_ERROR));
    }

    public function test_historial_conserva_instantanea_y_cambiar_pestana_no_recalcula(): void
    {
        $c = Livewire::test(ConocimientoPanel::class)->call('ejecutarPerfil');
        $primera = $c->get('ejecucion');
        $original = app(SesionLaboratorioExperimental::class)->consultar($this->autor, $primera);
        $c->call('ir', 'conocimiento')->call('ir', 'resultados');
        self::assertCount(1, app(SesionLaboratorioExperimental::class)->historial($this->autor));
        $c->set('escenarioSeleccionado', 'incompleto')->call('ejecutarPerfil')->call('abrirEjecucion', $primera);
        self::assertSame($original, app(SesionLaboratorioExperimental::class)->consultar($this->autor, $primera));
        $c->assertSee($original['salida']['resultado']['resumen']['petersen']['titulo']);
    }

    public function test_filtros_paginacion_casos_y_reglas_independientes(): void
    {
        Livewire::test(ConocimientoPanel::class)->call('ir', 'casos')
            ->call('setPage', 2, 'casosPage')->assertSet('paginators.casosPage', 2)
            ->set('buscarCasos', 'contradictoria')->assertSet('paginators.casosPage', 1)->assertSee('Evidencia contradictoria')
            ->set('buscarCasos', 'no-existe')->assertSee('Sin coincidencias')
            ->call('ir', 'conocimiento')->set('dominio', 'COG-VIS')->assertSee('Visuoconstrucción')->assertDontSee('Atención sostenida')
            ->set('buscarReglas', 'inexistente')->assertSee('Sin reglas que coincidan');
    }

    public function test_regla_y_red_usan_condiciones_y_relaciones_del_paquete(): void
    {
        $c = Livewire::test(ConocimientoPanel::class)->call('ir', 'conocimiento')
            ->call('abrirRegla', 'R-ATE-S01-P01')->assertSee('Detalle de regla')->assertSee('Condiciones');
        $c->set('modoConocimiento', 'red')->call('abrirNodo', 'CRIT-ATE')
            ->assertSee('Nodo seleccionado')->assertSee('Explorar nodo relacionado');
    }

    public function test_red_filtra_sin_fabricar_nodos_y_catalogo_abre_detalle(): void
    {
        Livewire::test(ConocimientoPanel::class)->call('ir', 'conocimiento')->set('modoConocimiento', 'red')
            ->assertSee('Explorador semántico')->set('buscarRed', 'no-existe')
            ->assertSee('Sin nodos que coincidan')->call('limpiarRed')
            ->set('tipoRed', 'CRITERIO')->assertSee('Lista accesible de nodos')
            ->set('modoConocimiento', 'catalogo')->set('tablaCatalogo', 'nodos_semanticos')
            ->call('abrirRegistro', 'CRIT-ATE')->assertSet('detalleAbierto', true)
            ->assertSee('Detalle de conocimiento')->call('cerrarDetalle')->assertSet('detalleAbierto', false);
    }

    public function test_resumen_abre_catalogos_y_red_con_estado_controlado(): void
    {
        Livewire::test(ConocimientoPanel::class)->call('explorarCategoria', 'fuentes_datos_expertas')
            ->assertSet('seccion', 'conocimiento')->assertSet('modoConocimiento', 'catalogo')
            ->assertSet('tablaCatalogo', 'fuentes_datos_expertas')->assertSee('Sin registros en esta categoría')
            ->call('explorarRed')->assertSet('modoConocimiento', 'red')->assertSee('Explorador semántico');
        Livewire::test(ConocimientoPanel::class)->call('explorarCategoria', 'usuarios')->assertStatus(422);
    }

    public function test_comparacion_de_version_almacenada_conserva_contenido(): void
    {
        InformeCognitivoFixture::conocimiento($this->autor);
        $consulta = app(ExplorarConocimientoExperto::class);
        $antes = DB::table('reglas_expertas')->get()->toArray();
        $cambios = $consulta->comparar('VER_TEST', 'VER_TEST');
        self::assertNotEmpty($cambios);
        foreach ($cambios as $c) {
            self::assertSame('Sin cambios', $c['estado']);
        }
        Livewire::test(ConocimientoPanel::class)->call('ir', 'conocimiento')->call('seleccionarVersion', 'VER_TEST')
            ->set('modoConocimiento', 'versiones')->set('versionComparada', 'VER_TEST')->assertSee('Sin cambios');
        self::assertEquals($antes, DB::table('reglas_expertas')->get()->toArray());
    }

    public function test_integridad_fallida_se_traduce_sin_exponer_excepcion(): void
    {
        InformeCognitivoFixture::conocimiento($this->autor);
        DB::table('versiones_modelo_experto')->where('cod_version_modelo', 'VER_TEST')->update(['cod_version_anterior' => 'VER_TEST']);
        Livewire::test(ConocimientoPanel::class)->call('ir', 'conocimiento')->call('seleccionarVersion', 'VER_TEST')
            ->assertSee('Error de integridad')->assertDontSee('Ciclo entre versiones');
    }

    public function test_comparacion_reconoce_equivalencia_con_pk_regeneradas_y_cambio_de_fundamento(): void
    {
        $gestor = app(GestionarConocimientoExperimental::class);
        InformeCognitivoFixture::conocimiento($this->autor);
        $contrato = EjemploTecnicoCOGMEM::paquete();
        $p = new PaqueteConocimiento('VER_TEST',
            app(CargadorConocimiento::class)->instantanea('VER_TEST'),
            $contrato->contratosRelaciones, true, $contrato->tipoReglaResultado, $contrato->contratosExtraccion);
        $a = $gestor->crear($this->autor, $p, 'TEST-COMPARA-A', 'Primera revisión sintética de comparación.');
        $b = $gestor->crear($this->autor, $p, 'TEST-COMPARA-B', 'Segunda revisión sintética de comparación.', $a);
        $consulta = app(ExplorarConocimientoExperto::class);
        $reglaA = array_values($consulta->obtener($a)['reglas'])[0];
        $reglaB = array_values($consulta->obtener($b)['reglas'])[0];
        self::assertNotSame($reglaA['id'], $reglaB['id']);
        foreach ($consulta->comparar($a, $b) as $c) {
            self::assertSame('Sin cambios', $c['estado']);
        }
        DB::table('reglas_expertas')->where('cod_regla_experta', $reglaB['id'])->update(['observacion' => 'Fundamento sintético revisado para comprobar diferencias.']);
        $cambio = $consulta->comparar($a, $b)[$reglaB['codigo']];
        self::assertSame('Modificada', $cambio['estado']);
        self::assertNotSame($cambio['antes']['fundamento'], $cambio['despues']['fundamento']);
        self::assertSame('Fundamento sintético revisado para comprobar diferencias.', $consulta->obtener($b)['reglas'][$reglaB['id']]['fundamento']);
    }

    public function test_fallo_motor_conserva_el_resultado_anterior_y_mensaje_seguro(): void
    {
        $c = Livewire::test(ConocimientoPanel::class)->call('ejecutarPerfil');
        $id = $c->get('ejecucion');
        $this->app->instance(ConsultarLaboratorioExperimental::class, new class
        {
            public function ejecutar(User $autor, string $caso): array
            {
                throw new \DomainException('Detalle interno confidencial');
            }
        });
        $c->call('ejecutarPerfil')->assertSet('ejecucion', $id)->assertSee('La evaluación no se completó')
            ->assertDontSee('Detalle interno confidencial');
    }

    public function test_id_sesion_ajena_y_caso_arbitrario_se_rechazan(): void
    {
        $id = app(SesionLaboratorioExperimental::class)->ejecutar($this->autor, 'memoria');
        $otra = User::factory()->create(['estado' => 'ACTIVO']);
        $otra->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($otra);
        Livewire::test(ConocimientoPanel::class)->call('abrirEjecucion', $id)->assertNotFound();
        Livewire::test(ConocimientoPanel::class)->set('escenarioSeleccionado', '../../residentes')->call('ejecutarPerfil')
            ->assertHasErrors('escenarioSeleccionado');
        self::assertSame([], app(SesionLaboratorioExperimental::class)->historial($otra));
    }

    public function test_produccion_no_ejecuta_experimentos(): void
    {
        $this->app->instance('env', 'production');
        Livewire::test(ConocimientoPanel::class)->assertSee('Ejecución experimental deshabilitada')
            ->call('ejecutarPerfil')->assertNotFound();
    }

    public function test_cuenta_inactiva_o_permiso_retirado_rechazan_interaccion(): void
    {
        $c = Livewire::test(ConocimientoPanel::class);
        $this->autor->update(['estado' => 'INACTIVO']);
        $c->call('ir', 'resultados')->assertForbidden();
        $this->autor->update(['estado' => 'ACTIVO']);
        $this->autor->refresh();
        Role::findByName('SUPERADMINISTRADOR')->revokePermissionTo('auditoria.ver');
        Livewire::test(ConocimientoPanel::class)->assertForbidden();
    }

    public function test_medico_enfermeria_y_vista_previa_no_ganan_laboratorio(): void
    {
        foreach (['MEDICO GENERAL/GERIATRA', 'ENFERMEROS', 'ADMINISTRADOR'] as $rol) {
            $this->autor->syncRoles([$rol]);
            $this->get(route('admin.sistema-experto.index'))->assertForbidden();
            Livewire::test(ConocimientoPanel::class)->assertForbidden();
        }
        $this->autor->syncRoles(['SUPERADMINISTRADOR']);
        session()->put(RolePreviewService::SESSION_KEY, 'MEDICO GENERAL/GERIATRA');
        Livewire::test(ConocimientoPanel::class)->assertForbidden();
    }
}
