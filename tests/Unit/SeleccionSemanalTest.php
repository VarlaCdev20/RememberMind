<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Compartido\Dominio\Calendario\SeleccionSemanal as S;
use PHPUnit\Framework\TestCase;

final class SeleccionSemanalTest extends TestCase
{
    public function test_alias_orden_y_repeticiones_no_cambian_la_identidad_del_conjunto(): void
    {
        $esperado = S::identidad([1, 3, 7]);
        foreach (['LUNES,MIÉRCOLES,DOMINGO', '7;1;3;1', 'MONDAY / WEDNESDAY / SUNDAY', 'DOM MIE LUN'] as $entrada) {
            self::assertSame($esperado, S::codigo($entrada, true));
            self::assertSame($esperado, S::codigo($entrada, false));
        }
        self::assertSame(S::TODOS, S::codigo(null, true));
        self::assertSame(S::TODOS, S::codigo('', false));
        self::assertSame(S::TODOS, S::codigo('TODOS', true));
        // El lector histórico de cuidado no consideraba TODOS un día concreto.
        self::assertSame(S::identidad([]), S::codigo('TODOS', false));
        self::assertSame(S::identidad([]), S::codigo('NINGUNO', false));
        self::assertNotSame(S::TODOS, S::identidad(range(1, 7)));
    }

    public function test_un_dia_desconocido_no_se_interpreta_como_todos(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        S::codigo('LUNES,DESCONOCIDO',false);
    }
}
