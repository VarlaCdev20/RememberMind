<?php

namespace Tests\Unit\SistemaExperto;

use App\Backend\Modulos\SistemaExperto\Adaptadores\AdaptadorFuentesCOGMEM;
use App\Backend\Modulos\SistemaExperto\Conocimiento\EjemploTecnicoCOGMEM as F;
use App\Backend\Modulos\SistemaExperto\Conocimiento\MapeadorSemantico;
use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteConocimiento;
use App\Backend\Modulos\SistemaExperto\DTO\ContextoEjecucion;
use App\Backend\Modulos\SistemaExperto\Inferencia\EvaluadorReglas;
use App\Backend\Modulos\SistemaExperto\MemoriaTrabajo\MemoriaTrabajo;
use App\Backend\Modulos\SistemaExperto\Servicios\MotorExperto;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NucleoExpertoTest extends TestCase
{
    private function evaluar(array $valores, array $contexto = [], ?PaqueteConocimiento $p = null): array
    {
        $p ??= F::paquete();
        $m = new MemoriaTrabajo(F::contexto());
        foreach ($valores as $i => $valor) {
            $e = (new AdaptadorFuentesCOGMEM)->adaptar($p, $m, F::fuente($valor, registro: 'APP_'.$i), 'MAP_INS');
            $m->vincular('MEM', $e->id, 'INSTRUMENTAL');
        }

        return (new MotorExperto)->evaluarCOGMEM($p, $m, 'MEM', [...F::contextoClinico(), ...$contexto]);
    }

    public static function variantes(): array
    {
        return [[null, 'NO_DISPONIBLE'], [' literal_A', 'VALOR_NO_RECONOCIDO'], ['LITERAL_A', 'VALOR_NO_RECONOCIDO'],
            ['literal_A ', 'VALOR_NO_RECONOCIDO'], ['literal', 'VALOR_NO_RECONOCIDO'], ['normal', 'VALOR_NO_RECONOCIDO']];
    }

    #[DataProvider('variantes')]
    public function test_null_y_variaciones_no_se_convierten_en_hechos(mixed $valor, string $estado): void
    {
        $e = (new MapeadorSemantico)->mapear(F::paquete(), F::contexto(), F::fuente($valor), 'MAP_INS');
        $this->assertSame($valor, $e->fuente->valorOriginal);
        $this->assertSame($estado, $e->representacion);
        $this->assertNull($e->valor);
        $this->assertFalse($e->utilizable());
    }

    public function test_mapeo_exacto_generico_conserva_identidad_y_procedencia(): void
    {
        $e = (new MapeadorSemantico)->mapear(F::paquete(), F::contexto(), F::fuente(), 'MAP_INS');
        $this->assertTrue($e->utilizable());
        $this->assertSame('VAR_INS', $e->variable);
        $this->assertSame('VAL_A', $e->valor);
        $this->assertSame('MV_INS_A', $e->mapeoValor);
    }

    public function test_relectura_y_participacion_en_varios_criterios_no_duplican_evidencia(): void
    {
        $p = F::paquete();
        $m = new MemoriaTrabajo(F::contexto());
        $a = new AdaptadorFuentesCOGMEM;
        $e = $a->adaptar($p, $m, F::fuente(), 'MAP_INS');
        $this->assertSame($e, $a->adaptar($p, $m, F::fuente(), 'MAP_INS'));
        $m->vincular('MEM', $e->id, 'INSTRUMENTAL');
        $m->vincular('ATE_TEST', $e->id, 'COMPARTIDA');
        $this->assertCount(1, $m->todas());
        $this->assertSame($m->paraCriterio('MEM')[0], $m->paraCriterio('ATE_TEST')[0]);
    }

    public function test_otro_residente_no_se_incorpora(): void
    {
        $f = F::fuente();
        $e = (new MapeadorSemantico)->mapear(F::paquete(), F::contexto(), $f, 'MAP_INS');
        $m = new MemoriaTrabajo(new ContextoEjecucion('EVAL_TEST', 'RES_AJENO', 'VER_TEST', F::contexto()->fechaCorte));
        $this->expectException(DomainException::class);
        $m->incorporar($e);
    }

    public function test_otra_evaluacion_no_se_incorpora(): void
    {
        $e = (new MapeadorSemantico)->mapear(F::paquete(), F::contexto(), F::fuente(), 'MAP_INS');
        $m = new MemoriaTrabajo(new ContextoEjecucion('EVAL_OTRA', 'RES_TEST', 'VER_TEST', F::contexto()->fechaCorte));
        $this->expectException(DomainException::class);
        $m->incorporar($e);
    }

    public function test_mapeo_inactivo_no_emite(): void
    {
        $t = F::tablas();
        $t['mapeos_variables_fuente'][1]['estado'] = 'INACTIVO';
        $e = (new MapeadorSemantico)->mapear(F::paquete($t), F::contexto(), F::fuente(), 'MAP_INS');
        $this->assertNull($e->valor);
        $this->assertSame('SIN_MAPEO_ACTIVO', $e->representacion);
    }

    public function test_dos_mapeos_exactos_activos_se_rechazan(): void
    {
        $t = F::tablas();
        $r = $t['mapeos_valores_fuente'][2];
        $r['cod_mapeo_valor_fuente'] = 'MV_DUP';
        $t['mapeos_valores_fuente'][] = $r;
        $this->expectException(DomainException::class);
        (new MapeadorSemantico)->mapear(F::paquete($t), F::contexto(), F::fuente(), 'MAP_INS');
    }

    public static function resultados(): array
    {
        return [[['literal_A'], 'DIFICULTAD_EVIDENCIADA'], [['literal_B'], 'SIN_DIFICULTAD_EVIDENCIADA'],
            [['literal_A', 'literal_B'], 'HALLAZGOS_MIXTOS']];
    }

    #[DataProvider('resultados')]
    public function test_resolvedor_d131_y_ruta_completa(array $valores, string $codigo): void
    {
        $r = $this->evaluar($valores);
        $this->assertSame('EV-CM-2', $r['evaluabilidad']['estado']);
        $this->assertSame($codigo, $r['resultado']['codigo']);
        $ruta = $r['traza']['rutas'][0];
        foreach (['evaluacion', 'residente', 'version', 'criterio', 'regla', 'condicion', 'evidencia', 'variable', 'valor', 'mapeo_variable', 'mapeo_valor', 'fuente', 'tabla', 'registro', 'campo'] as $campo) {
            $this->assertNotEmpty($ruta[$campo]);
        }
    }

    public function test_contexto_insuficiente_no_produce_resultado(): void
    {
        $r = $this->evaluar(['literal_A'], ['confusores_revisados' => false]);
        $this->assertSame('EV-CM-1', $r['evaluabilidad']['estado']);
        $this->assertNull($r['resultado']);
        $this->assertSame([], $r['traza']['reglas']);
    }

    public function test_sin_basal_y_limitacion_no_invalidante_conserva_ev2_y_modificador(): void
    {
        $r = $this->evaluar(['literal_A'], ['basal_disponible' => false, 'limitacion_no_invalidante' => true]);
        $this->assertSame('EV-CM-2', $r['evaluabilidad']['estado']);
        $this->assertSame('MOD-CM-1', $r['evaluabilidad']['modificador']);
    }

    public function test_invalidacion_del_unico_componente_no_deja_resultado(): void
    {
        $r = $this->evaluar(['literal_A'], ['invalidacion_necesaria' => true]);
        $this->assertSame('EV-CM-0', $r['evaluabilidad']['estado']);
        $this->assertNull($r['resultado']);
    }

    public function test_ev2_sin_regla_satisfecha_es_error_de_cobertura(): void
    {
        $t = F::tablas();
        $t['condiciones_regla_experta'][0]['cod_variable_experta'] = 'VAR_OBS';
        $t['condiciones_regla_experta'][1]['cod_variable_experta'] = 'VAR_OBS';
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('sin soporte');
        $this->evaluar(['literal_A'], p: F::paquete($t));
    }

    public function test_regla_and_no_cumple_si_falta_una_condicion_y_no_presume_contrario(): void
    {
        $t = F::tablas();
        $t['condiciones_regla_experta'][] = ['cod_condicion_regla' => 'COND_AND', 'cod_regla_experta' => 'RULE_A', 'cod_variable_experta' => 'VAR_OBS', 'cod_valor_semantico' => 'VAL_A'];
        $p = F::paquete($t);
        $m = new MemoriaTrabajo(F::contexto());
        $e = (new AdaptadorFuentesCOGMEM)->adaptar($p, $m, F::fuente(), 'MAP_INS');
        $m->vincular('MEM', $e->id, 'INSTRUMENTAL');
        $r = (new EvaluadorReglas)->evaluar($p, $m, 'MEM');
        $this->assertSame(['NO_CUMPLE', 'NO_CUMPLE'], array_column($r, 'estado'));
        $this->assertCount(2, $r[0]['condiciones']);
    }

    public function test_regla_activa_incompleta_se_rechaza(): void
    {
        $t = F::tablas();
        array_shift($t['condiciones_regla_experta']);
        $this->expectException(DomainException::class);
        F::paquete($t);
    }

    public function test_hallazgos_mixtos_no_puede_ser_consecuencia_de_una_regla(): void
    {
        $t = F::tablas();
        $t['consecuencias_regla_experta'][0]['cod_valor_semantico'] = 'RES_M';
        $this->expectException(DomainException::class);
        F::paquete($t);
    }

    public function test_no_habilita_conocimiento_clinico_ni_otro_criterio(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('no está autorizado');
        $this->evaluar(['literal_A'], p: F::paquete(soloPruebas: false));
    }

    public function test_determinismo_y_perfil_sin_agregacion(): void
    {
        $a = $this->evaluar(['literal_A', 'literal_B']);
        $this->assertSame($a, $this->evaluar(['literal_A', 'literal_B']));
        $perfil = (new MotorExperto)->perfil([$a]);
        $this->assertSame(['MEM'], array_keys($perfil));
        $this->assertArrayNotHasKey('puntaje_global', $perfil);
    }

    public function test_red_recupera_aristas_y_procedencia_sin_crear_inversas(): void
    {
        $red = F::paquete()->red;
        $this->assertCount(5, $red->entrantes('MEM'));
        $this->assertSame([], $red->salientes('MEM'));
        $this->assertSame('SUB', $red->conexiones('MEM', 'SUBCOMPONENTE_DE')[0]['nodo']['cod_nodo_semantico']);
        $this->assertSame('USR_TEST', $red->entrantes('MEM')[0]['cod_usuario_creacion']);
    }

    public function test_red_rechaza_otra_version_y_predicado_sin_contrato(): void
    {
        $t = F::tablas();
        $t['relaciones_semanticas'][0]['tipo_relacion'] = 'INVENTADO';
        $this->expectException(DomainException::class);
        F::paquete($t);
    }

    public function test_nodo_de_otra_version_se_rechaza(): void
    {
        $t = F::tablas();
        $t['nodos_semanticos'][0]['cod_version_modelo'] = 'VER_AJENA';
        $this->expectException(DomainException::class);
        F::paquete($t);
    }
}
