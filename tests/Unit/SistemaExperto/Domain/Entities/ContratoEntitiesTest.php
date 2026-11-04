<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\Entities;

use MotorExperto\dominio\orion\Entities\Cognicion\ResultadoDominio;
use MotorExperto\dominio\orion\Entities\Cognicion\ResultadoSubcomponente;
use MotorExperto\dominio\orion\Entities\Cognicion\VectorCognitivo;
use MotorExperto\dominio\orion\Entities\Conocimiento\NodoSemantico;
use MotorExperto\dominio\orion\Entities\Conocimiento\RedSemantica;
use MotorExperto\dominio\orion\Entities\Conocimiento\ReglaExperta;
use MotorExperto\dominio\orion\Entities\Conocimiento\RelacionSemantica;
use MotorExperto\dominio\orion\Entities\Dcl\PerfilRequisitosDcl;
use MotorExperto\dominio\orion\Entities\Dcl\ResultadoClasificacionDcl;
use MotorExperto\dominio\orion\Entities\Funcionalidad\EvidenciaIadl;
use MotorExperto\dominio\orion\Entities\Funcionalidad\VectorFuncionalidad;
use MotorExperto\dominio\orion\Entities\Hechos\EvidenciaCognitiva;
use MotorExperto\dominio\orion\Entities\Hechos\HechoExperto;
use MotorExperto\dominio\orion\Entities\Hechos\MemoriaTrabajo;
use Error;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use PHPUnit\Framework\TestCase;

/** Verificar constructores y comportamiento local de la Fase B. */
final class ContratoEntitiesTest extends TestCase
{
    /**
     * Comprueba clases, namespaces, propiedades, métodos y constantes documentados.
     * @return void
     */
    public function test_catorce_clases_final_readonly_con_documentacion_completa(): void
    {
        $root = realpath(dirname(__DIR__, 5).'/motor-experto/dominio/orion/Entities');
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
            self::assertSame('MotorExperto\\dominio\\orion\\Entities\\'.str_replace('/', '\\', substr($relativa, 0, -4)), $r->getName());
            self::assertStringStartsWith("<?php\n\ndeclare(strict_types=1);", file_get_contents($archivo));
            $doc = $r->getDocComment();
            self::assertNotFalse($doc);

            self::assertIsString($doc);
            self::assertMatchesRegularExpression('/\p{L}{3}/u', $doc);
            self::assertLessThanOrEqual(240, mb_strlen($doc));
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
     * @return void
     */
    public function test_propiedad_original_no_puede_reescribirse(): void
    {
        $hecho = EscenarioEntidades::hecho();
        $this->expectException(Error::class);
        $hecho->valor = 'OTRO';
    }

    /**
     * Impide borrar hechos desde una colección pública readonly.
     * @return void
     */
    public function test_coleccion_de_memoria_no_puede_mutarse_desde_fuera(): void
    {
        $hecho = EscenarioEntidades::hecho();
        $memoria = EscenarioEntidades::memoria([], [$hecho]);
        $this->expectException(Error::class);
        unset($memoria->hechos[$hecho->id]);
    }

}
