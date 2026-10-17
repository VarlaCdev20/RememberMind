<?php

namespace Tests\Unit;

use App\Models\CuracionHerida;
use App\Modulos\Cuidados\Aplicacion\Consultas\PrepararEvolucionHerida;
use Tests\TestCase;

class PrepararEvolucionHeridaTest extends TestCase
{
    public function test_compara_primera_y_ultima_area_valida_sin_usar_filas_incompletas(): void
    {
        $resultado = (new PrepararEvolucionHerida)->desde(collect([
            $this->curacion('FINAL', '2026-10-12', 2, 1),
            $this->curacion('INICIAL', '2026-10-01', 4, 2),
            $this->curacion('INTERMEDIA', '2026-10-05', 3, 2),
            $this->curacion('SIN_MEDIDAS', '2026-10-13', null, 2),
        ]));

        $this->assertSame(8.0, $resultado['resumen']['inicial']);
        $this->assertSame(2.0, $resultado['resumen']['ultima']);
        $this->assertSame(-75.0, $resultado['resumen']['variacion_desde_inicio']);
        $this->assertSame(['INICIAL', 'INTERMEDIA', 'FINAL', 'SIN_MEDIDAS'], array_column($resultado['serie'], 'codigo'));
        $this->assertNull($resultado['serie'][3]['area']);
    }

    public function test_cero_o_un_registro_no_inventan_variacion(): void
    {
        $proyeccion = new PrepararEvolucionHerida;
        $vacia = $proyeccion->desde(collect());
        $unica = $proyeccion->desde(collect([$this->curacion('UNICA', '2026-10-12', 2, 2)]));

        $this->assertSame([], $vacia['serie']);
        $this->assertNull($vacia['resumen']['inicial']);
        $this->assertNull($vacia['resumen']['variacion_desde_inicio']);
        $this->assertSame(4.0, $unica['resumen']['inicial']);
        $this->assertNull($unica['resumen']['variacion_desde_inicio']);
    }

    public function test_area_inicial_cero_no_divide_por_cero_y_aumento_conserva_signo(): void
    {
        $proyeccion = new PrepararEvolucionHerida;
        $cero = $proyeccion->desde(collect([
            $this->curacion('CERO', '2026-10-01', 0, 2),
            $this->curacion('FINAL', '2026-10-12', 2, 2),
        ]));
        $aumento = $proyeccion->desde(collect([
            $this->curacion('INICIAL', '2026-10-01', 1, 2),
            $this->curacion('FINAL', '2026-10-12', 2, 2),
        ]));

        $this->assertSame(0.0, $cero['resumen']['inicial']);
        $this->assertNull($cero['resumen']['variacion_desde_inicio']);
        $this->assertSame(100.0, $aumento['resumen']['variacion_desde_inicio']);
    }

    private function curacion(string $codigo, string $fecha, ?float $largo, ?float $ancho): CuracionHerida
    {
        return (new CuracionHerida([
            'cod_curacion' => $codigo, 'fecha_hora' => $fecha,
            'longitud' => $largo, 'ancho' => $ancho,
        ]))->setRelation('personal', null);
    }
}
