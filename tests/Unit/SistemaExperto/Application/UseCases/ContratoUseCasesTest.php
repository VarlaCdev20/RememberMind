<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Application\UseCases;

use App\SistemaExperto\Application\DTOs\EvidenciasEntradaDTO;
use App\SistemaExperto\Application\DTOs\InformeExpertoDTO;
use App\SistemaExperto\Application\DTOs\ResultadoDclDTO;
use App\SistemaExperto\Application\DTOs\ResultadoInferenciaDTO;
use App\SistemaExperto\Application\UseCases\AnalizarCasoExperimentalUseCase;
use App\SistemaExperto\Application\UseCases\ClasificarDclUseCase;
use App\SistemaExperto\Application\UseCases\EjecutarInferenciaUseCase;
use App\SistemaExperto\Application\UseCases\ExtraerHechosUseCase;
use App\SistemaExperto\Application\UseCases\GenerarInformeUseCase;
use App\SistemaExperto\Application\UseCases\PrepararMemoriaTrabajoUseCase;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use App\SistemaExperto\Domain\Repositories\Hechos\EvidenciaRepositoryInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * TÍTULO: Contratos de los casos de uso ejecutables con el Domain disponible.
 * CAPA: Application (pruebas unitarias).
 * ¿PARA QUÉ SIRVE? Comprueba documentación, tipos y dependencias de CU-01/CU-02/CU-05.
 * ¿QUÉ RECIBE? Las tres clases implementadas sobre contratos existentes.
 * ¿QUÉ HACE? Inspecciona los contratos públicos y las dependencias ejecutables.
 * ¿QUÉ DEVUELVE? Aserciones de arquitectura y documentación.
 * ¿QUÉ NO DEBE HACER? No afirma que los casos pendientes ya puedan ejecutarse.
 * RELACIÓN CON O.R.I.O.N.: Reglamento de Application y límites de la etapa C.
 */
final class ContratoUseCasesTest extends TestCase
{
    /** Verifica PHPDoc, tipos, namespaces y archivos; sin parámetros ni errores de lectura esperados. */
    public function test_casos_disponibles_tipados_inmutables_y_documentados(): void
    {
        foreach ([ExtraerHechosUseCase::class => EvidenciasEntradaDTO::class,
            PrepararMemoriaTrabajoUseCase::class => MemoriaTrabajo::class,
            GenerarInformeUseCase::class => InformeExpertoDTO::class,
            EjecutarInferenciaUseCase::class => ResultadoInferenciaDTO::class,
            ClasificarDclUseCase::class => ResultadoDclDTO::class,
            AnalizarCasoExperimentalUseCase::class => InformeExpertoDTO::class] as $clase => $retorno) {
            $r = new ReflectionClass($clase);
            self::assertTrue($r->isFinal());
            self::assertTrue($r->isReadOnly());
            self::assertSame('App\\SistemaExperto\\Application\\UseCases', $r->getNamespaceName());
            self::assertSame($retorno, $r->getMethod('ejecutar')->getReturnType()->getName());
            self::assertStringStartsWith("<?php\n\ndeclare(strict_types=1);", file_get_contents($r->getFileName()));
            $doc = $r->getDocComment();
            self::assertNotFalse($doc);
            foreach (['TÍTULO:', 'CAPA:', '¿PARA QUÉ SIRVE?', '¿QUÉ RECIBE?', '¿QUÉ HACE?',
                '¿QUÉ DEVUELVE?', '¿QUÉ NO DEBE HACER?', 'RELACIÓN CON O.R.I.O.N.:'] as $apartado) {
                self::assertStringContainsString($apartado, $doc, $clase);
            }
            foreach ($r->getProperties() as $propiedad) {
                self::assertTrue($propiedad->isReadOnly());
                self::assertNotNull($propiedad->getType());
                self::assertNotFalse($propiedad->getDocComment());
            }
            foreach ($r->getMethods() as $metodo) {
                self::assertNotFalse($metodo->getDocComment(), $clase.'::'.$metodo->getName());
                foreach ($metodo->getParameters() as $parametro) {
                    self::assertNotNull($parametro->getType());
                }
            }
        }
    }

    /** Revisa imports ejecutables sin tratar comentarios como dependencias; sin parámetros ni errores esperados. */
    public function test_orquestacion_depende_solo_de_dtos_y_domain(): void
    {
        foreach ([ExtraerHechosUseCase::class, PrepararMemoriaTrabajoUseCase::class, GenerarInformeUseCase::class,
            EjecutarInferenciaUseCase::class, ClasificarDclUseCase::class, AnalizarCasoExperimentalUseCase::class] as $clase) {
            foreach (token_get_all(file_get_contents((new ReflectionClass($clase))->getFileName())) as $token) {
                if (is_array($token) && in_array($token[0], [T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) {
                    self::assertMatchesRegularExpression('/^\\\\?App\\\\SistemaExperto\\\\(Application\\\\DTOs|Application\\\\UseCases|Domain)(?:\\\\|$)/', $token[1]);
                }
            }
        }
        $repositorio = (new ReflectionClass(ExtraerHechosUseCase::class))->getConstructor()->getParameters()[0];
        self::assertSame(EvidenciaRepositoryInterface::class, $repositorio->getType()->getName());
    }
}
