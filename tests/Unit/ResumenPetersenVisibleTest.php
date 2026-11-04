<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Modulos\ApoyoDecision\Presentacion\Soporte\ResumenPetersenVisible;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ResumenPetersenVisibleTest extends TestCase
{
    public function test_un_requisito_negativo_no_se_presenta_como_falta_de_medicion(): void
    {
        $resumen = ResumenPetersenVisible::presentar(['patron' => null,
            'estado' => 'SIN_PATRON_CLASIFICABLE_POR_REQUISITO',
            'impedimentos' => ['SIN_PATRON_CLASIFICABLE_POR_REQUISITO']], true);
        self::assertSame('No se produjo un patrón orientativo', $resumen['titulo']);
        self::assertSame('Sin patrón clasificable', $resumen['estado']);
        self::assertFalse($resumen['faltan_mediciones']);
    }

    public function test_una_revision_diferencial_negativa_no_inventa_un_impedimento_clinico(): void
    {
        $resumen = ResumenPetersenVisible::presentar(['patron' => null,
            'estado' => 'ABSTENCION_EVALUACION_DIFERENCIAL',
            'requisitos' => ['C04' => ['estado' => 'N']]], false);
        self::assertStringNotContainsString('impedimento sin resolver', $resumen['explicacion']);
        self::assertFalse($resumen['faltan_mediciones']);
    }

    #[DataProvider('bloqueosDistintosDeMediciones')]
    public function test_no_atribuye_otros_bloqueos_a_falta_de_mediciones(string $estado): void
    {
        $resumen = ResumenPetersenVisible::presentar(['patron' => null, 'estado' => $estado], false);
        self::assertFalse($resumen['faltan_mediciones']);
        self::assertNotSame('Perfil pendiente de valoración', $resumen['estado']);
    }

    public static function bloqueosDistintosDeMediciones(): array
    {
        return array_map(static fn ($estado) => [$estado], ['ABSTENCION_CONTRADICCION',
            'ABSTENCION_SIN_GOBIERNO_PETERSEN_VIGENTE', 'ABSTENCION_SIN_REGLA_PETERSEN_HABILITADA', 'ESTADO_NO_RECONOCIDO']);
    }

    public function test_faltantes_y_observaciones_no_se_convierten_en_patron(): void
    {
        $resumen = ResumenPetersenVisible::presentar(['patron' => null,
            'estado' => 'ABSTENCION_INFORMACION_INSUFICIENTE'], true);
        self::assertTrue($resumen['faltan_mediciones']);
        self::assertSame('Hay observaciones cognitivas; el perfil aún no está determinado', $resumen['titulo']);
    }
}
