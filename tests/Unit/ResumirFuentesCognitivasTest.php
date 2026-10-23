<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\ResumirFuentesCognitivas;
use PHPUnit\Framework\TestCase;

final class ResumirFuentesCognitivasTest extends TestCase
{
    public function test_solo_grafica_medidas_de_la_misma_fuente_en_dias_distintos(): void
    {
        $primera = $this->captura('A1', '2026-01-01T09:00:00-04:00', '1', -1.1);
        $mismoDia = $this->captura('A2', '2026-01-01T11:00:00-04:00', '1', -1.0);
        $otraVersion = $this->captura('A3', '2026-02-01T09:00:00-04:00', '2', -0.7);
        $otraFecha = $this->captura('A4', '2026-02-01T10:00:00-04:00', '1', -0.8);
        $perfil = [
            'dominios' => [
                'COG-MEM' => [
                    'componentes' => ['MEM-EPI' => ['nombre' => 'Recuerdo']],
                ],
            ],
        ];
        $presentador = new ResumirFuentesCognitivas;

        $sinCambioTemporal = $presentador->presentar(['capturas' => [$primera, $mismoDia, $otraVersion]], $perfil, null);
        self::assertNull($sinCambioTemporal['serie_comparable']);
        self::assertSame(0, $sinCambioTemporal['series_comparables']);
        self::assertSame(2, count($sinCambioTemporal['filas']));

        $conDosDias = $presentador->presentar(['capturas' => [$primera, $mismoDia, $otraVersion, $otraFecha]], $perfil, null);
        self::assertSame(1, $conDosDias['series_comparables']);
        self::assertCount(1, $conDosDias['series_comparables_lista']);
        self::assertSame('1', $conDosDias['serie_comparable']['version']);
        self::assertCount(3, $conDosDias['serie_comparable']['puntos']);
        self::assertSame(4, $conDosDias['instrumentales']);
    }

    public function test_separa_aplicacion_funcional_de_medicion_cognitiva(): void
    {
        $observacion = $this->captura('O1', '2026-01-01T09:00:00-04:00', '1', -1.1);
        $observacion['instrumento_actual']['tipo'] = 'OBSERVACIONAL';
        $funcional = $this->captura('F1', '2026-01-02T09:00:00-04:00', '1', -1.1);
        $funcional['instrumento_actual']['tipo'] = 'FUNCIONAL';
        $cognitiva = $this->captura('C1', '2026-01-03T09:00:00-04:00', '1', -1.1);

        $resumen = (new ResumirFuentesCognitivas)->presentar(['capturas' => [$observacion, $funcional, $cognitiva]], null, null);

        self::assertSame(1, $resumen['observacionales']);
        self::assertSame(1, $resumen['funcionales']);
        self::assertSame(1, $resumen['instrumentales']);
        self::assertSame(['Aplicación cognitiva', 'Aplicación funcional', 'Observación clínica'], array_column($resumen['filas'], 'tipo'));
    }

    private function captura(string $id, string $fecha, string $version, float $z): array
    {
        return [
            'cod_aplicacion' => $id,
            'cod_instrumento' => 'I1',
            'instrumento_actual' => ['tipo' => 'COGNITIVO'],
            'nombre_instrumento' => 'Instrumento sintético',
            'version_instrumento' => $version,
            'fecha_hora' => $fecha,
            'autor' => 'Profesional sintético',
            'motivos' => [],
            'extraccion' => [
                'perfil' => 'P1', 'metodo' => 'Medición sintética',
                'normalizacion' => [
                    'referencia' => 'Referencia sintética',
                    'componentes' => ['MEM-EPI' => ['estado' => 'NORMALIZADO', 'z' => $z, 'puntuacion_bruta' => 4]],
                ],
            ],
        ];
    }
}
