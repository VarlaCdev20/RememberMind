<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Repositories;

use MotorExperto\dominio\orion\Entities\Cognicion\VectorCognitivo;
use MotorExperto\dominio\orion\Entities\Conocimiento\RedSemantica;
use MotorExperto\dominio\orion\Entities\Conocimiento\ReglaExperta;
use MotorExperto\dominio\orion\Entities\Dcl\ResultadoClasificacionDcl;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use MotorExperto\dominio\orion\Repositories\Conocimiento\RedSemanticaRepositoryInterface;
use MotorExperto\dominio\orion\Repositories\Conocimiento\ReglaExpertaRepositoryInterface;
use MotorExperto\dominio\orion\Repositories\Evaluaciones\EvaluacionExpertaRepositoryInterface;
use MotorExperto\dominio\orion\Repositories\Hechos\EvidenciaRepositoryInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/** Verificar contratos explícitos con fuentes artificiales y objetos reales. */
final class ContratoRepositoriosTest extends TestCase
{
    /**
     * Verifica interfaces puras, namespaces y documentación de cada método.
     * @return void
     */
    public function test_cuatro_puertos_documentados_sin_implementaciones(): void
    {
        $interfaces = [ReglaExpertaRepositoryInterface::class, RedSemanticaRepositoryInterface::class,
            EvidenciaRepositoryInterface::class, EvaluacionExpertaRepositoryInterface::class];
        foreach ($interfaces as $interfaz) {
            $r = new ReflectionClass($interfaz);
            self::assertTrue($r->isInterface());
            self::assertStringStartsWith('MotorExperto\\dominio\\orion\\Repositories\\', $r->getName());
            $doc = $r->getDocComment();
            self::assertIsString($doc);
            self::assertMatchesRegularExpression('/\p{L}{3}/u', $doc);
            self::assertLessThanOrEqual(240, mb_strlen($doc));
            foreach ($r->getMethods() as $metodo) {
                self::assertTrue($metodo->isAbstract());
                self::assertNotNull($metodo->getReturnType());
                self::assertNotFalse($metodo->getDocComment());
                foreach ($metodo->getParameters() as $parametro) {
                    self::assertNotNull($parametro->getType());
                    self::assertFalse($parametro->isOptional(), 'No se permite una versión o fecha implícita.');
                }
            }
        }
    }

    /**
     * Verifica tipos de dominio y consultas con versión y corte explícitos.
     * @return void
     */
    public function test_tipos_de_dominio_y_contexto_explicito(): void
    {
        $reglas = new ReflectionClass(ReglaExpertaRepositoryInterface::class);
        self::assertSame(ReglaExperta::class, $reglas->getMethod('obtener')->getReturnType()->getName());
        self::assertTrue($reglas->getMethod('obtener')->getReturnType()->allowsNull());
        $red = new ReflectionClass(RedSemanticaRepositoryInterface::class);
        self::assertSame(RedSemantica::class, $red->getMethod('porVersion')->getReturnType()->getName());
        self::assertTrue($red->getMethod('porVersion')->getReturnType()->allowsNull());
        $evidencia = (new ReflectionClass(EvidenciaRepositoryInterface::class))->getMethod('paraEvaluacion');
        self::assertSame(MemoriaTrabajo::class, $evidencia->getReturnType()->getName());
        self::assertSame(['evaluacionId', 'residenteId', 'versionConocimiento', 'fechaCorte'],
            array_map(static fn ($p): string => $p->getName(), $evidencia->getParameters()));
        self::assertSame(DateTimeImmutable::class, $evidencia->getParameters()[3]->getType()->getName());
        $evaluaciones = new ReflectionClass(EvaluacionExpertaRepositoryInterface::class);
        self::assertSame(VectorCognitivo::class, $evaluaciones->getMethod('guardar')->getParameters()[0]->getType()->getName());
        self::assertSame(ResultadoClasificacionDcl::class, $evaluaciones->getMethod('guardarClasificacion')->getParameters()[0]->getType()->getName());
        self::assertSame(['evaluacionId', 'residenteId', 'versionConocimiento', 'fechaCorte', 'versionContrato'],
            array_map(static fn ($p): string => $p->getName(), $evaluaciones->getMethod('obtenerClasificacion')->getParameters()));
    }

}
