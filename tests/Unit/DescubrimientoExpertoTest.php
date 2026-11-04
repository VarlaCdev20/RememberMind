<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;

use function RememberMind\Herramientas\verificarDescubrimientoExperto;

require_once dirname(__DIR__, 2).'/scripts/verificar-descubrimiento-experto.php';

final class DescubrimientoExpertoTest extends TestCase
{
    private function inventario(?string $omitida = null, bool $conMetodos = true): string
    {
        $xml = '<testSuite xmlns="https://xml.phpunit.de/testSuite"><tests>';
        foreach (['CircuitoInstrumentalInicialTest', 'RetiradaLaboratorioExpertoTest', 'ExploradorExpertoTest',
            'GobiernoReglasExpertasTest', 'ConocimientoCognitivoInstitucionalTest',
            'CicloObservacionalConservadoTest', 'ConservacionCompletaSQLTest'] as $nombre) {
            if ($nombre === $omitida) {
                continue;
            }
            $xml .= '<testClass name="Tests\\Feature\\'.$nombre.'">'
                .($conMetodos ? '<testMethod name="test_caso"/>' : '').'</testClass>';
        }

        return $xml.'</tests></testSuite>';
    }

    public function test_descubre_los_grupos_con_casos_ejecutables(): void
    {
        $resultado = verificarDescubrimientoExperto($this->inventario());
        self::assertCount(7, $resultado);
        self::assertSame(7, array_sum($resultado));
    }

    public function test_un_filtro_que_omite_el_circuito_inicial_no_pasa(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('CircuitoInstrumentalInicialTest');
        verificarDescubrimientoExperto($this->inventario('CircuitoInstrumentalInicialTest'));
    }

    public function test_las_clases_sin_metodos_no_cuentan_como_cobertura(): void
    {
        $this->expectException(RuntimeException::class);
        verificarDescubrimientoExperto($this->inventario(conMetodos: false));
    }

    public function test_un_informe_truncado_no_pasa(): void
    {
        $this->expectException(RuntimeException::class);
        verificarDescubrimientoExperto('<testSuite');
    }

    public function test_un_xml_ajeno_no_se_interpreta_como_descubrimiento_vacio(): void
    {
        $this->expectException(RuntimeException::class);
        verificarDescubrimientoExperto('<informe/>');
    }
}
