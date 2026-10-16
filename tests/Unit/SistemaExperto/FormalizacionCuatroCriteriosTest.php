<?php

namespace Tests\Unit\SistemaExperto;

use PHPUnit\Framework\TestCase;

/** Solo estructura documental: NO es un banco de validación clínica G12. */
final class FormalizacionCuatroCriteriosTest extends TestCase
{
    private function contrato(): array
    {
        $ruta = dirname(__DIR__, 3).'/docs/sistema-experto/FORMALIZACION_COG4_V1.json';
        return json_decode((string) file_get_contents($ruta), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_cuatro_criterios_y_15_componentes_estan_formalizados_sin_activacion(): void
    {
        $p = $this->contrato();
        $this->assertFalse($p['conocimiento_clinico_activo']);
        $this->assertFalse($p['autorizacion_inferencial']);
        $this->assertFalse($p['validacion_clinica']);
        $this->assertSame('FC4-20261016-v1.0-TECNICA', $p['version']);
        $this->assertSame(['COG-ATE', 'COG-EJE', 'COG-LEN', 'COG-VIS'], array_column($p['criterios'], 'codigo'));
        $this->assertSame([3, 5, 5, 2], array_map(
            static fn (array $c) => count($c['subcomponentes']), $p['criterios']
        ));
        $this->assertSame([6, 5, 6, 5], array_map(
            static fn (array $c) => count($c['evidencias']), $p['criterios']
        ));
        $this->assertSame(16, array_sum(array_map(
            static fn (array $c) => count($c['evaluabilidad']) + 1, $p['criterios']
        )));
    }

    public function test_ninguna_regla_candidata_es_ejecutable_o_emite_consecuencias(): void
    {
        $p = $this->contrato();
        $ids = [];
        foreach ($p['criterios'] as $c) {
            $this->assertSame('INACTIVO', $c['estado_tecnico']);
            $this->assertFalse($c['dictamen_firmado']);
            $this->assertSame([], $c['resultados_clinicos']);
            $this->assertSame('PENDIENTE_G12', $c['casos_referencia_profesional']);
            foreach ($c['subcomponentes'] as $s) {
                $this->assertArrayNotHasKey($s['codigo'], $ids);
                $ids[$s['codigo']] = true;
                $this->assertFalse($s['regla']['ejecutable']);
                $this->assertSame('PENDIENTE_G08', $s['instrumentacion']);
                $this->assertCount(6, $s['regla']['antecedentes_positivos_AND']);
                $this->assertStringStartsWith('REG-'.$s['codigo'], $s['regla']['codigo']);
            }
            foreach ($c['evidencias'] as $e) {
                $this->assertFalse($e['autorizada']);
                $this->assertSame('PENDIENTE_G06', $e['mapeo']);
            }
            foreach ($c['evaluabilidad'] as $ev) {
                $this->assertFalse($ev['resultados_permitidos']);
                $this->assertSame('PENDIENTE_G07', $ev['estado']);
            }
            $this->assertSame('PENDIENTE_G07', $c['modificador']['estado']);
        }
        $this->assertCount(15, $ids);
    }

    public function test_no_se_reutiliza_resolvedor_de_memoria_ni_permiten_datos_reales(): void
    {
        $p = $this->contrato();
        $this->assertContains('sin copiar D-131 de COG-MEM', $p['restricciones']);
        $this->assertContains('sin datos reales de residentes', $p['restricciones']);
        $this->assertContains('sin mapeos clínicos activos', $p['restricciones']);
        $this->assertContains('sin duplicar episodios', $p['restricciones']);
        foreach ($p['criterios'] as $c) {
            $this->assertSame(
                'MANTENER_ID_EVIDENCIA_Y_EPISODIO_UNICOS; SIN_SUMAR_SOPORTE_INDEPENDIENTE',
                $c['politica_solapamientos']
            );
            $this->assertContains('no_emision', $c['registro_traza']);
        }
    }
}
