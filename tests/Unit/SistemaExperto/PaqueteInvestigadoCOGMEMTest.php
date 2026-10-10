<?php

namespace Tests\Unit\SistemaExperto;

use App\Backend\Modulos\SistemaExperto\Conocimiento\MapeadorSemantico;
use App\Backend\Modulos\SistemaExperto\Conocimiento\PaqueteInvestigadoCOGMEM as P;
use App\Backend\Modulos\SistemaExperto\DTO\ContextoEjecucion;
use App\Backend\Modulos\SistemaExperto\DTO\FuenteBrutaCOGMEM;
use App\Backend\Modulos\SistemaExperto\MemoriaTrabajo\MemoriaTrabajo;
use App\Backend\Modulos\SistemaExperto\Servicios\MotorExperto;
use DateTimeImmutable;
use DomainException;
use PHPUnit\Framework\TestCase;

final class PaqueteInvestigadoCOGMEMTest extends TestCase
{
    public function test_propuesta_conserva_literales_sin_mapear_falsos_nulos_o_no_valorable(): void
    {
        $p = new P;
        $tablas = $p->tablas('USR_TEST', '2026-10-08 12:00:00');
        $this->assertCount(6, $tablas['mapeos_valores_fuente']);
        $this->assertSame(['CONSERVADA', 'ALTERACION_LEVE', 'ALTERADA'], array_values(array_unique(array_column($tablas['mapeos_valores_fuente'], 'valor_fuente_exacto'))));
        $this->assertSame(['memoria_reciente', 'memoria_remota'], array_column($tablas['mapeos_variables_fuente'], 'campo_valor'));
        $this->assertEmpty($tablas['reglas_expertas']);
        $this->assertEmpty($tablas['consecuencias_regla_experta']);
        $this->assertFalse($p->paquete('USR_TEST', '2026-10-08 12:00:00')->soloPruebasTecnicas);
    }

    public function test_paquete_pendiente_no_convierte_registros_en_evidencia_clinica(): void
    {
        $p = new P;
        $paquete = $p->paquete('USR_TEST', '2026-10-08 12:00:00');
        $c = new ContextoEjecucion('EVAL_TEST', 'RES_TEST', P::VERSION, new DateTimeImmutable('2026-10-08T12:00:00-04:00'));
        foreach (['CONSERVADA', 'ALTERADA', 'NO_VALORABLE', null, false, 'conservada', ' CONSERVADA'] as $literal) {
            $f = new FuenteBrutaCOGMEM($p->codigo('FD', 'SRC-CONTROL-COG'), 'controles_cognitivos', 'memoria_reciente',
                'CC_TEST', 'RES_TEST', new DateTimeImmutable('2026-10-08T11:00:00-04:00'), $literal, $literal, 'PER_TEST', true, true);
            $e = (new MapeadorSemantico)->mapear($paquete, $c, $f, $p->codigo('MV', 'memoria_reciente'));
            $this->assertSame($literal, $e->fuente->valorOriginal);
            $this->assertNull($e->valor);
            $this->assertFalse($e->utilizable());
        }
        $this->expectException(DomainException::class);
        (new MotorExperto)->evaluarCOGMEM($paquete, new MemoriaTrabajo($c), $p->codigo('NS', 'COG-MEM'), []);
    }
}
