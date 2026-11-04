<?php

declare(strict_types=1);

namespace Tests\Unit;

use MotorExperto\investigacion\dcl\ComparadorLongitudinalExperimental as Comparador;
use PHPUnit\Framework\TestCase;

final class ComparadorLongitudinalExperimentalTest extends TestCase
{
    private function observacion(): array
    {
        return ['uso' => 'TEST_ONLY', 'caso' => 'SINTETICO-01', 'componente' => 'MEM-S01', 'instrumento' => 'SIM-TAREA',
            'edicion' => 'SIM-01', 'norma' => 'SIM-NORMA', 'idioma' => 'SIM-ES', 'poblacion' => 'SIM-POBLACION',
            'version_mapeo' => 'SIM-MAP-01', 'tipo_valor' => 'int', 'valor' => 3, 'fecha' => '2026-10-15T09:00:00-04:00',
            'episodio' => 'SIM-EP-1', 'oportunidad_verificada' => true, 'estado' => 'VIGENTE'];
    }

    public function test_compara_y_conserva_sin_inferir_progresion_o_dcl(): void
    {
        $antes = $this->observacion();
        $despues = array_replace($antes, ['episodio' => 'SIM-EP-2', 'fecha' => '2026-10-16T09:00:00-04:00']);
        self::assertSame('INFORMACION_COMPARABLE', (new Comparador)->comparar($antes, $despues)['estado']);
        $despues['valor'] = 2;
        $r = (new Comparador)->comparar($antes, $despues);
        self::assertSame('CAMBIO_SENALADO_NO_CORROBORADO', $r['estado']);
        self::assertNull($r['diferencia_interpretada']);
        self::assertSame($despues, $r['actual']);
    }

    public function test_rechaza_contextos_distintos_episodios_repetidos_y_falta_de_oportunidad(): void
    {
        $antes = $this->observacion();
        foreach ([['instrumento' => 'OTRO'], ['norma' => 'OTRA'], ['oportunidad_verificada' => null], ['fecha' => '2026-02-30T09:00:00-04:00']] as $alteracion) {
            self::assertSame('COMPARACION_NO_VALIDA', (new Comparador)->comparar($antes, array_replace($antes, $alteracion))['estado']);
        }
        self::assertSame('COMPARACION_NO_DISPONIBLE', (new Comparador)->comparar(null, $antes)['estado']);
    }

    public function test_tipo_y_corte_no_se_ignoran_al_comparar(): void
    {
        $antes = $this->observacion();
        $despues = array_replace($antes, ['episodio' => 'SIM-EP-2', 'fecha' => '2026-10-16T09:00:00-04:00', 'valor' => '3']);
        self::assertContains('TIPO_BRUTO_INCOMPATIBLE', (new Comparador)->comparar($antes, $despues)['motivos']);
        $despues['valor'] = 3;
        self::assertContains('FUENTE_POSTERIOR_AL_CORTE', (new Comparador)->comparar($antes, $despues, new \DateTimeImmutable('2026-10-15T12:00:00-04:00'))['motivos']);
    }
}
