<?php

namespace Tests\Unit;

use App\Modulos\Reportes\Presentacion\Reportes\GraficasGerencia;
use PHPUnit\Framework\TestCase;

class GraficasGerenciaTest extends TestCase
{
    private function grafica(array $datos, string $unidad = 'personas'): array
    {
        $filas = [];
        foreach ($datos as $etiqueta => $valor) {
            $filas[] = ['indicador' => $etiqueta, 'valor' => $valor, 'unidad' => $unidad];
        }

        return (new GraficasGerencia)->series(['filas' => $filas])[0];
    }

    public function test_las_porciones_excluyen_el_total_y_explican_el_resto(): void
    {
        $grafica = $this->grafica(['Trabajadores registrados' => 10, 'Trabajadores activos' => 6, 'Trabajadores inactivos' => 3]);
        $this->assertSame([6, 3, 1], array_column($grafica['serie'], 'valor'));
        $this->assertSame(10, $grafica['total']);
        $this->assertSame('dona', $grafica['recomendado']);
        $this->assertSame('Resto de registros', $grafica['serie'][2]['etiqueta']);
        $this->assertStringContainsString('total menos', $grafica['nota']);
    }

    public function test_subconjuntos_superpuestos_no_se_convierten_en_torta(): void
    {
        $grafica = $this->grafica(['Solicitudes por trabajador' => 10, 'Pendientes de entrega o corrección' => 8, 'Con plazo vencido' => 4, 'Recibidas para revisión' => 2], 'solicitudes');
        $this->assertSame(['barras', 'radar'], $grafica['tipos']);
        $this->assertNull($grafica['total']);
        $this->assertSame('barras', (new GraficasGerencia)->tipo($grafica, 'torta'));
    }

    public function test_ausencias_y_totales_inconsistentes_no_fabrican_porciones(): void
    {
        $ausente = $this->grafica(['Trabajadores registrados' => 10, 'Trabajadores activos' => null, 'Trabajadores inactivos' => 0]);
        $this->assertSame(['barras'], $ausente['tipos']);
        $this->assertNull($ausente['serie'][1]['valor']);
        $inconsistente = $this->grafica(['Trabajadores registrados' => 2, 'Trabajadores activos' => 3]);
        $this->assertNotContains('dona', $inconsistente['tipos']);
    }

    public function test_los_estados_documentales_ofrecen_radar_con_conteos_originales(): void
    {
        $grafica = $this->grafica(['Archivos laborales registrados' => 10, 'Archivos validados' => 4, 'Archivos observados' => 2, 'Archivos cargados' => 3, 'Archivos pendientes de revisión' => 1], 'archivos');
        $this->assertSame('radar', $grafica['recomendado']);
        $this->assertSame([4, 2, 3, 1], array_column($grafica['serie'], 'valor'));
        $this->assertSame(4, $grafica['maximo']);
        foreach (['dona', 'torta', 'radar'] as $tipo) {
            $imagen = (new GraficasGerencia)->imagen($grafica, $tipo);
            $svg = base64_decode(substr($imagen, strlen('data:image/svg+xml;base64,')), true);
            $this->assertStringContainsString('<svg', $svg);
            $this->assertStringNotContainsString('NaN', $svg);
        }
    }

    public function test_cero_y_una_categoria_del_cien_por_ciento_tienen_geometria_valida(): void
    {
        $cero = $this->grafica(['Camas ocupadas' => 0, 'Camas disponibles' => 0, 'Camas no habilitadas' => 0], 'camas');
        $this->assertSame(0, $cero['total']);
        $svg = base64_decode(explode(',', (new GraficasGerencia)->imagen($cero, 'dona'), 2)[1]);
        $this->assertStringNotContainsString('<path', $svg);
        $llena = $this->grafica(['Camas ocupadas' => 0, 'Camas disponibles' => 1, 'Camas no habilitadas' => 0], 'camas');
        $svg = base64_decode(explode(',', (new GraficasGerencia)->imagen($llena, 'torta'), 2)[1]);
        $this->assertSame(2, substr_count($svg, ' A 96 96 '));
    }

    public function test_inicio_muestra_la_ocupacion_sobre_el_inventario_y_distingue_cero_de_ausencia(): void
    {
        $reporte = ['personal' => null, 'ocupadas' => 109, 'camas' => 110, 'avisos_documentales' => 0, 'filas' => [
            ['indicador' => 'Personal activo', 'valor' => null, 'unidad' => 'personas'],
            ['indicador' => 'Camas ocupadas', 'valor' => 109, 'unidad' => 'camas'],
            ['indicador' => 'Camas disponibles', 'valor' => 1, 'unidad' => 'camas'],
            ['indicador' => 'Camas no habilitadas', 'valor' => 0, 'unidad' => 'camas'],
            ['indicador' => 'Avisos documentales', 'valor' => 0, 'unidad' => 'avisos'],
            ['indicador' => 'Avisos con plazo vencido', 'valor' => 0, 'unidad' => 'avisos'],
        ]];

        [$personal, $camas, $avisos] = (new GraficasGerencia)->paraInicio($reporte);

        $this->assertNull($personal['resumen']['valor']);
        $this->assertTrue($personal['resumen']['solo_cifra']);
        $this->assertSame(99.1, $camas['resumen']['porcentaje']);
        $this->assertSame(110, $camas['resumen']['valor']);
        $this->assertSame([109, 1, 0], array_column($camas['serie'], 'valor'));
        $this->assertSame(0, $avisos['resumen']['valor']);
        $this->assertTrue($avisos['resumen']['solo_cifra']);
    }
}
