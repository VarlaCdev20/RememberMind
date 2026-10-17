<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Repositories;

use App\SistemaExperto\Domain\Entities\Cognicion\VectorCognitivo;
use App\SistemaExperto\Domain\Entities\Conocimiento\RedSemantica;
use App\SistemaExperto\Domain\Entities\Conocimiento\ReglaExperta;
use App\SistemaExperto\Domain\Entities\Dcl\ResultadoClasificacionDcl;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\Repositories\Conocimiento\RedSemanticaRepositoryInterface;
use App\SistemaExperto\Domain\Repositories\Conocimiento\ReglaExpertaRepositoryInterface;
use App\SistemaExperto\Domain\Repositories\Evaluaciones\EvaluacionExpertaRepositoryInterface;
use App\SistemaExperto\Domain\Repositories\Hechos\EvidenciaRepositoryInterface;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * TÍTULO: Contratos abstractos de las cuatro interfaces, sin persistencia simulada.
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
final class ContratoRepositoriosTest extends TestCase
{
    /**
     * Verifica interfaces puras, namespaces y documentación de cada método.
     *
     * @return void
     *
     * No recibe parámetros ni ejecuta implementaciones de persistencia.
     */
    public function test_cuatro_puertos_documentados_sin_implementaciones(): void
    {
        $interfaces = [ReglaExpertaRepositoryInterface::class, RedSemanticaRepositoryInterface::class,
            EvidenciaRepositoryInterface::class, EvaluacionExpertaRepositoryInterface::class];
        foreach ($interfaces as $interfaz) {
            $r = new ReflectionClass($interfaz);
            self::assertTrue($r->isInterface());
            self::assertStringStartsWith('App\\SistemaExperto\\Domain\\Repositories\\', $r->getName());
            foreach (['TÍTULO:', 'CAPA:', 'SUBSISTEMA:', 'RESPONSABILIDAD:', 'ENTRADAS:', 'SALIDAS:', 'RESTRICCIONES:', 'TRAZABILIDAD:'] as $campo) {
                self::assertStringContainsString($campo, $r->getDocComment());
            }
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
     *
     * @return void
     *
     * No recibe parámetros; no acredita funcionamiento de un adaptador futuro.
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
