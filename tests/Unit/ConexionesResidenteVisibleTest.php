<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Presentacion\Soporte\ConexionesResidenteVisible;
use DomainException;
use Tests\TestCase;

final class ConexionesResidenteVisibleTest extends TestCase
{
    private function perfil(): array
    {
        $fuente = ['residente' => 'RES_A', 'tabla' => 'valoraciones_petersen_originales', 'registro' => 'VP_1', 'fecha' => '2026-10-20T10:00:00-04:00'];
        $soporte = ['residente' => 'RES_A', 'tabla' => 'aplicaciones_instrumento', 'registro' => 'AP_1', 'tipo' => 'MEDICION', 'fecha' => '2026-10-19T10:00:00-04:00'];

        return ['fecha_corte' => '2026-10-22T10:00:00-04:00', 'dominios' => [
            'COG-MEM' => ['estado' => 'A', 'motivos' => [], 'valoraciones' => [
                ['estado' => 'A', 'justificacion' => 'Justificación original del caso sintético', 'origen' => $fuente, 'soportes' => [$soporte, $soporte]],
            ]],
            'COG-ATE' => ['estado' => 'U', 'motivos' => [], 'valoraciones' => []],
        ], 'requisitos' => ['C02' => ['estado' => 'S', 'valoraciones' => [], 'motivos' => [], 'derivado_de' => ['COG-MEM']]]];
    }

    public function test_sin_permiso_o_contrato_disponible_no_fabrica_un_perfil(): void
    {
        $dto = (new ConexionesResidenteVisible)->presentar('RES_A', null);
        self::assertFalse($dto['disponible']);
        self::assertSame([], $dto['elementos']);
    }

    public function test_conserva_los_vinculos_originales_y_no_duplica_soportes(): void
    {
        $original = $this->perfil();
        $dto = (new ConexionesResidenteVisible)->presentar('RES_A', $original);
        self::assertSame(1, $dto['valoraciones']);
        self::assertSame(1, $dto['soportes']);
        self::assertCount(3, $dto['elementos'][0]['grafo']['nodos']);
        self::assertCount(2, $dto['elementos'][0]['grafo']['aristas']);
        self::assertSame('Alteración sustentada', $dto['elementos'][0]['estado']);
        self::assertSame($this->perfil(), $original);
        self::assertStringNotContainsString('datos_originales', json_encode($dto, JSON_THROW_ON_ERROR));
    }

    public function test_un_area_sin_valoraciones_no_se_convierte_en_rendimiento_esperado(): void
    {
        $area = (new ConexionesResidenteVisible)->presentar('RES_A', $this->perfil())['elementos'][1];
        self::assertSame('Información insuficiente', $area['estado']);
        self::assertSame([], $area['grafo']['aristas']);
        self::assertCount(1, $area['grafo']['nodos']);
    }

    public function test_c02_solo_muestra_las_dependencias_que_declaro_el_motor(): void
    {
        $requisito = (new ConexionesResidenteVisible)->presentar('RES_A', $this->perfil())['elementos'][2];
        self::assertSame(['COG-MEM'], array_column($requisito['grafo']['aristas'], 'origen'));
        self::assertSame('C02', $requisito['grafo']['aristas'][0]['destino']);
    }

    public function test_un_soporte_de_otro_residente_no_se_publica_en_el_mapa(): void
    {
        $perfil = $this->perfil();
        $perfil['dominios']['COG-MEM']['valoraciones'][0]['soportes'][0]['residente'] = 'RES_B';
        $this->expectException(DomainException::class);
        (new ConexionesResidenteVisible)->presentar('RES_A', $perfil);
    }

    public function test_sin_perfil_puede_mostrar_aplicaciones_propias_sin_inventar_conclusiones(): void
    {
        $captura = ['cod_residente' => 'RES_A', 'cod_aplicacion' => 'AP_1', 'nombre_instrumento' => 'Registro sintético',
            'version_instrumento' => null, 'autor' => 'Profesional sintético', 'estado_original' => 'COMPLETA',
            'instrumento_actual' => ['tipo' => 'FUNCIONAL']];
        $dto = (new ConexionesResidenteVisible)->presentar('RES_A', null, ['capturas' => [$captura]]);
        self::assertTrue($dto['disponible']);
        self::assertSame('fuentes', $dto['elementos'][0]['grupo']);
        self::assertSame(0, $dto['valoraciones']);
        self::assertCount(1, $dto['elementos'][0]['grafo']['aristas']);
        self::assertStringNotContainsString('Alteración sustentada', json_encode($dto, JSON_THROW_ON_ERROR));
        $captura['cod_residente'] = 'RES_B';
        $this->expectException(DomainException::class);
        (new ConexionesResidenteVisible)->presentar('RES_A', null, ['capturas' => [$captura]]);
    }
}
