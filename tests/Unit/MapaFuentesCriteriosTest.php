<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Aplicacion\Servicios\ContratoFuentesPetersen as C;
use App\Modulos\ApoyoDecision\Presentacion\Livewire\Paginas\CapturaPetersenPanel;
use MotorExperto\inferencia\EvaluarPetersenInstitucional as E;
use PHPUnit\Framework\TestCase;

final class MapaFuentesCriteriosTest extends TestCase
{
    public function test_mapa_de_cinco_areas_y_requisitos_reutiliza_el_contrato_del_formulario(): void
    {
        $mapa = C::mapa();
        self::assertCount(10, $mapa);
        self::assertSame(C::TIPOS_SOPORTE, CapturaPetersenPanel::TIPOS);
        self::assertSame(array_keys(CapturaPetersenPanel::ELEMENTOS), array_values(array_diff(array_keys($mapa), ['C02'])));
        foreach ($mapa as $elemento => $detalle) {
            foreach ($detalle['soportes'] as $tipo => $soporte) {
                self::assertSame(C::admitidas($elemento, $tipo), $soporte['fuentes_admitidas']);
                foreach ($soporte['fuentes_admitidas'] as $fuente) {
                    self::assertArrayHasKey($fuente, C::FUENTES);
                }
            }
        }
        foreach (E::DOMINIOS as $dominio) {
            self::assertSame(['MEDICION'], array_keys($mapa[$dominio]['soportes']));
            self::assertSame(['cod_aplicacion'], $mapa[$dominio]['soportes']['MEDICION']['fuentes_admitidas']);
            self::assertSame(['alcance_dominio', 'contexto_interpretado'], $mapa[$dominio]['comprobaciones']);
        }
    }

    public function test_c02_es_derivado_y_las_fuentes_contextuales_no_se_convierten_en_medicion(): void
    {
        $mapa = C::mapa();
        self::assertSame('DERIVADO_DE_DOMINIOS', $mapa['C02']['origen']);
        self::assertSame(E::DOMINIOS, $mapa['C02']['derivado_de']);
        self::assertSame([], $mapa['C02']['soportes']);
        self::assertArrayNotHasKey('C02', CapturaPetersenPanel::ELEMENTOS);
        self::assertSame(['HISTORIA_PREVIA', 'CORROBORACION'], array_keys($mapa['C01']['soportes']));
        self::assertSame(['cod_control_iadl'], $mapa['C03']['soportes']['IADL']['fuentes_admitidas']);
        self::assertSame(['cod_valoracion_psicologica', 'cod_antecedente', 'cod_diagnostico', 'cod_documento_clinico'], $mapa['C04']['soportes']['DIFERENCIAL']['fuentes_admitidas']);
        self::assertSame(['cod_valoracion_revisada'], $mapa['C05']['soportes']['DOMINIO_REVISADO']['fuentes_admitidas']);
        foreach ($mapa as $detalle) {
            foreach ($detalle['soportes'] as $soporte) {
                self::assertNotContains('cod_prescripcion', $soporte['fuentes_admitidas']);
                self::assertNotContains('cod_signo_vital', $soporte['fuentes_admitidas']);
            }
        }
    }
}
