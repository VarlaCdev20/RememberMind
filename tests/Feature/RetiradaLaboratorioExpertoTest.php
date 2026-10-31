<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Instrumento;
use App\Models\User;
use App\Models\VersionModeloExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarConocimientoAdministrativo;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ExplorarConocimientoExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\FuentesValoracionOperativa;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ConocimientoPanel;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

final class RetiradaLaboratorioExpertoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $actor = User::factory()->create(['estado' => 'ACTIVO']);
        $actor->assignRole('SUPERADMINISTRADOR');
        $this->actingAs($actor);
    }

    public function test_autoload_del_producto_no_resuelve_los_ocho_componentes_exclusivos_retirados(): void
    {
        $clases = ['DTOs\\SolicitudInferenciaDTO', 'DTOs\\EvidenciasEntradaDTO', 'DTOs\\ResultadoInferenciaDTO',
            'DTOs\\ResultadoDclDTO', 'DTOs\\InformeExpertoDTO', 'DTOs\\PresentarPatronExperimental',
            'CasosDeUso\\ClasificarDclUseCase', 'CasosDeUso\\GenerarInformeUseCase'];
        $clases = array_map(static fn ($c) => 'App\\Modulos\\ApoyoDecision\\Aplicacion\\'.$c, $clases);
        $codigo = 'require $argv[1]; $c = json_decode($argv[2], true, flags: JSON_THROW_ON_ERROR); echo count(array_filter($c, "class_exists"));';
        $proceso = new \Symfony\Component\Process\Process([PHP_BINARY, '-r', $codigo, base_path('vendor/autoload.php'), json_encode($clases, JSON_THROW_ON_ERROR)]);
        $proceso->setTimeout(30);
        self::assertSame(0, $proceso->run(), $proceso->getErrorOutput());
        self::assertSame('0', $proceso->getOutput());
        self::assertFalse(method_exists(\App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarInformeCognitivo::class, 'presentarG09Prueba'));
        self::assertFalse(method_exists(\App\Modulos\ApoyoDecision\Aplicacion\DTOs\LenguajeResultadosExperto::class, 'estadoG09'));
    }

    public function test_consulta_de_enfermeria_rechaza_el_antiguo_payload_simulado(): void
    {
        $this->expectException(\DomainException::class);
        (new \App\Modulos\ApoyoDecision\Aplicacion\DTOs\ConsultaCognitivaEnfermeria)
            ->presentar(['simulacion_local' => ['activo' => true]], [], null);
    }

    public function test_sin_version_no_aparece_conocimiento_ficticio_y_las_secciones_retiradas_se_rechazan(): void
    {
        $consulta = app(ExplorarConocimientoExperto::class)->obtener(null);
        self::assertSame([], $consulta['reglas']);
        self::assertFalse($consulta['experimental']);
        Livewire::test(ConocimientoPanel::class)
            ->assertSee('No hay versiones cargadas')
            ->assertDontSee('Casos sintéticos')
            ->assertDontSee('Ejecutar un caso sintético')
            ->call('ir', 'laboratorio')->assertStatus(422);
        self::assertFalse(method_exists(ConocimientoPanel::class, 'ejecutarPerfil'));
        self::assertFalse(method_exists(ConocimientoPanel::class, 'abrirEjecucion'));
        $comandos = Artisan::all();
        foreach (['experto:simular-local', 'experto:probar-perfil', 'experto:archivo-perfil', 'experto:gestionar-conocimiento', 'sistema-experto:cargar-paquete-memoria'] as $nombre) {
            self::assertArrayNotHasKey($nombre, $comandos);
        }
    }

    public function test_una_version_tecnica_y_su_sucesora_no_se_ofrecen_ni_se_consultan_por_id(): void
    {
        $actor = auth()->user();
        foreach ([['V_SIM', 'SIM-RETIRADA', null], ['V_DESC', 'REVISION-NEUTRA', 'V_SIM'], ['V_REAL', 'COGNITIVO-REGISTRADO', null]] as [$id, $codigo, $anterior]) {
            VersionModeloExperto::unguarded(fn () => VersionModeloExperto::create(['cod_version_modelo' => $id, 'codigo_version' => $codigo,
                'nombre' => 'Versión de comprobación', 'estado' => 'ACTIVO', 'fecha_hora_creacion' => now(),
                'cod_usuario_creacion' => $actor->getKey(), 'cod_version_anterior' => $anterior]));
        }
        self::assertSame(['V_REAL'], ConsultarConocimientoAdministrativo::institucionales()->pluck('cod_version_modelo')->all());
        self::assertFalse(app(ConsultarConocimientoAdministrativo::class)->existeVersion('V_DESC'));
        try {
            app(ConsultarConocimientoAdministrativo::class)->instantanea('V_SIM');
            self::fail('No debe leerse el paquete técnico.');
        } catch (HttpException $error) {
            self::assertSame(404, $error->getStatusCode());
        }
        self::assertSame(3, VersionModeloExperto::count(), 'La retirada operativa no borra versiones históricas.');
    }

    public function test_los_instrumentos_tecnicos_se_excluyen_sin_borrar_el_catalogo(): void
    {
        foreach ([['I_REAL', 'COGNITIVO', '1'], ['I_TEST', 'TEST-MEM-S01', 'G08-TEST-v1'], ['I_COH', 'COH30-COG-MEM', 'TEST-1']] as [$id, $codigo, $version]) {
            Instrumento::create(['cod_instrumento' => $id, 'codigo' => $codigo, 'nombre' => 'Instrumento de comprobación',
                'tipo' => 'COGNITIVO', 'version' => $version, 'estado' => 'ACTIVO']);
        }
        self::assertSame(['I_REAL'], FuentesValoracionOperativa::instrumentoInstitucional(Instrumento::query())->pluck('cod_instrumento')->all());
        self::assertSame(3, Instrumento::count());
    }
    public function test_solo_se_excluye_el_residente_identificado_con_ambas_marcas_del_lote(): void
    {
        $marca = 'COHORTE-INFERENCIA-30-V1 · DATOS SINTÉTICOS, USO EXCLUSIVO DE PRUEBAS.';
        $ids = [];
        foreach ([['COH30-S01', $marca], ['COH30-S02', null], ['DOC-ORDINARIO', $marca]] as [$documento, $observacion]) {
            $r = \App\Models\Residente::factory()->create(['numero_documento' => $documento]);
            \App\Models\Admision::create(['cod_residente' => $r->getKey(), 'cod_usuario_registro' => auth()->id(),
                'fecha_hora_admision' => now(), 'motivo_ingreso' => 'Fixture de regresión.', 'estado' => 'VIGENTE', 'observacion' => $observacion]);
            $ids[] = $r->getKey();
        }
        self::assertSame(array_slice($ids, 1), FuentesValoracionOperativa::residentes(\App\Models\Residente::query())->orderBy('cod_residente')->pluck('cod_residente')->all());
        self::assertSame(3, \App\Models\Residente::count(), 'No se elimina ni se altera historia.');
    }

}
