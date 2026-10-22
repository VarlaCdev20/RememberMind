<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Presentacion\Soporte\MotivoPetersenVisible;
use PHPUnit\Framework\TestCase;

final class MotivoPetersenVisibleTest extends TestCase
{
    public function test_traduce_motivos_sin_exponer_identificadores_de_fuente_o_campos_internos(): void
    {
        self::assertSame('Faltan valoraciones originales o condiciones de interpretación suficientes.',
            MotivoPetersenVisible::explicar('ABSTENCION_INFORMACION_INSUFICIENTE'));
        self::assertSame('Falta documentar la oportunidad real de realizar la actividad en la valoración original.',
            MotivoPetersenVisible::explicar('CONDICION_ORIGINAL_NO_ACREDITADA:oportunidad_adecuada'));
        self::assertSame('La fuente no estaba vigente en la fecha considerada.',
            MotivoPetersenVisible::explicar('FUENTE_FUERA_DE_CORTE_O_NO_VIGENTE:COD_APL_123'));
    }
}
