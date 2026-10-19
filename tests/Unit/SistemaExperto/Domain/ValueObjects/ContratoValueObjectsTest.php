<?php

declare(strict_types=1);

namespace Tests\Unit\SistemaExperto\Domain\ValueObjects;

use MotorExperto\dominio\orion\ValueObjects\Cognicion\DominioCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EstadoDominioEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\EvaluabilidadEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\HallazgoInstrumentalEnum;
use MotorExperto\dominio\orion\ValueObjects\Cognicion\SubcomponenteCognitivoEnum;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\EstadoReglaEnum;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\OperadorCondicionEnum;
use MotorExperto\dominio\orion\ValueObjects\Conocimiento\TipoRelacionSemanticaEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\CriterioDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\EstadoCriterioDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\MotivoAbstencionEnum;
use MotorExperto\dominio\orion\ValueObjects\Dcl\SubtipoDclEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoDiferencialEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\EstadoIadlEnum;
use MotorExperto\dominio\orion\ValueObjects\Funcionalidad\OportunidadIadlEnum;
use BackedEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionEnum;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use PHPUnit\Framework\TestCase;

/**
 * TÍTULO: Contrato físico, documentación y carga pura de los 15 ValueObjects.
 *
 * CAPA: Domain (verificación unitaria).
 *
 * SUBSISTEMA: Contratos de ValueObjects.
 *
 * RESPONSABILIDAD:
 * Verificar los vocabularios y límites de la Fase A sin implementar inferencias.
 *
 * ENTRADAS:
 * Códigos documentales y literales técnicos artificiales, sin datos de residentes.
 *
 * SALIDAS:
 * Aserciones reproducibles sobre identidad, rechazo o documentación del contrato.
 *
 * RESTRICCIONES:
 * No inicia Laravel, no consulta una BDD y no interpreta instrumentos.
 * El resultado de estas pruebas no acredita validación clínica.
 *
 * TRAZABILIDAD:
 * Reglamento maestro, Fase A, documentación obligatoria y prueba 18.
 */
final class ContratoValueObjectsTest extends TestCase
{
    /**
     * Comprueba que cada literal declarado reconstruye la misma identidad.
     *
     * También exige que un código ajeno permanezca sin representación; no
     * se permite un reemplazo silencioso por normalidad o por nivel cero.
     *
     * @param class-string<BackedEnum> $enum Tipo del vocabulario bajo prueba.
     * @return void
     *
     * No se esperan excepciones con los casos válidos; un fallo se informa.
     */
    #[DataProvider('catalogos')]
    public function test_construccion_conserva_identidad_y_rechaza_valores_ajenos(string $enum): void
    {
        foreach ($enum::cases() as $case) {
            self::assertSame($case, $enum::from($case->value));
        }

        $invalid = (new ReflectionEnum($enum))->getBackingType()->getName() === 'int'
            ? -1 : 'FUERA_DEL_CONTRATO';
        self::assertNull($enum::tryFrom($invalid));
    }

    /**
     * Enumera exactamente los quince archivos autorizados de producción.
     *
     * @return iterable<string, array{class-string<BackedEnum>}> Catálogos tipados.
     *
     * No recibe parámetros ni lanza excepciones.
     */
    public static function catalogos(): iterable
    {
        foreach ([
            TipoRelacionSemanticaEnum::class,
            OperadorCondicionEnum::class,
            EstadoReglaEnum::class,
            DominioCognitivoEnum::class,
            SubcomponenteCognitivoEnum::class,
            EvaluabilidadEnum::class,
            HallazgoInstrumentalEnum::class,
            EstadoDominioEnum::class,
            EstadoIadlEnum::class,
            OportunidadIadlEnum::class,
            EstadoDiferencialEnum::class,
            CriterioDclEnum::class,
            EstadoCriterioDclEnum::class,
            SubtipoDclEnum::class,
            MotivoAbstencionEnum::class,
        ] as $enum) {
            yield $enum => [$enum];
        }
    }

    /**
     * Verifica el manifiesto, namespaces, strict_types y los PHPDoc requeridos.
     *
     * Revisa también cada caso y cada método propio; los métodos nativos del
     * lenguaje quedan fuera porque no se declaran en los archivos del proyecto.
     *
     * @return void
     *
     * No recibe parámetros ni oculta errores de lectura o reflexión.
     */
    public function test_estructura_y_documentacion_completas(): void
    {
        $root = dirname(__DIR__, 5).'/motor-experto/src/motor_experto/dominio/orion/ValueObjects';
        $actual = [];
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $actual[] = realpath($file->getPathname());
            }
        }

        $expected = [];
        foreach (self::catalogos() as [$enum]) {
            $reflection = new ReflectionEnum($enum);
            $path = $reflection->getFileName();
            $expected[] = realpath($path);
            $relative = str_replace('\\', '/', substr($path, strlen(realpath($root)) + 1));
            self::assertSame(
                'MotorExperto\\dominio\\orion\\ValueObjects\\'.str_replace('/', '\\', substr($relative, 0, -4)),
                $reflection->getName(),
            );
            self::assertStringStartsWith("<?php\n\ndeclare(strict_types=1);", file_get_contents($path));
            $doc = $reflection->getDocComment();
            self::assertNotFalse($doc, $enum);
            self::assertStringContainsString('COMPONENTE DEL SISTEMA EXPERTO:', $doc, $enum);
            foreach (['TÍTULO:', 'CAPA:', 'SUBSISTEMA:', 'RESPONSABILIDAD:', 'ENTRADAS:', 'SALIDAS:', 'RESTRICCIONES:', 'TRAZABILIDAD:'] as $label) {
                self::assertStringContainsString($label, $doc, $enum);
            }
            foreach ($reflection->getCases() as $case) {
                self::assertNotFalse($case->getDocComment(), $enum.'::'.$case->getName());
            }
            foreach ($reflection->getMethods() as $method) {
                if (! $method->isInternal()) {
                    self::assertNotFalse($method->getDocComment(), $enum.'::'.$method->getName());
                }
            }
        }
        sort($expected);
        sort($actual);
        self::assertCount(15, $actual);
        self::assertSame($expected, $actual);
    }

    /**
     * Busca dependencias Laravel en tokens ejecutables de todo el Domain.
     *
     * Los comentarios no se consideran imports. La carga previa de los quince
     * enums funciona con Composer y PHP estándar, sin iniciar la aplicación.
     *
     * @return void
     *
     * No recibe parámetros; errores de lectura no se silencian.
     */
    public function test_domain_no_depende_de_laravel(): void
    {
        $root = dirname(__DIR__, 5).'/motor-experto/src/motor_experto/dominio/orion';
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            foreach (token_get_all(file_get_contents($file->getPathname())) as $token) {
                if (is_array($token) && in_array($token[0], [T_NAME_QUALIFIED, T_NAME_FULLY_QUALIFIED], true)) {
                    self::assertDoesNotMatchRegularExpression(
                        '/^\\\\?(Illuminate|Laravel|Livewire)\\\\/',
                        $token[1],
                        $file->getPathname(),
                    );
                }
            }
        }
    }

}
