<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\ValueObjects\Funcionalidad;

use App\SistemaExperto\Domain\ValueObjects\Funcionalidad\EstadoDiferencialEnum;
use App\SistemaExperto\Domain\ValueObjects\Funcionalidad\EstadoIadlEnum;
use App\SistemaExperto\Domain\ValueObjects\Funcionalidad\OportunidadIadlEnum;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: Separación de oportunidad, desempeño y revisión diferencial.
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
 * Reglamento maestro R1/R5/R7; Documento Maestro sección 3.13.2.
 */
final class VocabularioFuncionalidadTest extends TestCase
{
    /**
     * Evita que el vocabulario presente ayuda o no ejecución como causa cognitiva.
     *
     * Solo prueba la frontera de representación: las compuertas funcionales
     * todavía corresponden a una fase posterior.
     *
     * @return void
     *
     * No recibe parámetros ni espera excepciones.
     */
    public function test_oportunidad_y_desempeno_son_dimensiones_distintas(): void
    {
        self::assertSame(EstadoIadlEnum::CON_AYUDA, EstadoIadlEnum::from('CON_AYUDA'));
        self::assertNull(EstadoIadlEnum::tryFrom('DEPENDENCIA_COGNITIVA'));
        self::assertNull(EstadoIadlEnum::tryFrom(OportunidadIadlEnum::SUSTITUIDA_POR_INSTITUCION->value));
        self::assertNull(OportunidadIadlEnum::tryFrom(EstadoIadlEnum::NO_REALIZADA->value));
        self::assertSame(OportunidadIadlEnum::SIN_OPORTUNIDAD, OportunidadIadlEnum::from('SIN_OPORTUNIDAD'));
    }

    /**
     * Conserva el diferencial pendiente separado de una revisión completada.
     *
     * @return void
     *
     * No recibe parámetros ni infiere un diagnóstico desde los estados.
     */
    public function test_revision_ausente_no_se_representa_como_completada(): void
    {
        self::assertSame(EstadoDiferencialEnum::PENDIENTE, EstadoDiferencialEnum::from('PENDIENTE'));
        self::assertSame(EstadoDiferencialEnum::IMPEDIMENTO_NO_RESUELTO, EstadoDiferencialEnum::from('IMPEDIMENTO_NO_RESUELTO'));
        self::assertNotSame(EstadoDiferencialEnum::COMPLETADO_SIN_IMPEDIMENTO, EstadoDiferencialEnum::PENDIENTE);
        self::assertNull(EstadoDiferencialEnum::tryFrom(''));
        self::assertNull(EstadoDiferencialEnum::tryFrom('SIN_DEMENCIA'));
    }

}
