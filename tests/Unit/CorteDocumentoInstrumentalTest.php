<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTimeImmutable;
use MotorExperto\dominio\instrumentos\CorteDocumentoInstrumental;
use PHPUnit\Framework\TestCase;

final class CorteDocumentoInstrumentalTest extends TestCase
{
    public function test_solo_admite_fecha_valida_conocida_anterior_o_igual_al_corte(): void
    {
        $corte = new DateTimeImmutable('2026-10-20T12:00:00.123456-04:00');
        foreach ([null, [], ['fecha_hora' => null], ['fecha_hora' => ''], ['fecha_hora' => 'mañana'],
            ['fecha_hora' => '2026-02-30T12:00:00.000000-04:00'], ['fecha_hora' => '2026-10-20T12:00:00.123457-04:00']] as $documento) {
            self::assertFalse(CorteDocumentoInstrumental::admite($documento, $corte));
        }
        self::assertTrue(CorteDocumentoInstrumental::admite(['fecha_hora' => '2026-10-20T12:00:00.123456-04:00'], $corte));
        self::assertTrue(CorteDocumentoInstrumental::admite(['fecha_hora' => '2026-10-20T12:00:00.123455-04:00'], $corte));
    }
}
