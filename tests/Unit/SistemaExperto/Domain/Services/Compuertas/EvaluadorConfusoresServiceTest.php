<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Services\Compuertas;

use App\SistemaExperto\Domain\Services\Compuertas\EvaluadorConfusoresService;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum as D;
use App\SistemaExperto\Domain\ValueObjects\Dcl\EstadoCriterioDclEnum as C;
use DomainException;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: EvaluadorConfusoresServiceTest.
 *
 * CAPA: Domain (verificación unitaria).
 *
 * SUBSISTEMA: Compuertas.
 *
 * RESPONSABILIDAD:
 * Verificar admisión, procedencia y contexto sin convertir ausencia en normalidad.
 *
 * ENTRADAS:
 * Evidencias y declaraciones artificiales explícitas.
 *
 * SALIDAS:
 * Aserciones sobre informes, rechazos y fuentes originales.
 *
 * RESTRICCIONES:
 * No consulta BDD, no usa mocks del motor ni interpreta instrumentos reales.
 *
 * TRAZABILIDAD:
 * Reglamento maestro R1/R2/R3/R4/R8 y contexto CTX-MEM-01.
 */
final class EvaluadorConfusoresServiceTest extends TestCase
{
    /**
     * Impide interpretar falta de revisión como ausencia de confusores.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_revision_ausente_conserva_pendiente_y_flags_desconocidos(): void
    {
        $r = (new EvaluadorConfusoresService())->evaluar(F::memoria(), D::MEMORIA);
        self::assertSame(C::PENDIENTE, $r['estado']);
        self::assertNull($r['invalidacion']);
        self::assertNull($r['advertencia']);
        self::assertNull($r['declaraciones']['basal_disponible']['valor']);
    }
    /**
     * Admite una revisión declarada con invalidación explícitamente descartada.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_revision_explicita_acredita_contexto_sin_diagnosticar(): void
    {
        $s = EscenarioCompuertas::preparar(contexto: ['confusores_revisados' => true, 'invalidacion_necesaria' => false]);
        $r = (new EvaluadorConfusoresService())->evaluar($s['memoria'], D::MEMORIA, $s['contexto']);
        self::assertSame(C::ACREDITADO, $r['estado']);
        self::assertFalse($r['invalidacion']);
        self::assertNull($r['advertencia']);
    }
    /**
     * Conserva una invalidación declarada sin atribuirle etiología.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_invalidador_explicito_bloquea(): void
    {
        $s = EscenarioCompuertas::preparar(contexto: ['confusores_revisados' => true, 'invalidacion_necesaria' => true]);
        $r = (new EvaluadorConfusoresService())->evaluar($s['memoria'], D::MEMORIA, $s['contexto']);
        self::assertSame(C::NO_ACREDITADO, $r['estado']);
        self::assertTrue($r['invalidacion']);
    }
    /**
     * Conserva la revisión pendiente aunque confusores_revisados sea true.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_invalidador_ausente_no_se_supone_false(): void
    {
        $s = EscenarioCompuertas::preparar(contexto: ['confusores_revisados' => true]);
        $r = (new EvaluadorConfusoresService())->evaluar($s['memoria'], D::MEMORIA, $s['contexto']);
        self::assertSame(C::PENDIENTE, $r['estado']);
        self::assertNull($r['invalidacion']);
    }
    /**
     * Impide escoger la revisión favorable entre declaraciones opuestas.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_contexto_contradictorio_conserva_ambas_fuentes(): void
    {
        $extra = F::hecho(['id' => 'SIM-CTX-EXTRA', 'origenId' => 'SIM-OR-EXTRA',
            'concepto' => 'COG-MEM:confusores_revisados', 'valor' => false]);
        $s = EscenarioCompuertas::preparar(contexto: EscenarioCompuertas::contextoCompleto(), contextoExtra: [$extra]);
        $r = (new EvaluadorConfusoresService())->evaluar($s['memoria'], D::MEMORIA, $s['contexto']);
        self::assertSame(C::CONTRADICTORIO, $r['estado']);
        self::assertCount(2, $r['declaraciones']['confusores_revisados']['soportes']);
        self::assertNull($r['declaraciones']['confusores_revisados']['valor']);
    }

    /**
     * Una revisión todavía pendiente no se oculta seleccionando otra favorable.
     *
     * @return void
     *
     * No recibe parámetros; no se esperan excepciones para las declaraciones originales.
     */
    public function test_revision_pendiente_no_se_oculta_tras_otra_favorable(): void
    {
        $extra = F::hecho(['id' => 'SIM-CTX-PENDIENTE', 'origenId' => 'SIM-OR-PENDIENTE',
            'concepto' => 'COG-MEM:confusores_revisados', 'valor' => null]);
        $s = EscenarioCompuertas::preparar(contexto: EscenarioCompuertas::contextoCompleto(), contextoExtra: [$extra]);
        $r = (new EvaluadorConfusoresService())->evaluar($s['memoria'], D::MEMORIA, $s['contexto']);
        self::assertSame(C::PENDIENTE, $r['estado']);
        self::assertNull($r['declaraciones']['confusores_revisados']['valor']);
        self::assertCount(2, $r['declaraciones']['confusores_revisados']['soportes']);
    }
    /**
     * Conserva MOD-CM-1 sin invalidar ni restar puntos.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_advertencia_memoria_es_ortogonal(): void
    {
        $s = EscenarioCompuertas::preparar(contexto: EscenarioCompuertas::contextoCompleto(['limitacion_no_invalidante' => true]));
        $r = (new EvaluadorConfusoresService())->evaluar($s['memoria'], D::MEMORIA, $s['contexto']);
        self::assertSame(C::ACREDITADO, $r['estado']);
        self::assertSame('MOD-CM-1', $r['modificador']);
    }
    /**
     * Conserva una advertencia de atención sin crear códigos oficiales nuevos.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_no_inventa_modificador_para_otro_dominio(): void
    {
        $s = EscenarioCompuertas::preparar(contexto: EscenarioCompuertas::contextoCompleto(['limitacion_no_invalidante' => true]), dominio: D::ATENCION);
        $r = (new EvaluadorConfusoresService())->evaluar($s['memoria'], D::ATENCION, $s['contexto']);
        self::assertTrue($r['advertencia']);
        self::assertNull($r['modificador']);
    }
    /**
     * Impide usar una revisión de memoria para acreditar atención.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_revision_de_otro_dominio_se_rechaza(): void
    {
        $s = EscenarioCompuertas::preparar(contexto: EscenarioCompuertas::contextoCompleto());
        $this->expectException(DomainException::class);
        (new EvaluadorConfusoresService())->evaluar($s['memoria'], D::ATENCION, $s['contexto']);
    }
    /**
     * Rechaza un concepto clínico ajeno al contrato explícito de revisión.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_no_interpreta_medicacion_como_confusor_por_si_sola(): void
    {
        $h = F::hecho(['concepto' => 'PRESCRIPCION', 'valor' => true]);
        $memoria = F::memoria([], [$h]);
        $this->expectException(DomainException::class);
        (new EvaluadorConfusoresService())->evaluar($memoria, D::MEMORIA, [$h]);
    }

}
