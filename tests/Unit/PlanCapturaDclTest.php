<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\PlanCapturaDcl;
use DateTimeImmutable;
use DomainException;
use MotorExperto\instrumentos\GuiaAdquisicionDcl;
use MotorExperto\hechos\ContextoEjecucion;
use MotorExperto\inferencia\EvaluarPetersenInstitucional as Motor;
use PHPUnit\Framework\TestCase;

final class PlanCapturaDclTest extends TestCase
{
    private function salidaVacia(): array
    {
        return (new Motor)->evaluar(new ContextoEjecucion('PLAN-TEST', 'SINTETICO-PLAN', 'VERSION-PLAN',
            new DateTimeImmutable('2026-10-31T12:00:00-04:00')), []);
    }

    public function test_sin_datos_organiza_nueve_faltantes_reales_y_no_pide_capturar_c02(): void
    {
        $salida = $this->salidaVacia();
        $copia = $salida;
        $plan = (new PlanCapturaDcl)->desdePetersen($salida);
        self::assertSame($copia, $salida);
        self::assertCount(5, $plan['pendientes']);
        $elementos = array_merge(...array_column($plan['pendientes'], 'por_revisar'));
        self::assertCount(9, $elementos);
        self::assertNotContains('C02', array_column($elementos, 'elemento'));
        self::assertFalse($plan['concede_permiso_registro']);
        self::assertArrayNotHasKey('diagnostico', $plan);
        self::assertArrayNotHasKey('persistido', $plan);
        self::assertSame('ABSTENCION_INFORMACION_INSUFICIENTE', $salida['impedimentos'][0]);
    }

    public function test_negativo_explicito_no_se_reclasifica_como_dato_ausente_o_favorable(): void
    {
        $salida = $this->salidaVacia();
        foreach ($salida['dominios'] as &$dominio) {
            $dominio['estado'] = 'P';
        }
        unset($dominio);
        foreach ($salida['requisitos'] as &$requisito) {
            $requisito['estado'] = 'N';
        }
        unset($requisito);
        $plan = (new PlanCapturaDcl)->desdePetersen($salida);
        self::assertSame([], $plan['pendientes']);
        self::assertSame('SIN_FALTANTES_DE_ESTADO_EN_ESTA_SALIDA', $plan['estado']);
        self::assertSame('N', $salida['requisitos']['C04']['estado']);
        self::assertNull($salida['patron']);
    }

    public function test_contradiccion_y_no_interpretable_reciben_acciones_distintas_sin_borrar_motivos(): void
    {
        $salida = $this->salidaVacia();
        $salida['dominios']['COG-MEM']['estado'] = 'X';
        $salida['dominios']['COG-ATE']['estado'] = 'I';
        $salida['dominios']['COG-MEM']['motivos'] = ['MOTIVO-ORIGINAL'];
        $plan = (new PlanCapturaDcl)->desdePetersen($salida);
        $acciones = array_column(array_merge(...array_column($plan['pendientes'], 'por_revisar')), 'accion', 'elemento');
        self::assertStringContainsString('contradictorios', $acciones['COG-MEM']);
        self::assertStringContainsString('interpretar', $acciones['COG-ATE']);
        self::assertSame(['MOTIVO-ORIGINAL'], $salida['dominios']['COG-MEM']['motivos']);
    }

    public function test_estado_incompatible_no_se_convierte_en_un_plan_aparentemente_completo(): void
    {
        $salida = $this->salidaVacia();
        $salida['dominios']['COG-MEM']['estado'] = 'S';
        $this->expectException(DomainException::class);
        (new PlanCapturaDcl)->desdePetersen($salida);
    }

    public function test_salida_incompleta_falla_explicitamente(): void
    {
        $salida = $this->salidaVacia();
        unset($salida['requisitos']['C03']);
        $this->expectException(DomainException::class);
        (new PlanCapturaDcl)->desdePetersen($salida);
    }

    public function test_no_acepta_resultado_de_otro_contrato(): void
    {
        $this->expectException(DomainException::class);
        (new PlanCapturaDcl)->desdePetersen(['contrato' => 'OBS-SNAP-2']);
    }

    public function test_guia_tiene_fuentes_identificadas_y_solo_orienta_campos_existentes(): void
    {
        $g = new GuiaAdquisicionDcl;
        $contrato = $g->contrato();
        self::assertSame('PERSONAS_MAYORES_DE_60_ANIOS', $contrato['poblacion_objetivo']);
        self::assertCount(6, $contrato['secciones']);
        foreach ($contrato['secciones'] as $seccion) {
            self::assertNotEmpty($seccion['registrar']);
            foreach ($seccion['fuentes'] as $id) {
                self::assertArrayHasKey($id, $g->fuentes());
                self::assertStringStartsWith('https://', $g->fuentes()[$id]['url']);
            }
        }
        self::assertArrayNotHasKey('umbrales', $contrato);
        self::assertArrayNotHasKey('reactivos', $contrato);
    }
}
