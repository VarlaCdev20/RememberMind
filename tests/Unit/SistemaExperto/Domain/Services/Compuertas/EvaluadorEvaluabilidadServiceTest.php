<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Services\Compuertas;

use MotorExperto\dominio\orion\Services\Compuertas\EvaluadorEvaluabilidadService;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum as D;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EvaluabilidadEnum as V;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum as H;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\SubcomponenteCognitivoEnum as S;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: Evaluabilidad técnica separada de resultados cognitivos.
 *
 * CAPA: Domain (verificación unitaria).
 *
 * SUBSISTEMA: Compuertas y semántica.
 *
 * RESPONSABILIDAD:
 * Verificar resultados técnicos trazables y cierres ante incertidumbre.
 *
 * ENTRADAS:
 * Perfiles, declaraciones y grafos completamente ficticios.
 *
 * SALIDAS:
 * Aserciones sobre niveles y vínculos explícitos, sin resultados clínicos.
 *
 * RESTRICCIONES:
 * No inicia Laravel ni consulta persistencia. No clasifica patrones Petersen.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R2/R3/R4/R6/R8/R10 y CTX-MEM-01.
 */
final class EvaluadorEvaluabilidadServiceTest extends TestCase
{
    /**
     * Integra técnicamente cada dominio con una referencia y contexto ficticios.
     *
     * La basal ausente sigue null y no impide el nivel 2; no se emite A/P.
     *
     * @param D $dominio Eje cognitivo de prueba.
     * @param string $codigo Código oficial esperado del nivel 2.
     * @return void
     *
     * No se esperan excepciones para los cinco escenarios válidos.
     */
    #[DataProvider('dominios')]
    public function test_cinco_dominios_integran_sin_exigir_basal(D $dominio, string $codigo): void
    {
        $e = F::evidencia('SIM-EV-'.$dominio->value, $dominio, H::ALTERACION_OBJETIVA_SIMULADA);
        $s = EscenarioCompuertas::preparar([$e], EscenarioCompuertas::contextoCompleto(), [$e->hecho->id => true], dominio: $dominio);
        $r = $this->evaluar($s, $dominio);
        self::assertSame(V::EVALUACION_INTEGRADA, $r['estado']);
        self::assertSame($codigo, $r['codigo']);
        self::assertTrue($r['habilitaResultado']);
        self::assertSame([$e], $r['soportesIntegracion']);
        self::assertNull($r['contexto']['declaraciones']['basal_disponible']['valor']);
        self::assertArrayNotHasKey('estadoDominio', $r);
        self::assertSame($s['memoria'], $r['memoria']);
    }

    /**
     * Proporciona prefijos documentales sin derivarlos del código implementado.
     *
     * @return iterable<string, array{D, string}> Cinco códigos explícitos.
     *
     * No recibe parámetros ni lanza excepciones.
     */
    public static function dominios(): iterable
    {
        yield 'memoria' => [D::MEMORIA, 'EV-CM-2'];
        yield 'atención' => [D::ATENCION, 'EV-CA-2'];
        yield 'ejecutiva' => [D::FUNCION_EJECUTIVA, 'EV-CE-2'];
        yield 'lenguaje' => [D::LENGUAJE, 'EV-CL-2'];
        yield 'visuoespacial' => [D::VISUOESPACIAL, 'EV-CV-2'];
    }

    /**
     * Conserva nivel 0 aunque existan declaraciones contextuales completas.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_sin_evidencia_no_se_habilita_resultado(): void
    {
        $s = EscenarioCompuertas::preparar(contexto: EscenarioCompuertas::contextoCompleto());
        $r = $this->evaluar($s);
        self::assertSame(V::NO_EVALUABLE, $r['estado']);
        self::assertFalse($r['habilitaResultado']);
        self::assertSame([], $r['soportesIntegracion']);
    }
    /**
     * Impide cerrar un dominio con un hallazgo descriptivo.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_observacion_aislada_solo_es_senal(): void
    {
        $e = F::evidencia();
        $s = EscenarioCompuertas::preparar([$e], EscenarioCompuertas::contextoCompleto(), [$e->hecho->id => true]);
        $r = $this->evaluar($s);
        self::assertSame(V::PRE_EVALUACION_SENAL, $r['estado']);
        self::assertSame('EV-CM-1', $r['codigo']);
        self::assertFalse($r['habilitaResultado']);
    }
    /**
     * Impide integrar el dominio desde una referencia limitada a un subcomponente.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_referencia_de_tarea_no_equivale_a_dominio_completo(): void
    {
        $e = F::evidencia(hallazgo: H::ALTERACION_OBJETIVA_SIMULADA, subcomponente: S::MEM_RECONOCIMIENTO);
        $s = EscenarioCompuertas::preparar([$e], EscenarioCompuertas::contextoCompleto(), [$e->hecho->id => true]);
        self::assertSame(V::PRE_EVALUACION_SENAL, $this->evaluar($s)['estado']);
    }
    /**
     * Conserva señal cuando una referencia completa carece de revisión contextual.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_contexto_incompleto_no_habilita_integracion(): void
    {
        $e = F::evidencia(hallazgo: H::ALTERACION_OBJETIVA_SIMULADA);
        $s = EscenarioCompuertas::preparar([$e], procedencias: [$e->hecho->id => true]);
        $r = $this->evaluar($s);
        self::assertSame(V::PRE_EVALUACION_SENAL, $r['estado']);
        self::assertContains('invalidacion_necesaria', $r['faltantes']);
        self::assertNull($r['contexto']['invalidacion']);
    }
    /**
     * Rechaza nivel 2 pese a que los demás campos sean conocidos.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_componente_no_validado_impide_integracion(): void
    {
        $e = F::evidencia(hallazgo: H::ALTERACION_OBJETIVA_SIMULADA);
        $s = EscenarioCompuertas::preparar([$e], EscenarioCompuertas::contextoCompleto(['componente_validado' => false]), [$e->hecho->id => true]);
        $r = $this->evaluar($s);
        self::assertSame(V::PRE_EVALUACION_SENAL, $r['estado']);
        self::assertContains('componente_validado', $r['faltantes']);
    }
    /**
     * Excluye una referencia aunque su contexto esté declarado completo.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_procedencia_no_verificada_impide_cualquier_integracion(): void
    {
        $e = F::evidencia(hallazgo: H::ALTERACION_OBJETIVA_SIMULADA);
        $s = EscenarioCompuertas::preparar([$e], EscenarioCompuertas::contextoCompleto());
        $r = $this->evaluar($s);
        self::assertSame(V::NO_EVALUABLE, $r['estado']);
        self::assertContains('PROCEDENCIA_DESCONOCIDA', $r['motivos']);
    }
    /**
     * Impide cerrar o mantener como señal una referencia puramente instrumental invalidada.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_invalidacion_sin_observacion_no_conserva_senal_instrumental(): void
    {
        $e = F::evidencia(hallazgo: H::ALTERACION_OBJETIVA_SIMULADA);
        $s = EscenarioCompuertas::preparar([$e], EscenarioCompuertas::contextoCompleto(['invalidacion_necesaria' => true]), [$e->hecho->id => true]);
        $r = $this->evaluar($s);
        self::assertSame(V::NO_EVALUABLE, $r['estado']);
        self::assertSame([], $r['soportesIntegracion']);
        self::assertContains('CONTEXTO_INVALIDANTE', $r['motivos']);
    }
    /**
     * Conserva una señal observacional admisible aunque el contexto impida integración.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_invalidacion_conserva_observacion_sin_integrar(): void
    {
        $e = F::evidencia();
        $s = EscenarioCompuertas::preparar([$e], EscenarioCompuertas::contextoCompleto(['invalidacion_necesaria' => true]), [$e->hecho->id => true]);
        $r = $this->evaluar($s);
        self::assertSame(V::PRE_EVALUACION_SENAL, $r['estado']);
        self::assertFalse($r['habilitaResultado']);
    }
    /**
     * Conserva el conflicto entre A/P ficticios sin adjudicar un resultado.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_referencias_completas_opuestas_impiden_integracion(): void
    {
        $a = F::evidencia('SIM-A', hallazgo: H::ALTERACION_OBJETIVA_SIMULADA);
        $p = F::evidencia('SIM-P', hallazgo: H::RENDIMIENTO_ESPERADO_SIMULADO);
        $s = EscenarioCompuertas::preparar([$a, $p], EscenarioCompuertas::contextoCompleto(),
            [$a->hecho->id => true, $p->hecho->id => true]);
        $r = $this->evaluar($s);
        self::assertSame(V::PRE_EVALUACION_SENAL, $r['estado']);
        self::assertContains('REFERENCIAS_COMPLETAS_CONTRADICTORIAS', $r['motivos']);
    }
    /**
     * Impide seleccionar solo la entrada favorable del dominio.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_no_interpretable_no_se_oculta_tras_referencia_positiva(): void
    {
        $a = F::evidencia('SIM-A', hallazgo: H::ALTERACION_OBJETIVA_SIMULADA);
        $i = F::evidencia('SIM-I', hallazgo: H::NO_INTERPRETABLE);
        $s = EscenarioCompuertas::preparar([$a, $i], EscenarioCompuertas::contextoCompleto(),
            [$a->hecho->id => true, $i->hecho->id => true]);
        $r = $this->evaluar($s);
        self::assertFalse($r['habilitaResultado']);
        self::assertContains('EVIDENCIA_NO_INTERPRETABLE', $r['motivos']);
    }
    /**
     * Conserva basal=false sin alterar el nivel integrado declarado por los requisitos pertinentes.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_basal_negada_no_es_una_senal_ni_un_bloqueo_automatico(): void
    {
        $e = F::evidencia(hallazgo: H::RENDIMIENTO_ESPERADO_SIMULADO);
        $s = EscenarioCompuertas::preparar([$e], EscenarioCompuertas::contextoCompleto(['basal_disponible' => false]), [$e->hecho->id => true]);
        $r = $this->evaluar($s);
        self::assertSame(V::EVALUACION_INTEGRADA, $r['estado']);
        self::assertFalse($r['contexto']['declaraciones']['basal_disponible']['valor']);
        self::assertNotContains('basal_disponible', $r['faltantes']);
    }
    /**
     * Conserva advertencia y nivel 2 sin transformar el resultado cognitivo.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_advertencia_no_invalidante_es_ortogonal_al_nivel(): void
    {
        $e = F::evidencia(hallazgo: H::RENDIMIENTO_ESPERADO_SIMULADO);
        $s = EscenarioCompuertas::preparar([$e], EscenarioCompuertas::contextoCompleto(['limitacion_no_invalidante' => true]), [$e->hecho->id => true]);
        $r = $this->evaluar($s);
        self::assertSame(V::EVALUACION_INTEGRADA, $r['estado']);
        self::assertSame('MOD-CM-1', $r['modificador']);
    }
    /**
     * Conserva las mismas fuentes y decisiones con otro orden de selección.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_informe_es_determinista_al_reordenar_entradas(): void
    {
        $a = F::evidencia('SIM-A', hallazgo: H::ALTERACION_OBJETIVA_SIMULADA);
        $b = F::evidencia('SIM-B');
        $s = EscenarioCompuertas::preparar([$a, $b], EscenarioCompuertas::contextoCompleto(),
            [$a->hecho->id => true, $b->hecho->id => true]);
        $r = $this->evaluar($s);
        $s['candidatas'] = array_reverse($s['candidatas']);
        $s['procedencias'] = array_reverse($s['procedencias']);
        $s['contexto'] = array_reverse($s['contexto']);
        self::assertSame($r, $this->evaluar($s));
    }

    /**
     * Ejecuta los colaboradores reales del servicio sobre un escenario artificial.
     *
     * @param array{memoria: \MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo, candidatas: array, contexto: array, procedencias: array} $escenario Entradas explícitas.
     * @param D $dominio Eje solicitado.
     * @return array Informe puro de evaluabilidad.
     * @throws \DomainException Si el escenario viola el contrato.
     */
    private function evaluar(array $escenario, D $dominio = D::MEMORIA): array
    {
        return (new EvaluadorEvaluabilidadService())->evaluar($escenario['memoria'], $dominio,
            $escenario['candidatas'], $escenario['procedencias'], $escenario['contexto']);
    }

}
