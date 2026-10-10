<?php

namespace Tests\Unit;

use App\Backend\Modulos\SistemaExperto\Conocimiento\InventarioExperto;
use PHPUnit\Framework\TestCase;

class InventarioExpertoTest extends TestCase
{
    public function test_el_conteo_operativo_sigue_detectando_tablas_no_aprobadas(): void
    {
        $tablas = ['residentes', 'signos_vitales', 'objetivos_signos_vitales',
            'versiones_modelo_experto', 'evidencias_evaluacion', 'expert_runs_no_aprobada'];

        $this->assertSame(['residentes', 'signos_vitales', 'objetivos_signos_vitales',
            'expert_runs_no_aprobada'], InventarioExperto::excluirDelInventarioOperativo($tablas));
    }

    public function test_las_23_expertas_no_ocultan_una_tabla_operativa_ausente(): void
    {
        $dict = file_get_contents(dirname(__DIR__, 2).'/docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md');
        preg_match_all('/^## \d+\. `([a-z_]+)`/m', $dict, $matches);
        $operativas = [...$matches[1], 'objetivos_signos_vitales'];
        $this->assertCount(71, $operativas);
        $this->assertCount(23, InventarioExperto::TABLAS);
        $this->assertSame($operativas,
            InventarioExperto::excluirDelInventarioOperativo([...$operativas, ...InventarioExperto::TABLAS]));

        array_shift($operativas);
        $this->assertCount(70,
            InventarioExperto::excluirDelInventarioOperativo([...$operativas, ...InventarioExperto::TABLAS]));
    }
}
