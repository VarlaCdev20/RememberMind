<?php

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\InventarioExperto;
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

    public function test_las_20_relaciones_base_expertas_no_ocultan_una_tabla_operativa_ausente(): void
    {
        $dict = file_get_contents(dirname(__DIR__, 2).'/docs/base-de-datos/REMEMBERMIND_BDD_70_TABLAS.md');
        preg_match_all('/^## \d+\. `([a-z_]+)`/m', $dict, $matches);
        $operativas = [...$matches[1], 'objetivos_signos_vitales'];
        $this->assertCount(71, $operativas);
        $this->assertCount(20, InventarioExperto::TABLAS);
        $this->assertSame($operativas,
            InventarioExperto::excluirDelInventarioOperativo([...$operativas, ...InventarioExperto::TABLAS]));

        array_shift($operativas);
        $this->assertCount(70,
            InventarioExperto::excluirDelInventarioOperativo([...$operativas, ...InventarioExperto::TABLAS]));
    }

    public function test_consolidacion_deja_24_expertas_antes_de_revision_sin_ocultar_tablas_desconocidas(): void
    {
        $this->assertCount(4, InventarioExperto::CONSERVACION);
        $this->assertCount(24, [...InventarioExperto::TABLAS, ...InventarioExperto::CONSERVACION]);
        $this->assertSame(['residentes', 'tabla_no_aprobada'], InventarioExperto::excluirDelInventarioOperativo([
            'residentes', ...InventarioExperto::TABLAS, ...InventarioExperto::CONSERVACION, 'tabla_no_aprobada',
        ]));
    }

    public function test_revision_profesional_y_consolidacion_dejan_25_expertas(): void
    {
        $this->assertCount(25, [...InventarioExperto::TABLAS, ...InventarioExperto::CONSERVACION, ...InventarioExperto::REVISION_PROFESIONAL]);
        $this->assertSame(['residentes', 'no_aprobada'], InventarioExperto::excluirDelInventarioOperativo([
            'residentes', 'revisiones_evaluacion_experta', 'no_aprobada',
        ]));
    }
}
