<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\ValueObjects\Funcionalidad;

use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoDiferencialEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoIadlEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\OportunidadIadlEnum;
use PHPUnit\Framework\TestCase;

/** Comprobar los límites semánticos del vocabulario aislado de esta fase. */
final class VocabularioFuncionalidadTest extends TestCase
{
    /**
     * Evita que el vocabulario presente ayuda o no ejecución como causa cognitiva.
     * @return void
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
     * @return void
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
