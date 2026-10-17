<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Entities;

use App\SistemaExperto\Domain\Entities\Cognicion\ResultadoDominio;
use App\SistemaExperto\Domain\Entities\Cognicion\ResultadoSubcomponente;
use App\SistemaExperto\Domain\Entities\Cognicion\VectorCognitivo;
use App\SistemaExperto\Domain\Entities\Conocimiento\NodoSemantico;
use App\SistemaExperto\Domain\Entities\Conocimiento\RedSemantica;
use App\SistemaExperto\Domain\Entities\Conocimiento\ReglaExperta;
use App\SistemaExperto\Domain\Entities\Conocimiento\RelacionSemantica;
use App\SistemaExperto\Domain\Entities\Dcl\PerfilRequisitosDcl;
use App\SistemaExperto\Domain\Entities\Dcl\ResultadoClasificacionDcl;
use App\SistemaExperto\Domain\Entities\Funcionalidad\EvidenciaIadl;
use App\SistemaExperto\Domain\Entities\Funcionalidad\VectorFuncionalidad;
use App\SistemaExperto\Domain\Entities\Hechos\EvidenciaCognitiva;
use App\SistemaExperto\Domain\Entities\Hechos\HechoExperto;
use App\SistemaExperto\Domain\Entities\Hechos\MemoriaTrabajo;
use Error;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: Manifiesto, documentación e inmutabilidad de las catorce entidades.
 *
 * CAPA: Domain (verificación unitaria).
 *
 * SUBSISTEMA: Entidades y sus invariantes.
 *
 * RESPONSABILIDAD:
 * Verificar constructores y comportamiento local de la Fase B.
 *
 * ENTRADAS:
 * Escenarios artificiales sin datos de residentes ni instrumentos reales.
 *
 * SALIDAS:
 * Aserciones de conservación, rechazo e inmutabilidad.
 *
 * RESTRICCIONES:
 * No inicia Laravel, no accede a BDD ni acredita validación clínica.
 *
 * TRAZABILIDAD:
 * Reglamento maestro, Fase B, documentación obligatoria e invariantes R1–R10.
 */
final class ContratoEntitiesTest extends TestCase
{
    /**
     * Comprueba clases, namespaces, propiedades, métodos y constantes documentados.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_catorce_clases_final_readonly_con_documentacion_completa(): void
    {
        $root = realpath(dirname(__DIR__, 5).'/app/SistemaExperto/Domain/Entities');
        $clases = [ReglaExperta::class, NodoSemantico::class, RelacionSemantica::class, RedSemantica::class,
            HechoExperto::class, EvidenciaCognitiva::class, MemoriaTrabajo::class,
            ResultadoSubcomponente::class, ResultadoDominio::class, VectorCognitivo::class,
            EvidenciaIadl::class, VectorFuncionalidad::class, PerfilRequisitosDcl::class, ResultadoClasificacionDcl::class];
        $esperados = [];
        foreach ($clases as $clase) {
            $r = new ReflectionClass($clase);
            self::assertTrue($r->isFinal());
            self::assertTrue($r->isReadOnly());
            $archivo = $r->getFileName();
            $esperados[] = realpath($archivo);
            $relativa = str_replace('\\', '/', substr($archivo, strlen($root) + 1));
            self::assertSame('App\\SistemaExperto\\Domain\\Entities\\'.str_replace('/', '\\', substr($relativa, 0, -4)), $r->getName());
            self::assertStringStartsWith("<?php\n\ndeclare(strict_types=1);", file_get_contents($archivo));
            $doc = $r->getDocComment();
            self::assertNotFalse($doc);
            self::assertStringContainsString('COMPONENTE DEL SISTEMA EXPERTO:', $doc, $clase);
            foreach (['TÍTULO:', 'CAPA:', 'SUBSISTEMA:', 'RESPONSABILIDAD:', 'ENTRADAS:', 'SALIDAS:', 'RESTRICCIONES:', 'TRAZABILIDAD:'] as $titulo) {
                self::assertStringContainsString($titulo, $doc, $clase);
            }
            foreach ($r->getProperties() as $propiedad) {
                self::assertTrue($propiedad->isReadOnly());
                self::assertNotNull($propiedad->getType());
                self::assertNotFalse($propiedad->getDocComment(), $clase.'::'.$propiedad->getName());
            }
            foreach ([...$r->getMethods(), ...$r->getReflectionConstants()] as $miembro) {
                self::assertNotFalse($miembro->getDocComment(), $clase.'::'.$miembro->getName());
            }
        }
        $actuales = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $archivo) {
            if ($archivo->isFile() && $archivo->getExtension() === 'php') {
                $actuales[] = realpath($archivo->getPathname());
            }
        }
        sort($actuales);
        sort($esperados);
        self::assertCount(14, $actuales);
        self::assertSame($esperados, $actuales);
    }

    /**
     * Impide reemplazar el valor de un hecho construido.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_propiedad_original_no_puede_reescribirse(): void
    {
        $hecho = EscenarioEntidades::hecho();
        $this->expectException(Error::class);
        $hecho->valor = 'OTRO';
    }

    /**
     * Impide borrar hechos desde una colección pública readonly.
     *
     * @return void
     *
     * No recibe parámetros; las excepciones esperadas se declaran en la prueba.
     */
    public function test_coleccion_de_memoria_no_puede_mutarse_desde_fuera(): void
    {
        $hecho = EscenarioEntidades::hecho();
        $memoria = EscenarioEntidades::memoria([], [$hecho]);
        $this->expectException(Error::class);
        unset($memoria->hechos[$hecho->id]);
    }

}
