<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Presentacion\Soporte\ResultadoInstitucionalVisible as V;
use MotorExperto\conservacion\CodecInstantanea as Codec;
use PHPUnit\Framework\TestCase;

final class ResultadoInstitucionalVisibleTest extends TestCase
{
    public function test_conserva_cinco_estados_distintos_y_cuenta_fuentes_no_campos(): void
    {
        $h = ['id' => '1', 'tipo' => 'APLICACION_INSTRUMENTAL', 'origen' => 'APP1',
            'concepto' => 'puntaje', 'fecha' => Codec::fecha('fecha', new \DateTimeImmutable('2026-11-19T12:00:00-04:00')), 'valor' => 0];
        $ri = ['dominios' => array_combine(['COG-MEM', 'COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'], ['A', 'P', 'U', 'I', 'X']),
            'requisitos' => ['C01' => 'S', 'C02' => 'N', 'C03' => 'U', 'C04' => 'I', 'C05' => 'X'],
            'hechos' => [$h, array_replace($h, ['id' => '2', 'concepto' => 'disponible', 'valor' => false]),
                array_replace($h, ['id' => '3', 'origen' => 'APP2', 'valor' => null]),
                array_replace($h, ['id' => '4', 'tipo' => 'JUICIO_PETERSEN', 'origen' => 'J1', 'valor' => true])],
            'criterios' => []];
        $original = $ri;
        $r = V::resumen($ri);
        self::assertSame(['alterado', 'esperado', 'insuficiente', 'no_interpretable', 'contradictorio'], array_column($r['areas'], 'grupo'));
        self::assertSame(['Memoria'], $r['alteradas']);
        self::assertSame(1, $r['esperadas']);
        self::assertSame(3, $r['sin_conclusion']);
        self::assertSame(2, $r['aplicaciones']);
        self::assertSame(1, $r['juicios']);
        self::assertSame(0, $r['respuestas']);
        self::assertSame(['0', 'No', 'Sin valor registrado', 'Sí'], array_column($r['hechos'], 'valor'));
        self::assertSame($original, $ri);
    }
}
