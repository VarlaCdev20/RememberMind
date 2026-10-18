<?php

namespace Tests\Feature;

use App\Modulos\ApoyoDecision\Aplicacion\DTOs\PresentarInformeCognitivo;
use DomainException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Prueba el vocabulario de seis estados G09 sin cargar o ejecutar el motor experimental. */
class PresentacionG09AisladaTest extends TestCase
{
    /** Catálogo literal del EvaluadorG09Experimental de pr15-ci-utf8, inspeccionado el 17/10/2026. */
    public static function estados(): array
    {
        return [['SIN_EVIDENCIA_ADMISIBLE'], ['SOLO_SENAL_CONTEXTO'], ['EVALUABLE_SIN_SOPORTE'],
            ['HALLAZGO_ACOTADO'], ['MULTIPLES_HALLAZGOS_PENDIENTES_DE_RESOLUCION'], ['EVIDENCIA_CONTRADICTORIA']];
    }

    /** Cada estado permanece reconocible y expresamente no publicable. */
    #[DataProvider('estados')]
    public function test_conserva_cada_estado_tecnico_sin_convertirlo_en_diagnostico(string $estado): void
    {
        $entrada = ['uso' => 'PRUEBA_TECNICA_NO_CLINICA', 'version' => 'G09-EXP-20261017', 'criterio' => 'COG-ATE', 'estado_tecnico' => $estado];
        $salida = app(PresentarInformeCognitivo::class)->presentarG09Prueba($entrada);
        $this->assertSame($estado, $salida['estado_original']);
        $this->assertFalse($salida['publicable']);
        $this->assertStringContainsString('Sin diagnóstico', $salida['limites']);
        $this->assertSame($estado, $entrada['estado_tecnico']);
        if ($estado === 'EVALUABLE_SIN_SOPORTE') {
            $this->assertStringContainsString('No equivale a preservación', $salida['alcance']);
        }
    }

    /** Rechaza intentos de atribuir el catálogo a uso asistencial o fuera de testing. */
    public function test_rechaza_presentacion_g09_asistencial(): void
    {
        $this->expectException(DomainException::class);
        app(PresentarInformeCognitivo::class)->presentarG09Prueba(['uso' => 'ASISTENCIAL', 'version' => 'G09-EXP-20261017', 'criterio' => 'COG-ATE', 'estado_tecnico' => 'HALLAZGO_ACOTADO']);
    }

    /** Un entorno distinto de testing no permite siquiera presentar el informe experimental. */
    public function test_rechaza_g09_fuera_de_testing(): void
    {
        $this->app->instance('env', 'local');
        $this->expectException(DomainException::class);
        app(PresentarInformeCognitivo::class)->presentarG09Prueba(['uso' => 'PRUEBA_TECNICA_NO_CLINICA', 'version' => 'G09-EXP-20261017', 'criterio' => 'COG-ATE', 'estado_tecnico' => 'HALLAZGO_ACOTADO']);
    }

    /** El presentador rechaza literales no reconocidos en vez de aplicar una etiqueta favorable por defecto. */
    public function test_rechaza_estado_g09_desconocido(): void
    {
        $this->expectException(DomainException::class);
        app(PresentarInformeCognitivo::class)->presentarG09Prueba(['uso' => 'PRUEBA_TECNICA_NO_CLINICA', 'version' => 'G09-EXP-20261017', 'criterio' => 'COG-ATE', 'estado_tecnico' => 'NORMAL']);
    }
}
