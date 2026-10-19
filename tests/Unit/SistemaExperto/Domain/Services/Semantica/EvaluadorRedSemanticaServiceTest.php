<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Services\Semantica;

use MotorExperto\dominio\orion\Entities\Conocimiento\NodoSemantico;
use MotorExperto\dominio\orion\Entities\Conocimiento\RedSemantica;
use MotorExperto\dominio\orion\Entities\Conocimiento\RelacionSemantica;
use MotorExperto\dominio\orion\Services\Semantica\EvaluadorRedSemanticaService;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\EstadoReglaEnum as E;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum as T;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: Relaciones existentes, tipadas y habilitadas explícitamente.
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
final class EvaluadorRedSemanticaServiceTest extends TestCase
{
    /**
     * Admite participación técnica solo con arista y declaraciones explícitas.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_vinculo_habilitado_con_contrato_preserva_arista_y_version(): void
    {
        [$red, $contratos] = $this->escenario();
        $r = (new EvaluadorRedSemanticaService())->evaluarRelacion($red, 'SUB', T::SUBCOMPONENTE_DE, 'DOM', $contratos, ['R1' => true]);
        self::assertSame(E::CUMPLE, $r['estado']);
        self::assertSame($red->relaciones['R1'], $r['relacion']);
        self::assertSame('SIM-V1', $r['versionConocimiento']);
        self::assertTrue($r['autorizaParticipacion']);
    }
    /**
     * Impide autorizar participación por la mera existencia del enlace.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_arista_existente_sin_habilitacion_permanece_desconocida(): void
    {
        [$red, $contratos] = $this->escenario();
        $r = (new EvaluadorRedSemanticaService())->evaluarRelacion($red, 'SUB', T::SUBCOMPONENTE_DE, 'DOM', $contratos);
        self::assertSame(E::DESCONOCIDO, $r['estado']);
        self::assertNull($r['habilitada']);
        self::assertFalse($r['autorizaParticipacion']);
    }
    /**
     * Conserva una denegación técnica explícita sin ejecutar inferencias.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_arista_deshabilitada_no_participa(): void
    {
        [$red, $contratos] = $this->escenario();
        $r = (new EvaluadorRedSemanticaService())->evaluarRelacion($red, 'SUB', T::SUBCOMPONENTE_DE, 'DOM', $contratos, ['R1' => false]);
        self::assertSame(E::BLOQUEADA, $r['estado']);
        self::assertFalse($r['habilitada']);
        self::assertFalse($r['autorizaParticipacion']);
    }
    /**
     * Conserva el vínculo semántico válido sin convertirlo en permiso inferencial.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_contrato_sin_participacion_no_habilita_inferencia(): void
    {
        [$red, $contratos] = $this->escenario();
        $contratos[0]['habilitaParticipacion'] = false;
        $r = (new EvaluadorRedSemanticaService())->evaluarRelacion($red, 'SUB', T::SUBCOMPONENTE_DE, 'DOM', $contratos, ['R1' => true]);
        self::assertSame(E::CUMPLE, $r['estado']);
        self::assertFalse($r['autorizaParticipacion']);
    }
    /**
     * Rechaza atribuir dirección contraria a una relación existente.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_no_inventa_arista_inversa(): void
    {
        [$red, $contratos] = $this->escenario();
        $r = (new EvaluadorRedSemanticaService())->evaluarRelacion($red, 'DOM', T::SUBCOMPONENTE_DE, 'SUB', $contratos);
        self::assertSame(E::NO_CUMPLE, $r['estado']);
        self::assertNull($r['relacion']);
    }
    /**
     * No convierte dos aristas distintas en una tercera no declarada.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_no_inventa_transitividad(): void
    {
        [$red, $contratos] = $this->escenario();
        $r = (new EvaluadorRedSemanticaService())->evaluarRelacion($red, 'SUB', T::PARTE_DE, 'BLOQUE', $contratos);
        self::assertSame(E::NO_CUMPLE, $r['estado']);
        self::assertFalse($r['autorizaParticipacion']);
        self::assertCount(2, $red->relaciones);
    }
    /**
     * Impide validar aristas de una versión con contratos ajenos.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_rechaza_contrato_de_otra_version(): void
    {
        [$red, $contratos] = $this->escenario();
        $contratos[0]['versionConocimiento'] = 'SIM-V2';
        $this->expectException(DomainException::class);
        (new EvaluadorRedSemanticaService())->validarRed($red, $contratos);
    }
    /**
     * Rechaza aplicar un contrato de VARIABLE a un nodo SUBCOMPONENTE.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_rechaza_tipos_de_extremos_incompatibles(): void
    {
        [$red, $contratos] = $this->escenario();
        $contratos[0]['tipoOrigen'] = 'VARIABLE';
        $this->expectException(DomainException::class);
        (new EvaluadorRedSemanticaService())->validarRed($red, $contratos);
    }
    /**
     * Impide escoger arbitrariamente uno de dos contratos de igual firma.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_rechaza_contratos_ambiguos(): void
    {
        [$red, $contratos] = $this->escenario();
        $contratos[] = $contratos[0];
        $this->expectException(DomainException::class);
        (new EvaluadorRedSemanticaService())->validarRed($red, $contratos);
    }
    /**
     * Exige contrato para cada arista, sin validar parcialmente una versión.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_rechaza_arista_sin_contrato(): void
    {
        [$red, $contratos] = $this->escenario();
        array_pop($contratos);
        $this->expectException(DomainException::class);
        (new EvaluadorRedSemanticaService())->validarRed($red, $contratos);
    }
    /**
     * Rechaza habilitaciones que intentarían crear una conexión no declarada.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_no_autoriza_id_de_arista_inexistente(): void
    {
        [$red, $contratos] = $this->escenario();
        $this->expectException(DomainException::class);
        (new EvaluadorRedSemanticaService())->evaluarRelacion($red, 'SUB', T::SUBCOMPONENTE_DE, 'DOM', $contratos, ['R-INVENTADA' => true]);
    }
    /**
     * Rechaza una habilitación textual sin contrato booleano explícito.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_no_coacciona_estado_textual_a_booleano(): void
    {
        [$red, $contratos] = $this->escenario();
        $this->expectException(DomainException::class);
        (new EvaluadorRedSemanticaService())->evaluarRelacion($red, 'SUB', T::SUBCOMPONENTE_DE, 'DOM', $contratos, ['R1' => 'ACTIVA']);
    }
    /**
     * Impide reemplazar un nodo solicitado por el primero disponible.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_nodo_ausente_se_rechaza(): void
    {
        [$red, $contratos] = $this->escenario();
        $this->expectException(DomainException::class);
        (new EvaluadorRedSemanticaService())->evaluarRelacion($red, 'INVENTADO', T::SUBCOMPONENTE_DE, 'DOM', $contratos);
    }

    /**
     * Prepara una red tipada ficticia con dos aristas y contratos explícitos.
     *
     * @return array{RedSemantica, list<array<string, mixed>>} Grafo y contratos técnicos.
     *
     * No recibe parámetros; no se esperan excepciones con estos literales.
     */
    private function escenario(): array
    {
        $sub = new NodoSemantico('SUB', 'SIM-V1', 'MEM-S01', 'Tarea ficticia', 'SUBCOMPONENTE', 'Concepto de prueba.');
        $dom = new NodoSemantico('DOM', 'SIM-V1', 'COG-MEM', 'Memoria', 'CRITERIO', 'Dominio documental.');
        $bloque = new NodoSemantico('BLOQUE', 'SIM-V1', 'BL-COG', 'Cognición', 'BLOQUE', 'Bloque documental.');
        $a = new RelacionSemantica('R1', $sub, T::SUBCOMPONENTE_DE, $dom);
        $b = new RelacionSemantica('R2', $dom, T::PARTE_DE, $bloque);

        return [new RedSemantica('SIM-V1', [$sub, $dom, $bloque], [$a, $b]), [
            ['versionConocimiento' => 'SIM-V1', 'tipo' => T::SUBCOMPONENTE_DE,
                'tipoOrigen' => 'SUBCOMPONENTE', 'tipoDestino' => 'CRITERIO', 'habilitaParticipacion' => true],
            ['versionConocimiento' => 'SIM-V1', 'tipo' => T::PARTE_DE,
                'tipoOrigen' => 'CRITERIO', 'tipoDestino' => 'BLOQUE', 'habilitaParticipacion' => false],
        ]];
    }

}
