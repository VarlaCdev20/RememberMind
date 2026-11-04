<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\Residente;
use App\Models\User;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\ConsultarGrafoExperto;
use App\Modulos\ApoyoDecision\Aplicacion\Consultas\LecturaResultadosExperto;
use App\Modulos\ApoyoDecision\Aplicacion\DTOs\GrafoInferenciaHistorica;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\ResultadosExpertoResidente;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Tests\Support\EsquemaSinTransaccion;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use MotorExperto\conservacion\IdentificadoresHistoricos;
use Tests\Support\InformeCognitivoFixture as F;
use Tests\TestCase;

class ExploradorExpertoTest extends TestCase
{
    use EsquemaSinTransaccion;

    private User $actor;

    private Personal $personal;

    private Residente $residente;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->actor = User::factory()->create(['estado' => 'ACTIVO']);
        $this->actor->assignRole('SUPERADMINISTRADOR');
        $this->personal = Personal::create(['cod_usuario' => $this->actor->getKey(), 'nombres' => 'Persona',
            'apellido_paterno' => 'Sintetica', 'numero_documento' => 'EXPERTO-UI-TEST', 'profesion' => 'MEDICINA', 'estado' => 'ACTIVO']);
        $this->residente = Residente::factory()->create();
        $this->actingAs($this->actor);
        F::conocimiento($this->actor);
    }

    private function lectura(string $id): array
    {
        return app(LecturaResultadosExperto::class)->consultar($this->actor->fresh(), $this->residente, $id, 'COG-MEM');
    }

    public function test_los_nombres_publicos_no_modifican_versiones_o_definiciones_conservadas(): void
    {
        DB::table('versiones_modelo_experto')->where('cod_version_modelo', 'VER_TEST')
            ->update(['codigo_version' => IdentificadoresHistoricos::PERFIL_COGNITIVO]);
        $nombreAnterior = implode('.', str_split(explode('-', IdentificadoresHistoricos::PERFIL_COGNITIVO, 2)[0])).'.';
        $definicion = 'Concepto de la red documental '.$nombreAnterior.'; su pertenencia no acredita un hallazgo.';
        DB::table('nodos_semanticos')->where('cod_nodo_semantico', 'MEM')->update(['definicion' => $definicion]);
        F::evaluacion($this->residente, $this->personal, 'ETIQUETAS');
        $lectura = $this->lectura('E_ETIQUETAS');
        self::assertSame(IdentificadoresHistoricos::PERFIL_COGNITIVO, $lectura['evaluacion']['version']);
        $grafos = app(ConsultarGrafoExperto::class)->consultar($this->actor, $this->residente, $lectura, 'COG-MEM');
        foreach (array_merge($grafos['semantico']['nodos'], $grafos['inferencia']['nodos']) as $nodo) {
            self::assertSame('Perfil cognitivo inicial', $nodo['version']);
        }
        $memoria = collect($grafos['semantico']['nodos'])->firstWhere('id', 'MEM');
        self::assertSame('Concepto de la base cognitiva; su pertenencia no acredita un hallazgo.', $memoria['datos']['Definición']);
        self::assertSame($definicion, DB::table('nodos_semanticos')->where('cod_nodo_semantico', 'MEM')->value('definicion'));
        self::assertSame(IdentificadoresHistoricos::PERFIL_COGNITIVO,
            DB::table('versiones_modelo_experto')->where('cod_version_modelo', 'VER_TEST')->value('codigo_version'));
        self::assertSame('Perfil cognitivo inicial', $grafos['semantico']['version']);
        self::assertSame('Perfil cognitivo inicial', $grafos['inferencia']['version']);
        $pantalla = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->assertSee('Perfil cognitivo inicial');
        $textoVisible = html_entity_decode(strip_tags($pantalla->html()), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        self::assertStringNotContainsString($nombreAnterior, $textoVisible);
        self::assertStringNotContainsString(IdentificadoresHistoricos::PERFIL_COGNITIVO, $textoVisible);
    }

    public function test_grafo_es_determinista_preserva_rutas_y_no_fabrica_datos_originales(): void
    {
        F::evaluacion($this->residente, $this->personal, 'UNO', valor: 'RES_M');
        $lectura = $this->lectura('E_UNO');
        $antes = DB::table('evaluaciones_condiciones_regla')->get()->toJson();
        $servicio = new GrafoInferenciaHistorica;
        $grafo = $servicio->presentar($lectura, true);
        $this->assertSame($grafo, $servicio->presentar($lectura, true));
        $this->assertCount(2, $grafo['reglas']);
        $this->assertSame(['Fuente', 'Mapeo', 'Evidencia', 'Condición', 'Regla', 'Consecuencia', 'Resultado'],
            array_values(array_unique(array_column($grafo['nodos'], 'tipo'))));
        foreach ($grafo['aristas'] as $arista) {
            $this->assertContains($arista['origen'], array_column($grafo['nodos'], 'id'));
            $this->assertContains($arista['destino'], array_column($grafo['nodos'], 'id'));
        }
        $this->assertSame('No conservado en esta evaluación', $grafo['nodos'][0]['datos']['Valor original']);
        $this->assertSame($antes, DB::table('evaluaciones_condiciones_regla')->get()->toJson());
    }

    public function test_red_semantica_incluye_componentes_y_variables_entrantes_sin_invertir_aristas(): void
    {
        F::evaluacion($this->residente, $this->personal, 'RED');
        $grafos = app(ConsultarGrafoExperto::class)->consultar($this->actor, $this->residente, $this->lectura('E_RED'), 'COG-MEM');
        $semantico = $grafos['semantico'];
        $this->assertContains('MEM', array_column($semantico['nodos'], 'id'));
        $this->assertContains('SUB', array_column($semantico['nodos'], 'id'));
        $this->assertContains(['origen' => 'SUB', 'destino' => 'MEM', 'relacion' => 'SUBCOMPONENTE_DE'], $semantico['aristas']);
        $this->assertSame(['INFORME-LECTURA-01'], array_values(array_unique(array_column($semantico['nodos'], 'version'))));
        if ($directorio = getenv('RM_EXPERTO_QA_DIR')) {
            if (! is_dir($directorio)) {
                mkdir($directorio, 0700, true);
            }
            $html = $this->get(route('admin.medico.residente.resultados-experto', $this->residente))->assertOk()->getContent();
            file_put_contents($directorio.'/decision-historico.html', $html);
        }
    }

    public function test_regla_no_cumplida_no_conecta_consecuencia_al_resultado(): void
    {
        F::evaluacion($this->residente, $this->personal, 'NO');
        DB::table('evidencias_soporte_condicion')->delete();
        DB::table('evaluaciones_condiciones_regla')->update(['estado_condicion' => 'NO_CUMPLE']);
        DB::table('evaluaciones_reglas')->update(['estado_regla' => 'NO_CUMPLE']);
        $g = (new GrafoInferenciaHistorica)->presentar($this->lectura('E_NO'), true);
        $this->assertSame('NO_CUMPLE', $g['reglas'][0]['estado']);
        $this->assertEmpty(array_filter($g['aristas'], fn ($a) => $a['destino'] === 'resultado'));
        $this->assertNotEmpty(array_filter($g['aristas'], fn ($a) => $a['relacion'] === 'declara; no ejecutada'));
    }

    public function test_comparacion_rechaza_evaluacion_ajena_y_fechas_iguales_sin_cambiar_historia(): void
    {
        F::evaluacion($this->residente, $this->personal, 'INICIAL');
        F::evaluacion($this->residente, $this->personal, 'RECIENTE');
        $c = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('abrirEvolucion')->call('cambiarComparacion', 'inicial', 'E_INICIAL')
            ->call('cambiarComparacion', 'reciente', 'E_RECIENTE');
        $c->call('cambiarComparacion', 'reciente', 'E_INICIAL')->assertHasErrors('comparacion');
        $otro = Residente::factory()->create();
        F::evaluacion($otro, $this->personal, 'AJENA');
        $c->call('cambiarComparacion', 'inicial', 'E_AJENA')->assertNotFound();
        $this->assertDatabaseCount('evaluaciones_expertas', 3);
    }

    public function test_dato_instrumental_sin_permiso_no_se_serializa_en_grafo_o_tabla(): void
    {
        F::evaluacion($this->residente, $this->personal, 'RESERVADA');
        $grafo = (new GrafoInferenciaHistorica)->presentar($this->lectura('E_RESERVADA'), false);
        $json = json_encode($grafo);
        $this->assertStringNotContainsString('APP_RESERVADA_A', $json);
        $this->assertStringNotContainsString('VAR-MEM-INST-01', $json);
        $this->assertStringContainsString('restringid', $json);
        $this->assertSame([], $grafo['reglas'][0]['filas'][0]['datos']);
    }

    public function test_detalle_grafo_no_acepta_nodo_de_otra_evaluacion_y_reautoriza(): void
    {
        F::evaluacion($this->residente, $this->personal, 'A');
        F::evaluacion($this->residente, $this->personal, 'B');
        $c = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('seleccionarEvaluacion', 'E_A')
            ->call('abrirSignificado')->assertSet('significadoAbierto', true)
            ->call('abrirConsulta', 'registro')->assertSet('consultaAbierta', true)->assertSet('significadoAbierto', false)
            ->call('cerrarConsulta')->call('abrirNodoGrafo', 'inferencia', 'evidencia:EV_A_A')
            ->assertSet('nodoGrafo', 'evidencia:EV_A_A')->assertSet('consultaAbierta', true)
            ->call('seleccionarEvaluacion', 'E_B')->assertSet('consultaAbierta', false);
        $c->call('abrirNodoGrafo', 'inferencia', 'evidencia:EV_A_A')->assertNotFound();
        $this->assertDatabaseCount('evaluaciones_expertas', 2);
    }

    public function test_evidencia_del_resultado_abre_su_area_sin_confundirla_con_el_area_explorada(): void
    {
        F::evaluacion($this->residente, $this->personal, 'CONTEXTO');
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])
            ->call('seleccionarEvaluacion', 'E_CONTEXTO')->call('seleccionarCriterio', 'COG-ATE')
            ->call('abrirRegistro', 'EV_CONTEXTO_A', 'COG-MEM')
            ->assertSet('criterio', 'COG-MEM')->assertSet('evaluacion', 'E_CONTEXTO')
            ->assertSet('evidencia', 'EV_CONTEXTO_A')->assertSet('consultaAbierta', true);
        $this->assertDatabaseCount('evaluaciones_expertas', 1);
    }

    public function test_consulta_directa_del_grafo_exige_autorizacion_tecnica_existente(): void
    {
        $this->actor->syncRoles(['MEDICO GENERAL/GERIATRA']);
        $this->expectException(AuthorizationException::class);
        app(ConsultarGrafoExperto::class)->consultar($this->actor->fresh(), $this->residente, ['evaluacion' => null], 'COG-MEM');
    }

    public function test_ventanas_no_siguen_abiertas_tras_baja_de_cuenta(): void
    {
        $c = Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->residente])->call('abrirSignificado');
        $this->actor->update(['estado' => 'INACTIVO']);
        $c->call('abrirConsulta', 'registro')->assertNotFound();
        $this->assertDatabaseCount('evaluaciones_expertas', 0);
    }
}
