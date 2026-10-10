<?php

namespace Tests\Unit\SistemaExperto;

use App\Backend\Modulos\SistemaExperto\Adaptadores\AdaptadorFuentesCOGMEM;
use App\Backend\Modulos\SistemaExperto\Adaptadores\InspectorComponenteInstrumental;
use App\Backend\Modulos\SistemaExperto\Conocimiento\EjemploTecnicoCOGMEM as F;
use App\Backend\Modulos\SistemaExperto\Conocimiento\MapeadorSemantico;
use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteConocimiento;
use App\Backend\Modulos\SistemaExperto\DTO\ContextoEjecucion;
use App\Backend\Modulos\SistemaExperto\MemoriaTrabajo\MemoriaTrabajo;
use App\Backend\Modulos\SistemaExperto\Servicios\MotorExperto;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

class RegresionesContratoExpertoTest extends TestCase
{
    public function test_el_corte_temporal_no_puede_cambiar_dentro_de_la_evaluacion(): void
    {
        $otroCorte = new ContextoEjecucion('EVAL_TEST', 'RES_TEST', 'VER_TEST', new DateTimeImmutable('2026-10-09T12:00:00-04:00'));
        $e = (new MapeadorSemantico)->mapear(F::paquete(), $otroCorte, F::fuente(), 'MAP_INS');
        $this->expectException(DomainException::class);
        (new MemoriaTrabajo(F::contexto()))->incorporar($e);
    }

    public function test_nodo_subcomponente_no_sustituye_un_nodo_variable(): void
    {
        $t = F::tablas();
        $t['nodos_semanticos'][3]['tipo_nodo'] = 'SUBCOMPONENTE';
        $t['relaciones_semanticas'] = array_values(array_filter($t['relaciones_semanticas'], fn ($r) => $r['cod_nodo_origen'] !== 'INS'));
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('tipo VARIABLE');
        F::paquete($t);
    }

    public function test_no_toma_un_contrato_de_participacion_de_otro_tipo(): void
    {
        $p = F::paquete();
        $contratos = $p->contratosRelaciones;
        $contratos[1]['habilita_participacion'] = false;
        $contratos[] = ['tipo_relacion' => 'TEST_APORTA', 'tipo_origen' => 'BLOQUE', 'tipo_destino' => 'CRITERIO', 'modalidades' => [null], 'habilita_participacion' => true];
        $p = new PaqueteConocimiento('VER_TEST', F::tablas(), $contratos, true, 'RESULTADO', F::paquete()->contratosExtraccion);
        $this->assertFalse($p->variableParticipa('VAR_INS', 'MEM'));
    }

    public function test_un_mismo_episodio_no_fabrica_hallazgos_mixtos(): void
    {
        $p = F::paquete();
        $m = new MemoriaTrabajo(F::contexto());
        $a = new AdaptadorFuentesCOGMEM;
        $e1 = $a->adaptar($p, $m, F::fuente('literal_A', registro: 'APP_A'), 'MAP_INS');
        $e2 = $a->adaptar($p, $m, F::fuente('literal_B', registro: 'APP_B'), 'MAP_INS');
        $m->vincular('MEM', $e1->id, 'INSTRUMENTAL');
        $m->vincular('MEM', $e2->id, 'INSTRUMENTAL');
        $m->relacionar($e1->id, $e2->id, 'MISMO_EPISODIO_CLINICO');
        $m->relacionar($e2->id, $e1->id, 'MISMO_EPISODIO_CLINICO');
        $this->assertCount(1, $m->relaciones());
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('no independientes');
        (new MotorExperto)->evaluarCOGMEM($p, $m, 'MEM', F::contextoClinico());
    }

    public function test_consecuencia_de_regla_ajena_al_tipo_resultado_no_se_ejecuta(): void
    {
        $t = F::tablas();
        foreach ($t['reglas_expertas'] as &$r) {
            $r['tipo_regla'] = 'ADMISIBILIDAD';
        } unset($r);
        $p = F::paquete($t);
        $m = new MemoriaTrabajo(F::contexto());
        $e = (new AdaptadorFuentesCOGMEM)->adaptar($p, $m, F::fuente(), 'MAP_INS');
        $m->vincular('MEM', $e->id, 'INSTRUMENTAL');
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('sin soporte');
        (new MotorExperto)->evaluarCOGMEM($p, $m, 'MEM', F::contextoClinico());
    }

    public function test_criterio_inactivo_no_emite_resultado(): void
    {
        $t = F::tablas();
        $t['nodos_semanticos'][0]['estado'] = 'INACTIVO';
        $p = F::paquete($t);
        $m = new MemoriaTrabajo(F::contexto());
        $this->expectException(DomainException::class);
        (new MotorExperto)->evaluarCOGMEM($p, $m, 'MEM', F::contextoClinico());
    }

    public function test_vinculo_criterio_dominio_inactivo_no_emite_resultado(): void
    {
        $t = F::tablas();
        $t['criterios_dominios_resultado'][0]['estado'] = 'INACTIVO';
        $this->expectException(DomainException::class);
        (new MotorExperto)->evaluarCOGMEM(F::paquete($t), new MemoriaTrabajo(F::contexto()), 'MEM', F::contextoClinico());
    }

    private function instrumental(): array
    {
        return [
            ['cod_aplicacion' => 'APP_TEST', 'cod_instrumento' => 'INST_TEST', 'estado' => 'COMPLETA', 'puntaje_total' => 30, 'clasificacion' => 'NORMAL'],
            ['cod_instrumento' => 'INST_TEST', 'version' => 'TECH_1'],
            ['Q1' => ['cod_instrumento' => 'INST_TEST', 'tipo_respuesta' => 'TEXTO'], 'Q2' => ['cod_instrumento' => 'INST_TEST', 'tipo_respuesta' => 'TEXTO']], [],
            [['cod_respuesta' => 'R1', 'cod_aplicacion' => 'APP_TEST', 'cod_pregunta' => 'Q1', 'valor_texto' => 'A'],
                ['cod_respuesta' => 'R2', 'cod_aplicacion' => 'APP_TEST', 'cod_pregunta' => 'Q2', 'valor_texto' => 'B']],
            ['solo_pruebas_tecnicas' => true, 'instrumento' => 'INST_TEST', 'version_instrumento' => 'TECH_1', 'componente' => 'COMP_TEST', 'metodo' => 'TEST_EXACTO',
                'preguntas_requeridas' => ['Q1' => ['campo' => 'valor_texto', 'tipo' => 'string', 'tipo_respuesta' => 'TEXTO', 'valores_permitidos' => ['A']],
                    'Q2' => ['campo' => 'valor_texto', 'tipo' => 'string', 'tipo_respuesta' => 'TEXTO', 'valores_permitidos' => ['B']]],
                'patrones' => [['respuestas' => ['Q1' => 'A', 'Q2' => 'B'], 'valor_fuente' => 'literal_A']]],
        ];
    }

    public function test_instrumento_completo_tecnico_verifica_preguntas_y_metodo_exacto(): void
    {
        $r = (new InspectorComponenteInstrumental)->inspeccionar(...$this->instrumental());
        $this->assertTrue($r['verificado']);
        $this->assertSame('literal_A', $r['valor']);
        $this->assertSame(['Q1', 'Q2'], $r['procedencia']['preguntas']);
    }

    public function test_orden_de_respuestas_no_cambia_el_valor_ni_la_procedencia(): void
    {
        $i = $this->instrumental();
        $inspector = new InspectorComponenteInstrumental;
        $original = $inspector->inspeccionar(...$i);
        $i[4] = array_reverse($i[4]);
        $this->assertSame($original, $inspector->inspeccionar(...$i));
    }

    public function test_completa_con_una_respuesta_no_demuestra_componente_completo(): void
    {
        $i = $this->instrumental();
        array_pop($i[4]);
        $r = (new InspectorComponenteInstrumental)->inspeccionar(...$i);
        $this->assertFalse($r['verificado']);
        $this->assertNull($r['valor']);
        $this->assertSame(['COMPONENTE_INCOMPLETO'], $r['motivos']);
    }

    public function test_version_null_no_es_version_instrumental_reconocida(): void
    {
        $i = $this->instrumental();
        $i[1]['version'] = null;
        $i[5]['version_instrumento'] = null;
        $this->assertFalse((new InspectorComponenteInstrumental)->inspeccionar(...$i)['verificado']);
    }

    public function test_pregunta_de_otro_instrumento_se_rechaza(): void
    {
        $i = $this->instrumental();
        $i[2]['Q1']['cod_instrumento'] = 'INST_AJENO';
        $this->expectException(DomainException::class);
        (new InspectorComponenteInstrumental)->inspeccionar(...$i);
    }

    public function test_opcion_de_otra_pregunta_se_rechaza(): void
    {
        $i = $this->instrumental();
        $i[4][0]['cod_opcion'] = 'OP_AJENA';
        $i[3]['OP_AJENA'] = ['cod_pregunta' => 'Q2'];
        $this->expectException(DomainException::class);
        (new InspectorComponenteInstrumental)->inspeccionar(...$i);
    }

    public function test_puntaje_y_clasificacion_no_sustituyen_paquete_instrumental(): void
    {
        $i = $this->instrumental();
        $i[5]['solo_pruebas_tecnicas'] = false;
        $this->expectException(DomainException::class);
        (new InspectorComponenteInstrumental)->inspeccionar(...$i);
    }
}
