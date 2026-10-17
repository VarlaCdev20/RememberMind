<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Services\Compuertas;

use App\SistemaExperto\Domain\Services\Compuertas\VerificadorEvidenciaService;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\DominioCognitivoEnum as D;
use App\SistemaExperto\Domain\ValueObjects\Cognicion\HallazgoInstrumentalEnum as H;
use DomainException;
use Tests\Unit\SistemaExperto\Domain\Entities\EscenarioEntidades as F;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: VerificadorEvidenciaServiceTest.
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
final class VerificadorEvidenciaServiceTest extends TestCase
{
    /**
     * Conserva la evidencia y su revisión originales sin modificar memoria.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_admite_original_con_procedencia_explicita_y_preserva_fuentes(): void
    {
        $e = F::evidencia();
        $s = EscenarioCompuertas::preparar([$e], procedencias: [$e->hecho->id => true]);
        $r = (new VerificadorEvidenciaService())->verificar($s['memoria'], D::MEMORIA, [$e], $s['procedencias']);
        self::assertSame([$e], $r['admitidas']);
        self::assertSame([], $r['excluidas']);
        self::assertSame([$s['procedencias'][0]], $r['procedencias'][$e->hecho->id]['soportes']);
        self::assertSame($e, $s['memoria']->evidencia($e->hecho->id));
    }
    /**
     * Rechaza admisión sin prueba de procedencia y conserva null.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_procedencia_ausente_permanece_desconocida(): void
    {
        $e = F::evidencia();
        $s = EscenarioCompuertas::preparar([$e]);
        $r = (new VerificadorEvidenciaService())->verificar($s['memoria'], D::MEMORIA, [$e]);
        self::assertSame([], $r['admitidas']);
        self::assertNull($r['procedencias'][$e->hecho->id]['valor']);
        self::assertContains('PROCEDENCIA_DESCONOCIDA', $r['excluidas'][0]['motivos']);
    }
    /**
     * Conserva el rechazo explícito sin transformarlo en resultado cognitivo.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_procedencia_negada_no_es_ausencia_ni_normalidad(): void
    {
        $e = F::evidencia();
        $s = EscenarioCompuertas::preparar([$e], procedencias: [$e->hecho->id => false]);
        $r = (new VerificadorEvidenciaService())->verificar($s['memoria'], D::MEMORIA, [$e], $s['procedencias']);
        self::assertFalse($r['procedencias'][$e->hecho->id]['valor']);
        self::assertContains('PROCEDENCIA_NO_VERIFICADA', $r['excluidas'][0]['motivos']);
    }
    /**
     * Conserva una revisión original cuyo resultado todavía es desconocido.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_declaracion_null_no_se_convierte_en_false(): void
    {
        $e = F::evidencia();
        $s = EscenarioCompuertas::preparar([$e], procedencias: [$e->hecho->id => null]);
        $r = (new VerificadorEvidenciaService())->verificar($s['memoria'], D::MEMORIA, [$e], $s['procedencias']);
        self::assertNull($r['procedencias'][$e->hecho->id]['valor']);
        self::assertSame([$s['procedencias'][0]], $r['procedencias'][$e->hecho->id]['soportes']);
    }
    /**
     * Rechaza elegir silenciosamente entre dos revisiones incompatibles.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_procedencias_opuestas_se_conservan_como_contradiccion(): void
    {
        $e = F::evidencia();
        $otra = F::hecho(['id' => 'SIM-PROC-OTRA', 'origenId' => 'SIM-OR-PROC-OTRA',
            'concepto' => 'VERIFICACION_PROCEDENCIA:'.$e->hecho->id, 'valor' => false]);
        $s = EscenarioCompuertas::preparar([$e], procedencias: [$e->hecho->id => true], procedenciaExtra: [$otra]);
        $r = (new VerificadorEvidenciaService())->verificar($s['memoria'], D::MEMORIA, [$e], $s['procedencias']);
        self::assertSame([], $r['admitidas']);
        self::assertTrue($r['procedencias'][$e->hecho->id]['contradictoria']);
        self::assertNull($r['procedencias'][$e->hecho->id]['valor']);
        self::assertContains('PROCEDENCIA_CONTRADICTORIA', $r['excluidas'][0]['motivos']);
    }
    /**
     * Rechaza cero como sustituto ambiguo de una revisión booleana.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_declaracion_numerica_no_se_coacciona_a_booleano(): void
    {
        $e = F::evidencia();
        $s = EscenarioCompuertas::preparar([$e], procedencias: [$e->hecho->id => 0]);
        $this->expectException(DomainException::class);
        (new VerificadorEvidenciaService())->verificar($s['memoria'], D::MEMORIA, [$e], $s['procedencias']);
    }
    /**
     * Excluye el dato desconocido aunque su procedencia esté verificada.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_null_original_no_se_admite_como_hallazgo(): void
    {
        $e = F::evidencia(hallazgo: H::DESCONOCIDO, cambiosHecho: ['valor' => null]);
        $s = EscenarioCompuertas::preparar([$e], procedencias: [$e->hecho->id => true]);
        $r = (new VerificadorEvidenciaService())->verificar($s['memoria'], D::MEMORIA, [$e], $s['procedencias']);
        self::assertSame([], $r['admitidas']);
        self::assertContains('EVIDENCIA_DESCONOCIDA', $r['excluidas'][0]['motivos']);
    }
    /**
     * Admite un dato conocido explícito conservando su valor, sin convertirlo a P.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_false_original_se_conserva_sin_inferir_preservacion(): void
    {
        $e = F::evidencia(cambiosHecho: ['valor' => false]);
        $s = EscenarioCompuertas::preparar([$e], procedencias: [$e->hecho->id => true]);
        $r = (new VerificadorEvidenciaService())->verificar($s['memoria'], D::MEMORIA, [$e], $s['procedencias']);
        self::assertFalse($r['admitidas'][0]->hecho->valor);
        self::assertSame(H::HALLAZGO_DESCRIPTIVO, $r['admitidas'][0]->hallazgo);
    }
    /**
     * Admite una sola instancia y explica la repetición.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_repeticion_de_candidata_no_se_cuenta_dos_veces(): void
    {
        $e = F::evidencia();
        $s = EscenarioCompuertas::preparar([$e], procedencias: [$e->hecho->id => true]);
        $r = (new VerificadorEvidenciaService())->verificar($s['memoria'], D::MEMORIA, [$e, $e], $s['procedencias']);
        self::assertSame([$e], $r['admitidas']);
        self::assertContains('EVIDENCIA_DUPLICADA', $r['excluidas'][0]['motivos']);
    }
    /**
     * Conserva fuentes distintas sin presentarlas como corroboraciones independientes.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_mismo_episodio_se_identifica_como_dependencia(): void
    {
        $a = F::evidencia('SIM-A', cambiosHecho: ['episodioId' => 'SIM-COMPARTIDO']);
        $b = F::evidencia('SIM-B', cambiosHecho: ['episodioId' => 'SIM-COMPARTIDO']);
        $s = EscenarioCompuertas::preparar([$a, $b], procedencias: [$a->hecho->id => true, $b->hecho->id => true]);
        $r = (new VerificadorEvidenciaService())->verificar($s['memoria'], D::MEMORIA, [$b, $a], $s['procedencias']);
        self::assertSame([$a, $b], $r['admitidas']);
        self::assertSame(['EPISODIO_REUTILIZADO'], $r['dependencias'][0]['motivos']);
    }
    /**
     * Rechaza una candidata reconstruida que sustituiría al objeto original.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_evidencia_ajena_a_la_instantanea_se_rechaza(): void
    {
        $a = F::evidencia();
        $otra = F::evidencia();
        $this->expectException(DomainException::class);
        (new VerificadorEvidenciaService())->verificar(F::memoria([$a]), D::MEMORIA, [$otra]);
    }
    /**
     * Rechaza una candidata de otra evaluación antes de valorar su contenido.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_evidencia_de_otro_caso_se_rechaza(): void
    {
        $otra = F::evidencia(cambiosHecho: ['evaluacionId' => 'SIM-OTRA']);
        $this->expectException(DomainException::class);
        (new VerificadorEvidenciaService())->verificar(F::memoria(), D::MEMORIA, [$otra]);
    }
    /**
     * Explica la exclusión de un eje ajeno sin trasladar su hallazgo.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_evidencia_de_otro_dominio_se_excluye(): void
    {
        $e = F::evidencia(dominio: D::ATENCION);
        $s = EscenarioCompuertas::preparar([$e], procedencias: [$e->hecho->id => true]);
        $r = (new VerificadorEvidenciaService())->verificar($s['memoria'], D::MEMORIA, [$e], $s['procedencias']);
        self::assertSame([], $r['admitidas']);
        self::assertContains('OTRO_DOMINIO', $r['excluidas'][0]['motivos']);
    }
    /**
     * Rechaza repetir una revisión original en la colección de declaraciones.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_revisiones_repetidas_no_se_cuentan_dos_veces(): void
    {
        $e = F::evidencia();
        $s = EscenarioCompuertas::preparar([$e], procedencias: [$e->hecho->id => true]);
        $this->expectException(DomainException::class);
        (new VerificadorEvidenciaService())->verificar($s['memoria'], D::MEMORIA, [$e], [...$s['procedencias'], ...$s['procedencias']]);
    }

}
