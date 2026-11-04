<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Services;

use MotorExperto\dominio\Services\Compuertas\EvaluadorConfusoresService;
use MotorExperto\dominio\Services\Compuertas\EvaluadorEvaluabilidadService;
use MotorExperto\dominio\Services\Compuertas\VerificadorEvidenciaService;
use MotorExperto\dominio\Services\Dcl\ClasificadorPetersenService;
use MotorExperto\dominio\Services\Dcl\EvaluadorRequisitosDclService;
use MotorExperto\dominio\Services\Explicacion\GeneradorExplicacionService;
use MotorExperto\dominio\Services\Inferencia\EvaluadorReglasService;
use MotorExperto\dominio\Services\Inferencia\IntegradorDominiosService;
use MotorExperto\dominio\Services\Inferencia\MotorInferenciaService;
use MotorExperto\dominio\Services\Semantica\EvaluadorRedSemanticaService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/** Verificar contratos explícitos con fuentes artificiales y objetos reales. */
final class ContratoServiciosTest extends TestCase
{
    /**
     * Verifica documentación, tipos e inmutabilidad de los colaboradores.
     * @return void
     */
    public function test_servicios_puros_tipados_y_documentados(): void
    {
        foreach ([VerificadorEvidenciaService::class, EvaluadorConfusoresService::class,
            EvaluadorEvaluabilidadService::class, EvaluadorRedSemanticaService::class,
            MotorInferenciaService::class, EvaluadorReglasService::class, IntegradorDominiosService::class,
            ClasificadorPetersenService::class, EvaluadorRequisitosDclService::class, GeneradorExplicacionService::class] as $clase) {
            $r = new ReflectionClass($clase);
            self::assertTrue($r->isFinal());
            self::assertTrue($r->isReadOnly());
            self::assertStringStartsWith("<?php\n\ndeclare(strict_types=1);", file_get_contents($r->getFileName()));
            $doc = $r->getDocComment();
            self::assertIsString($doc);
            self::assertMatchesRegularExpression('/\p{L}{3}/u', $doc);
            self::assertLessThanOrEqual(240, mb_strlen($doc));
            foreach ($r->getProperties() as $propiedad) {
                self::assertTrue($propiedad->isReadOnly());
                self::assertNotFalse($propiedad->getDocComment());
            }
            foreach ($r->getMethods() as $metodo) {
                self::assertNotFalse($metodo->getDocComment());
                if (! $metodo->isConstructor()) {
                    self::assertNotNull($metodo->getReturnType());
                }
                foreach ($metodo->getParameters() as $p) {
                    self::assertNotNull($p->getType());
                }
            }
        }
    }
}
