<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\DisponibilidadReglaCatalogo;
use Carbon\CarbonImmutable;
use MotorExperto\conocimiento\ReglasPetersen;
use PHPUnit\Framework\TestCase;

final class DisponibilidadReglaCatalogoTest extends TestCase
{
    private function caso(): array
    {
        $p = (new ReglasPetersen)->construir('VERSION-UNIT', 'AUTOR-UNIT', '2026-10-30 10:00:00');

        return [$p->tabla('reglas_expertas')[0], ['cod_version_modelo' => $p->version,
            'codigo_version' => ReglasPetersen::CODIGO, 'estado' => 'ACTIVO',
            'fecha_hora_vigencia' => '2026-10-30 10:00:00', 'fecha_hora_retiro' => null], $p->declaracion()['tablas'],
            CarbonImmutable::parse('2026-10-31 12:00:00')];
    }

    public function test_pendiente_no_exige_un_aval_para_mostrar_habilitacion_de_metadatos(): void
    {
        [$regla, $version, $tablas, $corte] = $this->caso();
        self::assertSame('PENDIENTE', $regla['estado_revision']);
        $r = (new DisponibilidadReglaCatalogo)->describir($regla, $version, $tablas, $corte);
        self::assertTrue($r['habilitada_en_catalogo']);
        self::assertSame([], $r['motivos']);
        self::assertFalse($r['acredita_ejecucion_clinica']);
    }

    public function test_version_futura_retirada_e_historica_no_aparentan_disponibilidad_operativa(): void
    {
        [$regla, $version, $tablas, $corte] = $this->caso();
        foreach ([['fecha_hora_vigencia' => '2026-11-01 10:00:00'],
            ['fecha_hora_retiro' => '2026-10-31 12:00:00'], ['codigo_version' => 'SIM-HISTORICA']] as $cambio) {
            $r = (new DisponibilidadReglaCatalogo)->describir($regla, array_replace($version, $cambio), $tablas, $corte);
            self::assertFalse($r['habilitada_en_catalogo']);
            self::assertNotEmpty($r['motivos']);
        }
    }

    public function test_dependencia_rechazada_impide_habilitacion_sin_mutar_definiciones(): void
    {
        [$regla, $version, $tablas, $corte] = $this->caso();
        foreach ($tablas['valores_semanticos'] as &$v) {
            $v['estado_aprobacion'] = 'PENDIENTE';
        }
        unset($v);
        $copia = $tablas;
        $r = (new DisponibilidadReglaCatalogo)->describir($regla, $version, $tablas, $corte);
        self::assertFalse($r['habilitada_en_catalogo']);
        self::assertNotEmpty($r['motivos']);
        self::assertSame($copia, $tablas);
    }

    public function test_ausencia_de_consecuencia_no_se_presenta_como_regla_utilizable(): void
    {
        [$regla, $version, $tablas, $corte] = $this->caso();
        $tablas['consecuencias_regla_experta'] = [];
        self::assertFalse((new DisponibilidadReglaCatalogo)->describir($regla, $version, $tablas, $corte)['habilitada_en_catalogo']);
    }
}
