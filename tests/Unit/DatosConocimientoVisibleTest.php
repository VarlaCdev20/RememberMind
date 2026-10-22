<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Presentacion\Soporte\DatosConocimientoVisible;
use DomainException;
use PHPUnit\Framework\TestCase;

final class DatosConocimientoVisibleTest extends TestCase
{
    private function tablas(): array
    {
        return ['nodos_semanticos' => [['cod_nodo_semantico' => 'N1', 'nombre' => 'Concepto original']],
            'variables_expertas' => [['cod_variable_experta' => 'V1', 'cod_nodo_semantico' => 'N1']],
            'reglas_expertas' => [['cod_regla_experta' => 'R1', 'nombre' => 'Regla original']],
            'condiciones_regla_experta' => [['cod_condicion_regla' => 'C1', 'cod_regla_experta' => 'R1', 'cod_variable_experta' => 'V1']]];
    }

    public function test_conserva_referencias_exactas_en_ambas_direcciones_sin_mutar_originales(): void
    {
        $tablas = $this->tablas();
        $antes = $tablas;
        $dto = (new DatosConocimientoVisible)->detalle($tablas, 'variables_expertas', 'V1');
        self::assertSame(['Referencia a', 'Utilizado por'], array_column($dto['enlaces'], 'direccion'));
        self::assertSame(['N1', 'C1'], array_column($dto['enlaces'], 'id'));
        self::assertCount(3, $dto['grafo']['nodos']);
        self::assertCount(2, $dto['grafo']['aristas']);
        self::assertSame($antes, $tablas);
    }

    public function test_no_conecta_por_coincidencia_de_nombre_ni_referencia_ausente(): void
    {
        $tablas = $this->tablas();
        $tablas['variables_expertas'][0]['cod_nodo_semantico'] = 'OTRA_VERSION';
        $tablas['variables_expertas'][0]['nombre'] = 'Concepto original';
        $dto = (new DatosConocimientoVisible)->detalle($tablas, 'nodos_semanticos', 'N1');
        self::assertSame([], $dto['enlaces']);
        self::assertCount(1, $dto['grafo']['nodos']);
    }

    public function test_registro_fuera_de_la_instantanea_es_rechazado(): void
    {
        $this->expectException(DomainException::class);
        (new DatosConocimientoVisible)->detalle($this->tablas(), 'reglas_expertas', 'REGISTRO_AJENO');
    }

    public function test_categorias_sin_filas_se_presentan_con_cero_documental(): void
    {
        $categorias = (new DatosConocimientoVisible)->categorias($this->tablas());
        self::assertCount(12, $categorias);
        self::assertSame(0, $categorias['fuentes_datos_expertas']['cantidad']);
    }
}
