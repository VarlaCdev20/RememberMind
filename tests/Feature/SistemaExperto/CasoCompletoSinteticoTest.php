<?php

namespace Tests\Feature\SistemaExperto;

use App\Backend\Modulos\SistemaExperto\Conocimiento\InventarioExperto;
use App\Backend\Modulos\SistemaExperto\Servicios\LecturaFuenteEvidencia;
use App\Backend\Modulos\SistemaExperto\Servicios\LecturaResultadosExperto;
use App\Backend\Modulos\SistemaExperto\Servicios\LecturaTecnicaPruebas;
use App\Frontend\Livewire\Medico\Clinica\ResultadosExpertoResidente;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Support\SistemaExperto\CasoCompletoSintetico;
use Tests\Support\SistemaExperto\FixtureLecturaExperta;
use Tests\Support\SistemaExperto\PruebaConBaseDesechable;

class CasoCompletoSinteticoTest extends PruebaConBaseDesechable
{
    private array $caso;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('sistema_experto.lectura_tecnica_pruebas.habilitada', true);
        $this->caso = CasoCompletoSintetico::crear();
        $this->actingAs($this->caso['usuario']);
    }

    public function test_respuestas_reales_de_qa_generan_resultado_y_reglas_persistidas(): void
    {
        $this->assertDatabaseCount('evaluaciones_expertas', 1);
        $this->assertDatabaseCount('respuestas_instrumento', 3);
        $this->assertDatabaseHas('evaluaciones_expertas', ['cod_evaluacion_experta' => 'EVAL_COMPLETA', 'origen_activacion' => 'PRUEBA_TECNICA']);
        $this->assertDatabaseHas('resultados_criterio', ['cod_valor_semantico' => 'RES_D']);
        $this->assertDatabaseHas('evaluaciones_reglas', ['cod_regla_experta' => 'RULE_A', 'estado_regla' => 'CUMPLE']);
        $this->assertDatabaseHas('evaluaciones_reglas', ['cod_regla_experta' => 'RULE_B', 'estado_regla' => 'NO_CUMPLE']);
        $datos = $this->leer();
        $this->assertTrue($datos['prueba_tecnica']);
        $this->assertSame([], $datos['detalle']['integridad']);
        $this->assertSame('DIFICULTAD_EVIDENCIADA', $datos['detalle']['resultado']['codigo']);
        $this->assertStringContainsString('no determina el estado cognitivo', $datos['detalle']['interpretacion']);
        $this->assertFalse($datos['perfil']['COG-ATE']['autorizado']);
    }

    public function test_enfermeria_ve_fuente_completa_y_fundamento_sin_escribir(): void
    {
        $antes = $this->huella();
        $id = array_key_first($this->leer()['detalle']['evidencias']);
        Livewire::test(ResultadosExpertoResidente::class, ['residente' => $this->caso['residente']])
            ->assertSee('Inferencia técnica ejecutada')->assertSee('Dificultad evidenciada')
            ->assertSee('Verificación de la prueba técnica')->assertSee('Fecha de corte de QA')
            ->call('verEvidencia', $id)->assertSee('Recuerdo inmediato')->assertSee('NO_RECORDADO')->assertSee('COMPLETADO')
            ->assertSee('sin validación clínica')->assertDontSee('Confirmar concordancia');
        $this->assertSame($antes, $this->huella());
    }

    public function test_bandera_tecnica_no_publica_pruebas_en_entorno_produccion(): void
    {
        $this->app->instance('env', 'production');
        $this->assertFalse(LecturaTecnicaPruebas::habilitada());
        $datos = app(LecturaResultadosExperto::class)->consultar($this->caso['usuario'], $this->caso['residente'], null, 'COG-MEM');
        $this->assertNull($datos['evaluacion']);
        $this->assertNull($datos['informe_tecnico']);
    }

    public function test_bandera_tecnica_no_habilita_base_principal(): void
    {
        $c = DB::connection();
        $nombre = $c->getDatabaseName();
        try {
            $c->setDatabaseName('remembermind_dev');
            $this->assertFalse(LecturaTecnicaPruebas::habilitada());
        } finally {
            $c->setDatabaseName($nombre);
        }
    }

    public function test_informe_de_otra_evaluacion_o_residente_no_se_publica(): void
    {
        foreach (['evaluacion', 'residente', 'version'] as $campo) {
            $informe = $this->caso['informe'];
            $informe[$campo] = 'OTRO_REGISTRO';
            config()->set('sistema_experto.lectura_tecnica_pruebas.informe', $informe);
            $this->assertNull($this->leer()['informe_tecnico']);
        }
    }

    public function test_fuente_sigue_exigiendo_permiso_y_residente_asignado(): void
    {
        $id = array_key_first($this->leer()['detalle']['evidencias']);
        $this->caso['usuario']->revokePermissionTo('aplicaciones_instrumento.ver');
        $fuente = app(LecturaFuenteEvidencia::class)->consultar($this->caso['usuario'], $this->caso['residente'], 'EVAL_COMPLETA', $id);
        $this->assertSame(['estado' => 'Acceso al dato fuente restringido por permiso o contrato.'], $fuente);
        $otro = FixtureLecturaExperta::residenteAdicional('OTRO');
        $this->get(route('admin.enfermeria.pacientes.resultados-experto', $otro))->assertNotFound();
    }

    private function leer(): array
    {
        return app(LecturaResultadosExperto::class)->consultar($this->caso['usuario'], $this->caso['residente'], 'EVAL_COMPLETA', 'COG-MEM');
    }

    private function huella(): array
    {
        $huella = [];
        foreach (array_merge(InventarioExperto::TABLAS, ['aplicaciones_instrumento', 'respuestas_instrumento', 'activity_log', 'alertas']) as $tabla) {
            $huella[$tabla] = hash('sha256', json_encode(DB::table($tabla)->get()->all(), JSON_THROW_ON_ERROR));
        }

        return $huella;
    }
}
