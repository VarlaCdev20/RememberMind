<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Presentacion\Soporte\FechaVisible;
use PHPUnit\Framework\TestCase;

final class FechaVisibleTest extends TestCase
{
    public function test_muestra_fechas_historicas_locales_e_iso_sin_exponer_microsegundos_ni_zona(): void
    {
        self::assertSame('17/10/2026 12:00', FechaVisible::mostrar('17/10/2026 12:00:00.000000-04:00'));
        self::assertSame('21/10/2026 22:56', FechaVisible::mostrar('2026-10-21T22:56:47.186906-04:00'));
        self::assertSame('21/10/2026', FechaVisible::mostrar('2026-10-21'));
        self::assertSame('Fecha no disponible', FechaVisible::mostrar(null));
        self::assertSame('Fecha no disponible', FechaVisible::mostrar('dato inválido'));
    }
}
