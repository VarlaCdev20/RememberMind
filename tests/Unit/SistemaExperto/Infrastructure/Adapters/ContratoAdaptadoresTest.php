<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Infrastructure\Adapters;

use App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas\AdaptadorControlesCognitivos;
use App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas\AdaptadorIadlExperimental;
use App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas\AdaptadorInstrumentos;
use App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas\AdaptadorSeguimientosPedagogicos;
use App\SistemaExperto\Infrastructure\Adapters\FuentesOperativas\AdaptadorValoracionesFuncionales;
use App\SistemaExperto\Infrastructure\Adapters\MapeoFuentes\ExtractorVariableMapeada;
use App\SistemaExperto\Infrastructure\Adapters\MapeoFuentes\ResolutorFuenteExperta;
use DomainException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * TÍTULO:
 * Contrato de las siete clases de adaptadores.
 *
 * CAPA:
 * Infrastructure - Adapters.
 *
 * ¿PARA QUÉ SIRVE?
 * Recupera datos brutos autorizados sin interpretación clínica.
 *
 * ¿QUÉ INFORMACIÓN RECIBE?
 * Clases finales de Infrastructure y metadatos físicos de los modelos existentes.
 *
 * ¿QUÉ HACE?
 * Inspecciona tipos, readonly, strict_types y PHPDoc en español.
 *
 * ¿QUÉ DEVUELVE?
 * Aserciones sobre los siete componentes exigidos por esta etapa.
 *
 * ¿QUÉ NO DEBE HACER?
 * No sustituye las pruebas de integración ni habilita nuevos catálogos.
 */
final class ContratoAdaptadoresTest extends TestCase
{
    /** Comprueba clases y comentarios sin abrir conexiones; sin parámetros ni excepciones esperadas. */
    public function test_siete_clases_tipadas_documentadas_y_sin_modelos_duplicados(): void
    {
        $clases = [AdaptadorControlesCognitivos::class, AdaptadorSeguimientosPedagogicos::class,
            AdaptadorValoracionesFuncionales::class, AdaptadorInstrumentos::class, AdaptadorIadlExperimental::class,
            ResolutorFuenteExperta::class, ExtractorVariableMapeada::class];
        foreach ($clases as $clase) {
            $r = new ReflectionClass($clase);
            self::assertTrue($r->isFinal());
            self::assertTrue($r->isReadOnly());
            self::assertStringStartsWith('App\\SistemaExperto\\Infrastructure\\Adapters\\', $r->getNamespaceName());
            self::assertStringStartsWith("<?php\n\ndeclare(strict_types=1);", str_replace("\r\n", "\n", file_get_contents($r->getFileName())));
            $doc = $r->getDocComment();
            self::assertNotFalse($doc);
            foreach (['TÍTULO:', 'CAPA:', '¿PARA QUÉ SIRVE?', '¿QUÉ INFORMACIÓN RECIBE?',
                '¿QUÉ HACE?', '¿QUÉ DEVUELVE?', '¿QUÉ NO DEBE HACER?'] as $seccion) {
                self::assertStringContainsString($seccion, $doc);
            }
            foreach ($r->getMethods(ReflectionMethod::IS_PUBLIC) as $metodo) {
                self::assertNotFalse($metodo->getDocComment());
                foreach ($metodo->getParameters() as $parametro) {
                    self::assertNotNull($parametro->getType());
                    self::assertStringContainsString('$'.$parametro->getName(), $metodo->getDocComment());
                }
                if (! $metodo->isConstructor()) {
                    self::assertNotNull($metodo->getReturnType());
                }
            }
        }
        $raiz = dirname((new ReflectionClass(ExtractorVariableMapeada::class))->getFileName(), 2);
        self::assertCount(7, [...glob($raiz.'/FuentesOperativas/*.php'), ...glob($raiz.'/MapeoFuentes/*.php')]);
        foreach ([AdaptadorControlesCognitivos::class, AdaptadorSeguimientosPedagogicos::class,
            AdaptadorValoracionesFuncionales::class, AdaptadorInstrumentos::class] as $adaptador) {
            $modelo = new ($adaptador::MODELO);
            self::assertSame($modelo->getTable(), $adaptador::TABLA);
            self::assertSame($modelo->getKeyName(), $adaptador::PK);
        }
    }

    /** @throws DomainException No se admiten clases arbitrarias a partir de una clave configurable. */
    public function test_registro_rechaza_instancias_ajenas_a_los_cinco_adaptadores(): void
    {
        $this->expectException(DomainException::class);
        new ResolutorFuenteExperta('sqlite', ['SIM-MAL' => new \stdClass]);
    }

    /** @throws DomainException No se registra una columna no existente para un selector instrumental. */
    public function test_selector_rechaza_columnas_de_interpretacion_inventadas(): void
    {
        $this->expectException(DomainException::class);
        new AdaptadorInstrumentos(['SIM-V1' => ['SIM-SEL' => [
            'instrumento' => 'SIM-INST', 'version' => 'SIM-ED', 'pregunta' => 'SIM-PREG', 'campo' => 'diagnostico_dcl',
        ]]]);
    }
}
