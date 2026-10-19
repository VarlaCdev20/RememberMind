<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Services;

use MotorExperto\dominio\orion\Services\Compuertas\EvaluadorConfusoresService;
use MotorExperto\dominio\orion\Services\Compuertas\EvaluadorEvaluabilidadService;
use MotorExperto\dominio\orion\Services\Compuertas\VerificadorEvidenciaService;
use MotorExperto\dominio\orion\Services\Dcl\ClasificadorPetersenService;
use MotorExperto\dominio\orion\Services\Dcl\EvaluadorRequisitosDclService;
use MotorExperto\dominio\orion\Services\Explicacion\GeneradorExplicacionService;
use MotorExperto\dominio\orion\Services\Inferencia\EvaluadorReglasService;
use MotorExperto\dominio\orion\Services\Inferencia\IntegradorDominiosService;
use MotorExperto\dominio\orion\Services\Inferencia\MotorInferenciaService;
use MotorExperto\dominio\orion\Services\Semantica\EvaluadorRedSemanticaService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * TÍTULO: Documentación y frontera de los diez servicios solicitados.
 *
 * CAPA: Domain (verificación unitaria).
 *
 * SUBSISTEMA: Repositorios y compuertas.
 *
 * RESPONSABILIDAD:
 * Verificar contratos explícitos con fuentes artificiales y objetos reales.
 *
 * ENTRADAS:
 * Identidades, declaraciones y referencias ficticias de prueba.
 *
 * SALIDAS:
 * Objetos sintéticos o aserciones sobre preservación y rechazo.
 *
 * RESTRICCIONES:
 * No inicia Laravel, no consulta persistencia ni acredita validación clínica.
 *
 * TRAZABILIDAD:
 * Reglamento maestro, Fases C/D e invariantes R1–R10.
 */
final class ContratoServiciosTest extends TestCase
{
    /**
     * Verifica documentación, tipos e inmutabilidad de los colaboradores.
     *
     * @return void
     *
     * No recibe parámetros ni ejecuta componentes fuera del alcance C/D.
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
            foreach (['TÍTULO:', 'CAPA:', 'SUBSISTEMA:', 'RESPONSABILIDAD:', 'ENTRADAS:', 'SALIDAS:', 'RESTRICCIONES:', 'TRAZABILIDAD:'] as $campo) {
                self::assertStringContainsString($campo, $r->getDocComment());
            }
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
