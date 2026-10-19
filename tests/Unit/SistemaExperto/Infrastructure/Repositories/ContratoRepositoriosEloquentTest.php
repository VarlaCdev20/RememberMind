<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Infrastructure\Repositories;

use MotorExperto\dominio\orion\Repositories\Conocimiento\RedSemanticaRepositoryInterface;
use MotorExperto\dominio\orion\Repositories\Conocimiento\ReglaExpertaRepositoryInterface;
use MotorExperto\dominio\orion\Repositories\Evaluaciones\EvaluacionExpertaRepositoryInterface;
use MotorExperto\dominio\orion\Repositories\Hechos\EvidenciaRepositoryInterface;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\EvaluacionExpertaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\EvidenciaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\RedSemanticaEloquentRepository;
use App\Modulos\ApoyoDecision\Infraestructura\Persistencia\Repositorios\ReglaExpertaEloquentRepository;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * TÍTULO: Compatibilidad y documentación de los cuatro puertos Eloquent.
 * CAPA: Infrastructure - Repositories (pruebas unitarias).
 * ¿PARA QUÉ SIRVE? Conserva las interfaces existentes sin ampliar Domain.
 * ¿QUÉ INFORMACIÓN RECIBE? Clases reales y firmas de los contratos aprobados.
 * ¿QUÉ HACE? Comprueba implementaciones, tipos, readonly y PHPDoc en español.
 * ¿QUÉ DEVUELVE? Aserciones sobre el contrato público solicitado.
 * ¿QUÉ NO DEBE HACER? No sustituye las pruebas de consultas y bases desechables.
 */
final class ContratoRepositoriosEloquentTest extends TestCase
{
    /** Verifica los cuatro contratos y su documentación sin BDD; sin parámetros ni excepciones esperadas. */
    public function test_las_cuatro_clases_conservan_las_firmas_del_dominio(): void
    {
        $clases = [
            ReglaExpertaEloquentRepository::class => ReglaExpertaRepositoryInterface::class,
            RedSemanticaEloquentRepository::class => RedSemanticaRepositoryInterface::class,
            EvidenciaEloquentRepository::class => EvidenciaRepositoryInterface::class,
            EvaluacionExpertaEloquentRepository::class => EvaluacionExpertaRepositoryInterface::class,
        ];
        $archivos = [];
        foreach ($clases as $clase => $puerto) {
            $reflexion = new ReflectionClass($clase);
            self::assertTrue($reflexion->implementsInterface($puerto));
            self::assertTrue($reflexion->isFinal());
            self::assertTrue($reflexion->isReadOnly());
            self::assertSame('App\\Modulos\\ApoyoDecision\\Infraestructura\\Persistencia\\Repositorios', $reflexion->getNamespaceName());
            $archivo = $reflexion->getFileName();
            $archivos[] = basename($archivo);
            self::assertStringStartsWith("<?php\n\ndeclare(strict_types=1);", str_replace("\r\n", "\n", file_get_contents($archivo)));
            $doc = $reflexion->getDocComment();
            self::assertNotFalse($doc);
            foreach (['TÍTULO:', 'CAPA:', '¿PARA QUÉ SIRVE?', '¿QUÉ INFORMACIÓN RECIBE?',
                '¿QUÉ HACE?', '¿QUÉ DEVUELVE?', '¿QUÉ NO DEBE HACER?'] as $seccion) {
                self::assertStringContainsString($seccion, $doc);
            }
            foreach ($reflexion->getMethods(ReflectionMethod::IS_PUBLIC) as $metodo) {
                self::assertNotFalse($metodo->getDocComment());
                self::assertStringContainsString('@throws', $metodo->getDocComment());
                foreach ($metodo->getParameters() as $parametro) {
                    self::assertNotNull($parametro->getType());
                    self::assertStringContainsString('$'.$parametro->getName(), $metodo->getDocComment());
                }
                if (! $metodo->isConstructor()) {
                    $original = (new ReflectionClass($puerto))->getMethod($metodo->getName());
                    self::assertSame((string) $original->getReturnType(), (string) $metodo->getReturnType());
                    self::assertSame(count($original->getParameters()), count($metodo->getParameters()));
                    foreach ($original->getParameters() as $indice => $parametro) {
                        self::assertSame((string) $parametro->getType(), (string) $metodo->getParameters()[$indice]->getType());
                    }
                }
            }
        }
        $reales = array_map('basename', glob(dirname($archivo).'/*.php'));
        sort($reales);
        sort($archivos);
        self::assertSame($archivos, $reales);
    }
}
