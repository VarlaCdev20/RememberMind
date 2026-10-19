<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\ValueObjects\Conocimiento;

use MotorExperto\dominio\orion\ValueObjects\Conocimiento\EstadoReglaEnum;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\OperadorCondicionEnum;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: Predicados normalizados y separación de cumplimiento y habilitación.
 *
 * CAPA: Domain (verificación unitaria).
 *
 * SUBSISTEMA: Contratos de ValueObjects.
 *
 * RESPONSABILIDAD:
 * Comprobar los límites semánticos del vocabulario aislado de esta fase.
 *
 * ENTRADAS:
 * Códigos documentales y propuestas técnicas, sin información clínica real.
 *
 * SALIDAS:
 * Aserciones sobre representación y rechazo, sin resultados de inferencia.
 *
 * RESTRICCIONES:
 * No inicia Laravel, no consulta BDD, no clasifica ni acredita validación clínica.
 *
 * TRAZABILIDAD:
 * Documento Maestro D-123/D-130; reglamento maestro R1/R8/R10.
 */
final class VocabularioConocimientoTest extends TestCase
{
    /**
     * Verifica que la candidatura no se oculta en el nombre del predicado.
     *
     * @return void
     *
     * No recibe parámetros ni espera excepciones.
     */
    public function test_predicado_no_incorpora_estado_ni_sinonimos(): void
    {
        self::assertSame(TipoRelacionSemanticaEnum::SUBCOMPONENTE_DE, TipoRelacionSemanticaEnum::from('SUBCOMPONENTE_DE'));
        self::assertNull(TipoRelacionSemanticaEnum::tryFrom('SUBCOMPONENTE_CANDIDATO_DE'));
        self::assertNull(TipoRelacionSemanticaEnum::tryFrom('IS_A'));
        self::assertNull(TipoRelacionSemanticaEnum::tryFrom('CANDIDATA'));
    }

    /**
     * Verifica que el estado de evaluación no es habilitación ni normalidad.
     *
     * @return void
     *
     * No recibe parámetros ni espera excepciones.
     */
    public function test_estado_regla_conserva_desconocimiento_y_bloqueo(): void
    {
        self::assertSame(EstadoReglaEnum::DESCONOCIDO, EstadoReglaEnum::from('DESCONOCIDO'));
        self::assertSame(EstadoReglaEnum::BLOQUEADA, EstadoReglaEnum::from('BLOQUEADA'));
        self::assertNull(EstadoReglaEnum::tryFrom('ACTIVO'));
        self::assertNull(EstadoReglaEnum::tryFrom('P'));
        self::assertNull(EstadoReglaEnum::tryFrom(''));
    }

    /**
     * Verifica que una expresión arbitraria no se convierte en operador.
     *
     * @return void
     *
     * No recibe parámetros ni ejecuta la expresión usada como cadena artificial.
     */
    public function test_operadores_son_tokens_y_no_expresiones_ejecutables(): void
    {
        self::assertSame(OperadorCondicionEnum::Y, OperadorCondicionEnum::from('Y'));
        self::assertNull(OperadorCondicionEnum::tryFrom('eval($entrada)'));
        self::assertNull(OperadorCondicionEnum::tryFrom('A == true'));
    }

}
