<?php

namespace Tests\Unit;

use App\Backend\Modulos\Enfermeria\Servicios\TendenciaIncidentesService;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class TendenciaIncidentesServiceTest extends TestCase
{
    private TendenciaIncidentesService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TendenciaIncidentesService::class);
    }

    public function test_agrupa_siete_dias_y_compara_con_los_siete_anteriores_sin_contar_fuera_de_ventana(): void
    {
        $datos = $this->service->agrupar(collect([
            $this->fila('2026-09-14', 'CAÍDA', 99),
            $this->fila('2026-09-15', 'CAÍDA', 2),
            $this->fila('2026-09-21', 'CAÍDA', 2),
            $this->fila('2026-09-22', 'CAÍDA', 1),
            $this->fila('2026-09-28', 'CAÍDA', 2),
            $this->fila('2026-09-29', 'CAÍDA', 99),
        ]), CarbonImmutable::parse('2026-09-28 14:00', config('app.timezone')));

        $this->assertCount(7, $datos['labels']);
        $this->assertSame('Hoy', $datos['labels'][6]);
        $this->assertSame([1, 0, 0, 0, 0, 0, 2], $datos['datasets'][0]['data']);
        $this->assertSame(3, $datos['total_periodo']);
        $this->assertSame(4, $datos['total_anterior']);
        $this->assertSame(-25, $datos['variacion']);
        $this->assertSame('↓ 25%', $datos['variacion_texto']);
        $this->assertSame('decrease', $datos['variacion_tono']);
        $this->assertSame('2026-09-22', $datos['inicio_periodo']);
        $this->assertSame('2026-09-21', $datos['fin_anterior']);
    }

    public function test_cero_y_periodo_previo_cero_tienen_mensajes_finitos(): void
    {
        $hoy = CarbonImmutable::parse('2026-09-28', config('app.timezone'));
        $cero = $this->service->agrupar(collect(), $hoy);
        $nuevo = $this->service->agrupar(collect([$this->fila('2026-09-28', 'CAÍDA', 2)]), $hoy);

        $this->assertSame(0, $cero['total_periodo']);
        $this->assertSame('Sin cambios', $cero['variacion_texto']);
        $this->assertNull($cero['categoria_principal']);
        $this->assertSame([0, 0, 0, 0, 0, 0, 0], $cero['datasets'][0]['data']);
        $this->assertSame('Nuevos incidentes en este periodo', $nuevo['variacion_texto']);
        $this->assertNull($nuevo['variacion']);
    }

    public function test_categoria_frecuente_empate_y_maximo_tres_series_sin_inferir_texto_libre(): void
    {
        $datos = $this->service->agrupar(collect([
            $this->fila('2026-09-28', 'CAÍDA', 4),
            $this->fila('2026-09-28', 'ALTERACIÓN CONDUCTUAL', 4),
            $this->fila('2026-09-28', 'RECHAZO DE MEDICACIÓN', 2),
            $this->fila('2026-09-28', 'PROBLEMA CON DISPOSITIVO', 1),
        ]), CarbonImmutable::parse('2026-09-28', config('app.timezone')));

        $this->assertSame(11, $datos['total_periodo']);
        $this->assertSame('Varias categorías', $datos['categoria_principal']);
        $this->assertSame(4, $datos['categoria_principal_total']);
        $this->assertCount(3, $datos['datasets']);
        $this->assertSame('Otros', $datos['datasets'][2]['label']);
        $this->assertSame(3, $datos['datasets'][2]['data'][6]);
    }

    public function test_incremento_usa_corales_y_la_fecha_se_interpreta_en_zona_local(): void
    {
        $this->app['config']->set('app.timezone', 'America/La_Paz');
        $instanteUtc = CarbonImmutable::parse('2026-09-29 01:00:00', 'UTC');
        $datos = $this->service->agrupar(collect([
            $this->fila('2026-09-21', 'CAÍDA', 1),
            $this->fila('2026-09-28', 'CAÍDA', 2),
        ]), $instanteUtc);

        $this->assertSame('2026-09-28', $datos['fin_periodo']);
        $this->assertSame(100, $datos['variacion']);
        $this->assertSame('↑ 100%', $datos['variacion_texto']);
        $this->assertSame('increase', $datos['variacion_tono']);
    }

    private function fila(string $dia, string $tipo, int $total): object
    {
        return (object) ['dia' => $dia, 'tipo_incidente' => $tipo, 'total' => $total];
    }
}
